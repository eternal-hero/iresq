{{--
  string: $id
  string: $text
  string: $background
  object: $link
--}}

<div class="secondary banner-cta" id={{ $id }} style="background-image: url('{{ $background }}')">
  <h5 class="banner-cta-text">{{ $text }}</h5>
  <div class="banner-cta-link">
      @unless(empty($link))
      @include('components.iresq-button', [
        'id' => 'banner-cta-button', 
        'link' => $link['url'], 
        'type' => 'outlined-light', 
        'target' => $link['target'],
        'text' => $link['title']
      ])
      @endunless
  </div>
</div>