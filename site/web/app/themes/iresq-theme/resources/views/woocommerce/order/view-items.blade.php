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

$numbers        = explode('-', get_query_var('view-items'), 2);
$order_id       = $numbers[0];
$item_number    = $numbers[1];
$order          = wc_get_order( $order_id );
$item_number_subtotal = 0;

if ( ! $order ) {
  return;
}

$order_items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$order_client_po = get_post_meta( $order->get_id(), 'Client PO#', true );

/*
 *	Get the model of the device
 */
$item_model             = '';
$product_permalink      = '';
$item_serial_number     = '';
$item_status            = '';

// loop over all of the items and set the variables according to the item number
foreach ( $order_items as $item_id => $item ) {
  $tmp_item_details = wc_get_order_item_meta($item_id, 'device_item_details');
  $tmp_item_number = $tmp_item_details['item_number'];

	if ( $tmp_item_number === $item_number ) {
    $item_status            = $tmp_item_details['item_status'];
		$product                = $item->get_product();
    $product_id             = $item->get_product_id();
    
    $repair_type 			 	    = [];
    $terms = get_the_terms( $product_id, 'repairs' );
    if($terms) {
      foreach(get_the_terms( $product_id, 'repairs' ) as $repair_id => $repair) {
        array_push($repair_type, $repair->name);
      }
      $repair_type = implode(', ', $repair_type);
    }

    $item_serial_number 		= wc_get_order_item_meta($item_id, 'device_serial_number');
    $product_attributes     = $product->get_attributes();
    $is_visible             = $product && $product->is_visible();
    $product_permalink      = apply_filters( 
                              'woocommerce_order_item_permalink',
                              $is_visible ? $product->get_permalink( $item ) : '', 
                              $item,
                              $order 
                            );

    if ( $product_attributes ) {
        foreach ( $product_attributes as $attr_id => $attr ) {
            if ( $attr->is_taxonomy() ) {
                if ( $attr_id === 'pa_brand' ) {
                    continue;
                } else {
                    $item_model = $product->get_attribute($attr_id);
                }
            }
        }
    }
    break;
	} else {
    // skip over this item as it has a different item number
    continue;
	}
}

/**
 * This array stores the status values to display to the customer in their account page.
 * FileMaker has more status values than we want the customer to see, so each displayed
 * value also contains an array of mapped values. Now we can loop through and only display
 * `Item received` if the status is either `quote` or `process`.
 */
$website_statuses = array(
  'Item Received' => array('quote', 'process'),
  'Item being repaired/diagnosed' => array('on hold'),
  'Item diagnosed/Item repaired' => array('cust pickup', 'accounting', 'posted'),
  'Device shipped back to you!' => array('shipping')
);
?>

<section class="woocommerce-order-details">
  @php do_action( 'woocommerce_order_details_before_order_table', $order ); @endphp

  <h3 class="woocommerce-order-details__account">{{ esc_html_e('Account #' . $order->get_user_id(), 'woocommerce' ) }}</h3>
  <h3 class="woocommerce-order-details__title">{{ esc_html_e('Order #' . $order->get_order_number(), 'woocommerce' ) }}</h3>
  <p class="woocommerce-order-details__order-date">
    {{ esc_html_e( 'placed ' . $order->get_date_created()->format( 'm/d/Y' ), 'woocommerce' ) }}
    <strong>@php !empty($order_client_po) ? esc_html_e( ' PO #' . $order_client_po, 'woocommerce' ) : '' @endphp</strong>
  </p>

  <h5 class="woocommerce-order-details__item-status">{{ esc_html_e( 'Status', 'woocommerce' ) }}</h5>

  {{-- Loop through the website status values and check if the current status matches any mapped values to set the active status --}}
  <ul class="woocommerce-item-status-wrapper">
    @foreach ($website_statuses as $status => $mapped_statuses)
      @if(in_array(strtolower($item_status), $mapped_statuses))
        <li class="item-status-step active-step">{!! $status !!}</li>
      @else
        <li class="item-status-step">{!! $status !!}</li>
      @endif
    @endforeach
  </ul>

  <h5 class="woocommerce-order-details__item-number">
    @php
      echo apply_filters( 'woocommerce_order_item_name', 
          sprintf( '<span>Item #%s | <a href="%s"><span class="model">%s<span class="serial"> %s</span></span</a></span>', $item_number, $product_permalink, $item_model, $item_serial_number ) 
        ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    @endphp
  </h5>
  <h5 class="woocommerce-order-details__item-title"><?php esc_html_e( 'Products/Service', 'woocommerce' ); ?></h5>

  <table class="woocommerce-table woocommerce-table--item-details shop_table item_details">
    <tbody>
      <?php 
        do_action( 'woocommerce_order_details_before_order_table_items', $order );

        foreach ( $order_items as $item_id => $item ) {
          $order_item_details = wc_get_order_item_meta($item_id, 'device_item_details');
          $order_item_status = $order_item_details['item_status'];
          $order_item_number = $order_item_details['item_number'];

          if ($order_item_number == $item_number ) {
            $product              = $item->get_product();
            $product_id           = $item->get_product_id();
            $serial_number        = wc_get_order_item_meta($item_id, 'device_serial_number');
            $terms = get_the_terms( $product_id, 'repairs' );
            if($terms) {
              foreach(get_the_terms( $product_id, 'repairs' ) as $repair_id => $repair) {
                array_push($repair_type, $repair->name);
              }
              $repair_type = implode(', ', $repair_type);
            }
            $item_number_subtotal = $item_number_subtotal + $item->get_total();

            wc_get_template(
              'order/view-items-details.blade.php',
              array(
                'item'            => $item,
                'item_id'         => $item_id,
                'order'           => $order,
                'product'         => $product,
                'product_id'      => $product_id,
                'serial_number'   => $serial_number,
                'repair_type'     => $repair_type
              )
            );
          }
        }

        do_action( 'woocommerce_order_details_after_order_table_items', $order ); 
      ?>
    </tbody>

    <tfoot>
      <tr>
        <th scope="row"><?php echo esc_html( 'Subtotal' ); ?></th>
        <td><?php echo wc_price($item_number_subtotal); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
      </tr>
		</tfoot>
  </table>
  @php do_action( 'woocommerce_order_details_after_order_table', $order ); @endphp
</section>


