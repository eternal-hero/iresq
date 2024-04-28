@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp

    {{-- components/form-content-left.blade.php --}}
    @include('components.form-content-left', [
      'id' => 'free-estimate-form-content-left',
      'form_section_heading' => get_field('form_section_heading'),
      'form_section_paragraph' => get_field('form_section_paragraph'),
      'add_list' => get_field('add_list'),
      'list' => get_field('list'),
      'form_heading' => get_field('form_heading'),
      'form_short_code' => get_field('form_short_code')
    ])

    {{-- components/image-content-right.blade.php --}}
    @include('image-content-right', [
      'id' => 'free-estimate-content-right',
      'image' => get_field('image_left'),
      'button' => get_field('add_right_button'),
      'link_right' => (get_field('add_right_button')) ? get_field('link_right') : '',
      'background' => get_field('background_right'), 
      'header' => get_field('header_right'),
      'paragraph' => get_field('paragraph_right')
    ])

  @endwhile
@endsection