{{--
  Template Name: Customers Flexible Template
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post(); @endphp

    @if(have_rows('customers_flexible_template_blocks'))

      @php 
        $counter = 0;
      @endphp

      @while(have_rows('customers_flexible_template_blocks'))
        @php 
          the_row(); 

          //Make section ids
          $counter++;
          $id = get_row_layout() . '-' . get_the_id() . '-' . $counter; 
        @endphp

          @if(get_row_layout())
              @include('blocks.' . get_row_layout())
          @endif
        @endwhile
    @endif

  @endwhile
@endsection
