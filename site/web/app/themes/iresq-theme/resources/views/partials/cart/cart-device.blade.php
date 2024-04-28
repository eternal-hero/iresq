@foreach ( $looped_items as $serial_number => $serial_number_group )
<div class="device-cart-container">
    <div class="device-details">
        <div>
            Item #{{sprintf('%03d', $loop->iteration)}} |
            @if($serial_number_group['item_product_name'])
            {{ $serial_number_group['item_product_name'] }}
            @endif
        </div>
    </div>
    @foreach ( $serial_number_group['items'] as $cart_item_key => $cart_item )
    @php
    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
    @endphp
    @if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) )
    @php $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key ); @endphp

    <div class="woocommerce-cart-form__cart-item device-part-container {!! esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ) !!}">

        <div class="product-remove">
            <?php
									echo apply_filters(
										'woocommerce_cart_item_remove_link',
										sprintf(
											'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
											esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
											esc_html__( 'Remove this item', 'woocommerce' ),
											esc_attr( $product_id ),
											esc_attr( $_product->get_sku() )
										),
										$cart_item_key
									);
								?>
        </div>

        <div class="product-thumbnail">
            @php $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key ); @endphp

            @if ( ! $product_permalink )
            {{ $thumbnail }}
            @else
            @php
            printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
            @endphp
            @endif
        </div>

        <div class="product-name" data-title="{!! esc_attr_e( 'Product', 'woocommerce' ) !!}" data-cart-item-id={{$cart_item_key}}>

            @if ( ! $product_permalink )
            {!! wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' ) !!}
            @else
            {!! wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) ) !!}
            @endif

            @php
            do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
            @endphp

            {{-- Meta data  --}}
            {!! wc_get_formatted_cart_item_data( $cart_item ) !!}

            {{-- Backorder notification. --}}
            @if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) )
            {!! wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) ) !!}
            @endif
        </div>

        <div class="product-price" data-title="{{ esc_attr_e( 'Price', 'woocommerce' ) }}">
            {!! apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ) !!}
        </div>

        <div class="product-quantity" data-title="{{ esc_attr_e( 'Quantity', 'woocommerce' ) }}">
            @if ( $_product->is_sold_individually() )
            @php
            $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
            @endphp
            @else
            @php
            $product_quantity = woocommerce_quantity_input(
            array(
            'input_name' => "cart[{$cart_item_key}][qty]",
            'input_value' => $cart_item['quantity'],
            'max_value' => $_product->get_max_purchase_quantity(),
            'min_value' => '0',
            'product_name' => $_product->get_name(),
            ),
            $_product,
            false
            );
            @endphp
            @endif

            {!! apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ) !!}
        </div>

        <div class="product-subtotal" data-title="{!! esc_attr_e( 'Subtotal', 'woocommerce' ) !!}">
            {!! apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) !!}
        </div>
    </div>
    @endif
    @endforeach

    <div class="serial-number-container">
        <div class="serial-number-label">
            <strong>Please enter your device serial number</strong>
        </div>
        <div class="serial-number-input">
            <input type="text" class="input-text" data-old-serial-number="{{ $serial_number_group['item_sn'] }}" value="{{ $serial_number_group['item_sn'] }}" placeholder="Serial Number">
        </div>
        <div class="serial-number-explanation">
            For some services, a serial number is required. If you don't provide it now we will contact you later to get it if we need it.
        </div>
    </div>
</div>
@endforeach
