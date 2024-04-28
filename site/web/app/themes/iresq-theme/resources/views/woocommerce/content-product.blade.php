<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
$product_id 						= $product->get_id();
$device_brand           = $product->get_attribute('pa_brand');
$type                   = get_the_terms( $product_id, 'product_cat' )[0]->name;
$thumbnail 							= $product->get_image();
$shortName = get_field('single_short_name', $product_id);
$turnaroundTime = get_field('single_product_turnaround_time', $product_id);
$description = $product->get_short_description()
		? $product->get_short_description()
		: $product->get_description();

$model = '';
$product_attributes = $product->get_attributes();
if ( $product_attributes ) {
		foreach ( $product_attributes as $attr_id => $attr ) {
				if ( $attr->is_taxonomy() ) {
						if ( $attr_id === 'pa_brand' ) {
								continue;
						} else {
								$model = $product->get_attribute($attr_id);
						}
				}
		}
}

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
        }

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( '', $product ); ?>>
	<div class="product-header-wrapper">
		<h6 class="product-brand">{{ $device_brand }} <span class="product-model">{!! $model !!}</span></h6>
	</div>
	<div class="product-body-wrapper">
		<div class="product-image-wrapper">
			{!! $thumbnail !!}
		</div>
		<div class="product-information-wrapper">
			<h3 class="product-repair-header">
				{!! $shortName !!}
			</h3>
			<p class="product-description">{!! limit_text($description, 40, $product->get_id()) !!}</p>
			@if($turnaroundTime)
				<p class="product-description">Expected turnaround time: {!! $turnaroundTime !!}</p>
			@endif
			<div class="product-price">{!! wc_price($product->get_price()) !!}</div>

			<div class="product-device--container">
				<div class="product--select-device">
						<label for="wc-select-device">Select/Add a Device</label>
						<select name="wc-select-device" class="iresq-select-input iresq-text-input">
								<?php foreach ($availableDevices as $device) { ?>
										<option value="<?php echo $device['key']; ?>" data-serial-number="<?php echo $device['serial']; ?>"><?php echo $device['name']; ?></option>
								<?php } ?>
								<option value="<?php echo (int)end($availableDevices)['key'] + 1; ?>">Add New Device</option>
						</select>
				</div>
				<div class="single_serial_number">
						<label for="wc-select-device">Serial #</label>
						<input type="text" id="wc-serial-number" class="iresq-text-input" name="wc-serial-number" value="<?php echo $availableDevices[0]['serial']; ?>" placeholder="Add the Serial #">
				</div>
		    </div>

			<div class="product-button-wrapper">
			<?php
				/**
				 * Hook: woocommerce_after_shop_loop_item.
				 *
				 * @hooked woocommerce_template_loop_product_link_close - 5
				 * @hooked woocommerce_template_loop_add_to_cart - 10
				 */
				do_action( 'woocommerce_after_shop_loop_item' );
			?>
			<a href="/product/{{ $product->get_slug() }}" class="product-link-button">Learn more</a>
			</div>
		</div>
	</div>
</li>
