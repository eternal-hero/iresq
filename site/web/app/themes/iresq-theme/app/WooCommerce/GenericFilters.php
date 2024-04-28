<?php

namespace App\WooCommerce;

class GenericFilters
{
    public function __construct()
    {
        add_filter('woocommerce_add_to_cart_fragments', [$this, 'cartCountFragments'], 10, 1);
        add_filter('woocommerce_add_cart_item_data', [$this, 'addSerialNumberAndItemNumberToCart'], 1, 4);
        add_filter('woocommerce_account_menu_items', [$this, 'accountMenuItems']);
        add_filter('woocommerce_min_password_strength', [$this, 'passwordStrength']);
        add_filter('woocommerce_is_sold_individually', '__return_true');
        add_filter('woocommerce_cart_shipping_method_full_label', [$this, 'addPriceToFreeRate'], 10, 2);
        add_filter('woocommerce_add_to_cart', [$this, 'updateCartDevices'], 10, 6);
        add_filter('woocommerce_add_to_cart', [$this, 'updateUserDevices'], 20, 6);
        add_filter('woocommerce_no_shipping_available_html', [$this, 'changeNoShipMethodMessage']);
        add_filter('woocommerce_cart_no_shipping_available_html', [$this, 'changeNoShipMethodMessage']);
        add_filter('woocommerce_cart_needs_shipping', [$this, 'decideIfShippingIsRequired']);
        add_filter( 'woocommerce_login_redirect', [$this, 'loginRedirect']);
        add_filter( 'woocommerce_registration_redirect', [$this, 'registerRedirect']);

        add_action('woocommerce_cart_totals_before_order_total', [$this, 'cart_custom_shipping_message_row']);
        add_action('woocommerce_review_order_before_order_total', [$this, 'cart_custom_shipping_message_row']);
    }

    /**
     * Create a dynamic bag count.
     *
     * @param mixed $fragments
     */
    public function cartCountFragments($fragments)
    {
        $fragments['span.cart-count'] = '<span class="cart-count">'.WC()->cart->get_cart_contents_count().'</span>';

        return $fragments;
    }

    public function decideIfShippingIsRequired()
    {
        $cart = WC()->cart;
        if (is_null($cart)) {
            return true;
        }

        $cartItems = $cart->get_cart();
        $availableDevices = [];
        foreach ($cartItems as $cart_item_key => $cart_item) {
            $cart_item_sn = isset($cart_item['device_serial_number']) ? $cart_item['device_serial_number'] : '';
            $cart_item_dn = isset($cart_item['device_number']) ? $cart_item['device_number'] : '';
            if (!in_array($cart_item_dn, array_column($availableDevices, 'key'))) {
                if ($cart_item_sn) {
                    $availableDevices[] = [
                        'key' => $cart_item_dn,
                        'serial' => $cart_item_sn,
                        'name' => "Device #{$cart_item_dn} (#{$cart_item_sn})",
                    ];
                } else {
                    $availableDevices[] = [
                        'key' => $cart_item_dn,
                        'serial' => '',
                        'name' => "Device #{$cart_item_dn}",
                    ];
                }
            }
        }

        return count($availableDevices) < 5;
    }

    public function cart_custom_shipping_message_row()
    {
        if (!WC()->cart->needs_shipping()) {
            $shipping_message = __('Costs will be calculated on next step.', 'woocommerce'); ?>
            <tr class="shipping">
                <th><?php _e('Shipping', 'woocommerce'); ?></th>
                <td class="message" data-title="<?php esc_attr_e('Shipping', 'woocommerce'); ?>"><?php echo $this->changeNoShipMethodMessage(); ?></td>
            </tr>
        <?php
        }
    }

    /**
     * Add the serial number and item number data to the cart object.
     *
     * @param array $cart_item_data cart item meta data
     * @param int   $product_id     product ID
     * @param int   $variation_id   variation ID
     * @param bool  $quantity       Quantity
     */
    public function addSerialNumberAndItemNumberToCart($cart_item_data, $product_id, $variation_id, $quantity)
    {
        if (isset($_POST['wc-serial-number'])) {
            // Add the item data
            $cart_item_data['device_serial_number'] = $_POST['wc-serial-number'];
            $cart_item_data['device_number'] = $_POST['wc-select-device'];
        } elseif (isset($_POST['serialNumber'])) {
            // Add the item data
            $cart_item_data['device_serial_number'] = $_POST['serialNumber'];
            $cart_item_data['device_number'] = $_POST['deviceNumber'];
        }

        return $cart_item_data;
    }

    /**
     * Create new account menu items.
     *
     * @return array $menu_order New account menu order
     */
    public function accountMenuItems()
    {
        return [
            'edit-account' => __('Account details', 'woocommerce'),
            'orders' => __('Orders', 'woocommerce'),
            'devices' => __('Devices', 'woocommerce'),
            'edit-address' => __('Addresses', 'woocommerce'),
            'payment-methods' => __('Payment methods', 'woocommerce'),
            'customer-logout' => __('Logout', 'woocommerce'),
        ];
    }

