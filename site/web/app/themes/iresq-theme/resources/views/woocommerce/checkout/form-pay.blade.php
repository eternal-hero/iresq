<?php
/**
 * Pay for order form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-pay.php.
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

$totals = $order->get_order_item_totals();
/**
 * Create the array of devices
 */
 $looped_items = array();
 foreach ($order->get_items() as $item_id => $item) {
	$product_id = $item->get_product_id();
	$product = wc_get_product($product_id);
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
	$subtotal = wc_get_order_item_meta($item_id, '_line_subtotal', true);
	$invoiceNumber = wc_get_order_item_meta($item_id, 'invoice_number', true);
	$invoiceNumber = $invoiceNumber == '' ? $invoiceNumber : substr($invoiceNumber, -3);
	$cart_item_sn = wc_get_order_item_meta($item_id, 'device_serial_number', true);

    if( !isset($looped_items[$invoiceNumber]) ) {
        $looped_items[$invoiceNumber] = array(
            'items'	=> array(
                $item_id => $item,
            ),
						'item_product_name' => $device_name,
            'item_subtotal'	=> $subtotal,
            'item_sn'				=> $cart_item_sn,
        );
    } else {
        $looped_items[$invoiceNumber]['items'][$item_id] = $item;
        $looped_items[$invoiceNumber]['item_subtotal'] += $subtotal;
    }
}

ksort($looped_items, SORT_NUMERIC);
?>
<form id="order_review" method="post">
	<input type="hidden" value="true" name="repayment_form" />
	<table class="shop_table">
		<thead>
			<tr>
				<th class="product-name" colspan="75%">{!! esc_html_e( 'Product', 'woocommerce' ) !!}</th>
				<th class="product-total" colspan="25%">{!! esc_html_e( 'Subtotal', 'woocommerce' ) !!}</th>
			</tr>
		</thead>
		<tbody>
			@foreach ( $looped_items as $invoiceNumber => $invoice_group)
				<tr class="item-by-serial-number">
					<td colspan="100%">
						Item #{{$invoiceNumber}} |
						@if($invoice_group['item_product_name'])
							{{ $invoice_group['item_product_name'] }} |
						@endif
						<span class="item-serial-number">{{$invoice_group['item_sn']}}</span>
					</td>
				</tr>
				@foreach ( $invoice_group['items'] as $cart_item_key => $cart_item )
					@php
						$itemSubtotal = wc_get_order_item_meta($cart_item_key, '_line_subtotal', true);
					@endphp
					<tr class="cart_item">
						<td class="product-name" colspan="75%">
							{{$cart_item->get_name()}}
						</td>
						<td class="product-total" colspan="25%">
							<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>{{$itemSubtotal}}</bdi></span>
						</td>
					</tr>
					@endforeach
				@endforeach
		</tbody>
		<tfoot>
			<?php if ( $totals ) : ?>
				<?php foreach ( $totals as $total ) : ?>
					<tr>
						<th scope="row" colspan="75%"><?php echo $total['label']; ?></th>
						<td class="product-total" colspan="25%"><?php echo $total['value']; ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tfoot>
	</table>

	<div id="payment">
		<?php if ( $order->needs_payment() ) : ?>
			<ul class="wc_payment_methods payment_methods methods">
				<?php
				if ( ! empty( $available_gateways ) ) {
					foreach ( $available_gateways as $gateway ) {
						wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
					}
				} else {
					echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters( 'woocommerce_no_available_payment_methods_message', esc_html__( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) ) . '</li>'; // @codingStandardsIgnoreLine
				}
				?>
			</ul>
		<?php endif; ?>
		<div class="form-row">
			<input type="hidden" name="woocommerce_pay" value="1" />

			<?php wc_get_template( 'checkout/terms.php' ); ?>

			<?php do_action( 'woocommerce_pay_order_before_submit' ); ?>

			<?php echo apply_filters( 'woocommerce_pay_order_button_html', '<button type="submit" class="button alt" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // @codingStandardsIgnoreLine ?>

			<?php do_action( 'woocommerce_pay_order_after_submit' ); ?>

			<?php wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' ); ?>
		</div>
	</div>
</form>
