@php
$cartAmount = WC()->cart->get_cart_contents_count() == 0 ? '0' : WC()->cart->get_cart_contents_count();
$custom_logo_id = get_theme_mod( 'custom_logo' );
$logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
@endphp

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
        'link' => $annoucement_link_url,
        'type' => 'solid-dark',
        'target' => $annoucement_link_target,
        'text' => $annoucement_link_title
        ])
    </div>
    @endif
</div>
@endif

<header class="banner-v2">
    <div class="desktop-container">
        <a class="logo" href="{{ home_url('/') }}">
            <img loading="lazy" src="{!! $logo[0] !!}" alt="iResQ">
        </a>
        <div class="menu--container">
            <div class="top-menu">
                <div class="has-sep">
                    <a href="/my-account/" class="business-customer-link">
                        <div class="business-customer-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="33.067" height="31" viewBox="0 0 33.067 31">
                                <path id="briefcase-blank-thin" d="M29.967,6.2H24.8V2.583A2.584,2.584,0,0,0,22.217,0H10.85A2.584,2.584,0,0,0,8.267,2.583V6.2H3.1A3.1,3.1,0,0,0,0,9.3V27.9A3.1,3.1,0,0,0,3.1,31H29.967a3.1,3.1,0,0,0,3.1-3.1V9.3A3.1,3.1,0,0,0,29.967,6.2ZM9.3,2.583a1.553,1.553,0,0,1,1.55-1.55H22.217a1.553,1.553,0,0,1,1.55,1.55V6.2H9.3ZM32.033,27.9a2.069,2.069,0,0,1-2.067,2.067H3.1A2.069,2.069,0,0,1,1.033,27.9V9.3A2.069,2.069,0,0,1,3.1,7.233H29.967A2.069,2.069,0,0,1,32.033,9.3Z"/>
                            </svg>                          
                        </div>
                        
                        <div class="business-customer-container">
                            <div class="lead-text">Business Customer?</div>
                            <div class="sub-text">Login Here</div>
                        </div>
                    </a>
                </div>
                
                <div class="has-sep has-last-sep">
                    <a href="/my-account/" class="my-account-link">
                        <div class="my-account-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="41.25" height="41.25" viewBox="0 0 41.25 41.25">
                                <path id="circle-user-thin" d="M20.625,10.313a6.445,6.445,0,1,0,6.445,6.445A6.444,6.444,0,0,0,20.625,10.313Zm0,11.6a5.156,5.156,0,1,1,5.156-5.156A5.162,5.162,0,0,1,20.625,21.914ZM20.625,0A20.625,20.625,0,1,0,41.25,20.625,20.623,20.623,0,0,0,20.625,0Zm0,39.961A18.854,18.854,0,0,1,9.1,36.059a8.929,8.929,0,0,1,8.951-8.989H23.2a8.93,8.93,0,0,1,8.951,8.991A18.89,18.89,0,0,1,20.625,39.961Zm12.786-4.874A10.273,10.273,0,0,0,23.2,25.781H18.047A10.271,10.271,0,0,0,7.839,35.087a19.336,19.336,0,1,1,25.572,0Z"/>
                            </svg>                              
                        </div>
                        <div class="my-account-text-container">
                            @if(is_user_logged_in())
                            <div class="lead-text">My Account</div>
                            @else
                            <div class="lead-text">Login</div>
                            <div class="sub-text">Login Here</div>
                            @endif
                        </div>
                    </a>
                </div>

                <div class="nac-search-form">
                    {{ get_search_form() }}
                </div>
                
                <a href="/cart/" class="shopping-bag-link shopping-bag-link--not-scrolling">
                    <div class="shopping-link-wrapper">
                        
                        <svg xmlns="http://www.w3.org/2000/svg" width="34.347" height="30.65" viewBox="0 0 34.347 30.65">
                            <path id="cart-shopping-thin" d="M3.831,0a.479.479,0,0,1,.463.358L4.7,1.916H32.434A1.942,1.942,0,0,1,34.278,4.35l-3.233,11.5A1.916,1.916,0,0,1,29.2,17.241H8.7L9.949,22.03H29.692a.479.479,0,1,1,0,.958H9.524a.471.471,0,0,1-.407-.359L3.461.958H.479A.479.479,0,0,1,.479,0ZM4.951,2.873l3.5,13.409H29.2a.966.966,0,0,0,.922-.7L33.356,4.09a.96.96,0,0,0-.922-1.217Zm2.712,24.9a2.873,2.873,0,1,1,2.873,2.873A2.874,2.874,0,0,1,7.662,27.777Zm2.873,1.916A1.916,1.916,0,1,0,8.62,27.777,1.914,1.914,0,0,0,10.536,29.692ZM30.65,27.777A2.873,2.873,0,1,1,27.777,24.9,2.874,2.874,0,0,1,30.65,27.777Zm-2.873-1.916a1.916,1.916,0,1,0,1.916,1.916A1.914,1.914,0,0,0,27.777,25.861Z"/>
                        </svg>                          
                        
                        <span class="cart-count">
                            {{ $cartAmount }}
                        </span>
                    </div>
                </a>
            </div>
            <nav class="nav-primary-desktop">
                @if (has_nav_menu('desktop_navigation'))
                {!! wp_nav_menu(['theme_location' => 'desktop_navigation', 'menu_class' => 'nav']) !!}
                @endif
                
                <a href="/cart/" class="shopping-bag-link shopping-bag-link--scrolling">
                    <div class="shopping-link-wrapper">
                        
                        <svg xmlns="http://www.w3.org/2000/svg" width="34.347" height="30.65" viewBox="0 0 34.347 30.65">
                            <path id="cart-shopping-thin" d="M3.831,0a.479.479,0,0,1,.463.358L4.7,1.916H32.434A1.942,1.942,0,0,1,34.278,4.35l-3.233,11.5A1.916,1.916,0,0,1,29.2,17.241H8.7L9.949,22.03H29.692a.479.479,0,1,1,0,.958H9.524a.471.471,0,0,1-.407-.359L3.461.958H.479A.479.479,0,0,1,.479,0ZM4.951,2.873l3.5,13.409H29.2a.966.966,0,0,0,.922-.7L33.356,4.09a.96.96,0,0,0-.922-1.217Zm2.712,24.9a2.873,2.873,0,1,1,2.873,2.873A2.874,2.874,0,0,1,7.662,27.777Zm2.873,1.916A1.916,1.916,0,1,0,8.62,27.777,1.914,1.914,0,0,0,10.536,29.692ZM30.65,27.777A2.873,2.873,0,1,1,27.777,24.9,2.874,2.874,0,0,1,30.65,27.777Zm-2.873-1.916a1.916,1.916,0,1,0,1.916,1.916A1.914,1.914,0,0,0,27.777,25.861Z"/>
                        </svg>                          
                        
                        <span class="cart-count">
                            {{ $cartAmount }}
                        </span>
                    </div>
                </a>
            </nav>
        </div>
    </div>
</header>

<div class="new-header-filler"></div>

<div class="banner">
    <div class="container">
        <!-- Mobile menu items -->
        <div class="mobile-container">

            <!-- mobile action bar -->
            <div class="mobile-action-bar">
                <a href="{{ home_url('/') }}">
                    <img loading="lazy" src="{!! $logo[0] !!}" class="mobile-home" alt="iResQ">
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
                                {{ $cartAmount }}
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
    </div>
</div>
<div class="mobile-box-shadow"></div>

<script>
    jQuery(document).on('click', '.banner-v2 .nac-search-form', function() {
        jQuery('.banner-v2 .nac-search-form').addClass('opened');
        jQuery('.banner-v2 .search-field').show(215);
    })

    jQuery('.search-field').on('blur', function() {
        jQuery('.banner-v2 .search-field').hide(215);
        jQuery('.banner-v2 .nac-search-form').removeClass('opened');
    })

    var header = jQuery('.banner-v2');

    jQuery(window).on('scroll', function() {
        if (jQuery(window).scrollTop() > 0) {
            header.addClass('is-scrolling');
        } else {
            header.removeClass('is-scrolling');
        }
    })
    
</script>
