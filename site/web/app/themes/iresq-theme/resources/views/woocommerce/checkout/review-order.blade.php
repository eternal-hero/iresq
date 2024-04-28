<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

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
?>
<table class="shop_table woocommerce-checkout-review-order-table">
	<thead>
		<tr>
			<th class="product-name">{!! esc_html_e( 'Product', 'woocommerce' ) !!}</th>
			<th class="product-total">{!! esc_html_e( 'Subtotal', 'woocommerce' ) !!}</th>
		</tr>
	</thead>
	<tbody>
		@php do_action( 'woocommerce_review_order_before_cart_contents' ); @endphp

		@foreach ( $looped_items as $serial_number => $serial_number_group)
		<tr class="item-by-serial-number">
			<td>
				Item #{{sprintf('%03d', $loop->iteration)}} |
				@if($serial_number_group['item_product_name'])
					{{ $serial_number_group['item_product_name'] }} | 
				@endif
				<span class="item-serial-number">{{$serial_number_group['item_sn']}}</span>
			</td>
		</tr>
		@foreach ( $serial_number_group['items'] as $cart_item_key => $cart_item )
		@php
		$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		@endphp

		@if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) )
		<tr class="{!! esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ) !!}">
			<td class="product-name" data-cart-item-id="{{$cart_item_key}}">
				{{-- phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --}}
				{!! apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' !!}

				{{-- phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --}}
				{!! apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ) !!}

				{{-- phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --}}
				{!! wc_get_formatted_cart_item_data( $cart_item ) !!}
			</td>
			<td class="product-total">
				{{-- phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --}}
				{!! apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) !!}
			</td>
		</tr>
		@endif
		@endforeach
		@endforeach

		@php do_action( 'woocommerce_review_order_after_cart_contents' ); @endphp
	</tbody>
	<tfoot>

		<tr class="cart-subtotal">
			<th>{!! esc_html_e( 'Subtotal', 'woocommerce' ) !!}</th>
			<td>{!! wc_cart_totals_subtotal_html() !!}</td>
		</tr>

		@foreach ( WC()->cart->get_coupons() as $code => $coupon )
		<tr class="cart-discount coupon-{!! esc_attr( sanitize_title( $code ) ) !!}">
			<th>{!! wc_cart_totals_coupon_label( $coupon ) !!}</th>
			<td>{!! wc_cart_totals_coupon_html( $coupon ) !!}</td>
		</tr>
		@endforeach

		@if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() )

		@php do_action( 'woocommerce_review_order_before_shipping' ); @endphp

		@php wc_cart_totals_shipping_html(); @endphp

		@php do_action( 'woocommerce_review_order_after_shipping' ); @endphp

		@endif

		@foreach ( WC()->cart->get_fees() as $fee )
		<tr class="fee">
			<th>{!! esc_html( $fee->name ) !!}</th>
			<td>{!! wc_cart_totals_fee_html( $fee ) !!}</td>
		</tr>
		@endforeach

		@if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() )
		@if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) )
		{{--  phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited --}}
		@foreach ( WC()->cart->get_tax_totals() as $code => $tax )
		<tr class="tax-rate tax-rate-{!! esc_attr( sanitize_title( $code ) ) !!}">
			<th>{!! esc_html( $tax->label ) !!}</th>
			<td>{!! wp_kses_post( $tax->formatted_amount ) !!}</td>
		</tr>
		@endforeach
		@else
		<tr class="tax-total">
			<th>{!! esc_html( WC()->countries->tax_or_vat() ) !!}</th>
			<td>{!! wc_cart_totals_taxes_total_html() !!}</td>
		</tr>
		@endif
		@endif

		@php do_action( 'woocommerce_review_order_before_order_total' ) @endphp

		<tr class="order-total">
			<th>{!! esc_html_e( 'Total', 'woocommerce' ) !!}</th>
			<td>{!! wc_cart_totals_order_total_html() !!}</td>
		</tr>

		@php do_action( 'woocommerce_review_order_after_order_total' ); @endphp

	</tfoot>
</table>