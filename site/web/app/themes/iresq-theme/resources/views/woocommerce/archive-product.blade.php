
{{--
	The Template for displaying product archives, including the main shop page which is a post type archive

	This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.

	HOWEVER, on occasion WooCommerce will need to update template files and you
	(the theme developer) will need to copy the new files to your theme to
	maintain compatibility. We try to do this as little as possible, but it does
	happen. When this occurs the version of the template file will be bumped and
	the readme will list any important changes.

	@see https://docs.woocommerce.com/document/template-structure/
	@package WooCommerce/Templates
	@version 3.4.0
--}}

@php if(!defined('ABSPATH')) { exit; } @endphp
@extends('layouts.app')

@section('content')

@php 

$alternate_layout = get_field('activate_alternate_product_category_layout', 'options');
$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); // get current term

if ($term) {
	$parent = get_term($term->parent, get_query_var('taxonomy') ); // get parent term
	$children = get_term_children($term->term_id, get_query_var('taxonomy')); // get children
}

@endphp

@if($alternate_layout && $children && (sizeof($children) > 0))
	<div class="tw-container tw-px-4 tw-pt-24 xl:tw-pt-0">
		<section class="tw-flex tw-flex-wrap tw-justify-between tw--mx-4">
			<div class="tw-w-full lg:tw-w-2/3 tw-px-4">
				@include('partials.content-product-category-parent')
			</div>
			<div class="tw-w-full lg:tw-w-1/4 tw-px-4">
				@include('partials.sidebar-product-category')
			</div>
		</section>
	</div>
@else
	@if($alternate_layout)
		<div class="tw-container tw-px-4">
			@include('partials.page-header-product-category', [
				'show_description' => false,
				'title' => $term->name
			])
		</div>
	@endif
	<section class="shop-listing">
		<div class="shop-listing-filters">
			@php
			if ( function_exists( 'aws_get_search_form' ) ) { aws_get_search_form(); }
			echo do_shortcode('[wcpf_filters id="182"]');
			@endphp
		</div>

		<div class="shop-listing-products">
			@if ( woocommerce_product_loop() )

				{{--
					Hook: woocommerce_before_shop_loop.
					
					@hooked woocommerce_output_all_notices - 10
					@hooked woocommerce_result_count - 20
					@hooked woocommerce_catalog_ordering - 30
				--}}

				@php woocommerce_product_loop_start(); @endphp

				@if ( wc_get_loop_prop( 'total' ) )
					@while ( have_posts() )
						@php
							the_post();
							do_action( 'woocommerce_shop_loop' );
							wc_get_template_part( 'content', 'product' );
						@endphp
					@endwhile
				@endif

				@php woocommerce_product_loop_end(); @endphp

				{{--
				Hook: woocommerce_after_shop_loop.
				
				@hooked woocommerce_pagination - 10
				--}}
				@php do_action( 'woocommerce_after_shop_loop' ) @endphp
			@else
				{{--
				Hook: woocommerce_no_products_found.
				
				@hooked wc_no_products_found - 10
				--}}
				@php do_action( 'woocommerce_no_products_found' ) @endphp
			@endif
		</div>

		{{--
		Hook: woocommerce_after_main_content.
		
		@hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		--}}
		@php 
			do_action( 'woocommerce_after_main_content' );
			do_action('get_sidebar', 'shop');
			do_action('get_footer', 'shop');
		@endphp
	</section>
@endif

@endsection
