@php 
  $title = get_sub_field('secondary_hero_title');
  $background = get_sub_field('secondary_hero_image');
@endphp

<div class="secondary-hero {{($background) ? '' : 'no-image'}}" id={{ $id }}>
  <div class="secondary-hero-title">
    <h1 class="hero-large">{{ $title }}</h1>
  </div>
  @if($background)
  <div class="secondary-hero-image" style="background-image: url({{ $background }})"; ></div>
  @endif
</div>