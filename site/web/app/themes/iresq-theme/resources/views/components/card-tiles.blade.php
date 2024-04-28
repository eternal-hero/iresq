{{--
  Variables:
  string: $id
  string: $type
  object: $tiles
--}}
{{-- 
  TYPE OPTIONS ARE horizontal OR vertical 
  horizontal = THREE IN A ROW 
  vertical = FOUR IN A ROW
--}}
@if(!empty($tiles))
  <div class="card-tiles-section {{ $type }}" id={{ $id }}>
    @foreach ($tiles as $tile)
      <div class="card">
        @if($type === "horizontal")
        <h3 class="card-header">{{ $tile['card_header'] }}</h3>
        @elseif ($type === "vertical")
        <h4 class="card-header">{{ $tile['card_header'] }}</h4>
        @endif
        <div class="card-body-image">
          <div class="toggle"></div>
          @if($tile['card_image'])
          <img 
          src="{{ esc_url($tile['card_image']['url']) }}"
          alt="{{ esc_attr($tile['card_image']['alt']) }}"
          loading="lazy" 
          class="card-image"
          >
          @else 
          <div class="card-image empty"></div>
          @endif
        </div>
        <div class="card-body-content">
          <p>{{ $tile['card_text'] }}</p>
          @if ($tile['card_link'])
          @include('components.iresq-button', [
            'id' => 'content-right-button',
            'link' => $tile['card_link']['url'],
            'type' => 'outlined-light', 'target' => $tile['card_link']['target'],
            'text' => $tile['card_link']['title']
          ])
          @endif
        </div>
      </div>

    @endforeach
  </div>
@endif