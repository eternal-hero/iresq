<?php

namespace App\Filemaker;

use INTERMediator\FileMakerServer\RESTAPI\FMDataAPI as FMDataAPI;

class Payment
{
    private $filemaker;

    public function __construct()
    {
        // Docs: https://github.com/msyk/FMDataAPI/blob/master/samples/FMDataAPI_Sample.php
        $this->filemaker = new FMDataAPI('PAYMENT_', $_ENV['FILEMAKER_UNAME'], $_ENV['FILEMAKER_PW'], $_ENV['FILEMAKER_URL']);
        $this->filemaker->setTimeout(15);
    }

    /**
     * Add payment to invoice order record.
     *
     * @param true|\WC_Order|\WC_Order_Refund $order
     * @param \WC_Order_Item                  $item
     * @param object                          $filemakerInvoiceRecord
     * @param decimal                         $invoiceOrderAmount
     *
     * @return bool
     */
    public function createPaymentRecord($order, $filemakerInvoiceRecord, $invoiceOrderAmount)
    {
        // payload to send to the endpoint
        $data = [
            'nAmount' => "{$invoiceOrderAmount}",
            'tInvoiceID' => "{$filemakerInvoiceRecord->field('Item No')}",
        ];
        $script = ['script' => 'Apply Payment to Invoice'];

        if ('authorize_net_cim_credit_card' == $order->get_payment_method()) {
            $cardType = get_post_meta($order->get_id(), '_wc_authorize_net_cim_credit_card_card_type', true);
            $data['tPayType'] = $cardType;
        } elseif ('paypal' == $order->get_payment_method()) {
            $data['tPayType'] = 'PayPal';
        } else {
            return; // Hard return as no payment was collected
        }

        $recId = $this->filemaker->layout('Payment_api')->create($data, null, $script);
        $record = $this->filemaker->layout('Payment_api')->getRecord($recId);

        $order->add_order_note('Added payment to FileMaker invoice');

        return $record;
    }
}
