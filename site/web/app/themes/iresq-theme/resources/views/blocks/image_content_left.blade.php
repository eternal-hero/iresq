@php
    $background  = get_sub_field('background_left');
    $header = get_sub_field('header_left');
    $paragraph = get_sub_field('paragraph_left');
    $button = get_sub_field('add_left_button');
    $link_left = get_sub_field('link_left');
    $image = get_sub_field('image_right');
@endphp

@unless(empty($image))
<div class="image-content-left {{ ($background) ? 'content-transparent' : '' }}" id={{ $id }}>
  <div class="content-left">
    <h2 class="content-header-left">{{ $header }}</h2>
    <span class="content-paragraph-left">{!! $paragraph !!}</span>

    {{-- only create the button container if the user has selected they want one shown--}}
    @if($button && !empty($link_left))
      <span class="content-button-left">
        @include('components.iresq-button', [
          'id' => 'content-left-button', 
          'link' => $link_left['url'], 
          'type' => 'solid-dark', 
          'target' => $link_left['target'],
          'text' => $link_left['title']
        ])
      </span>
    @endif
  </div>
  <img loading="lazy" src="{{ esc_url($image['url']) }}" alt="{{ esc_attr($image['alt']) }}" class="image-right">
</div>
@endunless