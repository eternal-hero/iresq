<?php

namespace App;

use App\Filemaker\Invoice;
use App\Filemaker\LineItem;
use App\Filemaker\Payment;
use INTERMediator\FileMakerServer\RESTAPI\FMDataAPI as FMDataAPI;
use INTERMediator\FileMakerServer\RESTAPI\Supporting\FileMakerRelation;

add_action('admin_notices', function () {
    if (!empty($_GET['iresq-filemaker-create'])) {
        if ('error' == $_GET['iresq-filemaker-create']) {
?>
            <div class="notice notice-error is-dismissible">
                <p>An order was found in FileMaker with this order #. The order could not be created.</p>
            </div>
        <?php
        }

        if ('succes' == $_GET['iresq-filemaker-create']) {
        ?>
            <div class="notice notice-success is-dismissible">
                <p>Order was successfully added to FileMaker.</p>
            </div>
        <?php
        }

        if ('create-error' == $_GET['iresq-filemaker-create']) {
        ?>
            <div class="notice notice-error is-dismissible">
                <p>The order could not be created in Filemaker. Filemaker error: <?php echo $_GET['iresq-filemaker-create-error-message']; ?></p>
            </div>
        <?php
        }
    }
});

/*
 * Update order from Filemaker from the custom action order
 *
 * @param \WC_Order $order
 */
add_action('woocommerce_order_action_update_order_filemaker', function ($order) {
    // Verify order exists in FileMaker
    $invoice = new Invoice();
    $returnedInvoices = $invoice->fetchAllInvoiceRecordByOrderNumber($order);
    if (is_null($returnedInvoices) || empty($returnedInvoices)) {
        if (is_admin()) {
            add_filter('wp_redirect', function ($location) {
                return add_query_arg('iresq-filemaker-update', 'error', $location);
            });
        }
    } else {
        foreach ($returnedInvoices as $invoice) {
            update_filemaker_order($order, $invoice);
        }
        $refetchOrder = new \WC_Order($order->get_id());
        $parsedTotal = floatval($refetchOrder->get_total());
        if ($parsedTotal > 0) {
            $refetchOrder->update_status('pending');
        } else {
            $refetchOrder->update_status('processing');
        }

        $refetchOrder->add_order_note('Order data updated from FileMaker');

        if (is_admin()) {
            add_filter('wp_redirect', function ($location) {
                return add_query_arg('iresq-filemaker-update', 'success', $location);
            });
        }
    }
});

add_action('admin_notices', function () {
    if (!empty($_GET['iresq-filemaker-update'])) {
        if ('error' == $_GET['iresq-filemaker-update']) {
        ?>
            <div class="notice notice-error is-dismissible">
                <p>An order with that order # does not exist in FileMaker. Either create the order from WooCommerce, or verify the order # is correct.</p>
            </div>
        <?php
        }

        if ('succes' == $_GET['iresq-filemaker-update']) {
        ?>
            <div class="notice notice-success is-dismissible">
                <p>Order was successfully updated from FileMaker.</p>
            </div>
        <?php
        }
    }
});

/**
 * @param \WC_Order $order
 * @param FileMakerRelation $filemakerInvoiceRecord
 */
