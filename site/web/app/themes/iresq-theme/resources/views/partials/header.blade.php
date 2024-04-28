@if(get_field('show_announcement_banner', 'options'))
<div class="announcement-banner">
  <div class="text">{{ the_field('announcement_banner_text', 'options') }}</div>
  @php $annoucement_link= get_field('announcement_banner_link', 'options') @endphp
  @if($annoucement_link) @php
    $annoucement_link_url = $annoucement_link['url'];
    $annoucement_link_title = $annoucement_link['title'];
    $annoucement_link_target = $annoucement_link['target'] ? $annoucement_link['target'] : '_self';
  @endphp
  <div class="link">
    @include('components.iresq-button', [
      'id' => 'announcement_banner_link',
      'link' => get_field('annoucement_link_url'),
      'type' => 'solid-dark',
      'target' => get_field('annoucement_link_target'),
      'text' => get_field('annoucement_link_title')
      ])
  </div>
  @endif
  </div>
@endif

<header class="banner">
  <div class="container">
    <div class="secondary-container">
      <nav class="nav-secondary">
        @if (has_nav_menu('secondary_navigation'))
        {!! wp_nav_menu(['theme_location' => 'secondary_navigation', 'menu_class' => 'nav']) !!}
        @endif
      </nav>
      <div class="search-area">
        @if(is_user_logged_in())
        <div class="my-account">
          <a href="/my-account/">My account</a>
        </div>
        @else
        <div class="login">
          <a href="/my-account/" style="font-size: 14px;">Create an Account / Login</a>
        </div>
        @endif

        <a href="{!! wc_get_account_endpoint_url( 'edit-account' ) !!}" class="my-account-link">
          <i class="fal fa-user-circle"></i>
        </a>

        <a href="/cart/" class="shopping-bag-link">
          <div class="shopping-link-wrapper">
            <i class="fal fa-shopping-cart"></i>
            <span class="cart-count">
              <?php
                (WC()->cart->get_cart_contents_count() == 0)
                  ? $cartCountStr = '0'
                  : $cartCountStr = WC()->cart->get_cart_contents_count();
                echo $cartCountStr;
              ?>
            </span>
          </div>
        </a>

        <div class="nac-search-form">
          {{ get_search_form() }}
        </div>
      </div>
    </div>
    <div class="primary-container">
      <a class="brand" href="{{ home_url('/') }}">
        {!! the_custom_logo() !!}
      </a>
      <nav class="nav-primary">
        @if (has_nav_menu('primary_navigation'))
        {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']) !!}
        @endif
      </nav>
      <div class="repair-links">
        @include('components.iresq-button', [
          'id' => 'header-repair-link', 
          'link' => '/repair-form/', 
          'type' => 'solid-red',
          'text' => 'Start my repair'
          ])

        {{-- @include('components.iresq-button', [
          'id' => 'header-browse-link', 
          'link' => '/shop/', 
          'type' => 'solid-red',
          'text' => 'Browse all repairs'
          ]) --}}
      </div>


    </div>

    <!-- Mobile menu items -->
    <div class="mobile-container">

      <!-- mobile action bar -->
      <div class="mobile-action-bar">
        <a href="{{ home_url('/') }}">
          {!! the_custom_logo() !!}
          {{-- <img src="@asset('images/primary-logo-small.png')" class="mobile-home" alt="iResQ logo"> --}}
        </a>

        <!-- Create a new button component -->
        @include('components.iresq-button', [
          'id' => 'mobile-repair-link', 
          'link' => '/repair-form/', 
          'type' => 'outlined-red',
          'text' => 'Start my repair'
        ])

        <span class="mobile-action-bg">
          <i class="fal fa-bars mobile-action-indicator retracted"></i>
        </span>
      </div>

      <!-- mobile menu (initially hidden) -->
      <div class="mobile-menu retracted">
        <nav class="mobile-primary-nav-links">
          @if (has_nav_menu('primary_navigation'))
            {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'mobile-nav']) !!}
          @endif
        </nav>
        <nav class="mobile-secondary-nav-links">
          @if (has_nav_menu('secondary_navigation'))
            {!! wp_nav_menu(['theme_location' => 'secondary_navigation', 'menu_class' => 'mobile-nav']) !!}
          @endif
        </nav>
        <div class="mobile-nav-search">
          {{ get_search_form() }}
        </div>
        <div class="mobile-links">
          <a href="/cart/">
            <div class="mobile-bag-count">
              <i class="fal fa-shopping-cart"></i>
              <span class="bag-count">
                @php
                  (WC()->cart->get_cart_contents_count() == 0)
                  ? $cartCountStr = '0'
                  : $cartCountStr = WC()->cart->get_cart_contents_count();
                  echo $cartCountStr;
                @endphp
              </span>
            </div>
          </a>
          <a href="{!! wc_get_account_endpoint_url( 'edit-account' ) !!}">
            <span class="mobile-account-link">
              <i class="fal fa-user-circle"></i>
            </span>
          </a>
        </div>
      </div>
    </div>
    <!-- end mobile menu -->
  </div>
</header>

<div class="mobile-box-shadow"></div>
