{{--
  Variables:
    string: $id
    string: $form_left_heading
    string: $form_left_short_code
    string: $form_left_quote
    string: $form_left_author
--}}
@unless(!$form_left_heading)
<div class="form-quote-right-section" id="{{ $id }}">
    <div class="left-form">
        <h2 class="form-heading">{!! $form_left_heading !!}</h2>
        <div class="form-body">{!! $form_left_short_code !!}</div>
    </div>
    <div class="right-quote">
        @include('components.iresq-quote', [
            'id' => 'form-quote-right',
            'quote' => $form_left_quote,
            'author' => $form_left_author
        ])
    </div>
</div>
@endunless