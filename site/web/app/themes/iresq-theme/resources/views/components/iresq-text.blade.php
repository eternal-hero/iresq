{{--
  string: $label 
  string: $name
  string: $fa_icon
  string: $id
--}}
<div class="iresq-text-input">
  @unless(empty($label))
    <label class="text-label" for="{{ $name }}">{{ $label }}</label>
  @endunless
  <div class="text-input-wrapper">
    <i class="text-icon {{ $fa_icon }}"></i>
    <input class="text-input" type="text" name="{{ $name }}" id="{{ $id }}">
  </div>
</div>