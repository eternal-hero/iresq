<?php

namespace App;

use Roots\Sage\Assets\JsonManifest;
use Roots\Sage\Container;
use Roots\Sage\Template\Blade;
use Roots\Sage\Template\BladeProvider;

// Theme assets
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('sage/main.css', asset_path('css/main.css'), false, null);
    wp_enqueue_style('sage/critical.css', asset_path('css/critical.css'), false, null);
    wp_enqueue_script('sage/main.js', asset_path('js/main.js'), ['jquery'], null, false);
    wp_enqueue_script('sage/vue.js', asset_path('js/vue.js'), null, null, true);

    if (is_single() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }

    /**
     * Add image objects here to localize the full dist path in javascript files.
     * They can be targeted in JS files as such: iresq_logos.small_red_logo.
     */
    $iresq_data = [
        'homeUrl' => get_bloginfo('url'),
        'small_red_logo' => asset_path('images/primary-logo-small.png'),
        'small_white_logo' => asset_path('images/secondary-logo-white-small.png'),
    ];

    wp_localize_script('sage/main.js', 'iresq_ajax', ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('ajax-nonce')]);
    wp_localize_script('sage/main.js', 'iresq_logos', $iresq_data);
}, 100);

// Theme setup
add_action('after_setup_theme', function () {
    /*
     * Enable features from Soil when plugin is activated
     * @link https://roots.io/plugins/soil/
     */
    add_theme_support('soil', [
        'clean-up',
        'disable-asset-versioning',
        'disable-trackbacks',
        /*'google-analytics' => [
            'should_load' => true,
            'google_analytics_id' => 'UA-XXXYYY',
            'anonymize_ip' => true,
        ],*/
        'nav-walker',
        'nice-search',
        'relative-urls',
    ]);

    add_theme_support(
        'custom-logo',
        array(
            'height'      => 248,
            'width'       => 262,
            'flex-height' => true,
        )
    );

    // Enable features from woocommerce when plugin is activated
    add_theme_support('woocommerce');

    /*
     * Enable plugins to manage the document title
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /*
     * Register navigation menus
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
        'desktop_navigation' => __('Desktop Navigation', 'sage'),
        'secondary_navigation' => __('Secondary Navigation', 'sage'),
        'footer_primary' => __('Footer Primary Navigation', 'sage'),
        'footer_secondary' => __('Footer Secondary Navigation', 'sage'),
        'footer_tertiary' => __('Footer Tertiary Navigation', 'sage'),
    ]);

    /*
     * Enable post thumbnails
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /*
     * Enable HTML5 markup support
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', ['caption', 'comment-form', 'comment-list', 'gallery', 'search-form']);

    /*
     * Enable selective refresh for widgets in customizer
     * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#theme-support-in-sidebars
     */
    add_theme_support('customize-selective-refresh-widgets');

    /*
     * Use main stylesheet for visual editor
     * @see resources/assets/styles/layouts/_tinymce.scss
     */
    add_editor_style(asset_path('styles/main.css'));
}, 20);

// Register sidebars
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];
    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);
    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});

/*
 * Updates the `$post` variable on each iteration of the loop.
 * Note: updated value is only available for subsequently loaded views, such as partials
 */
add_action('the_post', function ($post) {
    sage('blade')->share('post', $post);
});