    /**
     * Change the strength of the passwords on account creation.
     * 3 => Strong (default) | 2 => Medium | 1 => Weak | 0 => Very Weak (anything).
     *
     * @param mixed $strength
     */
    public function passwordStrength($strength)
    {
        return 0;
    }

    /**
     * If shipping rate is 0, concatenate ": $0.00" to the label.
     *
     * @param mixed $label
     * @param mixed $method
     */
    public function addPriceToFreeRate($label, $method)
    {
        if (!($method->cost > 0)) {
            $label .= ': '.wc_price(0);
        }

        return $label;
    }

    public function updateUserDevices($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
    {
        if (!is_user_logged_in()) {
            return;
        }

        $user_id = get_current_user_id();
        $user_devices = '' != get_user_meta(get_current_user_id(), 'devices', true) ? get_user_meta(get_current_user_id(), 'devices', true) : [];

        // check if there is any user data. If not, create an empty array
        if (!is_array($user_devices)) {
            $user_devices = [];
        }

        // Check if a serial number was updated
        $serial_number = $cart_item_data['device_serial_number'];
        $device_number = $cart_item_data['device_number'];
        $deviceCount = 1;
        $changedDevices = [];

        // See if device exists. Update if so.
        foreach ($user_devices as $device) {
            $device_sn = isset($device['device_serial_number']) ? $device['device_serial_number'] : '';
            if ($deviceCount == $device_number && $device_sn != $serial_number) {
                $user_devices = $this->changeArrayKey($user_devices, $device_sn, $serial_number);
                $user_devices[$serial_number]['device_serial_number'] = $serial_number;
                update_user_meta($user_id, 'devices', $user_devices);

                return;
            }
            ++$deviceCount;
        }

        // Device could not be found - adding a new one.

        // get all of the device fields we need from the purchased product
        $product = wc_get_product($product_id);
        $product_attributes = $product->get_attributes();
        $device_brand = $product->get_attribute('pa_brand');
        $device_name = $device_brand;
        $type = get_the_terms($product_id, 'product_cat')[0]->name;
        $device_link = get_permalink(wc_get_page_id('shop')).'?product_cat='.$type.'&brand='.$device_brand;

        // loop through the attributes since there are mulitple model attr depending on the device type
        if ($product_attributes) {
            foreach ($product_attributes as $attr_index => $attr) {
                if ($attr->is_taxonomy()) {
                    // we don't want the brand because that's already in the URL
                    if ('pa_brand' === $attr_index) {
                        continue;
                    }
                    // attach the model to the device name
                    $device_name = $device_name.' '.$product->get_attribute($attr_index);

                    // gets the slugs of the parent attributes and the values of the attributes for the URL
                    $mode_term_group = wp_get_post_terms($product->get_id(), $attr_index, 'all');
                    $model_taxonomy = get_taxonomy($mode_term_group[0]->taxonomy);

                    if (isset($model_taxonomy)) {
                        $parent_model_slug = (str_replace('pa_', '', $model_taxonomy->name));
                    }
                    $model_value = $attr->get_slugs()[0];
                    $device_link .= '&'.$parent_model_slug.'='.$model_value;
                }
            }
        }

        // clean the URL up
        $device_link = str_replace(' ', '', strtolower($device_link));

        $new_device = [
            'device_name' => $device_name,
            'device_type' => $type,
            'device_serial_number' => $serial_number,
            'device_link' => $device_link,
        ];

        // add the new device to the users devices
        $user_devices[$serial_number] = $new_device;
        update_user_meta($user_id, 'devices', $user_devices);
    }

    public function updateCartDevices($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
    {
        $cartItems = WC()->cart->get_cart();
        foreach ($cartItems as $loop_cart_item_key => $cart_item) {
            $device_number = isset($cart_item['device_number']) ? $cart_item['device_number'] : '';

            if (
                !empty($cart_item_data['device_number'])
                && !empty($cart_item_data['device_number']) 
                && $cart_item_data['device_number'] == $device_number
            ) {
                $cart_item['device_serial_number'] = $cart_item_data['device_serial_number'];
                WC()->cart->cart_contents[$loop_cart_item_key] = $cart_item;
            }
        }

        WC()->cart->set_session();
    }

    public function changeNoShipMethodMessage()
    {
        return "Please give us a call to discuss shipping options available for your order. You can reach us directly at <a href='tel:1-888-447-3728'>1-888-447-3728</a>";
    }

    private function changeArrayKey($array, $old_key, $new_key)
    {
        if (!array_key_exists($old_key, $array)) {
            return $array;
        }

        $keys = array_keys($array);
        $keys[array_search($old_key, $keys)] = $new_key;

        return array_combine($keys, $array);
    }

    public function loginRedirect($redirect)
    {
        $redirect_page_id = url_to_postid( $redirect );
        $checkout_page_id = wc_get_page_id( 'checkout' );

        if( $redirect_page_id == $checkout_page_id ) {
            return $redirect;
        }

        return get_home_url();
    }

    public function registerRedirect($redirect)
    {
        return get_home_url();
    }
}

new GenericFilters();
