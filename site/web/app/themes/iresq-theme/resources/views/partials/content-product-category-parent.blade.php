@php
    if (get_field('manually_choose_categories', $term) && get_field('categories', $term)) {
        $children = get_field('categories', $term);
    }
    $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
@endphp

@include('partials.page-header-product-category', [
    'include_sidebar' => false,
    'show_description' => false,
    'title' => get_the_title()
])

<h2 class="tw-font-semibold tw-text-3xl md:tw-text-5xl">{{ get_field('select_your_category_title', $term) ?: 'Select your ' . $term->name }}</h2>
<div class="tw-flex tw-flex-wrap tw-items-stretch tw--mx-4 tw-pb-16">
    @foreach ($children as $child)
        @include('partials.content-product-category-parent-card', [
          'categoryDefaultImage' => wp_get_attachment_image($thumbnail_id, 'medium', false, [
              'class' => 'tw-mx-auto tw-w-full sm:tw-w-3/4 md:tw-w-1/2',
          ])
        ])
    @endforeach
</div>
