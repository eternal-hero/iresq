<?php

namespace App\WooCommerce;

use App\Filemaker\Invoice;
use App\Filemaker\LineItem;
use App\Filemaker\Payment;
use App\Shipping\EasyPostEnhancements;
use Exception;
use INTERMediator\FileMakerServer\RESTAPI\FMDataAPI as FMDataAPI;

class OrderProcessing
{
    protected $disableFilemakerChanges = false;

    public function __construct()
    {
        $this->disableFilemakerChanges = isset($_ENV['DISABLE_FILEMAKER_CHANGES']) && 'true' == $_ENV['DISABLE_FILEMAKER_CHANGES'];
        add_action('woocommerce_order_status_processing', [$this, 'createFilemakerOrder']);
        add_action('woocommerce_order_actions', [$this, 'customActionButtons']);
        add_action('woocommerce_order_action_submit_order_filemaker', [$this, 'custom_actionButton_submitOrderToFilemaker']);
        add_action('woocommerce_order_action_get_order_json', [$this, 'custom_actionButton_viewOrderJson']);
        add_action('woocommerce_order_status_changed', [$this, 'statusChangeProcessPayment'], 99, 4);
        add_action('init', [$this, 'addUpdatingOrderStatus']);
        add_filter('wc_order_statuses', [$this, 'addUpdatingToAdminOrderStatusList']);
        add_action('woocommerce_payment_complete', [$this, 'paymentProcessedFilemakerRecord']);
        add_filter('woocommerce_endpoint_order-received_title', [$this, 'additionalPaymentOrderReceivedTitle'], 10, 2);
        add_action('woocommerce_email_order_meta', [$this, 'addLabelsToOrderEmails'], 10, 3);
    }

    public function additionalPaymentOrderReceivedTitle($oldTitle)
    {
        global $wp;
        $order_id = isset($wp->query_vars['order-received']) ? absint($wp->query_vars['order-received']) : 0;
        $order = wc_get_order($order_id);

        $invoiceArray = get_post_meta($order->get_id(), 'filemakerInvoicesAmountOwe', true);
        if ('' == $invoiceArray) {
            return $oldTitle;
        }

        return 'Payment Received';
    }

    /**
     * Create the filemaker record(s) by looping through the order items.
     *
     * @param int $order_id WooCommerce Order ID
     */
    public function createFilemakerOrder($order_id, $dumpOrder = false)
    {
        $this->generateShippingLabels($order_id);

        if ($this->disableFilemakerChanges) {
            return false;
        }

        if ('1' == get_post_meta($order_id, 'created_in_filemaker', true) && !$dumpOrder) {
            return false;
        }

        // Get all product data needed for each device in the order
        $order = wc_get_order($order_id); // full woocommerce order object
        if (!$order) {
            return false;
        }

        $invoice = new Invoice();
        $lineItem = new LineItem();
        $payment = new Payment();

        /**
         * We want to create an invoice for each individual item, so we
         * need to loop over the entire order and create a new invoice for
         * each item that all share the same order number.
         */
        $devices = [];
        foreach ($order->get_items() as $item_id => $item) {
            $cart_item_dn = isset($item['device_number']) ? $item['device_number'] : '';
            $devices[$cart_item_dn][$item_id] = $item;
        }

        $deviceCount = 1;
        foreach ($devices as $deviceNumber => $items) {
            $invoiceRecord = null;
            // Record the line items and the initial invoice for this device
            foreach ($items as $item_id => $item) {
                $invoiceRecord = $invoice->createInvoiceRecord($order, $item, $item_id, $deviceNumber, $deviceCount, $dumpOrder);
                if (!$invoiceRecord) {
                    $order->add_order_note('Failed to add invoice #' . $deviceNumber . ' to FileMaker');

                    throw new Exception('Failed to process the invoice in FileMaker');
                }
                $order->add_order_note('Added invoice for  #' . $deviceNumber . ' to FileMaker');

                break;
            }

            foreach ($items as $item_id => $item) {
                $lineItem->createRecord($order, $item, $item_id, $invoiceRecord);
                $order->add_order_note('Added item #' . $item_id . ' to FileMaker');
            }

            // Only add shipping to the first device Invoice record
            if (1 == $deviceCount) {
                foreach ($order->get_items('shipping') as $item_id => $item) {
                    if (str_contains(strtolower($item->get_name()), 'pickup')) {
                        continue; // Do not submit the free pickup option to FileMaker
                    }
                    $lineItem->createShippingRecord($order, $item, $item_id, $invoiceRecord);
                    $order->add_order_note('Added shipping to FileMaker invoice');
                }
            }
            ++$deviceCount;
        }

        update_post_meta($order->get_id(), 'created_in_filemaker', true);
    }

