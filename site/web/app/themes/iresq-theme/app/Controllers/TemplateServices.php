<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class TemplateServices extends Controller
{
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
}
