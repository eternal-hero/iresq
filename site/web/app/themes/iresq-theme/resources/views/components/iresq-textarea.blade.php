{{--
  string: $label 
  string: $name
  string: $placeholder
  string: $id
  string: $rows
--}}
<div class="iresq-textarea-input">
  @unless (empty($label))
    <label class="textarea-label" for="{{ $name }}">{!! $label !!}</label>
  @endunless
  <textarea 
    class="textarea-input"
    name="{{ $name }}"
    id="{{ $id }}"
    placeholder="{{ $placeholder }}"
    rows="{{ $rows }}"
  ></textarea>
</div>