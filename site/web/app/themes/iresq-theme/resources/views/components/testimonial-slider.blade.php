{{--
  array: $testimonial_slider
    string: $title
    string: $name
    number: $rating
    wysiwyg: $review
    image_id: $image
--}}
@unless(empty($testimonial_slider))
  <section>
    <div class="tw-text-center tw-flex tw-justify-center">
      <div class="tw-w-full md:tw-w-8/12 lg:tw-w-6/12">
        <h2>What our customers are saying</h2>
      </div>
    </div>
    <div class="testimonial-reel">
      @foreach ($testimonial_slider as $slide)
        <div class="testmonial-slide tw-p-8">
          <div class="tw-bg-white tw-rounded tw-p-10 testimonial-slide-container tw-w-full">
            <div class="tw-flex tw-space-x-10 tw-items-center">
              <div class="testimonial-image tw-h-48 tw-w-48 tw-rounded-full tw-overflow-hidden tw-mx-auto tw-shadow tw-flex-shrink">
                {!! wp_get_attachment_image($slide['image'], 'medium') !!}
              </div>
              <div class="tw-flex-grow">
                <p class="testimonial-name">{{ $slide['name'] }}</p>
                <p class="testimonial-title">{{ $slide['title'] }}</p>
                <div class="testimonial-star-rating">
                  @for ($i = 0; $i < $slide['rating']; $i++)
                    <span class="fas fa-star tw-text-gold tw-text-3xl tw-mx-1"></span>
                  @endfor
                </div>
              </div>
            </div>
            <div class="testimonial-review">
              {!! $slide['review'] !!}
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </section>
@endunless

<script>

jQuery('.testimonial-reel').slick({
      centerMode: true,
      centerPadding: '40px',
      infinite: true,
      arrows: false,
      // autoplay: true,
      autoplaySpeed: 4000,
      slidesToShow: 3,
      responsive: [
        {
          breakpoint: 1093,
          settings: {
            slidesToShow: 2,
            centerMode: false,
          },
        },
        {
          breakpoint: 767,
          settings: {
            centerPadding: '0',
            slidesToShow: 1,
            centerMode: false,
          },
        },
      ],
    });
</script>
