<?php

namespace App\WooCommerce;

class Checkout
{
    public function __construct()
    {
        add_action('woocommerce_checkout_before_terms_and_conditions', [$this, 'addAgreementFields']);
        add_action('woocommerce_checkout_create_order', [$this, 'saveSignatureWaivedField'], 10, 2);
        add_action('woocommerce_checkout_process', [$this, 'agreementValidation']);
        add_action('woocommerce_checkout_fields', [$this, 'disableOrderNotes']);
        add_action('woocommerce_checkout_update_order_meta', [$this, 'updateOrderPostMetaOnCheckout']);
        add_filter('woocommerce_paypal_args', [$this, 'updatePaypalInvoiceId'], 10, 2);
        add_filter('woocommerce_get_order_item_totals', [$this, 'reorderAndAddAlreadyPaidToOrderItemTotals'], 10, 3);
        add_action('woocommerce_after_checkout_billing_form', [$this, 'customBillingAddressFields']);
        add_filter('woocommerce_checkout_fields', [$this, 'customCheckoutFieldsClasses']);
    }

    public function customCheckoutFieldsClasses($fields) {
        $fields['billing']['billing_company']['class'] = ['form-row', 'form-row-first'];
        $fields['billing']['billing_country']['class'] = ['form-row', 'form-row-last'];

        $fields['billing']['billing_address_1']['class'] = ['form-row', 'form-row-first form-row-first-2'];
        $fields['billing']['billing_address_2']['class'] = ['form-row', 'form-row-last form-row-last-2 no-label'];

        $fields['billing']['billing_city']['class'] = ['form-row', 'form-row-first form-row-first-2'];
        $fields['billing']['billing_state']['class'] = ['form-row', 'form-row-last form-row-last-2'];

        $fields['billing']['billing_postcode']['class'] = ['form-row', 'form-row-first form-row-first-2'];
        $fields['billing']['billing_phone']['class'] = ['form-row', 'form-row-last form-row-last-2'];

        unset($fields['billing']['billing_billing']);


        $fields['shipping']['shipping_company']['class'] = ['form-row', 'form-row-first'];
        $fields['shipping']['shipping_country']['class'] = ['form-row', 'form-row-last'];

        $fields['shipping']['shipping_address_1']['class'] = ['form-row', 'form-row-first form-row-first-2'];
        $fields['shipping']['shipping_address_2']['class'] = ['form-row', 'form-row-last form-row-last-2 no-label'];

        $fields['shipping']['shipping_city']['class'] = ['form-row', 'form-row-first form-row-first-2'];
        $fields['shipping']['shipping_state']['class'] = ['form-row', 'form-row-last form-row-last-2'];

        $fields['shipping']['shipping_postcode']['class'] = ['form-row', 'form-row-first form-row-first-2'];
        $fields['shipping']['shipping_phone']['class'] = ['form-row', 'form-row-last form-row-last-2'];

        unset($fields['shipping']['shipping_billing']);
        
        return $fields;
    }

    public function customBillingAddressFields($checkout)
    {
        woocommerce_form_field('preferred_contact', array(
            'type'          => 'checkbox',
            'class'         => array('form-row mycheckbox'),
            'label_class'   => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
            'input_class'   => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
            'required'      => false,
            'label'         => 'I would prefer to be contacted by phone',
         ), $checkout->get_value('preferred_contact'));
    }

    public function addAgreementFields()
    {
        $checkbox1_text = __(get_field('charger_cable_agreement', 'options'), 'woocommerce');
        $checkbox2_text = __(get_field('screen_protector_removal_agreement', 'options'), 'woocommerce');
        $checkbox3_text = __(get_field('lost_data_agreement', 'options'), 'woocommerce');
        $checkbox4_text = __(get_field('shipping_waiver_agreement', 'options'), 'woocommerce'); ?>
        <h3 style="padding-left: 13px">Shipping Waiver</h3>
        <p class="form-row custom-checkboxes">
            <label class="woocommerce-form__label checkbox charger_cable_agreement">
                <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="shipping_waiver_agreement">
                <span><?php echo esc_html($checkbox4_text); ?></span>
            </label>
        </p>
        <hr>
        <h3 id="acknowledgements" style="padding-left: 13px">Acknowledgements</h3>
        <div class="checkout__acknowledgements">
            <p class="form-row" style="padding-left: 13px"><?php echo esc_html($checkbox1_text); ?></p>
            <p class="form-row" style="padding-left: 13px"><?php echo esc_html($checkbox2_text); ?></p>
            <p class="form-row" style="padding-left: 13px"><?php echo esc_html($checkbox3_text); ?></p>
        </div>
        <a href="#acknowledgements" class="ack-read-more">Read More</a>

        <script>
            jQuery('.ack-read-more').on('click', () => {
                jQuery('.checkout__acknowledgements').toggleClass('opened');
                jQuery('#ack-input').attr('disabled', false);
                jQuery('.ack-read-more').hide();
            })
        </script>
        
        <p class="form-row custom-checkboxes">
            <label class="woocommerce-form__label checkbox lost_data_agreement">
                <input id="ack-input" type="checkbox" disabled="true" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="all_agreement">
                <span>I have read and agree to the acknowledgements listed above.</span> <span class="required">*</span>
            </label><br>
        </p>
    <?php
    }

