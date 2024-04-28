{{--
  Variables:
    string: $id
    string: $form_section_heading
    string: $form_section_paragraph
    boolean: $add_list
    object: $list
    string: $form_heading
    string: $form_short_code
--}}
@unless(!$form_section_heading)
<div class="form-content-left-section" id="{{ $id }}">
    <div class="left-content">
        <h2>{{ $form_section_heading }}</h2>
        <div>{!! $form_section_paragraph !!}</div>
        @if($add_list)
            @unless (empty($list))
                <ul>
                @foreach($list as $item)
                    <li>{!! $item['list_item'] !!}</li>
                @endforeach
                </ul>
            @endunless
        @endif
    </div>
    <div class="right-form">
        <h5 class="form-heading">{!! $form_heading !!}</h5>
        <div class="form-body">{!! $form_short_code !!}</div>
    </div>
</div>
@endunless