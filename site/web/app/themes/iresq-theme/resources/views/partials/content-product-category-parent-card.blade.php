@php
$category = get_term($child);
if ($category) {
	$thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);

	if ($thumbnail_id) {
		$imageObject = wp_get_attachment_image($thumbnail_id, 'medium', false, [
			'class' => 'tw-mx-auto tw-w-full sm:tw-w-3/4 md:tw-w-1/2',
		]);
	}
}
$image = wp_attachment_is_image($thumbnail_id) ? $imageObject : $categoryDefaultImage;
$link = get_term_link($category);
@endphp

<div class="tw-w-full sm:tw-w-1/2 md:tw-w-1/3 tw-px-4 tw-mb-8">
    <a href="{{ $link }}" style="min-height: 250px;"
        class="tw-shadow hover:tw-shadow-lg tw-transition-shadow tw-duration-300 tw-rounded-lg tw-pt-4 tw-px-4 tw-h-full tw-flex tw-flex-col tw-justify-between">
        <div class="tw-pr-4 lg:tw-pr-12">
            <h3 class="tw-mt-0 tw-mb-4 tw-text-primary tw-text-3xl tw-leading-tight tw-font-bold">
                {!! $category->name !!}</h3>
            <div class="tw-font-normal md:tw-text-lg md:tw-max-w-md">{!! $category->description !!}
            </div>
        </div>
        <div class="tw-max-w-xs tw-mx-auto">{!! $image !!}</div>
    </a>
</div>
