<?php

namespace App\Filemaker;

use App\WooCommerce\OrderProcessing;
use Exception;
use INTERMediator\FileMakerServer\RESTAPI\FMDataAPI as FMDataAPI;
use INTERMediator\FileMakerServer\RESTAPI\Supporting\FileMakerRelation;

class Invoice
{
    private $filemaker;

    public function __construct()
    {
        // Docs: https://github.com/msyk/FMDataAPI/blob/master/samples/FMDataAPI_Sample.php
        $this->filemaker = new FMDataAPI('Invoices', $_ENV['FILEMAKER_UNAME'], $_ENV['FILEMAKER_PW'], $_ENV['FILEMAKER_URL']);
        $this->filemaker->setTimeout(15);
    }

    /**
     * Processes the invoice order for a particular item.
     *
     * @param true|\WC_Order|\WC_Order_Refund $order
     * @param \WC_Order_Item                  $item
     * @param int|string                      $itemId
     * @param int|string                      $deviceNumber
     * @param int|string                      $deviceCount
     *
     * @return FileMakerRelation
     */
    public function createInvoiceRecord($order, $item, $itemId, $deviceNumber, $deviceCount, $dumpOrder = false)
    {
        /**
         * Order details.
         */
        $orderNumber = $order->get_order_number();
        $order_date = $order->get_date_created()->format('m-d-Y');
        $order_client_po = get_post_meta($order->get_id(), 'Client PO#', true);
        $preferred_contact = get_post_meta($order->get_id(), 'preferred_contact', true);
        $preferred_contact = $preferred_contact != '1' ? 'Email' : 'Phone';

        /**
         * Get Tracking Information.
         */
        $orderProcessing = new OrderProcessing();
        $trackingNumber = '';
        $returnTrackingNumber = '';
        if (1 == $deviceNumber) {
            $trackingNumber = $orderProcessing->getOrderTrackingNumber($order->get_id());
            $returnTrackingNumber = $orderProcessing->getOrderReturnTrackingNumber($order->get_id());
        }

        /**
         * Internal notes
         * Combining multiple fields in the checkout to one FileMaker field.
         */
        $device_issue = get_post_meta($order->get_id(), $deviceCount . '_issue_description', true);
        $device_story = get_post_meta($order->get_id(), $deviceCount . '_device_story', true);
        $device_password = get_post_meta($order->get_id(), $deviceCount . '_device_password', true);
        $max_repair_cost = get_post_meta($order->get_id(), $deviceCount . '_max_repair_cost', true);
        $signatureWaived = '' === get_post_meta($order->get_id(), 'signatureWaived', true) ? 'yes' : ''; // Empty string means signature is required. FileMaker expects Yes to be checked value in this case.
        $internal_notes = wp_kses_post("Web order received\n");
        if ($device_issue) {
            $internal_notes .= wp_kses_post('Customer Note: ' . $device_issue . "\n");
        }
        if ($device_password) {
            $internal_notes .= wp_kses_post('Password: ' . $device_password . "\n");
        }
        if ($max_repair_cost) {
            $internal_notes .= wp_kses_post('Max repair cost: ' . $max_repair_cost . "\n");
        }
        if ($device_story) {
            $internal_notes .= wp_kses_post('Device Story: ' . $device_story . "\n");
        }
        $sanitized_notes = wp_kses_post($device_issue);

        // Calculate tax amount for this invoice
        $taxAmount = 0;
        $taxRate = 0;
        foreach ($order->get_items() as $order_item_id => $order_item) {
            if ($taxRate == 0) {
                $tax = new \WC_Tax();
                $taxes = $tax->get_rates($order_item->get_tax_class());
                $rates = array_shift($taxes);
                $taxRate = $rates ? array_shift($rates) : 0;
                $taxRate = $taxRate / 100;
            }

            $cart_item_dn = isset($order_item['device_number']) ? $order_item['device_number'] : '';
            if ($cart_item_dn == $deviceNumber) {
                $itemTax = wc_get_order_item_meta($order_item_id, '_line_tax', true);
                $taxAmount = floatval($itemTax) + floatval($taxAmount);
            }
        }

        // Item details
        $product_id = $item->get_product_id();
        $product = wc_get_product($product_id);
        $repair_type = [];
        if (get_the_terms($product_id, 'repairs')) {
            foreach (get_the_terms($product_id, 'repairs') as $repair) {
                array_push($repair_type, $repair->name);
            }
        }
        $repair_type = implode(', ', $repair_type);
        $item_details = wc_get_order_item_meta($itemId, 'device_item_details');
        $item_number = $orderNumber . sprintf('%03d', $deviceCount); // Adds leading zeroes up to 3 digits. Example - 1 becomes 001 and 891 stays at 891
        $item_status = $item_details['item_status'];

        // If the item has shipping method contains the word "box" then save the status as "PowerBox", otherwise send over as "Process". This is handlded here so we have the shipping method saved to the order.
        $shippingType = '';
        $shippingItems = $order->get_items('shipping');
        if (count($shippingItems) > 0) {
            foreach ($shippingItems as $shippingItem) {
                $shippingType = $shippingItem->get_name();

                break;
            }
        }
        if (false !== strpos($shippingType, 'box')) {
            $item_details['item_status'] = 'Powerbox';
            wc_update_order_item_meta($itemId, __('device_item_details', 'woocommerce'), $item_details);
            $item_status = $item_details['item_status'];
        }

        $serial_number = wc_get_order_item_meta($itemId, 'device_serial_number');
        $model = '';
        $customerId = 0 == $order->get_customer_id() ? 'GUEST ' . $order->get_id() : $order->get_customer_id();
        $terms = 'paypal' == $order->get_payment_method() ? 'PayPal' : '';

        $shippingType = '';
        $shippingCharge = 0;
        if (1 == $deviceNumber) {
            $shippingItems = $order->get_items('shipping');
            if (count($shippingItems) > 0) {
                foreach ($shippingItems as $shippingItem) {
                    $shippingType = $shippingItem->get_meta('code');
                    $shippingName = $shippingItem->get_name();
                    $shippingCharge = $order->get_shipping_total();

                    break;
                }
            }
        }

        /**
         * Loop through the attributes and only set it when it's not the brand attribute.
         */
        $product_attributes = $product->get_attributes();
        if ($product_attributes) {
            foreach ($product_attributes as $attr_id => $attr) {
                if ($attr->is_taxonomy()) {
                    if ('pa_brand' === $attr_id) {
                        continue;
                    }
                    $model = $product->get_attribute($attr_id);
                }
            }
        }

        $isLocalPickup = 'Local Pickup in Kansas City' == $shippingName;

        // payload to send to the endpoint
        $fieldData = [
            'z_shipping_contact_option' => "{$preferred_contact}",
            'gEditFlag' => 'Edit',
            'Creator' => 'MAKE Digital',
            'DBA ID' => 'MRQ',
            'ReferredBy' => 'Web Site',
            'Major Status' => "{$item_status}",
            'Client Notes' => "{$sanitized_notes}",
            'Internal Notes' => "{$internal_notes}",
            'Invoice Date' => "{$order_date}",
            'Item No' => "{$item_number}",
            'Serial No' => "{$serial_number}",
            'Model' => "{$model}",
            'Order_number_website' => "{$order->get_order_number()}",
            'Repair Description' => "{$repair_type}",
            'z_customer_id' => "{$customerId}",
            'z_customer_user' => "{$order->get_user()->display_name}",
            'z_customer_email' => "{$order->get_billing_email()}",
            'z_billing_company' => "{$order->get_billing_company()}",
            'z_billing_first_name' => "{$order->get_billing_first_name()}",
            'z_billing_last_name' => "{$order->get_billing_last_name()}",
            'z_billing_address_1' => "{$order->get_billing_address_1()}",
            'z_billing_address_2' => "{$order->get_billing_address_2()}",
            'z_billing_city' => "{$order->get_billing_city()}",
            'z_billing_state' => "{$order->get_billing_state()}",
            'z_billing_postcode' => "{$order->get_billing_postcode()}",
            'z_billing_country' => "{$order->get_billing_country()}",
            'z_billing_phone' => "{$order->get_billing_phone()}",
            'z_billing_email' => "{$order->get_billing_email()}",
            'z_shipping_company' => $isLocalPickup ? 'iResQ' : "{$order->get_shipping_company()}",
            'z_shipping_first_name' => "{$order->get_shipping_first_name()}",
            'z_shipping_last_name' => "{$order->get_shipping_last_name()}",
            'z_shipping_address_1' => $isLocalPickup ? '15346 S. Keeler St.' : "{$order->get_shipping_address_1()}",
            'z_shipping_address_2' => $isLocalPickup ? '' : "{$order->get_shipping_address_2()}",
            'z_shipping_city' => $isLocalPickup ? 'Olathe' : "{$order->get_shipping_city()}",
            'z_shipping_state' => $isLocalPickup ? 'KS' : "{$order->get_shipping_state()}",
            'z_shipping_postcode' => $isLocalPickup ? '66062' : "{$order->get_shipping_postcode()}",
            'z_shipping_country' => $isLocalPickup ? 'USA' : "{$order->get_shipping_country()}",
            'PO No' => "{$order_client_po}",
            'Order_id_website' => "{$order->get_id()}",
            'ShipType' => "{$shippingType}",
            'Shipping Charge' => "{$shippingCharge}",
            'SignatureRequired' => "{$signatureWaived}",
            'ShipTrackingNo' => "{$trackingNumber}",
            'z_shipping_method' => "{$shippingName}",
            'ReturnTrackingNo' => "{$returnTrackingNumber}",
            'Tax API' => "{$taxAmount}",
            'n_Tax Rate' => "{$taxRate}",
            'Invoice Terms' => "{$terms}",
        ];
        $script = ['script' => 'API Run Account Script'];

        if ($dumpOrder) {
            wp_send_json($fieldData);
            return;
        }

        /**
         * Call filemaker to create a record and validate the result.
         */

        $recId = $this->filemaker->layout('Invoices_api')->create($fieldData, null, $script);
        $record = $this->filemaker->layout('Invoices_api')->getRecord($recId);

        return $record;
    }

