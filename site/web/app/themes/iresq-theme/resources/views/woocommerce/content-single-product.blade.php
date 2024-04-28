<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
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

$time = get_field('single_product_turnaround_time', $product->get_id() );
$complexity = get_field('single_product_complexity', $product->get_id() );

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );
if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>

@php 
  $alternate_layout = get_field('activate_alternate_product_category_layout', 'options');
@endphp

@if($alternate_layout)
  <div class="tw-container tw-px-4">
    @include('partials.page-header-product-category', [
        'include_sidebar' => false,
        'show_description' => false,
        'title' => get_the_title()
    ])
  </div>
@endif


<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

  <div class="repair-summary-section">
    
    <div class="repair-summary-wrapper">
      <div class="repair-summary">
        <h2 class="repair-summary-title">
          {!! the_field('single_product_title', 'option') !!}
        </h2>
        <p class="repair-summary-text">
          {!! the_field('single_product_description', 'option') !!}
        </p>
        @if($time)
          <div class="expectation-block turnaround-time">
            <div class="block-icon"></div>
            <h5 class="bold-text-light">Expected turnaround time</h5>
            <h6 class="light-text-dark">
                {!! $time !!}
            </h6>
          </div>
        @endif
        {{-- 03/09/2021 - Temporarily hiding the Repair Complexity until the client is ready --}}
        {{-- <div class="expectation-block repair-complexity">
          <div class="block-icon"></div>
          <h5 class="bold-text-light">Repair complexity</h5>
          <h6 class="light-text-dark">
            @if($complexity)
              {!! $complexity['label'] !!}
            @endif
          </h6>
        </div> --}}
      </div>
    </div>
    <div class="entry-summary-wrapper">
      <div class="summary entry-summary">
        <?php
          /**
           * Hook: woocommerce_single_product_summary.
           *
           * @hooked woocommerce_template_single_title - 5
           * @hooked woocommerce_template_single_rating - 10
           * @hooked woocommerce_template_single_price - 10
           * @hooked woocommerce_template_single_excerpt - 20
           * @hooked woocommerce_template_single_add_to_cart - 30
           * @hooked woocommerce_template_single_meta - 40
           * @hooked woocommerce_template_single_sharing - 50
           * @hooked WC_Structured_Data::generate_product_data() - 60
           */
          do_action( 'woocommerce_single_product_summary' );
        ?>
      </div>
    </div>
  </div>
</div>

