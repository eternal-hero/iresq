<?php

namespace RestAPI;

use App\Filemaker\Invoice;

class OrderProcessing
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'updateOrderRoute']);
    }

    public function updateOrderRoute()
    {
        // Example: https://iresq.local/wp-json/iresq/v1/awaiting-approval/13121
        register_rest_route(
            'iresq/v1',
            'awaiting-approval/(?P<id>\d+)',
            [
                'methods' => 'POST',
                'callback' => [$this, 'updateOrderFromFileMaker'],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function updateOrderFromFileMaker($data)
    {
        $orderId = $data['id']; // Must be "Order No" in FileMaker
        $message = $data->get_param('message');
        $itemNo = $data->get_param('itemNo');
        $fileMakerUser = $data->get_param('user');
        $order = wc_get_order($orderId);
        if (null == $order) {
            return new \WP_Error(404, 'Order could not be found with that order ID');
        }

        $invoice = new Invoice();
        $returnedInvoices = $invoice->fetchAllInvoiceRecordByOrderNumber($order);
        if (is_null($returnedInvoices) || empty($returnedInvoices)) {
            return new \WP_Error(500, 'FileMaker invoice record could not be found with that order ID');
        }

        try {
            foreach ($returnedInvoices as $invoice) {
                \App\update_filemaker_order($order, $invoice);
            }
            $refetchOrder = new \WC_Order($order->get_id());
            $parsedTotal = floatval($refetchOrder->get_total());
            if ($parsedTotal > 0) {
                $refetchOrder->update_status('pending');
            } else {
                $refetchOrder->update_status('processing');
            }

            $refetchOrder->add_order_note('Order data updated from FileMaker');

            // Send the customer an email that it is Awaiting Approval
            do_action('iresq_trigger_awaiting_approval_email', $order->get_id(), $message, $fileMakerUser, $itemNo, $order);
        } catch (\Exception $error) {
            return new \WP_Error(500, 'FileMaker invoice record could not be updated');
        }

        return new \WP_REST_Response('WooCommerce order updated', 200);
    }
}

new OrderProcessing();
