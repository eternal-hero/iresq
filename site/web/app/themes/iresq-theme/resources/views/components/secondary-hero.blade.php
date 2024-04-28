{{--
  string: $id
  string: $title
  string: $background
--}}
<div class="secondary-hero {{($background) ? '' : 'no-image'}}" id={{ $id }}>
  <div class="secondary-hero-title">
    <h1 class="hero-large">{{ $title }}</h1>
  </div>
  @if($background)
  <div class="secondary-hero-image" style="background-image: url({{ $background }})"; ></div>
  @endif
</div>