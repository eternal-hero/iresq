{{--
  Template Name: Misc Template
--}}

@extends('layouts.app')

@section('content')
  @include('components.secondary-hero', [
    'id' => 'customers-template-hero',
    'title' => $secondary_hero_title,
    'background' => ''
    ])
  <div class="misc-content-wrapper {{ $embedded_form ? 'tw-max-w-6xl tw-mx-auto tw-px-4' : '' }}">
    {!! $misc_content !!}

    @if($embedded_form)
      <div class="embedded-form__wrapper tw-pb-8">
        {!! do_shortcode($embedded_form) !!}
      </div>
    @endif

    @if($bottom_content)
      <div class="template-misc__bottom-content tw-pb-8">
        {!! $bottom_content !!}
      </div>
    @endif
  </div>
@endsection