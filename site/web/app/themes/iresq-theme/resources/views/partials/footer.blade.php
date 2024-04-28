<footer class="content-info">
  <div class="container">

    @include('components.footer-ribbon', [
      'ribbon_contents' => App::ribbonContent()
    ])

    <div class="footer-inner-content-wrapper">
      <div class="footer-inner-content">
        <div class="footer-brand">
          <a href="{{ home_url('/') }}">
            <img loading="lazy" src="@asset('images/secondary-logo-medium.png')" alt="iResQ Logo" class="footer-logo">
          </a>

          <ul class="social-links">
            <li class="social-link">
              <a target="_blank" href="https://www.facebook.com/pages/iResQ/141954385840854/"><i class="fab fa-facebook"></i></a>
            </li>
            <li class="social-link">
              <a target="_blank" href="https://www.instagram.com/iresqrepair/"><i class="fab fa-instagram"></i></a>
            </li>
            <li class="social-link">
              <a target="_blank" href="https://twitter.com/iResQ/"><i class="fab fa-twitter"></i></a>
            </li>
          </ul>

        </div>

        <nav class="footer-primary-navigation">
          @if (has_nav_menu('footer_primary'))
          {!! wp_nav_menu(['theme_location' => 'footer_primary', 'menu_class' => 'nav']) !!}
          @endif
        </nav>

        <nav class="footer-secondary-navigation">
          @if (has_nav_menu('footer_secondary'))
          {!! wp_nav_menu(['theme_location' => 'footer_secondary', 'menu_class' => 'nav']) !!}
          @endif
        </nav>

        <nav class="footer-tertiary-navigation">
          @if (has_nav_menu('footer_tertiary'))
          {!! wp_nav_menu(['theme_location' => 'footer_tertiary', 'menu_class' => 'nav']) !!}
          @endif
        </nav>

        <div class="footer-right">

          <div class="footer-phone-link">
            <a href="tel:8884473728">
              {{ the_field('iresq_phone_number', 'options') }}
            </a>
          </div>

          <div class="footer-address">
            <a href="{{ the_field('iresq_address_link', 'options') }}" target="_blank" title="iResQ Location Link">
              {{ the_field('iresq_address', 'options') }}
            </a>
          </div>

          {{--
            -- Create a new button component
            -- Pass in a unique ID, link, and the type of button for syling purposes.
            --}}
          <div class="footer-repair-button">
            @include('components.iresq-button', [
              'id' => 'footer-repair', 
              'link' => '/repair-form/', 
              'type' => 'solid-red',
              'text' => 'Start my repair'
              ])
          </div>

          <div class="footer-shop-link">
            @include('components.iresq-button', [
              'id' => 'footer-repair-bottom', 
              'link' => '/shop/', 
              'type' => 'solid-red',
              'text' => 'Browse all repairs'
              ])
          </div>

        </div>
      </div>

      <hr class="footer-hr">

      <div class="disclaimer-container">
        <div class="disclaimer-text">
          {!! the_field('footer_disclaimer_text', 'options') !!}
        </div>
        <div class="disclaimer-img">
          @php
          $vosb_image = get_field('vosb_logo', 'options');
          @endphp
          @if( !empty( $vosb_image ) )
              <img loading="lazy" src="<?php echo esc_url($vosb_image['url']); ?>" alt="<?php echo esc_attr($vosb_image['alt']); ?>" />
          @endif
        </div>
      </div>





  <div class="footer__make">
    <div class="makeCopy">
      <?php if(basename($_SERVER['REQUEST_URI']) != "") { ?>
        <p>©️ 1994-<?php echo date('Y'); ?> iResQ – All Rights Reserved.</p>
        <a href="https://makedigitalgroup.com/" target='_blank' class='footer-make-link' rel='nofollow'>
          Web Design & Marketing by
          <img loading="lazy" src="@asset('images/make-logo-white.png')" alt='MAKE Digital Agency logo teal with white lettering'>
        </a>
      <?php }else { ?>
        <p>©️ 1994-<?php echo date('Y'); ?> iResQ – All Rights Reserved.</p>
        <a href="https://makedigitalgroup.com/" target='_blank' class='footer-make-link'>
          Web Design & Marketing by
          <img loading="lazy" src="@asset('images/make-logo-white.png')" alt='MAKE Digital Agency logo teal with white lettering'>
        </a>
      <?php } ?>
    </div>
  </div>

</div>
</div>

</footer>
