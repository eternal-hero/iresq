{{--
  Template Name: Find a Claim Template
--}}

@php
$claimNo = isset($_POST['claimNo']) ? $_POST['claimNo'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$zip = isset($_POST['email']) ? $_POST['zip'] : '';
$formSubmitted = isset($_POST['submitted']) ? $_POST['submitted'] : false;
$noResultsFound = false;
if($formSubmitted) {
  $claim = new App\Filemaker\Claim();
  $filemakerClaim = $claim->fetchFirstInvoiceRecordByClaimNo($claimNo, $email, $zip);
  if(is_null($filemakerClaim)) {
    $noResultsFound = true;
    $formSubmitted = false;
  } else {
    $lineItems = $filemakerClaim->field('LINEITEMS');
    $partItems = $filemakerClaim->field('PARTITEMS');
    $billAddress = $filemakerClaim->field('BILLADDRESS')->getFirstRecord();
  }
}
@endphp

@extends('layouts.app')

@section('content')

@if(!$formSubmitted)
@include('components.secondary-hero', [
'id' => 'title',
'title' => 'Find My Claim',
'background' => ''
])
<div class="misc-content-wrapper">
  <form method="POST" class="find-claim-form">
    <h5>Complete the form below to find further details about your claim.</h5>
    @if($noResultsFound)
    <p style="color: #cc4712;">
      No claims were found with this information. Please try again.
    </p>
    @endif
    <input type="hidden" value="true" name="submitted" />
    <div>
      <div class="field-container">
        <label for="claimNo">Claim Number/PO*</label>
        <input type="text" name="claimNo" id="claimNo" required value="{{ $claimNo }}" />
      </div>
      <div class="field-container">
        <label for="claimNo">Billing Email*</label>
        <input type="email" name="email" id="email" required value="{{ $email }}" />
      </div>
      <div class="field-container">
        <label for="claimNo">Billing ZIP Code*</label>
        <input type="text" name="zip" id="zip" required value="{{ $zip }}" />
      </div>
      <div>
        *Note: All fields are required for submission to find further information about your claim.
      </div>
      <div class="submit-container">
        <input type="submit" class="button" />
      </div>
    </div>
  </form>
</div>

@else
@include('secondary_hero', [
'id' => 'title',
'title' => 'Claim Details',
'background' => ''
])

<div class="misc-content-wrapper" style="max-width: 900px; margin-bottom: 50px;">
  <h6>Below are the up-to-date details for your claim. For additional information or questions, please reach out to us by phone at <a href="tel:1-888-447-3728">1-888-447-3728</a>.</h6>
  <div>
    <p style="margin-bottom:0;"><strong>Claim Date</strong></p>
    <p style="margin-top:0;">{{$filemakerClaim->field('Invoice Date')}}</p>
  </div>
  <div>
    <p style="margin-bottom:0;"><strong>Status</strong></p>
    <p style="margin-top:0;">{{$filemakerClaim->field('Major Status')}}</p>
  </div>
  <div>
    <p style="margin-bottom:0;"><strong>Claim Total</strong></p>
    <p style="margin-top:0;">${{number_format($filemakerClaim->field('cInvoiceTotal'),2)}}</p>
  </div>

  <div>
    <p style="margin-bottom:0;"><strong>Billing Address</strong></p>
    <p style="margin-top:0;">
      {{ $billAddress->field('Address 1', 'BILLADDRESS') }}<br>
      {{ $billAddress->field('City', 'BILLADDRESS') }}, {{ $billAddress->field('ST', 'BILLADDRESS') }} {{ $billAddress->field('Zip', 'BILLADDRESS') }}
    </p>
  </div>

  <div>
    <p style="margin-bottom:0;"><strong>Items in Claim/Order</strong></p>
    @foreach($partItems as $item)
    @php($arrayLineItems = $lineItems->toArray())
    @php($lineItemKey = array_search($item->field('Product SKU', 'PARTITEMS'), array_column($arrayLineItems, 'LINEITEMS::Product SKU')))
    @php($lineItem = $lineItems->toArray()[$lineItemKey])
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid black;margin-top:10px;">
      <div class="claim--line-item-product-container">
        <p style="margin-top:0;margin-bottom:0;font-size: 18px;">{{$item->field('Part Name', 'PARTITEMS')}}</p>
        <p style="margin-top:0;font-size: 12px;">SKU: {{$item->field('Product SKU', 'PARTITEMS')}}</p>
      </div>
      <div>
        <p style="font-size: 23px;">${{number_format($lineItem->field('Line Amt', 'LINEITEMS'),2)}}</p>
      </div>
    </div>
    @endforeach
  </div>
</div>
@endif

@endsection
