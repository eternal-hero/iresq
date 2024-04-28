<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class App extends Controller
{
    public function siteName()
    {
        return get_bloginfo('name');
    }

    public static function title()
    {
        if (is_home()) {
            if ($home = get_option('page_for_posts', true)) {
                return get_the_title($home);
            }
            return __('Latest Posts', 'sage');
        }
        if (is_archive()) {
            return get_the_archive_title();
        }
        if (is_search()) {
            return sprintf(__('Search Results for %s', 'sage'), get_search_query());
        }
        if (is_404()) {
            return __('Not Found', 'sage');
        }
        return get_the_title();
    }

    public static function ribbonContent() {
        return get_field('ribbon_content', 'options');
    }

    public static function getSelectedOptions($tax, $placeholder) {
        $type = get_terms( array(
            'taxonomy' => $tax,
            'hide_empty' => false,
        ));
        $array = [$placeholder];
        foreach ($type as $term){
        array_push($array, $term->name);
        }
        return $array;
    }

    public static function blogQuery($repair, $device, $content) {
        if (get_query_var('paged')) {
            $paged = get_query_var('paged');
        } elseif (get_query_var('page')) {
            $paged = get_query_var('page');
        } else {
            $paged = 1;
        }

        // BUILD TAX_QUERY ACCORDION TO SELECTED FILTERS
        $taxonomyQuery = 
        array(
          'relation' => 'AND',
        );
        if ($repair != "") {
            $taxonomyQuery[0] = array(
                'taxonomy' => 'repair_type',
                'field'    => 'slug',
                'terms' => $repair
            );
        };
        if ($device != "") {
            $taxonomyQuery[0] = array(
                'taxonomy' => 'device',
                'field'    => 'slug',
                'terms' => $device
            );
        };
        if ($content != "") {
            $taxonomyQuery[0] = array(
                'taxonomy' => 'content_type',
                'field'    => 'slug',
                'terms' => $content
            );
        };
        $args =  array(
            'post_type' => 'post',
            'posts_per_page' => get_option( 'posts_per_page' ),
            'tax_query' => $taxonomyQuery,
            'paged' => $paged
        );

        //$filtered_posts = new \WP_Query($args);
        // if ($filtered_posts->have_posts()) {
        //     return $filtered_posts;
        // } else { 
        //     $filtered_posts = "noPosts";
        //     return $filtered_posts;
        // }
        return new \WP_Query($args);
    }
}


