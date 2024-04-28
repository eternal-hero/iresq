@extends('layouts.app')

@section('content')
    @include('components.secondary-hero', [
        'id' => 'expert-advice-hero',
        'background' => '',
        'title' => single_term_title("", false )
    ])

  @if (!have_posts())
    <div class="alert alert-warning">
      {{ __('Sorry, no results were found.', 'sage') }}
    </div>
    {!! get_search_form(false) !!}
  @endif
  <div class="blog-posts-wrapper"> 
    @while (have_posts()) @php the_post() @endphp
        @include('partials.content-'.get_post_type())
    @endwhile
  </div>

  {!! get_the_posts_navigation() !!}
@endsection
