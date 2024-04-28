<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.8.0
 */

defined( 'ABSPATH' ) || exit;
/**
 * Create the array to order serial numbers in
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
	$cart_item_sn = isset($cart_item['device_serial_number']) ? $cart_item['device_serial_number'] : '';
	$cart_device_number = isset($cart_item['device_number']) ? $cart_item['device_number'] : '';

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

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<div class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
		<div class="cart-table-header">
			<div class="product-remove">&nbsp;</div>
			<div class="product-thumbnail">&nbsp;</div>
			<div class="product-name">{!! esc_html_e( 'Product', 'woocommerce' ) !!}</div>
			<div class="product-price">{!! esc_html_e( 'Price', 'woocommerce' ) !!}</div>
			<div class="product-quantity">{!! esc_html_e( 'Quantity', 'woocommerce' ) !!}</div>
			<div class="product-subtotal">{!! esc_html_e( 'Subtotal', 'woocommerce' ) !!}</div>
		</div>
		@php do_action( 'woocommerce_before_cart_contents' ); @endphp

		@include('partials.cart.cart-device', ['looped_items' => $looped_items])

		@php do_action( 'woocommerce_cart_contents' ); @endphp
					
		<div class="actions">
			<div>
			@if ( wc_coupons_enabled() )
				<div class="coupon">
					<label for="coupon_code">{!! esc_html_e( 'Coupon:', 'woocommerce' ) !!}</label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="{!! esc_attr_e( 'Coupon code', 'woocommerce' ) !!}" /> <button type="submit" class="button" name="apply_coupon" value="{!! esc_attr_e( 'Apply coupon', 'woocommerce' ) !!}">{!! esc_attr_e( 'Apply coupon', 'woocommerce' ) !!}</button>
					@php do_action( 'woocommerce_cart_coupon' ); @endphp
				</div>
			@endif
			</div>

			<div>
				<a href="/repair-form" class="cart_return-to-shop"><button type="button" class="iresq-button outlined-dark">Add another device</button></a>

				@php do_action( 'woocommerce_cart_actions' ); @endphp

				@php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); @endphp
			</div>
		</div>
		@php do_action( 'woocommerce_after_cart_contents' ); @endphp
	</div>
	@php do_action( 'woocommerce_after_cart_table' ); @endphp
</form>

@php do_action( 'woocommerce_before_cart_collaterals' ); @endphp

<div class="cart-collaterals">
	@php
		/**
		 * Cart collaterals hook.
		 *
		 * @hooked woocommerce_cross_sell_display
		 * @hooked woocommerce_cart_totals - 10
		 */
		do_action( 'woocommerce_cart_collaterals' );
	@endphp
</div>

@php do_action( 'woocommerce_after_cart' ); @endphp
