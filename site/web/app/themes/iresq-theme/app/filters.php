<?php

namespace App;

// Add <body> classes
add_filter('body_class', function (array $classes) {
    // Add page slug if it doesn't exist
    if (is_single() || is_page() && !is_front_page()) {
        if (!in_array(basename(get_permalink()), $classes)) {
            $classes[] = basename(get_permalink());
        }
    }

    // Add class if sidebar is active
    if (display_sidebar()) {
        $classes[] = 'sidebar-primary';
    }

    /** Clean up class names for custom templates */
    $classes = array_map(function ($class) {
        return preg_replace(['/-blade(-php)?$/', '/^page-template-views/'], '', $class);
    }, $classes);

    return array_filter($classes);
});

// Add "… Continued" to the excerpt
add_filter('excerpt_more', function () {
    return ' &hellip; <a href="'.get_permalink().'">'.__('Continued', 'sage').'</a>';
});

// Template Hierarchy should search for .blade.php files
collect([
    'index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date', 'home',
    'frontpage', 'page', 'paged', 'search', 'single', 'singular', 'attachment', 'embed',
])->map(function ($type) {
    add_filter("{$type}_template_hierarchy", __NAMESPACE__.'\\filter_templates');
});

// Render page using Blade
add_filter('template_include', function ($template) {
    collect(['get_header', 'wp_head'])->each(function ($tag) {
        ob_start();
        do_action($tag);
        $output = ob_get_clean();
        remove_all_actions($tag);
        add_action($tag, function () use ($output) {
            echo $output;
        });
    });
    $data = collect(get_body_class())->reduce(function ($data, $class) use ($template) {
        return apply_filters("sage/template/{$class}/data", $data, $template);
    }, []);
    if ($template) {
        echo template($template, $data);

        return get_stylesheet_directory().'/index.php';
    }

    return $template;
}, PHP_INT_MAX);

// Render comments.blade.php
add_filter('comments_template', function ($comments_template) {
    $comments_template = str_replace(
        [get_stylesheet_directory(), get_template_directory()],
        '',
        $comments_template
    );

    $data = collect(get_body_class())->reduce(function ($data, $class) use ($comments_template) {
        return apply_filters("sage/template/{$class}/data", $data, $comments_template);
    }, []);

    $theme_template = locate_template(["views/{$comments_template}", $comments_template]);

    if ($theme_template) {
        echo template($theme_template, $data);

        return get_stylesheet_directory().'/index.php';
    }

    return $comments_template;
}, 100);

// Replace the default get_search_form
add_filter('get_search_form', function () {
    $form = '';
    echo template(realpath(get_template_directory().'/views/partials/searchform.blade.php'), []);

    return $form;
});

// Change add to cart button text on shop and single product page
add_filter('woocommerce_product_add_to_cart_text', function () {
    return __('Add to cart', 'woocommerce');
});
add_filter('woocommerce_product_single_add_to_cart_text', function () {
    return __('Add to cart', 'woocommerce');
});

// Query the new account tabs as valid endpoints
add_filter('woocommerce_get_query_vars', function ($vars) {
    foreach (['devices', 'view-items'] as $e) {
        $vars[$e] = $e;
    }

    return $vars;
});

// Rename My account > Orders "view" action button text
add_filter('woocommerce_my_account_my_orders_actions', function ($actions) {
    if (is_wc_endpoint_url('orders')) {
        $actions['view']['name'] = __('view details', 'woocommerce');
    }

    return $actions;
});

// Adds PO# to emails
add_filter('woocommerce_email_order_meta_keys', function ($keys) {
    $keys[] = 'Client PO#'; // This will look for a custom field called 'Client PO#' and add it to emails

    return $keys;
});

/*
 * Register the Repairs column in the importer.
 *
 * @param array $options
 * @return array $options
 */
add_filter('woocommerce_csv_product_import_mapping_options', function ($columns) {
    // column slug => column name
    $columns['repairs'] = 'Repairs';
    $columns['product_id'] = 'Product ID';
    $columns['product_turnaround'] = 'Product Turnaround';
    $columns['product_complexity'] = 'Product Complexity';
    $columns['short_name'] = 'Short Name';
    $columns['shipping_device_type'] = 'Shipping Device Type';
    $columns['slug'] = 'Slug';

    return $columns;
});

/*
 * Add automatic mapping support for 'Repairs'.
 * This will automatically select the correct mapping for columns named 'Repairs' or 'repairs'.
 *
 * @param array $columns
 * @return array $columns
 */
add_filter('woocommerce_csv_product_import_mapping_default_columns', function ($columns) {
    // potential column name => column slug
    $columns['Repairs'] = 'repairs';
    $columns['repairs'] = 'repairs';
    $columns['Slug'] = 'slug';
    $columns['Product ID'] = 'product_id';
    $columns['product id'] = 'product_id';
    $columns['Product Turnaround'] = 'product_turnaround';
    $columns['product turnaround'] = 'product_turnaround';
    $columns['Product Complexity'] = 'product_complexity';
    $columns['product complexity'] = 'product_complexity';
    $columns['Short Name'] = 'short_name';
    $columns['Shipping Device Type'] = 'shipping_device_type';

    return $columns;
});

/*
 * Set taxonomy.
 *
 * @param  array  $parsed_data
 * @return array
 */
add_filter('woocommerce_product_import_inserted_product_object', function ($product, $data) {
    $repairs = 'repairs';
    if (!empty($data['product_id'])) {
        update_field('product_id', $data['product_id'], $product->get_id());
    }
    if (!empty($data['product_complexity'])) {
        update_field('single_product_complexity', $data['product_complexity'], $product->get_id());
    }
    if (!empty($data['short_name'])) {
        update_field('single_short_name', $data['short_name'], $product->get_id());
    }
    if (!empty($data['shipping_device_type'])) {
        update_field('single_shipping_device_type', $data['shipping_device_type'], $product->get_id());
    }
    if (!empty($data['product_turnaround'])) {
        update_field('single_product_turnaround_time', $data['product_turnaround'], $product->get_id());
    }

    if (!empty($data['slug'])) {
        $product->set_slug(sanitize_title($data['slug']));
    }

    if (is_a($product, 'WC_Product')) {
        if (!empty($data[$repairs])) {
            $product->save();
            $repairs_values = $data[$repairs];
            $repairs_values = explode(',', $repairs_values);
            $terms = [];
            foreach ($repairs_values as $repairs_value) {
                if (!get_term_by('name', $repairs_value, $repairs)) {
                    $repairs_args = [
                        'cat_name' => $repairs_value,
                        'taxonomy' => $repairs,
                    ];
                    $repairs_value_cat = wp_insert_category($repairs_args);
                    array_push($terms, $repairs_value_cat);
                } else {
                    $repairs_value_cat = get_term_by('name', $repairs_value, $repairs)->term_id;
                    array_push($terms, $repairs_value_cat);
                }
            }
            wp_set_object_terms($product->get_id(), $terms, $repairs);
        }
    }
    $product->save();

    return $product;
}, 10, 2);

add_filter('password_hint', function ($hint_text) {
    return 'Please use a strong password. Passwords that consist of special characters (&#%!@), upper case/lower case characters and numbers are considered strong. Your password should be at least 8 characters long.';
}, 10, 1);

add_filter( 'style_loader_tag',  function( $html, $handle ) {
    if (strcmp($handle, 'sage/main.css') == 0) {
        $fallbackHTML = '<noscript>' . $html . '</noscript>';
        $html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html);
        $html .= $fallbackHTML;
    }
    
    return $html;
}, 10, 2 );
