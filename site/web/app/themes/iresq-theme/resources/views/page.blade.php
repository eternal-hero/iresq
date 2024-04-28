{{-- Single page --}}
{{-- DEFAULT TEMPLATE --}}
@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp
  @if(!is_account_page())
    @if(!is_cart() && !is_checkout() && !get_field('use_minimal_header'))
    <div class="post-hero">
      <div class="post-hero-title">
        <h1 class="post-hero-large">{!! get_the_title() !!}</h1>
      </div>
      @php
        $hero_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
        $thumbnail_id = get_post_thumbnail_id( get_the_ID() );
        $alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);  
      @endphp
      @if( !empty( $hero_image ) )
      <div class="post-image">
        <img loading="lazy" src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr($alt); ?>" />
      </div>
      @else 
        <div class="no-post-image"></div>
      @endif
    </div>
    @else
    <div class="title-container">
      <h1 class="post-hero-large">{!! get_the_title() !!}</h1>
    </div>
    @endif
  @endif
  <div class="default-template-content-wrapper">
    @include('partials.content-page')
    @php $link=(get_field('link')) @endphp
    @if($link)
    @php
    $link_url = $link['url'];
    $link_title = $link['title'];
    $link_target = $link['target'] ? $link['target'] : '_self';
    @endphp
    <a href="<?php echo esc_url( $link_url ); ?>">
      <button style="margin-top:80px;" id="default-template-button" class="solid-red iresq-button aligncenter" target="<?php echo esc_attr( $link_target ); ?>"><?php echo esc_html( $link_title ); ?></button>
    </a>
    @endif
  </div>
  @endwhile
@endsection
