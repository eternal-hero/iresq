<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create the array of devices
 */ 
 $looped_items = array();
foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	$product = $cart_item['data'];
	$product_attributes = $product->get_attributes();
	$device_brand = $product->get_attribute('pa_brand');
	$device_name = $device_brand;
	if ($product_attributes) {
			foreach ($product_attributes as $attr_index => $attr) {
					if ($attr->is_taxonomy()) {
							// we don't want the brand because that's already in the URL
							if ('pa_brand' === $attr_index) {
									continue;
							}
							// attach the model to the device name
							$device_name = $device_name.' '.$product->get_attribute($attr_index);
					}
			}
	}
	$quantity = $cart_item['quantity'];
	$price = WC()->cart->get_product_price( $product );
	$subtotal = $product->get_price() * $cart_item['quantity'];
	$cart_device_number = isset($cart_item['device_number']) ? $cart_item['device_number'] : '';;
	$cart_item_sn = isset($cart_item['device_serial_number']) ? $cart_item['device_serial_number'] : '';

    if( !isset($looped_items[$cart_device_number]) ) {
        $looped_items[$cart_device_number] = array(
            'items'	=> array(
                $cart_item_key => $cart_item,
            ),
						'item_product_name' => $device_name,
            'item_subtotal'	=> $subtotal,
            'item_sn'				=> $cart_item_sn,
        );
    } else {
        $looped_items[$cart_device_number]['items'][$cart_item_key] = $cart_item;
        $looped_items[$cart_device_number]['item_subtotal'] += $subtotal;
    }
}


do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="col2-set" id="customer_details">
			<div class="col-1">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>

			<div class="col-2">
				<h3 id="order_review_heading">Your device information</h3>
				<div class="device-info--container">
					<input type="hidden" name="order_device_count" value="{{ count($looped_items) }}" />
					@php($deviceOrderNumber = 1)
					@foreach ( $looped_items as $device_number => $serial_number_group)
						<p class="device-info--item-title">
							Item #{{sprintf('%03d', $loop->iteration)}} | 
							@if($serial_number_group['item_product_name'])
								{{ $serial_number_group['item_product_name'] }} | 
							@endif
							<span class="item-serial-number">{{$serial_number_group['item_sn']}}</span></p>
						<div class="col2-set">
							<div class="col-1">
								<p class="form-row notes">
									<label for="{{ $deviceOrderNumber }}_issue_description" class="">Description of issue&nbsp;<span class="optional">(optional)</span></label>
									<span class="woocommerce-input-wrapper">
										<textarea name="{{ $deviceOrderNumber }}_issue_description" class="input-text " id="{{ $deviceOrderNumber }}_issue_description" placeholder="Example - Broken screen and headphone jack not working properly." rows="3" cols="5"></textarea>
									</span>
								</p>
								<p class="form-row notes">
									<label for="{{ $deviceOrderNumber }}_device_story" class="">Device story&nbsp;<span class="optional">(optional)</span></label>
									<span class="woocommerce-input-wrapper">
										<textarea name="{{ $deviceOrderNumber }}_device_story" class="input-text " id="{{ $deviceOrderNumber }}_device_story" placeholder="Example - Fell into my bathtub prior to my getting in. Phew!" rows="3" cols="5"></textarea>
									</span>
								</p>
							</div>
							<div class="col-2">
								<p class="form-row device-pw form-row-wide">
									<label for="{{ $deviceOrderNumber }}_device_password" class="">Enter your passcode if your device has a passcode required for login&nbsp;<span class="optional">(optional)</span></label>
									<span class="woocommerce-input-wrapper password-input">
										<input type="password" class="input-text " name="{{ $deviceOrderNumber }}_device_password" id="{{ $deviceOrderNumber }}_device_password" placeholder="Enter passcode..." value=""><span class="show-password-input"></span>
									</span>
								</p>
								<p class="form-row checkout-disclaimer-fields form-row-wide">
									<label for="{{ $deviceOrderNumber }}_max_repair_cost" class="">For the fastest service possible, please provide the maximum amount we should proceed without contacting you. I understand my original credit/debit card will be used (We advice $150 for iPods, iPhones and Smartphones, and $250 for laptops)&nbsp;<span class="optional">(optional)</span></label>
									<span class="woocommerce-input-wrapper">
										<input type="text" class="input-text" name="{{ $deviceOrderNumber }}_max_repair_cost" id="{{ $deviceOrderNumber }}_max_repair_cost" placeholder="$100.00" value="" data-type="currency">
									</span>
								</p>
							</div>
						</div>
						@php($deviceOrderNumber++)
					@endforeach
				</div>
			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>
	
	<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
	
	<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>
	
	<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

	<div id="order_review" class="woocommerce-checkout-review-order">
		<?php do_action( 'woocommerce_checkout_order_review' ); ?>
	</div>

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
