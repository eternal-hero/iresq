<?php
/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.6.0
 */

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if ( ! $order ) {
	return;
}

$order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
$downloads             = $order->get_downloadable_items();
$show_downloads        = $order->has_downloadable_item() && $order->is_download_permitted();
$status                = wc_get_order_status_name( $order->get_status() );
$order_client_po 			 = get_post_meta( $order->get_id(), 'Client PO#', true );
$returnLabels 				 = get_post_meta($order->get_id(), 'wf_easypost_return_labels', true);
$amountOwe 				 		 = floatval(get_post_meta($order->get_id(), 'cInvoiceTotal', true));
$actualTotal 					 = $order->get_subtotal() + $order->get_shipping_total() + $order->get_total_tax();
$amountPaid 					 = number_format($actualTotal - $amountOwe, 2);

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}

/*
 *	Create the array to rearrange the order structure.
 *	Order # > Device Item # > Item(s)#
 */
$looped_items = array();
foreach ( $order_items as $item_id => $item ) {
	$item_details = wc_get_order_item_meta($item_id, 'device_item_details');
	$item_number = isset($item_details['item_number']) ? $item_details['item_number'] : '';
	$item_subtotal = $item->get_subtotal();

	if ( !isset($looped_items[$item_number]) ) {
		$looped_items[$item_number] = array(
			'product_items'				=> array(
				$item_id	=> $item
			),
			'item_number_status'	=> 'Item Received',
			'item_subtotal' => $item_subtotal
		);
	} else {
		$looped_items[$item_number]['product_items'][$item_id] = $item;
		$looped_items[$item_number]['item_subtotal'] += $item_subtotal;
	}
}
?>
<section class="woocommerce-order-details" data-order_id={!!$order_id!!}>
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	@if( is_account_page() )
	<h3 class="woocommerce-order-details__account"><?php esc_html_e( 'Account #' . $order->get_user_id(), 'woocommerce' ); ?> </h3>
	@endif

  <h3 class="woocommerce-order-details__title"><?php esc_html_e( 'Order #' . $order->get_order_number(), 'woocommerce' ); ?></h3>
  <p class="woocommerce-order-details__order-date">
		<?php esc_html_e( 'placed ' . $order->get_date_created()->format( 'm/d/Y' ), 'woocommerce' ); ?>
		<strong><?php !empty($order_client_po) ? esc_html_e( ' PO #' . $order_client_po, 'woocommerce' ) : '' ?></strong>
	</p>

	@if($returnLabels)
		@php($label = $returnLabels[0])
		<p class="shipping-label-cta__wrapper" style="display:flex;flex-wrap:wrap;align-items:center;margin-bottom: 50px;"><i class="shipping-label-cta__icon fa-3x fal fa-print" style="color:#b2111e;margin-right:20px"></i><a class="iresq-button solid-red shipping-label-cta__btn" href="{{esc_attr($label['url'])}}">CLICK HERE to download your prepaid shipping label</a></p>
	@endif

  <h5 class="woocommerce-order-details__products-title"><?php esc_html_e( 'Click individual item for details', 'woocommerce' ); ?></h5>
	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
		<tbody>
			<?php
			do_action( 'woocommerce_order_details_before_order_table_items', $order );

			foreach ( $looped_items as $item_id => $item ) {
				wc_get_template(
					'order/order-details-item.blade.php',
					array(
						'order'              => $order,
						'order_id'					 => $order_id,
						'item_id'            => $item_id,
						'items'              => $item['product_items'],
						'item_subtotal'      => $item['item_subtotal'],
						'item_number_status' => $item['item_number_status']
					)
				);
			}
			do_action( 'woocommerce_order_details_after_order_table_items', $order );
			?>
		</tbody>

		<tfoot>
			<?php
			foreach ( $order->get_order_item_totals() as $key => $total ) {
				?>
					<tr>
						<th scope="row"><?php echo esc_html( $total['label'] ); ?></th>
						<td><?php echo ( 'payment_method' === $key ) ? esc_html( $total['value'] ) : wp_kses_post( $total['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
					</tr>
					<?php if ( $amountOwe != 0 && $key == 'payment_method' ) : ?>
						<tr>
							<th>Amount Previously Paid:</th>
							<td>-${{$amountPaid}}</td>
						</tr>
					<?php endif; ?>
					<?php
			}
			?>

			<?php if ( $order->get_customer_note() ) : ?>
				<tr>
					<th><?php esc_html_e( 'Description of issue:', 'woocommerce' ); ?></th>
					<td><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
				</tr>
			<?php endif; ?>
		</tfoot>
	</table>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section>
