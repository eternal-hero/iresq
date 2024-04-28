{{--
  Template Name: Services
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    
    {{-- components/secondary-hero.blade.php --}}
    @include('components.secondary-hero', [
      'id' => 'services-devices-hero',
      'background' => get_field('secondary_hero_image'),
      'title' => get_field('secondary_hero_title')
      ])

    {{-- components/banner-cta.blade.php --}}
    @include('components.banner-cta', [
      'id' => 'services-devices-banner-cta',
      'text' => get_field('banner_cta_text'),
      'background' => get_field('banner_cta_background'),
      'link' => get_field('banner_cta_link')
    ])

    {{-- components/category-cards.blade.php --}}
    @include('components.category-cards')

    {{-- components/card-tiles.blade.php --}}
    @include('components.card-tiles', [
      'id' => 'services-devices-card-tiles',
      'type' => 'horizontal',
      'tiles' => get_field('tiles')
    ])

    {{-- components/image-content-right.blade.php --}}
    @include('components.image-content-right', [
      'id' => 'services-devices-right',  // String: unique id
      'image' => get_field('image_left'),  // Object: ACF image
      'button' => get_field('add_right_button'), // Boolean: ACF field determining whether or not to add a button link
      'link_right' => (get_field('add_right_button')) ? get_field('link_right') : '',  // Object: ACF link (dependent upon the value of $add_left_button)
      'background' => get_field('background_right'),  // Boolean: ACF field determining the background color (default is red)
      'header' => get_field('header_right'),
      'paragraph' => get_field('paragraph_right')
    ])

    {{-- components/tabbed-section.blade.php --}}
    @include('components.tabbed-section', [
      'id' => 'services-devices-tabbed-section',
      'section_header' => get_field('section_header'),
      'tab_content' => get_field('tab_content')
    ])

    {{-- components/image-content-left.blade.php --}}
    @include('components.image-content-left', [
      'id' => 'services-devices-left',  // String: unique id
      'image' => get_field('image_right'),  // Object: ACF image
      'button' => get_field('add_left_button'), // Boolean: ACF field determining whether or not to add a button link
      'link_left' => (get_field('add_left_button')) ? get_field('link_left') : '',  // Object: ACF link (dependent upon the value of $add_left_button)
      'background' => get_field('background_left'),  // Boolean: ACF field determining the background color (default is red)
      'header' => get_field('header_left'),
      'paragraph' => get_field('paragraph_left')
    ])

    {{-- components/image-content-right-secondary.blade.php --}}
    @include('components.image-content-right-secondary', [
      'id' => 'services-devices-right-secondary',  // String: unique id
      'image' => get_field('image_left_secondary'),  // Object: ACF image
      'button' => get_field('add_right_button_secondary'), // Boolean: ACF field determining whether or not to add a button link
      'link_right' => (get_field('add_right_button_secondary')) ? get_field('link_right_secondary') : '',  // Object: ACF link (dependent upon the value of $add_left_button)
      'background' => get_field('background_right_secondary'),  // Boolean: ACF field determining the background color (default is red)
      'header' => get_field('header_right'),
      'paragraph' => get_field('paragraph_right')
    ])

  @endwhile
@endsection
