@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp

  @include('components.homepage-hero', [
    'id' => 'frontpage-hero',
    'homepage_hero_type' => get_field('homepage_hero_type'),
    'homepage_hero_slides' => get_field('homepage_hero_slides'),
    'homepage_hero_video' => get_field('homepage_hero_video'),
    'background' => get_field('homepage_hero_image'),
    'title' => get_field('homepage_hero_title'),
    'description' => get_field('homepage_hero_description'),
    'left_link' => get_field('homepage_hero_left_link'),
    'right_link' => get_field('homepage_hero_right_link')
  ])

  @include('components.hero-button-group', [
    'id' => 'hero-button-group',
    'hero_button_group_title' => get_field('hero_button_group_title'),
    'hero_button_group_buttons' => get_field('hero_button_group_buttons')
  ])

  @include('components.tabbed-hero', [
    'id' => 'frontpage-tabbed-hero',
    'heroes' => get_field('tabbed_heroes')
  ])

  @include('components.testimonial-slider',[
    'id' => 'frontpage-testimonial-slider',
    'testimonial_slider' => get_field('testimonial_slider')
  ])

  @include('components.image-content-right', [
    'id' => 'front-page-right',
    'image' => get_field('image_left'),
    'button' => get_field('add_right_button'),
    'link_right' => (get_field('add_right_button')) ? get_field('link_right') : '',
    'background' => get_field('background_right'),
    'header' => get_field('header_right'),
    'paragraph' => get_field('paragraph_right')
  ])

    {{-- components/horizontal-text-accordion.blade.php --}}
    @include('components.horizontal-text-accordion', [
      'id' => 'front-page-horizontal-text-accordion',
      'size' => 'large',
      'section_heading' => get_field('section_heading'),
      'text_accordions' => get_field('text_accordions')
    ])

    {{-- components/image-content-left.blade.php --}}
    @include('components.image-content-left', [
      'id' => 'front-page-left',  // String: unique id
      'image' => get_field('image_right'),  // Object: ACF image
      'button' => get_field('add_left_button'), // Boolean: ACF field determining whether or not to add a button link
      'link_left' => (get_field('add_left_button')) ? get_field('link_left') : '',  // Object: ACF link (dependent upon the value of $add_left_button)
      'background' => get_field('background_left'),  // Boolean: ACF field determining the background color (default is red)
      'header' => get_field('header_left'),
      'paragraph' => get_field('paragraph_left')
    ])

    {{-- components/text-only.blade.php --}}
    @include('components.text-only', [
      'id' => 'front-page-text-only',
      'text' => $text
    ])

    {{-- components/card-tiles.blade.php --}}
    @include('components.card-tiles', [
      'id' => 'front-page-card-tiles',
      'type' => 'vertical',
      'tiles' => get_field('tiles')
    ])

    {{-- components/banner-cta.blade.php --}}
    @include('components.banner-cta', [
      'id' => 'front-page-banner-cta',
      'text' => get_field('banner_cta_text'),
      'background' => get_field('banner_cta_background'),
      'link' => get_field('banner_cta_link')
    ])

  @endwhile
@endsection