    /**
     * Add a custom action to order actions select box on edit order page.
     *
     * @param array $actions order actions array to display
     *
     * @return array updated actions
     */
    public function customActionButtons($actions)
    {
        global $theorder;

        if ('1' !== get_post_meta($theorder->get_id(), 'created_in_filemaker', true)) {
            $actions['submit_order_filemaker'] = 'Create Order in FileMaker';
        }
        $actions['update_order_filemaker'] = 'Update Order from FileMaker';
        $actions['get_order_json'] = 'View Order JSON';

        return $actions;
    }

    /**
     * Create order in Filemaker from the custom action order.
     *
     * @param \WC_Order $order
     */
    public function custom_actionButton_submitOrderToFilemaker($order)
    {
        // Verify order doesn't already exist in FileMaker
        $invoice = new Invoice();

        $result = $invoice->fetchInvoiceRecordByOrderNumber($order);
        if (is_null($result)) {
            try {
                $this->createFilemakerOrder($order->get_id());
                if (is_admin()) {
                    add_filter('wp_redirect', function ($location) {
                        return add_query_arg('iresq-filemaker-create', 'success', $location);
                    });
                }
            } catch (Exception $error) {
                add_filter('wp_redirect', function ($location) use ($error) {
                    return add_query_arg([
                        'iresq-filemaker-create' => 'create-error',
                        'iresq-filemaker-create-error-message' => urlencode($error->getMessage()),
                    ], $location);
                });
            }
        } else {
            if (is_admin()) {
                add_filter('wp_redirect', function ($location) {
                    return add_query_arg('iresq-filemaker-create', 'error', $location);
                });
            }
            update_post_meta($order->get_id(), 'created_in_filemaker', true);
        }
    }

    public function custom_actionButton_viewOrderJson($order)
    {
        $this->createFilemakerOrder($order->get_id(), true);
    }

    /**
     * Process the invoice payment if an order was flipped to Processing from On Hold while pending payment.
     *
     * @param mixed $order_id
     * @param mixed $old_status
     * @param mixed $new_status
     * @param mixed $order
     */
    public function statusChangeProcessPayment($order_id, $old_status, $new_status, $order)
    {
        if ('on-hold' == $old_status && 'processing' == $new_status) {
            if ($this->disableFilemakerChanges) {
                return;
            }

            if ('1' != get_post_meta($order_id, 'created_in_filemaker', true)) {
                $this->createFilemakerOrder($order_id);
            }

            // $this->processPayment($order);

            $invoice = new Invoice();
            $invoiceArray = get_post_meta($order_id, 'filemakerInvoicesAmountOwe', true);
            $invoiceTotal = 0;
            if ('' != $invoiceArray) {
                foreach ($invoiceArray as $amount) {
                    $invoiceTotal += floatval($amount);
                }
            }

            $invoiceRecord = $invoice->fetchInvoiceRecordByOrderNumber($order);

            // Check if the payment method is Cash on Delivery (COD)
            if ($order->get_payment_method() === 'cod') {
                // Trigger the Admin Repayment Email
                do_action('iresq_trigger_admin_repayment_email', $order_id, $invoiceRecord, 'net30', $order);
            }
        }
    }