    /**
     * Save the Signature Waived field on order submission.
     *
     * @param mixed $order
     * @param mixed $data
     */
    public function saveSignatureWaivedField($order, $data)
    {
        if (isset($_POST['shipping_waiver_agreement'])) {
            $order->update_meta_data('signatureWaived', empty($_POST['shipping_waiver_agreement']) ? '' : 'yes');
        }
    }

    public function agreementValidation()
    {
        if (!$_POST['all_agreement']) {
            wc_add_notice(__('Please acknowledge and accept all agreements.'), 'error');
        }
    }

    public function disableOrderNotes($fields)
    {
        unset($fields['order']['order_comments']);

        return $fields;
    }

    /**
    * Change the Invoice ID to PayPal to better support multi-payments
    *
    * @param \WC_Order $order
    */
    public function updatePaypalInvoiceId($paypal_args, $order)
    {
        $orderId = $order->get_id();
        $processedPayPalBefore = get_post_meta($orderId, '_processed_paypal_times', true);
        if ($processedPayPalBefore == '') {
            $processedPayPalBefore = 0;
        }
        $processedPayPalBefore = intval($processedPayPalBefore);
        $paypal_args['invoice'] = 'WC-'.$order->get_order_number().'-'.$processedPayPalBefore;
        $postIncrease = $processedPayPalBefore+1;
        update_post_meta($orderId, '_processed_paypal_times', $postIncrease);
        return $paypal_args;
    }

    public function updateOrderPostMetaOnCheckout($order_id)
    {
        if (!empty($_POST['shipping_waiver_agreement'])) {
            update_post_meta($order_id, 'signatureWaived', empty($_POST['shipping_waiver_agreement']) ? '' : 'yes');
        }

        if (!empty($_POST['client_po'])) {
            update_post_meta($order_id, 'Client PO#', sanitize_text_field($_POST['client_po']));
        }

        if (!empty($_POST['preferred_contact'])) {
            update_post_meta($order_id, 'preferred_contact', sanitize_text_field($_POST['preferred_contact']));
        }

        if (empty($_POST['order_device_count'])) {
            // dd('Order device count is empty');

            return; // Device data unknown - order did not process through the correct checkout process
        }

        $deviceCount = $_POST['order_device_count'];
        $currentDevice = 1;
        while ($currentDevice <= intval($deviceCount)) {
            $deviceStory = $currentDevice.'_device_story';
            $issueDescription = $currentDevice.'_issue_description';
            $devicePassword = $currentDevice.'_device_password';
            $maxRepairCost = $currentDevice.'_max_repair_cost';

            if (!empty($_POST[$deviceStory])) {
                update_post_meta($order_id, $deviceStory, sanitize_text_field($_POST[$deviceStory]));
            }
            if (!empty($_POST[$issueDescription])) {
                update_post_meta($order_id, $issueDescription, sanitize_text_field($_POST[$issueDescription]));
            }
            if (!empty($_POST[$devicePassword])) {
                update_post_meta($order_id, $devicePassword, sanitize_text_field($_POST[$devicePassword]));
            }
            if (!empty($_POST[$maxRepairCost])) {
                update_post_meta($order_id, $maxRepairCost, sanitize_text_field($_POST[$maxRepairCost]));
            }

            ++$currentDevice;
        }
    }

    public function reorderAndAddAlreadyPaidToOrderItemTotals($total_rows, $order, $tax_display)
    {
        // Change text for the order total
        $total_rows['order_total']['label'] = 'Total Due:';

        // Add the Already Paid row to the table, if it exists
        $invoiceArray = get_post_meta($order->get_id(), 'filemakerInvoicesAmountOwe', true);
        $invoiceTotal = 0;
        if ('' != $invoiceArray) {
            foreach ($invoiceArray as $amount) {
                $invoiceTotal += floatval($amount);
            }
        }
        $actualTotal = $order->get_subtotal() + $order->get_shipping_total() + $order->get_total_tax();
        $amountPaid = $actualTotal - $invoiceTotal;

        if ($invoiceTotal) {
            $alreadyPaidTotal['already_paid'] = [
                'label' => 'Total Paid:',
                'value' => '$'.$amountPaid
            ];
            $total_rows = array_splice($total_rows, 0, count($total_rows) - 1) + $alreadyPaidTotal + array_slice($total_rows, count($total_rows) - 1);
        }

        return $total_rows;
    }
}

new Checkout();
