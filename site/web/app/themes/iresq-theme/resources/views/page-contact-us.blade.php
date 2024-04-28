@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp

    {{-- components/secondary-hero.blade.php --}}
    @include('components.secondary-hero', [
        'id' => 'contact-us-hero',
        'title' => get_field('secondary_hero_title'),
        'background' => get_field('secondary_hero_image')
    ])

    {{-- components/form-content-left.blade.php --}}
    @include('components.form-content-left', [
      'id' => 'contact-us-form-content-left',
      'form_section_heading' => get_field('form_section_heading'),
      'form_section_paragraph' => get_field('form_section_paragraph'),
      'add_list' => get_field('add_list'),
      'list' => get_field('list'),
      'form_heading' => get_field('form_heading'),
      'form_short_code' => get_field('form_short_code')
    ])

  @endwhile
@endsection