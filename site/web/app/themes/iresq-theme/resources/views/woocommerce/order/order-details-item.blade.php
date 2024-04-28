<?php
/**
 * Order Item Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details-item.php.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
{{-- TODO 12/3 IF ITEMS COUNT IS GREATER THAN 1 --}}
@foreach ($items as $item_id => $item)
	@if( $loop->iteration == 1)
		@if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) )
			@php return; @endphp
		@else
			@php
				$product = $item->get_product();
				$product_id = $item->get_product_id();
			@endphp
			<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?>">

				<td class="woocommerce-table__product-name product-name">
					<?php
					
					$model 						 	= '';
					$repair_type 			 	= [];
					$terms = get_the_terms( $product_id, 'repairs' );
					if($terms) {
						foreach(get_the_terms( $product_id, 'repairs' ) as $repair_id => $repair) {
							array_push($repair_type, $repair->name);
						}
						$repair_type = implode(', ', $repair_type);
					}
					$item_details 		 	= wc_get_order_item_meta($item_id, 'device_item_details');
					$item_number 				= $item_details['item_number'];
					$serial_number 		 	= wc_get_order_item_meta($item_id, 'device_serial_number');
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
					

					// ORIGINAL: '<span>Item #%s | <a href="%s"><span class="model">%s<span class="serial"> %s</span></span</a></span>',
					// ORIGINAL 3rd param: get_item_url($item_number, $order_id)
					echo apply_filters( 'woocommerce_order_item_name', 
						sprintf( 
							'<span>Item #%s | <span class="model">%s<span class="serial"> %s</span></span></span>',
							$item_number,
							$model,
							$serial_number 
						)
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped


					do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

					wc_display_item_meta( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

					do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
					?>
				</td>

				<td class="woocommerce-table__product-total product-total">
					<?php echo wc_price($item_subtotal); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</td>
			</tr>
		@endif
	@else
		@php break; @endphp
	@endif
@endforeach