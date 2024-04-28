<?php

namespace App\WooCommerce;

use App\Filemaker\Invoice;
use Exception;

class BulkOrdersProcessing
{
    public function __construct()
    {
        add_action('wp_ajax_process_new_bulk_import', [$this, 'processNewBulkImport']);
        add_action('init', [$this, 'newBulkImportCPT'], 0);
        add_action('iresq_schedule_filemaker_new_bulk_import', [$this,'scheduleFileMakerImport']);
        if (! wp_next_scheduled('iresq_schedule_filemaker_new_bulk_import')) {
            wp_schedule_event(time(), '5min', 'iresq_schedule_filemaker_new_bulk_import');
        }
    }

    public function processNewBulkImport()
    {
        $rows = isset($_POST['rows']) ? $_POST['rows'] : [];
        $companyName = isset($_POST['companyName']) ? $_POST['companyName'] : '';
        $streetone = isset($_POST['streetone']) ? $_POST['streetone'] : '';
        $streettwo = isset($_POST['streettwo']) ? $_POST['streettwo'] : '';
        $city = isset($_POST['city']) ? $_POST['city'] : '';
        $state = isset($_POST['state']) ? $_POST['state'] : '';
        $zip = isset($_POST['zip']) ? $_POST['zip'] : '';
        foreach ($rows as $row) {
            $serial = $row[0];
            $po = $row[1];
            $claimno = $row[2];
            $notes = $row[3];
            if ($serial == '' && $po == '' && $notes == '') {
                continue;
            }
            $postId = wp_insert_post([
                'post_type' => 'new_bulk_import',
                'post_author'       =>  get_current_user_id(),
                'post_status'       =>  'publish'
            ]);
            add_post_meta($postId, 'userCreated', get_current_user_id());
            add_post_meta($postId, 'serial', $serial);
            add_post_meta($postId, 'po', $po);
            add_post_meta($postId, 'claimno', $claimno);
            add_post_meta($postId, 'notes', $notes);
            add_post_meta($postId, 'companyName', $companyName);
            add_post_meta($postId, 'streetone', $streetone);
            add_post_meta($postId, 'streettwo', $streettwo);
            add_post_meta($postId, 'city', $city);
            add_post_meta($postId, 'state', $state);
            add_post_meta($postId, 'zip', $zip);
        }
        wp_die();
    }

    public function scheduleFileMakerImport()
    {
        $itemsToImport = get_posts([
            'numberposts' => 30,
            'post_type' => 'new_bulk_import'
        ]);

        foreach ($itemsToImport as $item) {
            try {
                $invoice = new Invoice();
                $invoice->createBulkOrderInvoiceRecord($item->ID);
            } catch (Exception $ex) {
                // Safe to ignore
            }
            wp_delete_post($item->ID);
        }
    }

    // Register Custom Post Type
    public function newBulkImportCPT()
    {
        $labels = array(
            'name'                  => _x('New Bulk Imports', 'Post Type General Name', 'text_domain'),
            'singular_name'         => _x('New Bulk Import', 'Post Type Singular Name', 'text_domain'),
            'menu_name'             => __('New Bulk Import', 'text_domain'),
            'name_admin_bar'        => __('New Bulk Import', 'text_domain'),
            'archives'              => __('New Bulk Import Archives', 'text_domain'),
            'attributes'            => __('New Bulk Import Attributes', 'text_domain'),
            'parent_item_colon'     => __('Parent New Bulk Import:', 'text_domain'),
            'all_items'             => __('All New Bulk Import', 'text_domain'),
            'add_new_item'          => __('Add New New Bulk Import', 'text_domain'),
            'add_new'               => __('Add New', 'text_domain'),
            'new_item'              => __('New New Bulk Import', 'text_domain'),
            'edit_item'             => __('Edit New Bulk Import', 'text_domain'),
            'update_item'           => __('Update New Bulk Import', 'text_domain'),
            'view_item'             => __('View New Bulk Import', 'text_domain'),
            'view_items'            => __('View New Bulk Import', 'text_domain'),
            'search_items'          => __('Search New Bulk Import', 'text_domain'),
            'not_found'             => __('Not found', 'text_domain'),
            'not_found_in_trash'    => __('Not found in Trash', 'text_domain'),
            'featured_image'        => __('Featured Image', 'text_domain'),
            'set_featured_image'    => __('Set featured image', 'text_domain'),
            'remove_featured_image' => __('Remove featured image', 'text_domain'),
            'use_featured_image'    => __('Use as featured image', 'text_domain'),
            'insert_into_item'      => __('Insert into New Bulk Import', 'text_domain'),
            'uploaded_to_this_item' => __('Uploaded to this New Bulk Import', 'text_domain'),
            'items_list'            => __('New Bulk Import list', 'text_domain'),
            'items_list_navigation' => __('New Bulk Imports list navigation', 'text_domain'),
            'filter_items_list'     => __('Filter New Bulk Import list', 'text_domain'),
        );
        $args = array(
            'label'                 => __('New Bulk Import', 'text_domain'),
            'description'           => __('New bulk import records', 'text_domain'),
            'labels'                => $labels,
            'supports'              => false,
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => false,
            'show_in_menu'          => false,
            'menu_position'         => 5,
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => false,
            'has_archive'           => false,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'rewrite'               => false,
            'capability_type'       => 'page',
            'show_in_rest'          => false,
        );
        register_post_type('new_bulk_import', $args);
    }
}

new BulkOrdersProcessing();
