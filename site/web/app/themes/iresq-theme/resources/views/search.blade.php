{{-- Search Results --}}
@extends('layouts.app')

@section('content')

    @if (!have_posts())
    <div class="no-results">
      <div class="alert alert-warning">
        <h3>{{ __('Sorry, no results were found.', 'sage') }}</h3>
      </div>
      {!! get_search_form(false) !!}
    </div>
    @endif
  @if(have_posts())
  @include('partials.page-header')
    <div class="search-container">
      @while(have_posts()) @php the_post() @endphp
        @include('partials.content-search')
      @endwhile
    </div>
  @endif
  {!! get_the_posts_navigation() !!}
@endsection
