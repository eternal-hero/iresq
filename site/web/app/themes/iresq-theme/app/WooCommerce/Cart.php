<?php

namespace App\WooCommerce;

class Cart
{
    public function __construct()
    {
        add_filter('woocommerce_after_add_to_cart_quantity', [$this, 'showCartFieldsOnPage']);
    }

    /**
     * Add the new fields to the single and index product listing pages.
     * Tutorial: http://www.skyverge.com/blog/add-information-to-woocommerce-shop-page/.
     */
    public function showCartFieldsOnPage()
    {
        $availableDevices = [];
        // If user is logged in, we grab the existing devices. Otherwise - we rely on the data in the cart.
        if (is_user_logged_in()) {
            $devices = '' != get_user_meta(get_current_user_id(), 'devices', true) ? get_user_meta(get_current_user_id(), 'devices', true) : [];
            $itemCount = 1;
            foreach ($devices as $device) {
                if ($device['device_serial_number']) {
                    $availableDevices[] = [
                        'key' => $itemCount,
                        'serial' => $device['device_serial_number'],
                        'name' => "#{$itemCount} - {$device['device_name']} (#{$device['device_serial_number']})",
                    ];
                } else {
                    $availableDevices[] = [
                        'key' => $itemCount,
                        'serial' => '',
                        'name' => "#{$itemCount} - {$device['device_name']}",
                    ];
                }
                ++$itemCount;
            }
        } else {
            $cartItems = WC()->cart->get_cart();
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
        }

        if (empty($availableDevices)) {
            $availableDevices[] = [
                'key' => 1,
                'serial' => '',
                'name' => 'Device #1',
            ];
        } ?>

        <div class="product-device--container">
            <div class="product--select-device">
                <label for="wc-select-device">Select/Add a Device</label>
                <select name="wc-select-device" class="iresq-select-input iresq-text-input">';
                    <?php foreach ($availableDevices as $device) { ?>
                        <option value="<?php echo $device['key']; ?>" data-serial-number="<?php echo $device['serial']; ?>"><?php echo $device['name']; ?></option>
                    <?php } ?>
                    <option value="<?php echo end($availableDevices)['key'] + 1; ?>">Add New Device</option>
                </select>
            </div>

            <div class="single_serial_number">
                <label for="wc-select-device">Serial #</label>
                <input type="text" id="wc-serial-number" class="iresq-text-input" name="wc-serial-number" value="<?php echo $availableDevices[0]['serial']; ?>" placeholder="Add the Serial #">
            </div>
        </div>
        <?php
    }
}

new Cart();
