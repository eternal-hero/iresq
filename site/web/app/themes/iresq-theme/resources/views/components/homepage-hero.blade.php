{{--
  string: $id
  string: $background
  string: $title
  string: $description
  repeater: $homepage_hero_slides
  object: $left_link
  object: $right_link
  select: $homepage_hero_type (slider, image, video)
  string: $homepage_hero_video
--}}

@unless(!$title)

@if($homepage_hero_type === 'slider')
<div class="homepage-hero-slider">
    @php $index = 0; @endphp
    @while(have_rows('homepage_hero_slides'))
    @php the_row(); @endphp
    <div class="content-container">
        <div class="background-slide-image" style="background-image:url('{{ get_sub_field('background_image') }}');"></div>
        <div class="background-image-overlay"></div>
        @if($index == 0)
          <h1 class="title">{{ get_sub_field('heading') }}</h1>
        @else
          <h2 class="title">{{ get_sub_field('heading') }}</h2>
        @endif

        @if (!empty($slide) && !empty($slide['description']))
            <div class="description">{!! $slide['description'] !!}</div>
        @endif

        @if(!empty($slide))
            <div class="link-container">
                @if(!empty($slide['left_link']))
                <span class="button-left">
                    @include('components.iresq-button', [
                        'id' => 'content-left-button', 
                        'link' => $slide['left_link']['url'], 
                        'type' => 'solid-red', 
                        'target' => $slide['left_link']['target'],
                        'text' => $slide['left_link']['title']
                    ])
                </span>
                @endif

                @if(!empty($slide['right_link']))
                <span class="button-right">
                    @include('components.iresq-button', [
                        'id' => 'content-right-button', 
                        'link' => $slide['right_link']['url'], 
                        'type' => 'solid-red', 
                        'target' => $slide['right_link']['target'],
                        'text' => $slide['right_link']['title']
                    ])
                </span>
                @endif
            </div>
        @endif
    </div>
        @php $index++; @endphp
    @endwhile
</div>
@else
<div class="homepage-hero {{$homepage_hero_type}}" id={{ $id }} @if($homepage_hero_type==='image' )style="background-image: url({{ $background }})" @endif>
    @if($homepage_hero_type === 'video')
    <div class="video-container">
        {!! $homepage_hero_video !!}
    </div>
    @endif
    <div class="content-container">
        <h1 class="title">{{ $title }}</h1>
        <div class="description">{!! $description !!}</div>
        <div class="link-container">

            @if(!empty($left_link))
            <span class="button-left">
                @include('components.iresq-button', [
                    'id' => 'content-left-button', 
                    'link' => $left_link['url'], 
                    'type' => 'solid-red', 
                    'target' => $left_link['target'],
                    'text' => $left_link['title']
                ])
            </span>
            @endif

            @if(!empty($right_link))
            <span class="button-right">
                @include('components.iresq-button', [
                    'id' => 'content-right-button', 
                    'link' => $right_link['url'], 
                    'type' => 'solid-red', 
                    'target' => $right_link['target'],
                    'text' => $right_link['title']
                ])
            </span>
            @endif

        </div>
    </div>
</div>
@endif
@endunless
