<?php

/**
 * Do not edit anything in this file unless you know what you're doing.
 */

use Roots\Sage\Config;
use Roots\Sage\Container;

/**
 * Helper function for prettying up errors.
 *
 * @param string $message
 * @param string $subtitle
 * @param string $title
 */
$sage_error = function ($message, $subtitle = '', $title = '') {
    $title = $title ?: __('Sage &rsaquo; Error', 'sage');
    $footer = '<a href="https://roots.io/sage/docs/">roots.io/sage/docs/</a>';
    $message = "<h1>{$title}<br><small>{$subtitle}</small></h1><p>{$message}</p><p>{$footer}</p>";
    wp_die($message, $title);
};

// Ensure compatible version of PHP is used
if (version_compare('7.1', phpversion(), '>=')) {
    $sage_error(__('You must be using PHP 7.1 or greater.', 'sage'), __('Invalid PHP version', 'sage'));
}

// Ensure compatible version of WordPress is used
if (version_compare('4.7.0', get_bloginfo('version'), '>=')) {
    $sage_error(__('You must be using WordPress 4.7.0 or greater.', 'sage'), __('Invalid WordPress version', 'sage'));
}

// Ensure dependencies are loaded
if (!class_exists('Roots\\Sage\\Container')) {
    if (!file_exists($composer = __DIR__ . '/../vendor/autoload.php')) {
        $sage_error(
            __('You must run <code>composer install</code> from the Sage directory.', 'sage'),
            __('Autoloader not found.', 'sage')
        );
    }

    require_once $composer;
}

/*
 * Sage required files
 *
 * The mapped array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 */
array_map(function ($file) use ($sage_error) {
    $file = "../app/{$file}.php";
    if (!locate_template($file, true, true)) {
        $sage_error(sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file), 'File not found');
    }
}, ['helpers', 'setup', 'filters', 'admin', 'order-processing', 'post-types', 'WooCommerce/GenericFilters', 'WooCommerce/OrderProcessing', 'WooCommerce/Cart', 'WooCommerce/Checkout', 'WpAjax/wp_ajax_calls', 'RestAPI/OrderProcessing', 'B2B/BulkOrdersPage', 'WooCommerce/BulkOrdersProcessing', 'options-pages']);

/*
 * Here's what's happening with these hooks:
 * 1. WordPress initially detects theme in themes/sage/resources
 * 2. Upon activation, we tell WordPress that the theme is actually in themes/sage/resources/views
 * 3. When we call get_template_directory() or get_template_directory_uri(), we point it back to themes/sage/resources
 *
 * We do this so that the Template Hierarchy will look in themes/sage/resources/views for core WordPress themes
 * But functions.php, style.css, and index.php are all still located in themes/sage/resources
 *
 * This is not compatible with the WordPress Customizer theme preview prior to theme activation
 *
 * get_template_directory()   -> /srv/www/example.com/current/web/app/themes/sage/resources
 * get_stylesheet_directory() -> /srv/www/example.com/current/web/app/themes/sage/resources
 * locate_template()
 * ├── STYLESHEETPATH         -> /srv/www/example.com/current/web/app/themes/sage/resources/views
 * └── TEMPLATEPATH           -> /srv/www/example.com/current/web/app/themes/sage/resources
 */

array_map(
    'add_filter',
    ['theme_file_path', 'theme_file_uri', 'parent_theme_file_path', 'parent_theme_file_uri'],
    array_fill(0, 4, 'dirname')
);
Container::getInstance()
    ->bindIf('config', function () {
        return new Config([
            'assets' => require dirname(__DIR__) . '/config/assets.php',
            'theme' => require dirname(__DIR__) . '/config/theme.php',
            'view' => require dirname(__DIR__) . '/config/view.php',
        ]);
    }, true);

/*
 * Create a new rule type in ACF
 *
 * Since we are using clone fields for each page this will make the rules obvious
 * for each ACF component that will be cloned.
 */
add_filter('acf/location/rule_types', function ($rules) {
    $rules['Extra']['nowhere'] = 'Nowhere';

    return $rules;
});

add_filter('acf/location/rule_operators/nowhere', function ($choices) {
    if (isset($choices['=='])) {
        unset($choices['==']);
    }

    if (isset($choices['!='])) {
        unset($choices['!=']);
    }

    $choices['nowhere'] = '-';

    return $choices;
});

add_filter('acf/location/rule_values/nowhere', function ($choices) {
    return ['nowhere' => '-'];
});

add_filter('acf/location/rule_match/nowhere', function ($match, $rule, $options) {
    return false;
}, 10, 3);

/**
 * Intialize the woocommerce form to create devices.
 */
function iresq_get_account_fields()
{
    return apply_filters('iresq_account_fields', [
        'user_device' => [
            'type' => 'text',
            'label' => __('Devices', 'iresq'),
            'placeholder' => __('Jims iPad', 'iresq'),
            'required' => true,
            'hide_in_account' => false,
            'hide_in_admin' => false,
            'hide_in_checkout' => true,
            'hide_in_registration' => true,
            'sanitize' => 'wc_clean',
        ],
    ]);
}

/**
 * Create the URL for the item details view.
 *
 * @param [type] $item_number
 * @param [type] $order_id
 */
function get_item_url($item_number, $order_id)
{
    return apply_filters('woocommerce_get_item_details_url', wc_get_endpoint_url('view-items', $order_id . '-' . $item_number, wc_get_page_permalink('myaccount')));
}

function limit_text($text, $limit, $postId = null)
{
    if (str_word_count($text, 0) > $limit) {
        $words = str_word_count($text, 2);
        $pos = array_keys($words);
        $text = substr($text, 0, $pos[$limit]) . '...';

        if (!is_null($postId)) {
            $text .= ' <a href="' . get_the_permalink($postId) . '" style="color: #b2111e;text-decoration: none;">Read More</a>';
        }
    }

    return $text;
}

/**
 * Exclude categories from dynamic choices.
 *
 * @link https://wpforms.com/developers/how-to-exclude-posts-pages-or-categories-from-dynamic-choices/
 */

function wpf_dev_dynamic_choices_exclude($args, $field, $form_id)
{

    if (is_array($form_id)) {
        $form_id = $form_id['id'];
    }

    // Only on form #212 and field #16
    if ($form_id == 212 && $field['id'] == 16) {

        // Category IDs to exclude
        $args['exclude'] = '4,5,6,11';
    }

    return $args;
}

/**
 * Exclude categories from dynamic choices.
 *
 * @link https://wpforms.com/developers/how-to-exclude-posts-pages-or-categories-from-dynamic-choices/
 */
add_filter('wpforms_dynamic_choice_taxonomy_args', function ($args, $field, $form_id) {
    if ($field['dynamic_taxonomy'] == 'product_cat') {
        $terms = get_terms('product_cat', [
            'hide_empty' => false,
        ]);
        $excludedTerms = ['Uncategorized'];
        foreach ($terms as $key => $term) {
            if (in_array($term->name, $excludedTerms)) {
                unset($terms[$key]);
            }
        }
        // Category IDs to exclude
        $args['include'] = implode(',', array_column($terms, 'term_id'));
    }

    return $args;
}, 10, 3);
