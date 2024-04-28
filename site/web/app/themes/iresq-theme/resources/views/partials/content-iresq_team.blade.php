<div class="team__item">
    <div class="team__image-group">
        {!! get_the_post_thumbnail(null, 'post-thumbnail', ['class' => 'team__main-image']) !!}
        @if($alt_image = get_field('alternative_image'))
            <img 
                class="team__alt-image" 
                loading="lazy"
                src="{{ $alt_image['url'] }}" 
                alt="{{ $alt_image['alt'] }}" 
            />
        @endif
    </div>
    <h2 class="team__title">{{ get_the_title() }}</h2>
    @if($position = get_field('position'))
        <p>{{ $position }}</p>
    @endif
    @if(get_the_excerpt())
        <div class="team__content">{!! get_the_excerpt() !!}</div>
    @endif
    <a href="{{ get_the_permalink() }}" class="team__permalink">Read More</a>
</div>
