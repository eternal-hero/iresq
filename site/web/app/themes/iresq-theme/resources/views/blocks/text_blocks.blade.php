@php
    $text_blocks = get_sub_field('text_blocks');
@endphp
@unless (!$text_blocks)
<div class="text-blocks-section" id="{{ $id }}">
        @foreach($text_blocks as $block)
    @if($loop->first)
    <div class="blocks-wrapper count-{{$loop->count}}">
    @endif
        <div class="block">
            <h4 class="title">{!! $block['title'] !!}</h4>
            <p class="paragraph">{!! $block['paragraph'] !!}</p>
        </div>
        @if(($loop->count === 3) && ($loop->iteration % 3))
        <div class="vr-red"></div>
        @elseif(($loop->count === 2) && ($loop->first))
        <div class="vr-red two"></div>
        @endif
    @if($loop->last)
    </div>
    @endif
    @endforeach
</div>
@endunless