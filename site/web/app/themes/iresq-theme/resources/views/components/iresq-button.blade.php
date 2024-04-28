{{--
  Variables:
    string: $text
    string: $id
    string: $target
    string: $link
    string: $type
--}}
<a href={{ empty($link) ? "#" : $link }} target={{ empty($target) ? "_self" : $target }}>
  <button class="{{ $type }} iresq-button" id="{{ $id }}">
    {{ $text }}
  </button>
</a>