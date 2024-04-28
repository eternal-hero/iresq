<?php

namespace App\Shipping;

class ShippingMethodTable extends \WP_List_Table
{
  function __construct()
  {
    global $status, $page;

    parent::__construct(array(
      'singular'  => 'iresq_shipping_method',
      'plural'    => 'iresq_shipping_methods',
      'ajax'      => false
    ));
  }

  function column_default($item, $column_name)
  {
    switch ($column_name) {
      case 'price':
        return "$" . $item[ $column_name ];
      case 'name':
      case 'model':
      case 'device_type':
      case 'code':
        return $item[ $column_name ];
      case 'dimensions':
        return 'Height: ' . $item['height']. '<br>Length: ' . $item['length']. '<br>Width: ' . $item['width']. '<br>Weight: ' . $item['weight'];
      default:
        return print_r($item, true);
    }
  }

  function column_start_date($item)
  {
    return date('F jS, Y h:i a', strtotime($item['start_date']));
  }

  function column_end_date($item)
  {
    return date('F jS, Y h:i a', strtotime($item['end_date']));
  }

  function get_columns()
  {
    $columns = array(
      'name'         => 'Method Name',
      'model'         => 'FileMaker Model',
      'device_type'    => 'Device Type',
      'price'      => 'Price ($)',
      'code'        => 'Code (Ship By)',
      'dimensions' => 'Dimensions',
      'length' => 'Length',
      'height' => 'Height',
      'width' => 'Width',
      'weight' => 'Weight'
    );

    return $columns;
  }

  function get_sortable_columns()
  {
    $sortable_columns = [];

    return $sortable_columns;
  }

  function prepare_items()
  {
    $per_page = 50;
    $columns = $this->get_columns();
    $hidden = ['height', 'weight', 'length', 'width'];
    $currentPage = $this->get_pagenum();
    $sortable = $this->get_sortable_columns();

    $data = get_option('iresq_shipping_methods') ?: [];
    $this->_column_headers = array($columns, $hidden, $sortable);

    
    $this->set_pagination_args(array(
      'total_items'   => count($data),
      'per_page'      => $per_page,
    ));

    $data = array_slice($data,(($currentPage-1)*$per_page),$per_page);
    $this->items = $data;
  }
}
