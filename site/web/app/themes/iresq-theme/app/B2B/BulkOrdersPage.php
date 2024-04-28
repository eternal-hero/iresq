<?php

namespace App\B2B;

use App\Filemaker\Invoice;
use Exception;

use function App\template;

class UserProfilePage
{
    private static $invoiceClass;
    private static $endpoint = 'bulk-orders';

    public function __construct()
    {
        self::$invoiceClass = new Invoice();
        add_action('init', [$this, 'newEndpoints']);
        add_filter('woocommerce_get_query_vars', [$this, 'newQueries'], 0);
        add_filter('woocommerce_account_menu_items', [$this, 'addMenuItems']);
        add_filter('the_title', [$this, 'bulkOrderTitle']);
        add_action('woocommerce_account_'.self::$endpoint.'_endpoint', [$this, 'bulkOrdersContent']);

        add_action('wp_ajax_getmybulkinprogress', [$this, 'getMyBulkInProgress']);
        add_action('wp_ajax_getmybulkprocessed', [$this, 'getMyBulkProcessed']);
    }

    public function newEndpoints()
    {
        add_rewrite_endpoint(self::$endpoint, EP_PAGES);
    }

    public function newQueries($vars)
    {
        $vars[self::$endpoint] = self::$endpoint;
        return $vars;
    }

    public function bulkOrdersContent()
    {
        $filemakerAccountId = get_field('filemaker_account_id', "user_".get_current_user_id());
        if (!$filemakerAccountId) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            get_template_part(404);
            exit();
        } else {
            $lastInvoices = self::$invoiceClass->fetchLastInvoiceByAcctId($filemakerAccountId);
            $billInfo = [];
            $firstInvoice = [];
            try {
                if (count($lastInvoices) > 0) {
                    $firstInvoice = $lastInvoices[0];
                    $billInfo = $firstInvoice['portalData']['BILLADDRESS'][0];
                }
            } catch (Exception $ex) {
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
                get_template_part(404);
                exit();
            }

            wc_get_template('myaccount/bulk-orders.php', [
                'billInfo' => $billInfo,
                'firstInvoice' => $firstInvoice,
                'repairsInProcess' => [],
                'processedRepairs' => [],
            ]);
        }
    }

    public function bulkOrderTitle($title)
    {
        global $wp_query;

        $is_endpoint = isset($wp_query->query_vars[self::$endpoint]);
        if ($is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
            // New page title.
            $title = __('My Bulk Orders', 'woocommerce');

            remove_filter('the_title', 'bulkOrderTitle');
        }

        return $title;
    }

    public function addMenuItems($menu_links)
    {
        $filemakerAccountId = get_field('filemaker_account_id', "user_".get_current_user_id());
        if ($filemakerAccountId) {
            $new = [self::$endpoint => 'My Bulk Orders'];
            $menu_links = array_slice($menu_links, 0, 1, true) + $new + array_slice($menu_links, 1, null, true);
        }
        return $menu_links;
    }

    public function getMyBulkInProgress()
    {
        $userId = get_current_user_id();
        $filemakerAccountId = get_field('filemaker_account_id', "user_".$userId);
        $limit = intval($_REQUEST["length"]);
        $offset = intval($_REQUEST["start"]);
        $dateRange = "";
        if (!empty($_REQUEST["date_filter"])) {
            $startDate = date('m/d/Y', strtotime('-30 days'));
            $endDate = date('m/d/Y');
            $dateRange = $startDate."...".$endDate;
        }

        $filterOptions = [
            'PO No' => $_REQUEST["po_filter"],
            'Serial No' => $_REQUEST["serial_filter"],
            'Invoice Date' => $dateRange,
            'Major Status' => $_REQUEST["status_filter"],
        ];
        $invoices = self::$invoiceClass->fetchInvoicesByAcctIdAreInProgress($filemakerAccountId, $limit, $offset, $filterOptions);

        echo json_encode(array('data' => $invoices));
        wp_die();
    }

    public function getMyBulkProcessed()
    {
        $userId = get_current_user_id();
        $filemakerAccountId = get_field('filemaker_account_id', "user_".$userId);
        $limit = intval($_REQUEST["length"]);
        $offset = intval($_REQUEST["start"]);
        $dateRange = "";
        if (!empty($_REQUEST["date_filter"])) {
            $startDate = date('m/d/Y', strtotime('-30 days'));
            $endDate = date('m/d/Y');
            $dateRange = $startDate."...".$endDate;
        }

        $filterOptions = [
            'PO No' => $_REQUEST["po_filter"],
            'Serial No' => $_REQUEST["serial_filter"],
            'Invoice Date' => $dateRange,
            'Major Status' => "POSTED",
        ];
        $invoices = self::$invoiceClass->fetchInvoicesByAcctIdAreProcessed($filemakerAccountId, $limit, $offset, $filterOptions);

        echo json_encode(array('data' => $invoices));
        wp_die();
    }
}

new UserProfilePage();
