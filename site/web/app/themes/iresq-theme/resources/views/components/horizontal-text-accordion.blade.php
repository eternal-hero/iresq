{{--
  Variables:
    string: $id
    string: $size
    string: $section_heading
    object: $text_accordions
--}}
{{-- 
  SIZE OPTIONS ARE large OR small 
  large = 450px height, 500px width for active
  small = 250px height, 300px width for active
--}}
@unless (!$text_accordions)
    <div class="horizontal-text-accordion-section {{ $size }}" id="{{ $id }}">
        <div class="heading-wrapper">
            <h2>{{ $section_heading }}</h2>
        </div>
        <div class="accordion-wrapper">
            @foreach ($text_accordions as $accordion)
                <div class="accordion {{ ($loop->first) ? 'active' : '' }}">
                    <h5 class="accordion-heading vertical">{{ $accordion['accordion_heading'] }}</h5>
                    <div class="content-wrapper">
                        <h5 class="accordion-heading horizontal">{{ $accordion['accordion_heading'] }}</h5>
                        <div class="fade {{ ($loop->first) ? 'active' : '' }}">
                            <p>{{ $accordion['accordion_paragraph'] }}</p>
                            @if($accordion['add_accordion_button'] && !empty($accordion['accordion_button']))
                                <span class="content-button">
                                    @include('components.iresq-button', [
                                        'id' => 'text-accordion-button {{$loop->iteration}}',
                                        'link' => $accordion['accordion_button']['url'],
                                        'type' => 'outlined-light',
                                        'target' => $accordion['accordion_button']['target'],
                                        'text' => $accordion['accordion_button']['title']
                                    ])
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endunless

