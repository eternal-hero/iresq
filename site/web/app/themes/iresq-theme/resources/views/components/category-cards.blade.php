@if(have_rows('category_cards_section'))
      <div class="tw-container tw-px-4 md:tw-px-12">
        @while(have_rows('category_cards_section'))
          @php 
            the_row();

            $title = get_sub_field('title');
            $rich_text = get_sub_field('rich_text');
          @endphp

          <div class="tw-text-center tw-mb-8 md:tw-mb-12">
            @if($title)
              <h2 class="tw-text-3xl md:tw-text-5xl tw-font-bold tw-mt-0">{!! $title !!}</h2>
            @endif

            @if($rich_text)
              <div>{!! $rich_text !!}</div>
            @endif
          </div>

          @if(have_rows('category_cards'))
            <div class="tw-flex tw-items-stretch tw-flex-wrap tw--mx-4 md:tw--mx-12">
              @while(have_rows('category_cards'))
                @php 
                  the_row();

                  $kicker = get_sub_field('kicker');
                  $title = get_sub_field('title');
                  $image = wp_get_attachment_image_url(get_sub_field('image')['id'], 'medium', false);
                  $alt = get_sub_field('image')['alt'];
                  $link = get_sub_field('link');
                @endphp

                <a href="{{ $link['url'] }}" title="{{ $link['title'] }}" class="tw-w-full sm:tw-w-1/2 lg:tw-w-1/4 tw-px-4 md:tw-px-12">
                  <div 
                    class="tw-bg-no-repeat tw-bg-cover tw-bg-center tw-mb-8 md:tw-mb-16 tw-p-4 tw-rounded-2xl tw-shadow-lg hover:tw-shadow-2xl tw-transition-shadow tw-duration-300" 
                    aria-role="img" 
                    label="{{ $alt }}" 
                    style="background-image: url('{{ $image }}'); padding-bottom: 80%;">
                    @if($kicker)
                      <div class="tw-my-0 tw-text-primary tw-text-bold tw-text-lg tw-uppercase">{!! $kicker !!}</div>
                    @endif

                    @if($title)
                      <h3 class="tw-my-0 tw-text-2xl md:tw-text-4xl">{!! $title !!}</h3>
                    @endif
                  </div>
                </a>
              @endwhile
            </div>
          @endif

        @endwhile
      </div>
    @endif