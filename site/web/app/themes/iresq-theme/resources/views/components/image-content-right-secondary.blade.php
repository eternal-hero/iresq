{{--
  Variables:
    boolean: $background 
    string: $id
    string: $header
    string: $paragraph
    boolean: $button
    object: $link_left
    object: $image
--}}
@unless(empty($image))
<div class="image-content-right-secondary {{ ($background) ? 'content-transparent' : '' }}" id={{ $id }}>
  <img loading="lazy" src="{{ esc_url($image['url']) }}" alt="{{ esc_attr($image['alt']) }}" class="image-left">
  <div class="content-right">
    <h2 class="content-header-right">{{ $header }}</h2>
    <span class="content-paragraph-right">{!! $paragraph !!}</span>

    {{-- only create the button container if the user has selected they want one shown--}}
    @if($button && !empty($link_right))
      <span class="content-button-right">
        @include('components.iresq-button', [
          'id' => 'content-right-button', 
          'link' => $link_right['url'], 
          'type' => 'solid-dark', 
          'target' => $link_right['target'],
          'text' => $link_right['title']
        ])
      </span>
    @endif
  </div>
</div>
@endunless