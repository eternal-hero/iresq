<?php

use App\Controllers\PageRepairForm;

/**
 * Delete selected device from user meta.
 *
 * @param int    $user_id
 * @param string $meta_key
 * @param string $remove_value
 */
function remove_meta_device()
{
    // If a user doesnt't have the authorization nonce then boot them out
    if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
        exit('You\'re not in the right place.');
    }
    $user_id = $_POST['user_id'];
    $devices = '' != get_user_meta(get_current_user_id(), 'devices', true) ? get_user_meta(get_current_user_id(), 'devices', true) : [];
    $device_to_remove = $_POST['deviceToRemove'];

    if (isset($devices[$device_to_remove])) {
        unset($devices[$device_to_remove]);
    }

    // Write the user meta record with the removed value
    update_user_meta($user_id, 'devices', $devices);

    return $devices;

    exit();
}
add_action('wp_ajax_remove_meta_device', 'remove_meta_device');
add_action('wp_ajax_nopriv_remove_meta_device', 'remove_meta_device');

/*
 * Renders the forms next page on user action
 *
 * @return void
 */
add_action('wp_ajax_handle_repair_form', 'handle_repair_form');
add_action('wp_ajax_nopriv_handle_repair_form', 'handle_repair_form');
function handle_repair_form()
{
    // set variables
    $html = '';
    $next_type = '';
    $next_options = [];
    $next_class = '';
    $next_title = '';
    $next_card = '';

    // get the post query variables
    $selection_query = !empty($_POST['selectionQuery']) ? $_POST['selectionQuery'] : '';
    $selection_type = !empty($_POST['selectionType']) ? $_POST['selectionType'] : '';
    $form_data = !empty($_POST['formData']) ? $_POST['formData'] : [];

    if (!empty($selection_type) && !empty($selection_query)) {
        if ('device' == $selection_type) {
            $next_type = 'Brand';
            $next_card = 'brand-input';
            $next_class = strtolower($next_type).'-wrapper';
            $next_title = "What brand is your <span class='previous-page-value'>{$form_data['device']}?</span>";
            $next_options = PageRepairForm::getBrands($selection_query);
        } elseif ('brand' == $selection_type) {
            $next_type = 'Model';
            $next_card = 'model-input';
            $next_class = strtolower($next_type).'-wrapper';
            $next_title = "What model is your <span class='previous-page-value'>{$form_data['brand']} {$form_data['device']}?</span>";
            $next_options = PageRepairForm::getModels($form_data['device'], $selection_query);
        }
    }

    $html .=
      "<div class='form-card-wrapper {$next_class}'>
        <h2 class='repair-form-title'>{$next_title}</h2>";

    foreach ($next_options as $query => $name) {
        if ('Model' != $next_type) {
            $image_url = PageRepairForm::getImage($name, $form_data['device']);
        }
        $html .=
        "<label class='form-card {$next_class}-card'>
          <input 
            class='card-input-element'
            type='radio'
            name='{$next_type}'
            value='{$query}'
            data-name={$name}
          />
          <div class='card-input {$next_card}'>
            <h6 class='card-heading'>{$name}</h6>";

        if (!empty($image_url)) {
            $html .= "<img src='{$image_url}' class='card-image'>";
        }

        $html .= '</div></label>';
    }

    $html .= '</div>';

    echo $html;

    exit();
}

/*
 * Updates the serial number
 *
 * @return void
 */
add_action('wp_ajax_update_serial_number', 'update_serial_number');
add_action('wp_ajax_nopriv_update_serial_number', 'update_serial_number');
function update_serial_number()
{
    global $woocommerce;
    $cart = $woocommerce->cart->cart_contents;

    // If a user doesnt't have the authorization nonce then boot them out
    if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
        exit('You\'re not in the right place.');
    }

    if (!WC()->cart->is_empty()) {
        $newSn = (isset($_POST['newSN'])) ? $_POST['newSN'] : '';
        $oldSn = (isset($_POST['oldSn'])) ? $_POST['oldSn'] : '';

        foreach ($cart as $cart_item_key => $cart_item) {
            $device_sn = $cart_item['device_serial_number'];
            if ($device_sn == $oldSn) {
                $woocommerce->cart->cart_contents[$cart_item_key]['device_serial_number'] = $newSn;
            }
        }
        $woocommerce->cart->set_session();
    }
    wp_die();
}