    /**
     * Create bulk order invoice
     */
    public function createBulkOrderInvoiceRecord($postId)
    {
        $serial_number = get_post_meta($postId, 'serial', true);
        $order_client_po = get_post_meta($postId, 'po', true);
        $order_claim_po = get_post_meta($postId, 'claimno', true);
        $order_client_companyName = get_post_meta($postId, 'companyName', true);
        $order_client_streetone = get_post_meta($postId, 'streetone', true);
        $order_client_streettwo = get_post_meta($postId, 'streettwo', true);
        $order_client_city = get_post_meta($postId, 'city', true);
        $order_client_state = get_post_meta($postId, 'state', true);
        $order_client_zip = get_post_meta($postId, 'zip', true);
        $userId = get_post_meta($postId, 'userCreated', true);
        $customerId = get_field('filemaker_account_id', "user_" . $userId);
        $customer = new \WC_Customer($userId);

        $fmLastInvoice = $this->fetchLastInvoiceByAcctId($customerId);
        // Declare default billing values as empty
        $billingCompany = "";
        $billingFirstName = "";
        $billingLastName = "";
        $billingAddress1 = "";
        $billingAddress2 = "";
        $billingCity = "";
        $billingState = "";
        $billingZip = "";
        $billingCountry = "";
        $shippingFirstName = "";
        $shippingLastName = "";
        $shippingCountry = "";
        if ($fmLastInvoice != null) {
            $shippingFirstName = $fmLastInvoice['fieldData'][''];
            $shippingLastName = $fmLastInvoice['fieldData'][''];
            $shippingCountry = $fmLastInvoice['fieldData']['ACTUALSHIPADDRESS::Country'];
            try {
                if (!is_null($fmLastInvoice[0]['portalData']['BILLADDRESS'])) {
                    $billInfo = $fmLastInvoice[0]['portalData']['BILLADDRESS'][0];
                    $billingAddress1 = $billInfo['BILLADDRESS::Address 1'];
                    $billingAddress2 = $billInfo['BILLADDRESS::Address 2'];
                    $billingCity = $billInfo['BILLADDRESS::City'];
                    $billingState = $billInfo['BILLADDRESS::ST'];
                    $billingZip = $billInfo['BILLADDRESS::Zip'];
                    $billingCountry = $billInfo['BILLADDRESS::Country'];
                }
            } catch (Exception $ex) {
                // This may or may not be an array
            }
        }

        /**
         * Order details.
         */
        $order_date = date('m-d-Y');
        $notes = get_post_meta($postId, 'notes', true);
        if ($notes) {
            $internal_notes = wp_kses_post('Customer Note: ' . $notes . "\n");
        }
        $sanitized_notes = wp_kses_post($notes);

        // payload to send to the endpoint
        $fieldData = [
            'gEditFlag' => 'Edit',
            'Creator' => 'MAKE Digital',
            'DBA ID' => 'MRQ',
            'ReferredBy' => 'Web Site',
            'Client Notes' => "{$sanitized_notes}",
            'Internal Notes' => "{$internal_notes}",
            'Invoice Date' => "{$order_date}",
            'Serial No' => "{$serial_number}",
            'z_customer_id' => "{$customerId}",
            'z_customer_user' => "{$customer->get_display_name()}",
            'z_customer_email' => "{$customer->get_email()}",
            'z_billing_company' => "{$billingCompany}",
            'z_billing_first_name' => "{$billingFirstName}",
            'z_billing_last_name' => "{$billingLastName}",
            'z_billing_address_1' => "{$billingAddress1}",
            'z_billing_address_2' => "{$billingAddress2}",
            'z_billing_city' => "{$billingCity}",
            'z_billing_state' => "{$billingState}",
            'z_billing_postcode' => "{$billingZip}",
            'z_billing_country' => "{$billingCountry}",
            'z_shipping_company' => "{$order_client_companyName}",
            'z_shipping_first_name' => "{$shippingFirstName}",
            'z_shipping_last_name' => "{$shippingLastName}",
            'z_shipping_address_1' => "{$order_client_streetone}",
            'z_shipping_address_2' => "{$order_client_streettwo}",
            'z_shipping_city' => "{$order_client_city}",
            'z_shipping_state' => "{$order_client_state}",
            'z_shipping_postcode' => "{$order_client_zip}",
            'z_shipping_country' => "{$shippingCountry}",
            'PO No' => "{$order_client_po}",
            'Claim No' => "{$order_claim_po}"
        ];
        $script = ['script' => 'API Run Account Script'];
        $this->filemaker->layout('Invoices_api')->create($fieldData, null, $script);

        return null;
    }

