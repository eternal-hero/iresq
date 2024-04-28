<?php

namespace App;

// Register taxonomy for the posts page
add_action('init', function () {
    // remove the woocommerce breadcrumbs
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);

    register_taxonomy(
        'repair_type',
        'post',
        [
            'label' => __('Repair Type'),
            'rewrite' => ['slug' => 'repair_type'],
            'hierarchical' => true,
            'show_admin_column' => true,
        ]
    );
    register_taxonomy(
        'device',
        'post',
        [
            'label' => __('Device'),
            'rewrite' => ['slug' => 'device'],
            'hierarchical' => true,
            'show_admin_column' => true,
        ]
    );
    register_taxonomy(
        'content_type',
        'post',
        [
            'label' => __('Content Type'),
            'rewrite' => ['slug' => 'content_type'],
            'hierarchical' => true,
            'show_admin_column' => true,
        ]
    );
    register_taxonomy('category', []);
    register_taxonomy('post_tag', []);

    /**
     * Custom product taxonomy.
     */
    $repair_labels = [
        'name' => 'Repairs',
        'singular_name' => 'Repair',
        'menu_name' => 'Repairs',
        'all_items' => 'All Repairs',
        'parent_item' => 'Parent Repair',
        'parent_item_colon' => 'Parent Repair:',
        'new_item_name' => 'New Repair',
        'add_new_item' => 'Add New Repair',
        'edit_item' => 'Edit Repair',
        'update_item' => 'Update Repair',
        'separate_items_with_commas' => 'Separate Repairs with commas',
        'search_items' => 'Search Repairs',
        'add_or_remove_items' => 'Add or remove Repairs',
        'choose_from_most_used' => 'Choose from the most used repairs',
        'no_terms' => 'No repairs',
        'items_list' => 'Repairs list',
        'items_list_navigation' => 'Repairs list navigation',
        'back_to_items' => 'Back to repairs',
    ];
    $repair_args = [
        'labels' => $repair_labels,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_in_menu' => true,
        'publicly_queryable' => true,
        'show_tagcloud' => true,
        'rewrite' => ['slug' => 'repairs'],
    ];
    register_taxonomy('repairs', 'product', $repair_args);

    // Unregister product taxonomy that isn't used
    register_taxonomy('product_tag', 'product', [
        'public' => false,
        'show_ui' => false,
        'show_admin_column' => false,
        'show_in_nav_menus' => false,
        'show_tagcloud' => false,
    ]);

    // Register new account page endpoint
    add_rewrite_endpoint('devices', EP_PAGES);
    add_rewrite_endpoint('item-details', EP_ROOT | EP_PAGES);
});

add_action( 'init', __NAMESPACE__ . '\\create_iresq_team_post_type' );

function create_iresq_team_post_type() {
    $labels = array(
        'name'               => _x( 'iResQ Team', 'post type general name', 'textdomain' ),
        'singular_name'      => _x( 'iResQ Team', 'post type singular name', 'textdomain' ),
        'menu_name'          => _x( 'iResQ Team', 'admin menu', 'textdomain' ),
        'name_admin_bar'     => _x( 'iResQ Team', 'add new on admin bar', 'textdomain' ),
        'add_new'            => _x( 'Add New', 'iresq_team', 'textdomain' ),
        'add_new_item'       => __( 'Add New iResQ Team', 'textdomain' ),
        'new_item'           => __( 'New iResQ Team', 'textdomain' ),
        'edit_item'          => __( 'Edit iResQ Team', 'textdomain' ),
        'view_item'          => __( 'View iResQ Team', 'textdomain' ),
        'all_items'          => __( 'All iResQ Team', 'textdomain' ),
        'search_items'       => __( 'Search iResQ Team', 'textdomain' ),
        'parent_item_colon'  => __( 'Parent iResQ Team:', 'textdomain' ),
        'not_found'          => __( 'No iResQ Team found.', 'textdomain' ),
        'not_found_in_trash' => __( 'No iResQ Team found in Trash.', 'textdomain' )
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __( 'Description.', 'textdomain' ),
        'menu_icon'          => 'dashicons-businessperson',
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'team', 'with_front' => false ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => true,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' )
    );

    register_post_type( 'iresq_team', $args );
}

function custom_order_iresq_team_archive( $query ) {
    if ( is_post_type_archive( 'iresq_team' ) && $query->is_main_query() ) {
        $query->set( 'orderby', 'menu_order' );
        $query->set( 'order', 'ASC' );
        $query->set( 'posts_per_page', -1 );
    }
}
add_action( 'pre_get_posts', __NAMESPACE__ . '\\custom_order_iresq_team_archive' );
