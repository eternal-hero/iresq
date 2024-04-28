{{-- Archive page (used by blog page, category archives, author archives, etc.) --}}
@extends('layouts.app')

@section('content')

  @if (!have_posts())
    <div class="alert alert-warning">
      {{ __('Sorry, no results were found.', 'sage') }}
    </div>
    {!! get_search_form(false) !!}
  @endif

  @include('components.secondary-hero', [
    'id' => 'expert-advice-hero',
    'background' => get_field('secondary_hero_image'),
    'title' => get_field('secondary_hero_title')
  ])

  {{-- START INPUTS --}}
  <form action="" method="post" class="inline-input-group" id="blog-filter">

    @php 
    $repair_types = App::getSelectedOptions('repair_type', 'Select repair type')
    @endphp
    @include('components.iresq-select', [
      'label' => 'Filter by repair type',
      'name' => 'repair',
      'fa_icon' => 'fal fa-chevron-circle-down',
      'id' => 'expert-advice-repair-field',
      'options' => $repair_types,
    ])

    @php 
    $device_types = App::getSelectedOptions('device', 'Select device');
    @endphp
    @include('components.iresq-select', [
      'label' => 'Filter by device',
      'name' => 'device',
      'fa_icon' => 'fal fa-chevron-circle-down',
      'id' => 'expert-advice-device-field',
      'options' => $device_types
    ])

    @php 
    $content_types = App::getSelectedOptions('content_type', 'Select content type');
    @endphp
    @include('components.iresq-select', [
      'label' => 'Filter by content type',
      'name' => 'content',
      'fa_icon' => 'fal fa-chevron-circle-down',
      'id' => 'expert-advice-content-field',
      'options' => $content_types
    ])

    <button class="blog-filter-button" type="submit" name="submit" value="Submit"><i class="fal fa-search"></i></button>
  </form>
  {{-- END INPUTS --}}
  @php
    $repairSearch = "";
    $deviceSearch = "";
    $contentSearch = "";

    if(isset($_POST['submit'])){
      $repairSearch = $_POST['repair'];
      $deviceSearch = $_POST['device'];
      $contentSearch = $_POST['content'];
    }

    $filtered_posts = App::blogQuery($repairSearch, $deviceSearch, $contentSearch);

    Global $wp_query;
    $temp = $wp_query;
    $wp_query = null;
    $wp_query = $filtered_posts;
  @endphp
    @if ($wp_query->have_posts()) 
      <div class="blog-posts-wrapper"> 
        @while ($wp_query->have_posts()) @php $wp_query->the_post(); @endphp
          @include('partials.content-'.get_post_type())
        @endwhile
      </div>
      <nav>
        @php
        the_posts_pagination(array(
          'mid_size'  => 2,
          'prev_text' => __('Previous', 'iresq'),
          'next_text' => __('Next', 'iresq'),
        ));
        @endphp
      </nav>
    @else
      <h2 class="no-posts">
        Sorry, no results were found for your search. <span>Please try a different search or browse the posts below.</span>
      </h2>
      <hr class="red-gradient">
      <div class="blog-posts-wrapper">
        @while (have_posts()) @php the_post() @endphp
          @include('partials.content-'.get_post_type())
        @endwhile
      </div>
    @endif
  @php 
    $wp_query = null;
    $wp_query = $temp;
    wp_reset_postdata();
  @endphp
@endsection

