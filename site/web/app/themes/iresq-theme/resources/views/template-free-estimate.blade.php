{{--
Template Name: Free Estimate Form
--}}
@extends('layouts.app')

@section('content')
<div id="vue-app">
  <section id="repair-form">
    {{-- <repair-form-steps model-value="model" class="tw-px-4"></repair-form-steps> --}}

    <div class="tw-flex tw-flex-wrap tw--mx-5 tw-items-center tw-px-5">
      <div class="tw-w-full lg:tw-w-5/12 tw-px-5 lg:tw-px-24 tw-hidden lg:tw-block">
        {!! the_content() !!}
      </div>

      <div class="tw-w-full lg:tw-w-7/12 tw-px-5">
        <div class="tw-text-left">
          <h3>{!! get_field('heading') !!}</h3>
          {!! do_shortcode(get_field('form_shortcode')) !!}
        </div>
        <div class="tw-text-center tw-pt-5">
          <div style="font-size:12px; margin-bottom: 5px; margin-top: 5px; line-height: 1.3; font-weight: normal;">
            {!! get_field('disclaimer') !!}
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<style>
  .wpforms-form .wpforms-submit-container {
    text-align: left;
  }

  .wpforms-container .wpforms-field-label-inline {
    margin-left: 0px;
  }

  .wpforms-field-checkbox ul li {
    display: flex;
    align-items: center;
  }

  .wpforms-field-checkbox ul li {
    margin-top: 0px;
    margin-bottom: 10px;
  }

  .wpforms-form .wpforms-submit {
    width: 100%;
  }

  /*
 * Default WP Alignment Classes
 *****************************************************************************/

  .aligncenter,
  .alignleft,
  .alignright {
    display: block;
    padding: 0;
  }

  .aligncenter {
    float: none;
    margin: .5em auto 1em;
  }

  .alignright {
    float: right;
    margin: .5em 0 1em 1em;
  }

  .alignleft {
    float: left;
    margin: .5em 1em 1em 0;
  }

  .wp-caption {
    padding: 5px 0;
    border: 1px solid #555;
    background: #444;
    text-align: center;
  }

  .wp-caption img {
    display: inline;
  }

  .wp-caption p.wp-caption-text {
    margin: 5px 0 0;
    padding: 0;
    text-align: center;
    font-size: 75%;
    font-weight: 100;
    font-style: italic;
    color: #ddd;
  }
</style>
@endsection
