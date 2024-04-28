@extends('layouts.app')

@section('content')
    @include('partials.inline-styles.team')
    @include('components.secondary-hero', [
        'id' => 'team-hero',
        'background' => '',
        'title' => get_field('iresq_team_page_header_title', 'options')
    ])

  @if (!have_posts())
    <div class="alert alert-warning">
      {{ __('Sorry, no results were found.', 'sage') }}
    </div>
    {!! get_search_form(false) !!}
  @endif
  <div class="container">
    <div class="team__posts-wrapper"> 
      @while (have_posts()) @php the_post() @endphp
          @include('partials.content-'.get_post_type())
      @endwhile
    </div>
  </div>

  {!! get_the_posts_navigation() !!}
@endsection
