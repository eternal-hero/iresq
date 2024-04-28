{{--
  string: $label
  string: $name
  string: $fa_icon
  string: $id
  array: $options
--}}
<div class="iresq-select-input">
  @unless (empty($label))
    <label class="select-label" for="{{ $name }}">{!! $label !!}</label>
  @endunless
  <i class="select-icon {{ $fa_icon }}"></i>
  <div class="select-wrapper">
    <select class="select-input" name="{{ $name }}" id="{{ $id }}">
      @foreach ($options as $choice)
        <option @if($loop->first) value="" @else value="{{ $choice }}" @endif>{{ $choice }}</option>
      @endforeach
    </select>
  </div>
</div>
