@unless (empty($heroes))
<div class="tabbed-hero-section" id={{ $id }}>
  <div class="tabbed-hero-wrapper">
    @foreach ($heroes as $hero)
      <div class="tabbed-hero {{ ($loop->first) ? 'active' : '' }}" id="tab-{{$loop->iteration}}">
        <div class="tabbed-hero-content">
          <div class="tabbed-hero-header">{{ $hero['tab_content']['title'] }}</div>
          <p class="tabbed-hero-text">{{ $hero['tab_content']['text'] }}</p>

          {{-- only create the button container if it is not empty --}}
          @if(!empty($hero['tab_content']['button']))
            <div class="tabbed-hero-button">
              @include('components.iresq-button', [
                'id' => 'tabbed-button-{{ $loop->iteration }}',
                'link' => $hero['tab_content']['button']['url'],
                'type' => 'solid-red',
                'target' => $hero['tab_content']['button']['target'],
                'text' => $hero['tab_content']['button']['title']
              ])
            </div>
          @endif
        </div>

        @unless (empty($hero['tab_content']['graphic']))
          <div class="tabbed-hero-graphic-wrapper">
            <img 
              src="{{ esc_url($hero['tab_content']['graphic']['url']) }}"
              alt="{{ esc_attr($hero['tab_content']['graphic']['alt']) }}"
              loading="lazy"
              class="tabbed-hero-graphic"
            >
          </div>
        @endunless
      </div>
    @endforeach

    <div class="tabbed-hero-tabs-wrapper">
      <ul class="tabbed-hero-tabs">
        @foreach ($heroes as $hero)
          <li class="{{ ($loop->first) ? 'selected' : 'bordered' }}">
            <span data-tab="#tab-{{ $loop->iteration }}">
            {{ $hero['tab_header'] }}
            </span>
          </li>
        @endforeach
      </ul>
    </div>
    
  </div>

  <!-- Mobile tabs -->
  <div class="mobile-tabs-wrapper">
    <span id="left-arrow" class="mobile-arrow" data-tab="#tab-{{ count($heroes) }}">
      <i class="mobile-tab-select mobile-left-arrow fas fa-caret-left"></i>
    </span>
    <ul class="mobile-hero-tabs">
      @foreach ($heroes as $hero)
        <li class="{{ ($loop->first) ? 'selected' : '' }} header-{{$loop->iteration}}">
          {{ $hero['tab_header'] }}
        </li>
      @endforeach
    </ul>
    <span id="right-arrow" class="mobile-arrow" data-tab="#tab-2">
      <i class="mobile-tab-select mobile-right-arrow fas fa-caret-right"></i>
    </span>
  </div>
</div>
@endunless