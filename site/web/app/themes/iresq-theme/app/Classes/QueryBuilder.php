<?php

namespace App\Classes;

Class QueryBuilder {
  /**
     * Abstraction of the WP_Query.
     *
     * @param $args
     * @return \WP_Query
     */
    public static function getWPQuery($args)
    {
        return new \WP_Query($args);
    }

    /**
     * Prepares query by taxonomy.
     *
     * @param string $taxonomy
     * @param array $term_slugs, allows for multiple terms
     * @return \WP_Query
     */
    public static function queryProductsByTaxonomy(string $taxonomy, array $term_slugs)
    {
        return new \WP_Query([
            'post_type'             => 'product',
            'post_status'           => 'publish',
            'order'                 => 'ASC',
            'posts_per_page'        => -1,
            'tax_query'             => [
                [
                    'taxonomy'  => $taxonomy,
                    'field'     => 'slug',
                    'terms'     => $term_slugs,
                ],
            ],
        ]);
    }

    /**
     * Get posts filtered by taxonomy
     *
     * @param string $postType
     * @param string $taxonomy
     * @param array $terms
     * @return \WP_Query
     */
    public static function getTaxQuery(string $postType, string $taxonomy, array $terms = [])
    {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        return new \WP_Query([
            'post_type' => $postType,
            'post_status' => 'publish',
            'order' => 'ASC',
            'posts_per_page' => 10,
            'paged' => $paged,
            'tax_query' => [
                [
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $terms,
                ],
            ]
        ]);
    }

    /**
     * Returns all members of a category
     *
     * @param string $category
     * @param string $orderby
     * @param string $order
     * @param boolean $hide_empty
     * @return WP_Term|int|WP_Error
     */
    public static function getTermsByName(string $category, bool $hide_empty = false, string $orderby = 'name', string $order = 'desc') {
      $cat_args = array(
        'taxonomy' => $category,
        'orderby' => $orderby,
        'order' => $order,
        'hide_empty' => $hide_empty
      );

      $categories = get_terms($cat_args);

      return $categories;
    }
}