<?php

namespace App\Filemaker;

use INTERMediator\FileMakerServer\RESTAPI\FMDataAPI as FMDataAPI;
use INTERMediator\FileMakerServer\RESTAPI\Supporting\FileMakerRelation;

class LineItem
{
    private $filemaker;

    public function __construct()
    {
        $this->filemaker = new FMDataAPI('LINEITM_', $_ENV['FILEMAKER_UNAME'], $_ENV['FILEMAKER_PW'], $_ENV['FILEMAKER_URL']);
        $this->filemaker->setTimeout(15);
    }

    /**
     * Add line item to invoice order record.
     *
     * @param true|\WC_Order|\WC_Order_Refund $order
     * @param \WC_Order_Item                  $item
     * @param FileMakerRelation               $filemakerInvoiceRecord
     * @param mixed                           $itemId
     *
     * @return FileMakerRelation
     */
    public function createRecord($order, $item, $itemId, $filemakerInvoiceRecord)
    {
        $product_id = $item->get_product_id();
        $product = wc_get_product($product_id);

        // payload to send to the endpoint
        $fieldData = [
            'Invoice ID' => "{$filemakerInvoiceRecord->field('Item No')}",
            'Line_item_id' => "{$itemId}",
            'Line_item_product_id' => "{$product_id}",
            'Unit Price' => "{$order->get_item_total($item)}",
            'QtyOrdered' => "{$item->get_quantity()}",
            'QtyShip' => "{$item->get_quantity()}",
            'Description' => "{$item->get_name()}",
            'Product SKU' => "{$product->get_sku()}",
        ];
        $script = ['script' => 'Recalculate Invoice Totals'];

        $recId = $this->filemaker->layout('LineItem_api')->create($fieldData, null, $script);
        $record = $this->filemaker->layout('LineItem_api')->getRecord($recId);

        return $record;
    }

    /**
     * Add a shipping line item to the invoice.
     *
     * @param true|\WC_Order|\WC_Order_Refund $order
     * @param \WC_Order_Item                  $item
     * @param FileMakerRelation               $filemakerInvoiceRecord
     * @param mixed                           $itemId
     *
     * @return FileMakerRelation
     */
    public function createShippingRecord($order, $item, $itemId, $filemakerInvoiceRecord)
    {
        // payload to send to the endpoint
        $fieldData = [
            'Invoice ID' => "{$filemakerInvoiceRecord->field('Item No')}",
            'Line_item_id' => "{$itemId}",
            'Line_item_product_id' => "{$item->get_id()}",
            'Product ID' => '',
            'Unit Price' => "{$order->get_item_total($item)}",
            'QtyOrdered' => "{$item->get_quantity()}",
            'Description' => "{$item->get_name()}",
            'Product SKU' => "{$item->get_meta('sku')}",
        ];
        $script = ['script' => 'Recalculate Invoice Totals'];

        $recId = $this->filemaker->layout('LineItem_api')->create($fieldData, null, $script);
        $record = $this->filemaker->layout('LineItem_api')->getRecord($recId);

        return $record;
    }

    /**
     * Fetch line items by INVOICE "Item No".
     *
     * @param string $invoiceItemNo
     *
     * @return FileMakerRelation[]
     */
    public function fetchLineItemRecords($invoiceItemNo)
    {
        $query[] = [
            'Invoice ID' => "{$invoiceItemNo}",
        ];
        $result = $this->filemaker->layout('LineItem_api')->query($query);

        return $result;
    }
}
