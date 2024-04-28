<?php

namespace App;

use App\Filemaker\ShippingMethods;
use App\Shipping\FileMakerShippingMethods;
use App\Shipping\ShippingMethodTable;
use App\WCEmails\AdminRepaymentEmail;
use App\WCEmails\AwaitingApprovalEmail;
use App\WCEmails\NoShippingEmail;

// Theme customizer
add_action('customize_register', function (\WP_Customize_Manager $wp_customize) {
    // Add postMessage support
    $wp_customize->get_setting('blogname')->transport = 'postMessage';
    $wp_customize->selective_refresh->add_partial('blogname', [
        'selector' => '.brand',
        'render_callback' => function () {
            bloginfo('name');
        },
    ]);
});

// Customizer JS
add_action('customize_preview_init', function () {
    wp_enqueue_script('sage/customizer.js', asset_path('js/customizer.js'), ['customize-preview'], null, true);
});

// Custom Shipping Methods
add_action('admin_menu', function () {
    add_submenu_page('woocommerce', 'iResQ - FileMaker Shipping', 'iResQ - FileMaker Shipping', 'manage_woocommerce', 'iresq-filemaker-shipping', __NAMESPACE__ . '\\filemakerShippingAdminPage');
});

function filemakerShippingAdminPage()
{
    if (isset($_POST['action']) && 'iresq_filemaker_shipping_methods_refresh' == $_POST['action']) {
        updateShippingMethods();
    }
    $shippingMethodTable = new ShippingMethodTable();
    $shippingMethodTable->prepare_items(); ?>
    <div class="wrap">
        <h1>iResQ - FileMaker Shipping</h1>
        <form method="post" action="admin.php?page=iresq-filemaker-shipping">
            <input type="hidden" name="action" value="iresq_filemaker_shipping_methods_refresh" />
            <input type="submit" value="Update Shipping Methods" class="button save_order button-primary" />
        </form>
        <?php $shippingMethodTable->display(); ?>
    </div>
<?php
}

function updateShippingMethods()
{
    $fmShippingMethods = new ShippingMethods();
    $results = $fmShippingMethods->fetchAllRecords();
    $data = [];
    foreach ($results as $result) {
        $splitModel = explode('-', $result->field('Model'));
        $deviceType = count($splitModel) > 1 ? trim($splitModel[1]) : 'None';
        $data[] = [
            'name' => $result->field('Name'),
            'model' => $result->field('Model'),
            'device_type' => $deviceType,
            'price' => $result->field('Unit Price'),
            'sku' => $result->field('SKU'),
            'product_id' => $result->field('Product ID'),
            'height' => $result->field('Height'),
            'weight' => $result->field('Weight'),
            'length' => $result->field('Length'),
            'width' => $result->field('Width'),
            'code' => $result->field('Ship By'),
        ];
    }
    $exists = get_option('iresq_shipping_methods', null);
    if (is_null($exists)) {
        add_option('iresq_shipping_methods', $data);
    } else {
        update_option('iresq_shipping_methods', $data);
    }
    do_action('woocommerce_shipping_init');
}

