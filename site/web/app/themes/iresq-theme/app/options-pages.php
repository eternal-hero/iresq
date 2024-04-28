<?php 

namespace App;

// Add options page function
function add_iresq_team_options_page() {
    if( function_exists('acf_add_options_page') ) {
        acf_add_options_sub_page(array(
            'page_title'     => 'iResQ Team Options',
            'menu_title'    => 'iResQ Team Options',
            'parent_slug'     => 'edit.php?post_type=iresq_team',
        ));
    }
}
add_action('acf/init', __NAMESPACE__ . '\\add_iresq_team_options_page');
