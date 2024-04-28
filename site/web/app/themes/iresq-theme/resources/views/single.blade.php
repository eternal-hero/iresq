{{-- Single post page --}}
@extends('layouts.app')

@section('content')

  @while(have_posts()) @php the_post() @endphp

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

    @php
      $repair_type = get_the_terms(get_the_ID(), 'repair_type');
      $device_type = get_the_terms(get_the_ID(), 'device');
      $content_type = get_the_terms(get_the_ID(), 'content_type');
    @endphp

    <div class="single-post-details">

      
      @if ($repair_type && $repair_type[0]->name) 
        {{$repair_type[0]->name}} /
      @endif
      @if ($device_type && $device_type[0]->name)
        {{$device_type[0]->name}} /
      @endif
      @if ($content_type && $content_type[0]->name)
        {{$content_type[0]->name}}
      @endif

    </div>

    @include('partials.content-single-'.get_post_type())
    @php
      $link = (object)array('url'  => '/shop/', 'target' => '_self', 'title' => 'Browse All Repairs') 
    @endphp

    {{-- components/banner-cta.blade.php --}}
    @include('components.banner-cta', [
      'id' => 'services-devices-banner-cta',
      'text' => empty(get_field('banner_cta_text')) ? "Still stumped? You're in good company. That's why we're here." : get_field('banner_cta_text'),
      'background' => empty(get_field('banner_cta_background')) ? home_url( '/app/uploads/2020/11/Group-17.svg' ) : get_field('banner_cta_background'),
      'link' => empty(get_field('banner_cta_link')) ? get_field('link') : get_field('banner_cta_link')
    ])

    <div class="back-container">
      <a href="/expert-advice/" class="back">Back to the expert advice catalog</a>
    </div>

  @endwhile
@endsection
