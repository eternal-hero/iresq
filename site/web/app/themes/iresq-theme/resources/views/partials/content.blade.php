<article @php post_class(['expert-advice-post', (Home::getPreviewSize() == 'medium') ? 'medium' : 'large']) @endphp>
  @include('components.blog-post', [
    'id' => get_the_ID(),
    'link' => get_permalink(),
    'post_details' => array(
      'repair_type' => get_the_terms(get_the_ID(), 'repair_type'),
      'device_type' => get_the_terms(get_the_ID(), 'device'),
      'content_type' => get_the_terms(get_the_ID(), 'content_type'),
    ),
    'post_preview_size' => Home::getPreviewSize(),
    'is_video' => Home::isVideo(get_the_terms(get_the_ID(), 'content_type')),
    'title' => get_the_title(),
    'paragraph' => Home::getPreviewText()
  ])
</article>
