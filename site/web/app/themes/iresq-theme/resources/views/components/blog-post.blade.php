@php
$featuredImage = get_the_post_thumbnail_url($id, 'full');
$thumbnail_id = get_post_thumbnail_id( $id );
$alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);  
@endphp

<div class="blog-post @if( $post_preview_size == 'medium' ) blog-post-medium @else blog-post-large @endif" id={{$id}}>
  <a class="blog-link" href="{{ $link }}">
    @if ($featuredImage)
      <img loading="lazy" src="{{ esc_url($featuredImage) }}" alt="{{ esc_attr($alt) }}" class="post-image">
    @else
      <img loading="lazy" style="width: 80%; margin: 0 auto; object-fit: contain;" src="@asset('images/primary-logo-medium.png')" alt="iresq logo" class="post-image">
    @endif
    @if($is_video)
      <img loading="lazy" src="@asset('images/play.svg')" alt="white play icon" class="video-post">
    @endif
  </a>
  <div class="post-content">
    <a href="{{ $link }}">
      <h3 class="post-title">{!! $title !!}</h3>
    </a>
    <span class="post-details">
      @foreach ($post_details as $detail)
        @unless (empty($detail))
          @foreach ($detail as $type)
            @if($loop->iteration > 1) & @endif
            {{ $type->name }}
          @endforeach
          @if(!$loop->last) {!! "\t/\t" !!} @endif
        @endunless
      @endforeach
    </span>
    @if ($post_preview_size == 'large')
      <span class="post-text">{{ $paragraph }}</span>
    @endif
    <a href="{{ $link }}">
      <img loading="lazy" src="@if( $is_video ) @asset('images/play.svg') @else @asset('images/plus.svg')  @endif" alt="Blog post link" class="post-icon">
    </a>
  </div>
</div>