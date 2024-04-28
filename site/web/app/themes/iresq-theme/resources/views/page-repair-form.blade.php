@extends('layouts.app')

@section('content')
<div id="vue-app">
  <section id="repair-form">
    <div class="form-card-wrapper device-wrapper">
      <h2 class="repair-form-title">Select Your Device</h2>
      <repair-form-page :categories='{!! PageRepairForm::getDevicesJson() !!}'></repair-form-page>

      @php 
        $repair_form_button = get_field('repair_form_button', 'options');
      @endphp

      @if($repair_form_button)
        <a 
          href="{{ $repair_form_button['url'] }}"
          target="{{ $repair_form_button['target'] }}" 
          title="{{ $repair_form_button['title'] }}" 
          class="tw-inline-block tw-mt-24 tw-font-primary tw-font-bold tw-text-lg tw-bg-primary tw-text-white hover:tw-bg-white hover:tw-text-primary tw-border-primary tw-border-2 tw-border-solid tw-rounded-full tw-px-6 tw-py-4">{{ $repair_form_button['title'] }}</a>
      @endif
    </div>
  </section>
</div>
@endsection
