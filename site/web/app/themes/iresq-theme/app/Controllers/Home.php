<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class Home extends Controller {
  /**
   * Allows the ACF Field Groups to be grabbed just by the name.
   * 
   * e.g. if you have a field group named test_paragraph on this page,
   * then you can reference that value in the corresponding blade
   * template file by calling $test_paragraph.
   *
   * @var boolean
   */
  protected $acf = true;

  
  /**
   * the $protected acf = true line doesn't work for items in the 
   * WordPress post loop. So we'll need to create some static functions
   * to grab the ACF values we need for each post.
   */

  /**
   * Gets the ACF post_preview_text field
   *
   * @return string
   */
  public static function getPreviewText() {
    return get_field('post_preview_text');
  }

  /**
   * Gets the ACF post_preview_size field
   *
   * @return string
   */
  public static function getPreviewSize() {
    return get_field('post_preview_size');
  }

  /**
   * Checks if the post has a content type of video
   *
   * @param array $terms
   * @return boolean
   */
  public static function isVideo($terms) {
    if($terms) {
      foreach ($terms as $term) {
        if ( $term->slug == 'video' ) {
          return true;
        }
      }
    }
    return false;
  }
}