    /**
     * Fetch invoice by "Order_number_website".
     *
     * @param true|\WC_Order|\WC_Order_Refund $order
     *
     * @return FileMakerRelation|null
     */
    public function fetchInvoiceRecordByOrderNumber($order)
    {
        if (empty($order)) {
            return null;
        }
        
        $query = [[
            'Order_number_website' => "{$order->get_order_number()}",
        ]];
        $result = $this->filemaker->layout('Invoices_api')->query($query);

        return is_null($result) ? $result : $result->getFirstRecord();
    }

    /**
     * Fetch all invoices by "Order_number_website".
     *
     * @param true|\WC_Order|\WC_Order_Refund $order
     *
     * @return FileMakerRelation[]|null
     */
    public function fetchAllInvoiceRecordByOrderNumber($order)
    {
        if (empty($order)) {
            return null;
        }
        
        $query[] = [
            'Order_number_website' => "{$order->get_order_number()}",
        ];
        $result = $this->filemaker->layout('Invoices_api')->query($query);

        return $result;
    }

    /**
     * Fetch invoice by "Item No".
     *
     * @param int|string $itemNo
     *
     * @return FileMakerRelation|null
     */
    public function fetchInvoiceRecordByItemNo($itemNo)
    {
        if (empty($itemNo)) {
            return null;
        }
        
        $query[] = [
            'Item No' => "{$itemNo}",
        ];
        $result = $this->filemaker->layout('Invoices_api')->query($query);

        return is_null($result) ? $result : $result->getFirstRecord();
    }

