{{--
  Template Name: Customers Template
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    @include('components.secondary-hero', [
      'id' => 'customers-template-hero',
      'title' => get_field('secondary_hero_title'),
      'background' => get_field('secondary_hero_image')
      ])

    {{-- components/image-content-left.blade.php --}}
    @include('components.image-content-left', [
      'id' => 'template-customers-left',
      'image' => get_field('image_right'),
      'button' => get_field('add_left_button'), 
      'link_left' => (get_field('add_left_button')) ? get_field('link_left') : '',
      'background' => get_field('background_left'),
      'header' => get_field('header_left'),
      'paragraph' => get_field('paragraph_left')
    ])

    {{-- components/icon-bullet-list.blade.php --}}
    @include('components.icon-bullet-list', [
      'id' => 'template-customers-icon-bullet-list',
      'section_heading' => get_field('section_heading'),
      'list_items' => get_field('list_items'),
      'section_link' => get_field('section_link')
    ])

    {{-- components/image-content-right.blade.php --}}
    @include('components.image-content-right', [
      'id' => 'template-customers-right',
      'image' => get_field('image_left'),
      'button' => get_field('add_right_button'),
      'link_right' => (get_field('add_right_button')) ? get_field('link_right') : '',
      'background' => get_field('background_right'),
      'header' => get_field('header_right'),
      'paragraph' => get_field('paragraph_right')
    ])

    {{-- components/text-blocks.blade.php --}}
    @include('components.text-blocks', [
      'id' => 'template-customers-text-blocks',
      'text_blocks' => get_field('text_blocks')
    ])

    {{-- components/form-content-left.blade.php --}}
    <a id="contact"></a>
    @include('components.form-content-left', [
      'id' => 'template-customers-form-content-left',
      'form_section_heading' => get_field('form_section_heading'),
      'form_section_paragraph' =>get_field('form_section_paragraph'),
      'add_list' => get_field('add_list'),
      'list' => (!empty(get_field('list'))) ? get_field('list') : [],
      'form_heading' => get_field('form_heading'),
      'form_short_code' => get_field('form_short_code')
    ])

  @endwhile
@endsection
