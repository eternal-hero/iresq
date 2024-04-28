{{--
  Variables:
    boolean: $ribbon_contents
--}}
<div class="footer-ribbon" aria-hidden="true">
    @php $ribbon_content=$ribbon_contents @endphp
    @if($ribbon_content)
    {{-- LOAD ACF VALUES INTO ARRAY --}}
      @php $textArray = array(); @endphp
      @foreach ($ribbon_content as $phrase)
      @php array_push($textArray, $phrase['ribbon_phrase']) @endphp
      @endforeach
      {{-- DUPLICATE ACF VALUES TO ENSURE IT MAKES IT ACROSS THE SCREEN --}}
      @php $doubled=array_merge($textArray, $textArray) @endphp
      {{-- FIRST RIBBON --}}
      <div class="inner-ribbon one">
      @foreach ( $doubled as $print)
      <div class="item-container">
        <img loading="lazy" src="@asset('images/isolated-logo-white-small.png')" alt="iResQ Logo">
        <div class="ribbon-text">{{ $print }}</div>
      </div>
      @endforeach
    </div>
    {{-- SECOND RIBBON. MUST USE TWO TO KEEP GAP OUT OF MARQUEE --}}
    <div class="inner-ribbon two">
      @foreach ( $doubled as $print)
      <div class="item-container">
        <img loading="lazy" src="@asset('images/isolated-logo-white-small.png')" alt="iResQ Logo">
        <div class="ribbon-text">{{ $print }}</div>
      </div>
      @endforeach
    </div>
    @endif
  </div>