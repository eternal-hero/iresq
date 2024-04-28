{{--
  string: $id
  string: $large_text
  string: $small_text
--}}

<div class="star-rating" id="{{ $id }}">
  <div class="stars">
    @for ($i = 0; $i < 5; $i++)
      <svg 
        class="star"
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
      >
        <path d="M12 .288l2.833 8.718h9.167l-7.417 5.389 2.833 8.718-7.416-5.388-7.417 5.388 2.833-8.718-7.416-5.389h9.167z"/>
      </svg>
    @endfor
  </div>
  <h3>{!! $large_text !!}</h3>
  <p>{{ $small_text }}</p>
</div>