add_action('woocommerce_shipping_init', function () {
    if (isset($_POST['action']) && 'iresq_filemaker_shipping_methods_refresh' == $_POST['action']) {
        $fileMakerOptions = get_option('iresq_shipping_methods');
        $uniqueFMOptions = [];
        $newFedexOptions = [];
        $newUspsOptions = [];
        foreach ($fileMakerOptions as $option) {
            if (in_array($option['device_type'], $uniqueFMOptions)) {
                continue;
            }
            $uniqueFMOptions[] = strval($option['device_type']);
            $newFedexOptions[] = [
                'name' => $option['device_type'],
                'id' => $option['device_type'],
                'max_weight' => 20,
                'box_weight' => $option['weight'],
                'length' => $option['length'],
                'width' => $option['width'],
                'height' => $option['height'],
                'inner_length' => '',
                'inner_width' => '',
                'inner_height' => '',
                'enabled' => true,
            ];
            $newUspsOptions[] = [
                'name' => $option['device_type'],
                'id' => $option['device_type'],
                'max_weight' => 20,
                'box_weight' => floatval($option['weight']),
                'outer_length' => floatval($option['length']),
                'outer_width' => floatval($option['width']),
                'outer_height' => floatval($option['height']),
                'inner_length' => 0,
                'inner_width' => 0,
                'inner_height' => 0,
                'letter' => false,
            ];
        }
        if (class_exists('wf_fedex_woocommerce_shipping_method')) {
            $fedexMethod = new \wf_fedex_woocommerce_shipping_method();
            $fedexMethod->update_option('boxes', $newFedexOptions);
        }
        if (class_exists('WF_Easypost')) {
            $uspsMethod = new \WF_Easypost();
            $uspsMethod->update_option('boxes', $newUspsOptions);
        }
    }
});

add_action('iresq_update_shipping', __NAMESPACE__ . '\\updateShippingMethods');

if (!wp_next_scheduled('iresq_update_shipping')) {
    wp_schedule_event(time(), 'daily', 'iresq_update_shipping');
}

add_action('woocommerce_shipping_init', function () {
    new FileMakerShippingMethods();
});

// Add a custom email to the list of emails WooCommerce should load
add_filter('woocommerce_email_classes', function ($email_classes) {
    require 'WCEmails/NoShippingEmail.php';

    require 'WCEmails/AwaitingApprovalEmail.php';

    require 'WCEmails/AdminRepaymentEmail.php';
    $email_classes['NoShippingEmail'] = new NoShippingEmail();
    $email_classes['AwaitingApprovalEmail'] = new AwaitingApprovalEmail();
    $email_classes['AdminRepaymentEmail'] = new AdminRepaymentEmail();

    return $email_classes;
});

add_filter('woocommerce_shipping_methods', function ($methods) {
    $methods['iresq_filemaker'] = 'App\Shipping\FileMakerShippingMethods';

    return $methods;
});

add_action('woocommerce_before_calculate_totals', function ($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    if (did_action('woocommerce_before_calculate_totals') >= 2) {
        return;
    }

    $shippingMethods = $cart->calculate_shipping();
    if (0 == count($shippingMethods)) {
        return;
    }

    $height = 0;
    $width = 0;
    $length = 0;
    $weight = 0;
    $updateProductMeta = true;

    foreach ($shippingMethods as $method) {
        $meta = $method->get_meta_data();
        $height = $meta['height'];
        $width = $meta['width'];
        $length = $meta['length'];
        $weight = $meta['weight'];
        $updateProductMeta = $meta['updateProductMeta'];
    }

    if ($updateProductMeta) {
        foreach ($cart->get_cart() as $cart_item) {
            $product = wc_get_product($cart_item['data']->get_id());
            if ('' == $product->get_weight() || '' == $product->get_width()) {
                $product->set_weight($weight);
                $product->set_height($height);
                $product->set_width($width);
                $product->set_length($length);
                $product->save();
            }
        }
    }
}, 10000);

add_action('admin_init', function () {
    add_filter('manage_users_columns', function ($columns) {
        return array_slice($columns, 0, 1, true) + ['user_id' => 'ID'] + array_slice($columns, 1, count($columns) - 1, true);
    });

    add_filter('manage_users_custom_column', function ($value, $column_name, $user_id) {
        if ('user_id' == $column_name) {
            return $user_id;
        }

        return $value;
    }, 10, 3);

    add_filter('manage_users_sortable_columns', function ($columns) {
        // Add our columns to $columns array
        $columns['user_id'] = 'ID';

        return $columns;
    });
});

add_action('admin_head-users.php', function () {
    echo '<style>.column-user_id{width: 3%}</style>';
});
