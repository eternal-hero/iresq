@extends('layouts.app')

@section('content')
@while(have_posts()) @php the_post() @endphp

    @include('components.secondary-hero', [
      'id' => 'iresq-difference-hero',
      'title' => get_field('secondary_hero_title'),
      'background' => get_field('secondary_hero_image')
    ])

    {{-- components/image-content-right.blade.php --}}
    @include('components.image-content-right', [
      'id' => 'iresq-difference-right',  // String: unique id
      'image' => get_field('image_left'),  // Object: ACF image
      'button' => get_field('add_right_button'), // Boolean: ACF field determining whether or not to add a button link
      'link_right' => (get_field('add_right_button')) ? get_field('link_right') : '',  // Object: ACF link (dependent upon the value of $add_left_button)
      'background' => get_field('background_right'),  // Boolean: ACF field determining the background color (default is red)
      'header' => get_field('header_right'),
      'paragraph' => get_field('paragraph_right')
    ])

    {{-- components/star_rating.blade.php --}}
    @include('components.star-rating', [
      'id' => 'iresq-difference-star-rating',
      'large_text' => get_field('large_text'),
      'small_text' => get_field('small_text')
    ])

    {{-- components/image-content-left.blade.php --}}
    @include('components.image-content-left', [
      'id' => 'iresq-difference-left',  // String: unique id
      'image' => get_field('image_right'),  // Object: ACF image
      'button' => get_field('add_left_button'), // Boolean: ACF field determining whether or not to add a button link
      'link_left' => (get_field('add_left_button')) ? get_field('link_left') : '',  // Object: ACF link (dependent upon the value of $add_left_button)
      'background' => get_field('background_left'),  // Boolean: ACF field determining the background color (default is red)
      'header' => get_field('header_left'),
      'paragraph' => get_field('paragraph_left')
    ])

    @include('components.horizontal-text-accordion', [
      'id' => 'iresq-difference-horizontal-text-accordion',
      'size' => 'small',
      'section_heading' => get_field('section_heading'),
      'text_accordions' => get_field('text_accordions')
    ])

    {{-- components/banner-cta.blade.php --}}
    @include('components.banner-cta', [
      'id' => 'iresq-difference-banner-cta',
      'text' => get_field('banner_cta_text'),
      'background' => get_field('banner_cta_background'),
      'link' => get_field('banner_cta_link')
    ])

    {{-- components/horizontal-accordion.blade.php --}}
    @include('components.horizontal-accordion', [
      'accordion_images' => get_field('accordion_images'),
      'accordion_content' => get_field('accordion_content')
    ])

    @endwhile
@endsection