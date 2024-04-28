{{-- Single post page --}}
@extends('layouts.app')

@section('content')
  @include('partials.inline-styles.team')
  @while(have_posts()) @php the_post() @endphp
  <div class="single-team__wrapper">
  
    @php
      $hero_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
      $thumbnail_id = get_post_thumbnail_id( get_the_ID() );
      $alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);  
    @endphp
    @if( !empty( $hero_image ) )
      <img loading="lazy" class="single-team__main-image" src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr($alt); ?>" />
    @endif

    <h1 class="single-team__title">{!! get_the_title() !!}</h1>
    
    <div class="single-team__content">
      {!! get_the_content() !!}
    </div>

    <a href="{{ get_post_type_archive_link('iresq_team') }}" class="single-team__back">&lt; See Our Team</a>

  </div>
  @endwhile
@endsection
