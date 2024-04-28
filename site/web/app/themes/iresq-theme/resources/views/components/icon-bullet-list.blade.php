{{--
  Variables:
    string: $id
    string: $section_heading
    object: $list_items
    object: $section_link
--}}

@unless (!$list_items)

    <div class="icon-bullet-list-section" id="{{ $id }}">
        <div class="heading-wrapper">
            <h2>{!! $section_heading !!}</h2>
        </div>
        <div class="icons-wrapper">
            @foreach ($list_items as $list_item)
            <div class="list-item">
                <div class="icon">
                    @if($list_item['item_icon'])
                    <img loading="lazy" src="{{ esc_url($list_item['item_icon']['url']) }}" alt="{{ esc_attr($list_item['item_icon']['alt']) }}">
                    @else 
                    <div class="red-circle"></div>
                    @endif
                </div>    
                <div class="text-section">
                    <h5>{{ $list_item['item_heading'] }}</h5>
                    <p>{!! $list_item['item_paragraph'] !!}</p>
                </div>
            </div>
            @endforeach
        </div>

        @if($section_link)
        <div class="section-link">
            @include('components.iresq-button', [
                'id' => 'content-left-button',
                'link' => $section_link['url'],
                'type' => 'solid-dark',
                'target' => $section_link['target'],
                'text' => $section_link['title']
            ])
        </div>
        @endif

    </div>

@endunless