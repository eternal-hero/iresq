@php

$title = get_field('product_category_sidebar_title', 'options');
$content = get_field('product_category_sidebar_content', 'options');
$button = get_field('product_category_sidebar_button', 'options');

@endphp

@if ($title || $content || $button)
    <aside class="tw-rounded-lg tw-w-full tw-px-4 tw-pt-4 tw-bg-gray-100 tw-mt-4 md:tw-my-8 tw-pb-16">
        @if ($title)
            <h2 class="tw-font-semibold tw-text-4xl tw-leading-tight tw-mb-0">{!! $title !!}</h2>
        @endif

        @if ($content)
            <div class="tw-mb-16">{!! $content !!}</div>
        @endif

        @if ($button)
            <a href="{{ $button['url'] }}" title="{{ $button['title'] }}" target="{{ $button['target'] }}"
                rel="{{ $button['target'] === '_blank' ? 'noreferrer' : '' }}"
                class="tw-bg-primary tw-px-6 tw-py-4 tw-text-white tw-rounded-full">{{ $button['title'] }}</a>
        @endif
    </aside>
@endif