// Setup Sage options
add_action('after_setup_theme', function () {
    // Add JsonManifest to Sage container
    sage()->singleton('sage.assets', function () {
        return new JsonManifest(config('assets.manifest'), config('assets.uri'));
    });

    // Add Blade to Sage container
    sage()->singleton('sage.blade', function (Container $app) {
        $cachePath = config('view.compiled');
        if (!file_exists($cachePath)) {
            wp_mkdir_p($cachePath);
        }
        (new BladeProvider($app))->register();

        return new Blade($app['view']);
    });

    // Create @asset() Blade directive
    sage('blade')->compiler()->directive('asset', function ($asset) {
        return '<?= '.__NAMESPACE__."\\asset_path({$asset}); ?>";
    });
    
    // Alias your blade components here
    // sage('blade')->compiler()->component('components.secondary-hero', 'secondary_hero');
    // sage('blade')->compiler()->component('components.iresq-button', 'button');
    // sage('blade')->compiler()->component('components.image-content-right', 'image_content_right');
    // sage('blade')->compiler()->component('components.image-content-right-secondary', 'image_content_right_secondary');
    // sage('blade')->compiler()->component('components.image-content-left', 'image_content_left');
    // sage('blade')->compiler()->component('components.text-field', 'textfield');
    // sage('blade')->compiler()->component('components.blog-post', 'blog_post');
    // sage('blade')->compiler()->component('components.iresq-text', 'iresq_text');
    // sage('blade')->compiler()->component('components.iresq-select', 'iresq_select');
    // sage('blade')->compiler()->component('components.iresq-textarea', 'iresq_textarea');
    // sage('blade')->compiler()->component('components.iresq-quote', 'iresq_quote');
    // sage('blade')->compiler()->component('components.tabbed-hero', 'tabbed_hero');
    // sage('blade')->compiler()->component('components.testimonial-slider', 'testimonial_slider');
    // sage('blade')->compiler()->component('components.iresq-unordered-list-item', 'iresq_li');
    // sage('blade')->compiler()->component('components.banner-cta', 'banner_cta');
    // sage('blade')->compiler()->component('components.secondary-banner-cta', 'secondary_banner_cta');
    // sage('blade')->compiler()->component('components.star-rating', 'star_rating');
    // sage('blade')->compiler()->component('components.circle-images', 'circle_images');
    // sage('blade')->compiler()->component('components.horizontal-accordion', 'horizontal_accordion');
    // sage('blade')->compiler()->component('components.card-tiles', 'card_tiles');
    // sage('blade')->compiler()->component('components.tabbed-section', 'tabbed_section');
    // sage('blade')->compiler()->component('components.form-content-left', 'form_content_left');
    // sage('blade')->compiler()->component('components.text-blocks', 'text_blocks');
    // sage('blade')->compiler()->component('components.horizontal-text-accordion', 'horizontal_text_accordion');
    // sage('blade')->compiler()->component('components.form-quote-right', 'form_quote_right');
    // sage('blade')->compiler()->component('components.text-only', 'text_only');
    // sage('blade')->compiler()->component('components.icon-bullet-list', 'icon_bullet_list');
    // sage('blade')->compiler()->component('components.footer-ribbon', 'footer_ribbon');
    // sage('blade')->compiler()->component('components.homepage-hero', 'homepage_hero');
    // sage('blade')->compiler()->component('components.category-cards', 'category_cards');
    // sage('blade')->compiler()->component('components.hero-button-group', 'hero_button_group');
});

// B2B role.
add_role(
    'business',
    __('Corporate/Educational Account'),
    [
        'read' => true,
    ]
);

// Add universal custom fields for site settings
if (function_exists('acf_add_options_page')) {
    acf_add_options_page([
        'page_title' => 'Global Fields',
        'menu_title' => 'Global Fields',
        'menu_slug' => 'global-fields',
        'redirect' => false,
    ]);
}

// Remove the unused product taxonomies from the product table
add_action('admin_init', function () {
    add_filter('manage_product_posts_columns', function ($columns) {
        unset($columns['product_tag']);

        return $columns;
    }, 100);
});

// Set the content for the devices endpoint on the account page
add_action('woocommerce_account_devices_endpoint', function () {
    $devices = [];

    wc_get_template('myaccount/devices.blade.php', [
        'devices' => $devices,
    ]);
});

/*
 * Set the content for the item details endpoint
 *
 * @param String $order_item_number
 */
add_action('woocommerce_account_view-items_endpoint', function ($order_item_number) {
    wc_get_template('order/view-items.blade.php', [
        'order_item_number' => $order_item_number,
    ]);
}, 10, 2);

