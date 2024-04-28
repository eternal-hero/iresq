@php

$description = '';

if (isset($term)){
    if (get_field('product_page_header_title', $term)) {
        $title = get_field('product_page_header_title', $term);
    }

    if (get_field('product_page_header_description', $term)) {
        $description = get_field('product_page_header_description', $term);
    } else {
        $description = $term->description;
    }
} else {
    if (get_field('product_page_header_title')) {
        $title = get_field('product_page_header_title');
    }
}

@endphp


<div class="tw-w-full">
    <h1 class="tw-text-4xl md:tw-text-6xl tw-font-semibold tw-mb-0">{{ $title }}</h1>

    @include('partials.breadcrumbs')

    @if($description)
        <div class="tw-font-normal md:tw-text-lg">
            {!! $description !!}
        </div>
    @endif
</div>
