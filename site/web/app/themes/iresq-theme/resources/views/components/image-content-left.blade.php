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