<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
	return;
}

?>

<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?>">
  <td class="woocommerce-table__product-name product-name">
    @php
      $is_visible        = $product && $product->is_visible();
      $product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

      echo apply_filters('woocommerce_order_item_name', 
                          $product_permalink 
                          ? 
                          sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) 
                          : 
                          $item->get_name(), $item, $is_visible 
                        ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

      $qty          = $item->get_quantity();
      $refunded_qty = $order->get_qty_refunded_for_item( $item_id );

      if ( $refunded_qty ) {
        $qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
      } else {
        $qty_display = esc_html( $qty );
      }

      echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', $qty_display ) . '</strong>', $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		@endphp
  </td>

  <td class="woocommerce-table__product-total product-total">
		<?php echo $order->get_formatted_line_subtotal( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</td>
</tr>
