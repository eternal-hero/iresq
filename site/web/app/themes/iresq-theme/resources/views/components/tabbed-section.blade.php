{{--
  string: $id
  string: $section_header
  object: $tab_content
--}}

@if($tab_content)
    <div class="tabbed-section" id="{{ $id }}">
        @if($section_header)
            <h2 class="tabbed-section-header">{{ $section_header }}</h2>
        @endif
        <div class="tabs-wrapper">
            <ul class="tab-nav">
                @foreach ($tab_content as $titles)
                    <li class="{{ ($loop->first) ? 'active' : '' }}" data-tab="{{$loop->iteration}}">{{ $titles->title }}</li>
                @endforeach
            </ul>
            
            @foreach ($tab_content as $content)
            <div class="tab-content {{ ($loop->first) ? 'active' : '' }}" id="tab-content-{{$loop->iteration}}">
                <div class="tab-content-left">
                    @if($content['image'])
                    <img 
                    src="{{ esc_url($content['image']['url']) }}"
                    alt="{{ esc_attr($content['image']['alt']) }}" 
                    class="tab-image"
                    loading="lazy"
                    >
                    @else 
                    <div class="tab-image"></div>
                    @endif
                </div>
                <div class="tab-content-right">
                    <div class="tab-content-right-paragraph">
                        {!! $content['paragraph'] !!}
                    </div>
                    @if($content['link'])
                        @include('components.iresq-button', [
                            'id' => 'content-right-button',
                            'link' => $content['link']['url'],
                            'type' => 'solid-red', 'target' => $content['link']['target'],
                            'text' => $content['link']['title']
                        ])
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $( document ).ready(function() {
            var navItem = $('ul.tab-nav > li');
            navItem.on('click', function() {
                $(navItem).removeClass('active');
                $(this).addClass('active');
                var tabNum = $(this).data('tab');
                $('.tab-content').removeClass('active');
                $('#tab-content-'+tabNum).addClass('active');
            });
        });
    </script>
@endif