function update_filemaker_order($order, $filemakerInvoiceRecord)
{
    $lineItem = new LineItem();

    $lineItemResults = $lineItem->fetchLineItemRecords($filemakerInvoiceRecord->field('Item No'));
    $order->update_status('updating');

    // Clear out the items in the order that don't exist first
    foreach ($order->get_items() as $item_id => $item) {
        $foundItem = false;
        $product_id = $item->get_product_id();
        $product = wc_get_product($product_id);
        $invoiceNumber = wc_get_order_item_meta($item_id, 'invoice_number', true);

        foreach ($lineItemResults as $lineItem) {
            if ('Shipping' == $lineItem->field('Device Type')) {
                continue;
            }
            if ($lineItem->field('Product SKU') == $product->get_sku()) {
                $foundItem = true;
            }
        }

        if ('' == $invoiceNumber) {
            wc_delete_order_item($item_id);
            continue;
        }

        if (!$foundItem && '' == $invoiceNumber) {
            wc_delete_order_item($item_id);
        }
    }

    foreach ($lineItemResults as $lineItem) {
        if ('Shipping' == $lineItem->field('Device Type')) {
        } else {
            // Loop through all of the items to see if this FileMaker item exists in the order
            $itemFound = false;
            foreach ($order->get_items() as $item_id => $item) {
                $invoiceNumber = wc_get_order_item_meta($item_id, 'invoice_number', true);
                if ('' == $invoiceNumber || $invoiceNumber != $lineItem->field('Invoice ID')) {
                    continue;
                }
                $product_id = $item->get_product_id();
                $product = wc_get_product($product_id);
                if ($lineItem->field('Product SKU') != $product->get_sku()) {
                    continue;
                }
                if ('' == $invoiceNumber) {
                    $item->add_meta_data('invoice_number', $lineItem->field('Invoice ID'));
                    $item->add_meta_data('device_serial_number', $filemakerInvoiceRecord['Serial No']);
                }
                $item->save();
                wc_update_order_item_meta($item_id, '_qty', $lineItem->field('QtyOrdered'));
                wc_update_order_item_meta($item_id, '_line_subtotal', $lineItem->field('Unit Price')); // price per item
                wc_update_order_item_meta($item_id, '_line_total', $lineItem->field('Unit Price') * $lineItem->field('QtyOrdered'));

                $itemFound = true;
            }

            if (!$itemFound) {
                // Add the missing item to the order
                $foundProductId = wc_get_product_id_by_sku($lineItem->field('Product SKU'));
                if (0 == $foundProductId) {
                    // Could not find product. What do we do? For now, just skipping.
                    continue;
                }
                $product = wc_get_product($foundProductId);
                $itemId = $order->add_product($product, $lineItem->field('QtyOrdered'), [
                    'subtotal' => $lineItem->field('Unit Price'),
                    'total' => $lineItem->field('Unit Price') * $lineItem->field('QtyOrdered'),
                ]);
                $item = $order->get_item($itemId);
                $item->add_meta_data('invoice_number', $lineItem->field('Invoice ID'));
                $item->add_meta_data('device_serial_number', $filemakerInvoiceRecord->field('Serial No'));
                $item->save();
            }
        }
    }

    // Repull the order data
    $order = new \WC_Order($order->get_id());
    $order->calculate_taxes();
    $order->calculate_totals();
    $order->save();
    addOrUpdateUnPaidBalance($order, $filemakerInvoiceRecord->field('Item No'), $filemakerInvoiceRecord->field('cInvoice Balance'));
}

/**
 * @param \WC_Order $order
 * @param float     $invoiceTotal
 * @param mixed     $invoiceNumber
 * @param mixed     $amountOwe
 */
function addOrUpdateUnPaidBalance($order, $invoiceNumber, $amountOwe)
{
    $invoiceArray = get_post_meta($order->get_id(), 'filemakerInvoicesAmountOwe', true);
    $invoiceArray = '' == $invoiceArray ? [] : $invoiceArray;
    $invoiceArray[$invoiceNumber] = $amountOwe;
    update_post_meta($order->get_id(), 'filemakerInvoicesAmountOwe', $invoiceArray);
    $order->calculate_taxes();
    $order->calculate_totals();
    $order->save();
}

add_action('woocommerce_order_after_calculate_totals', function ($and_taxes, $order) {
    $invoiceArray = get_post_meta($order->get_id(), 'filemakerInvoicesAmountOwe', true);
    if ('' != $invoiceArray) {
        $total = 0;
        foreach ($invoiceArray as $amount) {
            $total += floatval($amount);
        }
        $order->set_total($total);
    }
}, 10, 2);

// Custom row for "Already Paid" in the admin page
add_action('woocommerce_admin_order_totals_after_tax', function ($order_id) {
    // Here set your data and calculations
    $order = wc_get_order($order_id);
    $invoiceArray = get_post_meta($order_id, 'filemakerInvoicesAmountOwe', true);
    $invoiceTotal = 0;
    if ('' != $invoiceArray) {
        foreach ($invoiceArray as $amount) {
            $invoiceTotal += floatval($amount);
        }
    }
    $actualTotal = (float)$order->get_subtotal() + (float)$order->get_shipping_total() + (float)$order->get_total_tax();
    $amountPaid = $actualTotal - $invoiceTotal;
    if (0 != $invoiceTotal) {
        ?>
        <tr>
            <td class="label">Already Paid:</td>
            <td width="1%"></td>
            <td class="custom-total"><strong>-$<?php echo number_format($amountPaid, 2); ?></strong></td>
        </tr>
<?php
    }
}, 10, 1);
