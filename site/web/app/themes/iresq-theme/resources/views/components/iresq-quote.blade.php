{{--
  string: $id 
  string: $quote
  string: $author
--}}
<div class="iresq-quote-wrapper" id="{{ $id }}">
  <span class="quote-icon">“</span>
  <div class="quote-text">{{ $quote }}</div>
  <span class="quote-author">{{ $author }}</span>
</div>