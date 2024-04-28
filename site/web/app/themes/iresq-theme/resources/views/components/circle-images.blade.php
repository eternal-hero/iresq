{{--
  Variables:
    string: $id
    string: $header
    object: $circle_images
--}}

<div class="circle-images" id={{ $id }}>
    <h2 class="circle-images-heading">{{ $header }}</h2>
    @if(!empty($circle_images))
        <div class="circle-images-wrapper">
            @foreach ($circle_images as $circle_image)
                <img 
                src="{{ esc_url($circle_image['circle_image']['url']) }}"
                alt="{{ esc_attr($circle_image['circle_image']['alt']) }}" 
                class="circle-image"
                loading="lazy"
                >
            @endforeach
        </div>
    @endif
</div>