    /**
     * Fetch invoices by "Bill Acct ID".
     *
     * @param int|string $itemNo
     *
     * @return FileMakerRelation[]|null
     */
    public function fetchAllInvoicesByAcctId($acctId)
    {
        if (empty($acctId)) {
            return null;
        }
        
        $query[] = [
            'Bill Acct ID' => "=={$acctId}",
        ];
        $sort[] = [
            'fieldName' => "Invoice Date",
            'sortOrder' => "descend",
        ];
        $result = $this->filemaker->layout('Invoices_api')->query($query, $sort);

        return $result;
    }

    /**
     * Fetch invoices by "Bill Acct ID".
     *
     * @param int|string $itemNo
     *
     * @return FileMakerRelation[]|null
     */
    public function fetchLastInvoiceByAcctId($acctId)
    {
        if (empty($acctId)) {
            return null;
        }
        
        $query['limit'] = 1;
        $query[] = [
            'Bill Acct ID' => "=={$acctId}",
        ];
        $sort[] = [
            'fieldName' => "Invoice Date",
            'sortOrder' => "descend",
        ];
        $result = $this->filemaker->layout('Invoices_api')->query($query, $sort, 0, 1);

        return $result;
    }

    /**
     * Fetch invoices by "Bill Acct ID" that are POSTED, or fully processed.
     *
     * @param int|string $itemNo
     *
     * @return FileMakerRelation[]|null
     */
    public function fetchInvoicesByAcctIdAreProcessed($acctId, $limit = 100, $offset = 0, $filterOptions = [])
    {
        if (empty($acctId)) {
            return null;
        }
        
        $masterQuery = [];
        $masterQuery['Bill Acct ID'] = "=={$acctId}";
        foreach ($filterOptions as $key => $option) {
            $masterQuery[$key] = "{$option}";
        }

        $query[] = $masterQuery;

        $sort[] = [
            'fieldName' => "Invoice Date",
            'sortOrder' => "descend",
        ];
        $result = $this->filemaker->layout('Invoices_api')->query($query, $sort, $offset, $limit);

        return $result;
    }

    /**
     * Fetch invoices by "Bill Acct ID" that are in progress.
     *
     * @param int|string $itemNo
     *
     * @return FileMakerRelation[]|null
     */
    public function fetchInvoicesByAcctIdAreInProgress($acctId, $limit = 100, $offset = 0, $filterOptions = [])
    {
        if (empty($acctId)) {
            return null;
        }
        
        $masterQuery = [];
        $masterQuery['Bill Acct ID'] = "=={$acctId}";
        foreach ($filterOptions as $key => $option) {
            $masterQuery[$key] = "{$option}";
        }

        $query[] = $masterQuery;

        $query[] = [
            'Major Status' => "POSTED",
            "omit" => "true"
        ];
        $sort[] = [
            'fieldName' => "Invoice Date",
            'sortOrder' => "descend",
        ];
        $result = $this->filemaker->layout('Invoices_api')->query($query, $sort, $offset, $limit);

        return $result;
    }
}