// Redirect the user from the default woocommerce account dashboard page
add_action('template_redirect', function () {
    /*
     * If you're on the my-account page and the endpoint query is empty,
     * you're on the top level dashboard. If on the dashboard, redirect to
     * the edit-account endpoint.
     */
    if (is_account_page() && empty(WC()->query->get_current_endpoint())) {
        wp_safe_redirect(wc_get_account_endpoint_url('edit-account'));

        exit;
    }
});

/*
 * Add serial number(s) to cart item meta
 *
 * @param Object $item
 * @param String $cart_item_key
 * @param Array $values
 * @param Object $order
 */
add_action('woocommerce_checkout_create_order_line_item', function ($item, $cart_item_key, $values, $order) {
    foreach ($item as $cart_item_key => $values) {
        if (isset($values['device_serial_number'])) {
            $item->add_meta_data(__('device_serial_number', 'woocommerce'), $values['device_serial_number'], true);
        }
        if (isset($values['device_number'])) {
            $item->add_meta_data(__('device_number', 'woocommerce'), $values['device_number'], true);
        }
    }
}, 10, 4);

/*
 * Create the item numbers for a new WooCommerce order
 *
 * Update status by this line via Woo API
 * $new_status;
 * $order;
 * wc_update_order_item_meta()
 *
 * @param String $item_id Current item ID
 * @param Object $item WooCommerce item object
 * @param String $order_id Parent order ID for the current item
 */
add_action('woocommerce_new_order_item', function ($item_id, $item, $order_id) {
    $order = wc_get_order($order_id);
    $on = $order->get_order_number();
    $sn_in_array = false;
    $device_number = wc_get_order_item_meta($item_id, 'device_number');
    $order_items = $order->get_items();
    $order_items_count = count($order_items);
    $new_item_number = $on.sprintf('%03d', $order_items_count); // Adds leading zeroes up to 3 digits. Example - 1 becomes 001 and 891 stays at 891
    $item_details = [
        'item_number' => $new_item_number,
        'item_status' => 'Process',
    ];

    /*
     * Create item numbers for each unique device.
     * Unique devices are determined by their device number in the cart.
     *
     */
    if ($order_items_count > 1) {
        $matching_item_number = '';
        foreach ($order_items as $order_item_id => $order_item) {
            $order_item_device_number = wc_get_order_item_meta($order_item_id, 'device_number');
            $order_item_in = wc_get_order_item_meta($order_item_id, 'device_item_details');

            if ($order_item_in && ($device_number == $order_item_device_number) && ($order_item_id != $item_id)) {
                $sn_in_array = true;
                $matching_item_number = $order_item_in['item_number'];

                break;
            }
        }

        if ($sn_in_array) {
            $item_details['item_number'] = $matching_item_number;
            wc_add_order_item_meta($item_id, __('device_item_details', 'woocommerce'), $item_details);
        } else {
            wc_add_order_item_meta($item_id, __('device_item_details', 'woocommerce'), $item_details);
        }
    } else {
        wc_add_order_item_meta($item_id, __('device_item_details', 'woocommerce'), $item_details);
    }
}, 99, 3);

/*
 * Add PO# field to the checkout
 *
 * @param Object $checkout WooCommerce checkout object
 */
add_action('woocommerce_after_order_notes', function ($checkout) {
    echo "<h6 class='client-po-header'>Have a Purchase Order number? Enter it here </h6>";
    woocommerce_form_field('client_po', [
        'type' => 'text',
        'class' => ['client-po', 'form-row-wide'],
        'label' => __(''),
        'placeholder' => __('Enter PO#...'),
    ], $checkout->get_value('client_po'));
});

/*
 * Display Client PO# on the order edit page
 *
 * @param Object $order WooCommerce order object
 */
add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
    echo '<p><strong>'.__('Client PO#').':</strong> '.get_post_meta($order->get_id(), 'Client PO#', true).'</p>';
}, 10, 1);

add_filter('cron_schedules', function ($schedules) {
    if (!isset($schedules["5min"])) {
        $schedules["5min"] = array(
            'interval' => 5*60,
            'display' => __('Once every 5 minutes'));
    }
    return $schedules;
});
