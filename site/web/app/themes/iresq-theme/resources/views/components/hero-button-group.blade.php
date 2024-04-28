{{--
  string: $hero_button_group_title
  array: $hero_button_group_buttons
    object: link
--}}

@if($hero_button_group_title || $hero_button_group_buttons)
  <div id={{ $id }} class="hero-button-group tw-px-4 tw-py-8 tw-bg-gray-400">
    @if($hero_button_group_title)
      <h2 class="hero-button-group-title tw-text-center">{{ $hero_button_group_title }}</h2>
    @endif
    @if ($hero_button_group_buttons)
      <div class="hero-button-group-buttons tw-flex tw-flex-wrap tw-justify-center">
        @foreach ($hero_button_group_buttons as $button)
          @if($button['link'])
            <div class="tw-w-full md:tw-w-1/2 lg:tw-w-1/3 xl:tw-w-1/5 tw-px-4 tw-mb-8">
              <a href="{{ $button['link']['url'] }}" 
                target="{{ $button['link']['target'] }}">
                <button class="iresq-button solid-dark tw-w-full" >{{ $button['link']['title'] }}</button>
              </a>
            </div>
          @endif
        @endforeach
      </div>
    @endif
  </div>
@endif
