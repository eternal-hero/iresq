{{--
  Variables:
    object: $accordion_images
    object: $accordion_content
--}}

@unless (empty($accordion_images))

<div class="horizontal-accordion-section">
    <div class="image-content-right horizontal-accordion content-transparent">
        <div class="images">
            @foreach ($accordion_images as $image)
            <div class="image-wrapper" data-slide-number="{{ $loop->iteration }}" style="background-image: url('{{ esc_url($image['images']['url']) }}');">
                @if(!$loop->first)
                <img loading="lazy" src="@asset('images/play.svg')" alt="left arrow icon" class="left arrow-icon arrow-{{ $loop->iteration }}">
                @endif

                @if(!$loop->last)
                <img src="@asset('images/play.svg')" alt="right arrow icon" class="right arrow-icon arrow-{{ $loop->iteration }}">
                @endif
            </div>    
            @endforeach
        </div>
    
         <div class="content-right horizontal-accordion">
          <h2 class="content-header-right">{{ $accordion_content['accordion_header_right'] }}</h2>
          <span class="content-paragraph-right">{!! $accordion_content['accordion_paragraph_right'] !!}</span>

          {{-- only create the button container if the user has selected they want one shown--}}
           @if($accordion_content['accordion_add_right_button'] && !empty($accordion_content['accordion_link_right']))
            <span class="content-button-right">
              @include('components.iresq-button', [
                'id' => 'content-right-button', 
                'link' => $accordion_content['accordion_link_right']['url'], 
                'type' => 'solid-dark', 
                'target' => $accordion_content['accordion_link_right']['target'],
                'text' => $accordion_content['accordion_link_right']['title']
              ])
            </span>
          @endif
        </div> 
      </div>
</div>

@endunless
