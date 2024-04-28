@php
  $text = get_sub_field('banner_cta_text');
  $background = get_sub_field('banner_cta_background');
  $link = get_sub_field('banner_cta_link');
@endphp

@unless(!$text)
<div class="banner-cta" id={{ $id }} style="background-image: url('{{ $background }}')">
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
@endunless