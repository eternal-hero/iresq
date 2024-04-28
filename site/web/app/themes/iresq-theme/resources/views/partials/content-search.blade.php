<div class="search-card">
  <a href="{{ get_permalink() }}" @php post_class(['search-result']) @endphp>
      <h2 class="entry-title">{!! get_the_title() !!}</h2>
      @if (get_post_type() === 'post')
      
      @endif
      @if(the_excerpt())
      <div class="entry-summary">
        @php the_excerpt() @endphp
      </div>
      @endif
  </a>
</div>