    /**
     * If order has processed to FileMaker, send a payment record.
     *
     * @param mixed $order_id
     */
    public function paymentProcessedFilemakerRecord($order_id)
    {
        $checkTransient = get_transient("order_{$order_id}_paymentcompletedone");
        if (!$checkTransient) {
            set_transient("order_{$order_id}_paymentcompletedone", '1', 30);
        } else {
            return;
        }

        if ($this->disableFilemakerChanges) {
            return false;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        if ('1' != get_post_meta($order_id, 'created_in_filemaker', true)) {
            $this->createFilemakerOrder($order_id);
        }

        $invoiceArray = get_post_meta($order_id, 'filemakerInvoicesAmountOwe', true);
        $invoiceTotal = 0;
        if ('' != $invoiceArray) {
            foreach ($invoiceArray as $amount) {
                $invoiceTotal += floatval($amount);
            }
        }

        if ($invoiceArray) {
            $this->processAdditionalPayment($order, $invoiceArray);
        } else {
            $this->processPayment($order);
        }
    }

    public function getOrderTrackingNumber($orderId)
    {
        $trackingNumber = '';
        $labels = get_post_meta($orderId, 'wf_easypost_labels', true);
        if (!empty($labels)) {
            $trackingNumber = $labels[0]['tracking_number'];
        }

        return $trackingNumber;
    }

    public function getOrderReturnTrackingNumber($orderId)
    {
        $trackingNumber = '';
        $returnLabels = get_post_meta($orderId, 'wf_easypost_return_labels', true);
        if (!empty($returnLabels)) {
            $trackingNumber = $returnLabels[0]['tracking_number'];
        }

        return $trackingNumber;
    }

    public function addUpdatingOrderStatus()
    {
        register_post_status('wc-updating', [
            'label' => 'Updating Order',
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Updating order (%s)', 'Updating order (%s)'),
        ]);
    }

    public function addUpdatingToAdminOrderStatusList($order_statuses)
    {
        $new_order_statuses = [];
        // add new order status after processing
        foreach ($order_statuses as $key => $status) {
            $new_order_statuses[$key] = $status;

            if ('wc-processing' === $key) {
                $new_order_statuses['wc-updating'] = 'Updating Order';
            }
        }

        return $new_order_statuses;
    }

    public function generateShippingLabels($order_id)
    {
        if ('1' == get_post_meta($order_id, 'generatedLabels', true)) {
            return;
        }
        if (!class_exists('WF_Shipping_Easypost_Admin')) {
            return;
        }

        $easyPost = new \WF_Shipping_Easypost_Admin();
        $easyPostEnhancements = new EasyPostEnhancements();
        $order = $easyPost->wf_load_order($order_id);
        if (!$order) {
            return;
        }
        update_post_meta($order->get_id(), 'generatedLabels', true);

        // Grab the shipping method
        $selectedShipping = '';
        foreach ($order->get_items('shipping') as $item) {
            $selectedShipping = $item->get_meta('originalLabel');
            if (str_contains(strtolower($selectedShipping), 'pickup')) {
                return; // Do not print labels if free
            }

            break;
        }
        $selectedType = $this->getSelectedShippingType($selectedShipping);

        try {
            // Generate the package from Easy Post
            $package_data_array = $easyPost->wf_get_package_data($order);
            update_post_meta($order_id, '_wf_easypost_stored_packages', $package_data_array);
            $singlePackage = $package_data_array[0];
            $signatureWaived = $order->get_meta('signatureWaived');

            // Fetch the available service rates from USPS/FedEx TODO: See if needed
            $servicesResult = $easyPostEnhancements->elex_easypost_update_shipping_services($singlePackage['WeightOz'], $singlePackage['Length'], $singlePackage['Width'], $singlePackage['Height']);

            // Create the first label
            $easyPostEnhancements->wf_easypost_shipment_confirm($order_id, '', 'create', json_encode([$selectedType]), $signatureWaived);

            // Create the return label
            if ($this->getSelectedReturnLabel($selectedShipping)) {
                $easyPostEnhancements->wf_easypost_shipment_confirm($order_id, '', 'return', json_encode([$selectedType]), $signatureWaived);
            }

            // TODO: Print additional label
        } catch (Exception $e) {
            // Almost always a production API related issue
            error_log($e->getMessage());
        }
    }

    public function addLabelsToOrderEmails($order_obj, $sent_to_admin, $plain_text)
    {
        $returnLabels = get_post_meta($order_obj->get_id(), 'wf_easypost_return_labels', true);

        // we won't display anything if labels do not exist
        if ($returnLabels == "") {
            return;
        }
        $label = $returnLabels[0];
        $url = esc_attr($label['url']);
        echo '<h2 style="margin-bottom: 5px;">Prepaid Shipping Label</h2>
		<p style="margin-bottom: 40px;"><a href="' . $url . '">Click this link to download your prepaid shipping label</a></p>';
    }

    /**
     * @param true|\WC_Order|\WC_Order_Refund $order
     */
    private function processPayment($order)
    {
        $invoice = new Invoice();
        $payment = new Payment();
        $devices = [];
        $deviceCount = 1;
        $orderNumber = $order->get_order_number();

        foreach ($order->get_items() as $item_id => $item) {
            $cart_item_sn = isset($item['device_number']) ? $item['device_number'] : '';
            $devices[$cart_item_sn][$item_id] = $item;
        }

        foreach ($devices as $serialNumber => $items) {
            $item_number = $orderNumber . sprintf('%03d', $deviceCount);
            $invoiceRecord = $invoice->fetchInvoiceRecordByItemNo($item_number);
            if (is_null($invoiceRecord)) {
                return;
            }

            $paymentTotal = 0;
            foreach ($items as $item_id => $item) {
                $itemTotal = wc_get_order_item_meta($item_id, '_line_total', true);
                $itemTax = wc_get_order_item_meta($item_id, '_line_tax', true);
                $paymentTotal = $itemTotal + floatval($paymentTotal) + floatval($itemTax);
            }

            if (1 == $deviceCount) {
                $shippingTotal = $order->get_shipping_total();
                $paymentTotal = floatval($shippingTotal) + $paymentTotal;
            }
            $payment->createPaymentRecord($order, $invoiceRecord, $paymentTotal);
            update_post_meta($order->get_id(), 'first_payment_unrecorded', false);

            ++$deviceCount;
        }
    }

    /**
     * @param true|\WC_Order|\WC_Order_Refund $order
     * @param array                           $invoiceArray
     *
     * Pay off the additional amounts based on what is left for each invoice for the order
     */
    private function processAdditionalPayment($order, $invoiceArray)
    {
        $invoice = new Invoice();
        $payment = new Payment();

        foreach ($invoiceArray as $invoiceNumber => $amount) {
            $invoiceRecord = $invoice->fetchInvoiceRecordByItemNo($invoiceNumber);
            if (is_null($invoiceRecord)) {
                return;
            }
            $payment->createPaymentRecord($order, $invoiceRecord, $amount);

            // Send notification email to admins
            do_action('iresq_trigger_admin_repayment_email', $order->get_id(), $invoiceRecord, $amount, $order);
        }
    }

    private function getSelectedShippingType($selectedShipping)
    {
        if (str_contains($selectedShipping, 'USPS')) {
            return 'Priority';
        }

        if (str_contains($selectedShipping, 'Ground')) {
            return 'FEDEX_GROUND';
        }

        if (str_contains($selectedShipping, 'Overnight')) {
            return 'STANDARD_OVERNIGHT';
        }

        return 'FEDEX_GROUND';
    }

    private function getSelectedReturnLabel($selectedShipping)
    {
        return str_contains($selectedShipping, 'prepaid');
    }
}

new OrderProcessing();
