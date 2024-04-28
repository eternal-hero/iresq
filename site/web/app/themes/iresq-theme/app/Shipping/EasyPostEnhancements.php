<?php

namespace App\Shipping;

class EasyPostEnhancements extends \WF_Shipping_Easypost_Admin
{
    private $easypost_services = [
        // Domestic & International

        'USPS' => [
            // Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.

            'services' => [
                'FirstPackage' => 'First-Class Package (USPS)',

                'First' => 'First-Class Mail (USPS)',

                'Priority' => 'Priority Mail&#0174; (USPS)',

                'Express' => 'Priority Mail Express&#8482; (USPS)',

                'ParcelSelect' => 'USPS Parcel Select (USPS)',

                'LibraryMail' => 'Library Mail Parcel (USPS)',

                'MediaMail' => 'Media Mail Parcel (USPS)',

                'CriticalMail' => 'USPS Critical Mail (USPS)',

                'FirstClassMailInternational' => 'First Class Mail International (USPS)',

                'FirstClassPackageInternationalService' => 'First Class Package Service&#8482; International (USPS)',

                'PriorityMailInternational' => 'Priority Mail International&#0174; (USPS)',

                'ExpressMailInternational' => 'Express Mail International (USPS)',
            ],
        ],
        'FedEx' => [
            'services' => [
                'FIRST_OVERNIGHT' => 'First Overnight (FedEx)',

                'PRIORITY_OVERNIGHT' => 'Priority Overnight (FedEx)',

                'STANDARD_OVERNIGHT' => 'Standard Overnight (FedEx)',

                'FEDEX_2_DAY_AM' => 'FedEx 2 Day AM (FedEx)',

                'FEDEX_2_DAY' => 'FedEx 2 Day (FedEx)',

                'FEDEX_EXPRESS_SAVER' => 'FedEx Express Saver (FedEx)',

                'GROUND_HOME_DELIVERY' => 'FedEx Ground Home Delivery (FedEx)',

                'FEDEX_GROUND' => 'FedEx Ground (FedEx)',

                'INTERNATIONAL_PRIORITY' => 'FedEx International Priority (FedEx)',

                'INTERNATIONAL_ECONOMY' => 'FedEx International Economy (FedEx)',

                'INTERNATIONAL_FIRST' => 'FedEx International First (FedEx)',
            ],
        ],
        'UPS' => [
            'services' => [
                'Ground' => 'Ground (UPS)',

                '3DaySelect' => '3 Day Select (UPS)',

                '2ndDayAirAM' => '2nd Day Air AM (UPS)',

                '2ndDayAir' => '2nd Day Air (UPS)',

                'NextDayAirSaver' => 'Next Day Air Saver (UPS)',

                'NextDayAirEarlyAM' => 'Next Day Air Early AM (UPS)',

                'NextDayAir' => 'Next Day Air (UPS)',

                'Express' => 'Express (UPS)',

                'Expedited' => 'Expedited (UPS)',

                'ExpressPlus' => 'Express Plus (UPS)',

                'UPSSaver' => 'UPS Saver (UPS)',

                'UPSStandard' => 'UPS Standard (UPS)',
            ],
        ],
        'CanadaPost' => [
            'services' => [
                'ExpeditedParcel' => 'Expedited Parcel (CanadaPost)',

                'Priority' => 'Priority (CanadaPost)',

                'RegularParcel' => 'Regular Parcel (CanadaPost)',

                'Xpresspost' => 'Xpresspost (CanadaPost)',

                'ExpeditedParcelUSA' => 'Expedited Parcel USA (CanadaPost)',

                'PriorityWorldwideParcelUSA' => 'Priority Worldwide Parcel USA (CanadaPost)',

                'SmallPacketUSAAir' => 'Small Packet USA Air (CanadaPost)',

                'TrackedPacketUSA' => 'Tracked Packet USA (CanadaPost)',

                'XpresspostUSA' => 'Xpresspost USA (CanadaPost)',

                'PriorityWorldwidePakIntl' => 'Priority Worldwide Pak Intl (CanadaPost)',

                'InternationalParcelSurface' => 'International Parcel Surface (CanadaPost)',

                'PriorityWorldwideParcelIntl' => 'Priority Worldwide Parcel Intl (CanadaPost)',

                'SmallPacketInternationalSurface' => 'Small Packet International Surface (CanadaPost)',

                'SmallPacketInternationalAir' => 'Small Packet International Air (CanadaPost)',

                'TrackedPacketInternational' => 'Tracked Packet International (CanadaPost)',

                'XpresspostInternational' => 'Xpresspost International (CanadaPost)',
            ],
        ],
    ];

    /**
     * Custom modification of the original function to return updated services for provided weight, dimensions and package type.
     *
     * @param mixed $weight
     * @param mixed $length
     * @param mixed $width
     * @param mixed $height
     *
     * @return array
     */
    public function elex_easypost_update_shipping_services($weight = 0, $length = 0, $width = 0, $height = 0)
    {
        $easypost_settings = get_option('woocommerce_'.WF_EASYPOST_ID.'_settings', null);
        $easypost_user_id = isset($easypost_settings['user_id']) ? $easypost_settings['user_id'] : '';
        $easypost_password = isset($easypost_settings['password']) ? $easypost_settings['password'] : '';
        $this->timezone_offset = !empty($this->settings['timezone_offset']) ? intval($this->settings['timezone_offset']) * 60 : 0;
        update_option('from_update_shipping_service', 'yes');
        $current_order_id = get_option('current_order_id_easypost_elex');
        $enabled_services = get_option('easypost_enabled_services');
        $current_order = $this->wf_load_order($current_order_id);
        $package = [];
        $stored_packages = get_post_meta($current_order_id, '_wf_easypost_stored_packages', true);
        // $this->elex_easypost_restore_package_dimensions($current_order_id,$stored_packages,$_POST);
        $sender_state =
            $wf_usps_easypost = new \WF_Easypost();
        if ('yes' != $this->settings['insurance']) {
            $_POST['package_price'] = '';
        }
        $domestic = ['US', 'PR', 'VI'];
        if (in_array($current_order->shipping_country, $domestic)) {
            $country = 'US';
        } else {
            $country = $current_order->shipping_country;
        }
        $package_request = [
            'Rate' => [
                'FromZIPCode' => str_replace(' ', '', strtoupper($this->settings['zip'])),
                'ToZIPCode' => $current_order->shipping_postcode,
                'WeightLb' => '',
                'Amount' => '',
                'WeightOz' => $weight,
                'Length' => $length,
                'Width' => $width,
                'Height' => $height,
                'ShipDate' => date('Y-m-d', (current_time('timestamp') + $this->timezone_offset)),
                'InsuredValue' => '',
                'RectangularShaped' => 'false',
                'ToCountry' => $country,
            ],
        ];
        if (!class_exists('EasyPost\EasyPost')) {
            require_once WP_PLUGIN_DIR.'/easypost-woocommerce-shipping/easypost.php';
        }
        if ('Live' == $this->settings['api_mode']) {
            $easypost_api_key = $this->settings['api_key'];
        } else {
            $easypost_api_key = $this->settings['api_test_key'];
        }

        \EasyPost\EasyPost::setApiKey($easypost_api_key);

        $this->elex_ep_status_logger($package_request, $current_order_id, 'Preferred Service Request', $this->elex_ep_status_log);

        $responses[] = $this->get_results($package_request);

        $this->elex_ep_status_logger($responses, $current_order_id, 'Preferred Service Response', $this->elex_ep_status_log);

        $updated_rates = [];
        foreach ($responses as $key => $value) {
            $response_obj = $value['response'];
            if (isset($response_obj->rates)) {
                if (is_array($response_obj->rates)) {
                    foreach ($response_obj->rates as $key => $value) {
                        foreach ($enabled_services as $service_key => $service_value) {
                            if ($service_key == $value->service) {
                                $label = $service_value;
                                $service_names = $value->service;
                                $service_rates = $value->rate;
                                array_push(
                                    $updated_rates,
                                    [
                                        $service_names => ['label' => $label, 'cost' => $service_rates],
                                    ]
                                );
                            }
                        }
                    }
                }
            } else {
                echo __('Unable to get the rates for perferred services', 'wf-easypost');
            }
        }
        update_option('package_number', 0);
        update_post_meta($current_order_id, 'package_rates_0', $updated_rates);
        update_option('from_update_shipping_service', 'no');

        return $updated_rates;
    }

    /**
     * @param string $post_id          WooCommerce Order ID
     * @param mixed  $label_type
     * @param mixed  $buttonType
     * @param mixed  $selectedServices
     * @param mixed  $signatureWaived
     */
    public function wf_easypost_shipment_confirm($post_id = '', $label_type = '', $buttonType = 'create', $selectedServices = [], $signatureWaived = '')
    {
        $parts = parse_url($_SERVER['REQUEST_URI']);
        parse_str($parts['query'], $query);
        if (!ELEX_EASYPOST_AUTO_LABEL_GENERATE_STATUS_CHECK && !ELEX_EASYPOST_RETURN_ADDON_STATUS) {
            if (!$this->wf_user_check()) {
                echo "You don't have admin privileges to view this page.";

                exit;
            }
        }
        $wfeasypostmsg = '';
        // Load Easypost.com Settings.
        $easypost_settings = get_option('woocommerce_'.WF_EASYPOST_ID.'_settings', null);

        $api_mode = isset($easypost_settings['api_mode']) ? $easypost_settings['api_mode'] : 'Live';
        $wf_easypost_selected_service = $selectedServices;

        update_post_meta($post_id, 'wf_easypost_selected_service', $wf_easypost_selected_service);

        $selected_flatrate_box = isset($_GET['wf_easypost_flatrate_box']) ? $_GET['wf_easypost_flatrate_box'] : '';
        update_post_meta($post_id, 'wf_easypost_selected_flat_rate_service', $selected_flatrate_box);
        $order = $this->wf_load_order($post_id);
        if (!$order) {
            return;
        }
        $package_data_per_item = [];
        $package_data_array = $this->wf_get_package_data($order);

        $index = 0;
        if ('per_item' == $this->settings['packing_method']) {
            foreach ($package_data_array as $key => $value) {
                if ($package_data_array[$key]['BoxCount'] > 1) {
                    for ($i = 0; $i < $package_data_array[$key]['BoxCount']; ++$i) {
                        $package_data_per_item[$index] = $package_data_array[$key];
                        ++$index;
                    }
                } else {
                    $package_data_per_item[$index] = $package_data_array[$key];
                    ++$index;
                }
            }
            $package_data_array = $package_data_per_item;
        }
        if (isset($buttonType)) {
            if ('return' == $buttonType) {
                foreach ($package_data_array as $key => $value) {
                    if ($package_data_array[$key]['BoxCount'] > 1) {
                        for ($i = 0; $i < $package_data_array[$key]['BoxCount']; ++$i) {
                            $package_data_per_item[$index] = $package_data_array[$key];
                            ++$index;
                        }
                    } else {
                        $package_data_per_item[$index] = $package_data_array[$key];
                        ++$index;
                    }
                }
                $package_data_array = $package_data_per_item;
            }
        }
        $package_data_array = $this->manual_packages($package_data_array); // Filter data with manual packages
        if (empty($package_data_array)) {
            return false;
        }

        $easypost_printLabelType = isset($easypost_settings['printLabelType']) ? $easypost_settings['printLabelType'] : 'PNG';
        $easypost_packing_method = isset($easypost_settings['packing_method']) ? $easypost_settings['packing_method'] : 'per_item';

        $message = '';
        $shipment_details = [];

        $shipping_service_data = $this->wf_get_shipping_service_data($order);
        $default_service_type = $shipping_service_data['shipping_service'];
        $carrier_services_bulk = $this->easypost_services;
        $bulk_service = [];
        $service_selected = false;
        $carrier_name = '';
        foreach ($carrier_services_bulk as $service => $code) {
            if (1 == $this->bulk_label) {
                // Bulk action Flat rate label generation
                $shipping_service_data['shipping_service_name'] = $this->wf_label_generation_flat_service($shipping_service_data['shipping_service_name']);
                if (in_array($shipping_service_data['shipping_service_name'], $code['services'])) {
                    $service_selected = true;
                    $bulk_service = $code['services'];
                    foreach ($bulk_service as $key => $value) {
                        if ($value == $shipping_service_data['shipping_service_name']) {
                            $default_service_type = $key;
                            update_post_meta($post_id, 'wf_easypost_selected_service', $key);
                            $carrier_name = $service;
                        }
                    }
                } elseif (false == $service_selected) {
                    // For bulk shipment When Customer choose Flate rate service or Free Shipping.
                    if (1 == $this->bulk_label) {
                        if ($order->shipping_country == $this->settings['country']) {
                            $default_service_type = $this->settings['easypost_default_domestic_shipment_service'];
                            $bulk_service = $code['services'];
                            foreach ($bulk_service as $key => $value) {
                                if ($key == $default_service_type) {
                                    $default_service_type = $key;
                                    update_post_meta($post_id, 'wf_easypost_selected_service', $key);
                                    $carrier_name = $service;
                                }
                            }
                        } else {
                            if ('NA' != $this->settings['easypost_default_international_shipment_service']) {
                                $default_service_type = $this->settings['easypost_default_international_shipment_service'];
                                $bulk_service = $code['services'];
                                foreach ($bulk_service as $key => $value) {
                                    if ($key == $default_service_type) {
                                        $default_service_type = $key;
                                        update_post_meta($post_id, 'wf_easypost_selected_service', $key);
                                        $carrier_name = $service;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (1 != $this->bulk_label) {
            $carrier_name = [];

            foreach ($carrier_services_bulk as $service => $code) {
                $service_codes = get_post_meta($order->id, 'wf_easypost_selected_service', true);
                $decoded_service_array = json_decode($service_codes);
                foreach ($decoded_service_array as $key => $value) {
                    if (array_key_exists($value, $code['services'])) {
                        $carrier_name[] = $service;
                    }
                }
                if (isset($buttonType)) {
                    if ('return' == $buttonType) {
                        if ('manual' == $this->settings['return_address_addon']) {
                            $country = $this->settings['return_country_addon'];
                        } else {
                            $country = $this->settings['country'];
                        }
                        $bulk_service = $code['services'];
                        foreach ($bulk_service as $key => $value) {
                            if ($key == $wf_easypost_selected_service) {
                                $default_service_type = $key;
                                update_post_meta($post_id, 'wf_easypost_selected_service', $key);
                                $carrier_name = $service;
                            }
                        }
                    }
                }
            }
        }

        $shipment_details['options']['print_custom_1'] = $order->id;
        $shipment_details['options']['label_format'] = $easypost_printLabelType;

        // Signature option
        if ('yes' == $signatureWaived) {
            $shipment_details['options']['delivery_confirmation'] = 'NO_SIGNATURE';
        } else {
            $shipment_details['options']['delivery_confirmation'] = 'ADULT_SIGNATURE';
        }

        $specialrate = $this->get_special_rates_eligibility($default_service_type);
        if (!empty($specialrate)) {
            $shipment_details['options']['special_rates_eligibility'] = $specialrate;
        }
        //$shipment_details['from_address']['country'] = WC()->countries->get_base_country() ? WC()->countries->get_base_country() : '';

        $shipping_first_name = $order->shipping_first_name;
        $shipping_last_name = $order->shipping_last_name;
        $shipping_full_name = $shipping_first_name.' '.$shipping_last_name;
        if (isset($buttonType)) {
            if ('return' == $buttonType && 'manual' == $this->settings['return_address_addon']) { //Addon address for return label.
                $shipment_details['from_address']['name'] = isset($this->settings['return_name_addon']) ? $this->settings['return_name_addon'] : '';
                $shipment_details['from_address']['company'] = isset($this->settings['return_company_addon']) ? $this->settings['return_company_addon'] : '';
                $shipment_details['from_address']['street1'] = isset($this->settings['return_street1_addon']) ? $this->settings['return_street1_addon'] : '';
                $shipment_details['from_address']['street2'] = isset($this->settings['return_street2_addon']) ? $this->settings['return_street2_addon'] : '';
                $shipment_details['from_address']['city'] = isset($this->settings['return_city_addon']) ? $this->settings['return_city_addon'] : '';
                $shipment_details['from_address']['state'] = isset($this->settings['return_state_addon']) ? $this->settings['return_state_addon'] : '';
                $shipment_details['from_address']['zip'] = isset($this->settings['return_zip_addon']) ? $this->settings['return_zip_addon'] : '';
                $shipment_details['from_address']['email'] = isset($this->settings['return_email_addon']) ? $this->settings['return_email_addon'] : '';
                $shipment_details['from_address']['phone'] = isset($this->settings['return_phone_addon']) ? $this->settings['return_phone_addon'] : '';
                $shipment_details['from_address']['country'] = isset($this->settings['return_country_addon']) ? $this->settings['return_country_addon'] : '';
            } else {
                $shipment_details['from_address']['name'] = isset($easypost_settings['name']) ? $easypost_settings['name'] : '';
                $shipment_details['from_address']['company'] = isset($easypost_settings['company']) ? $easypost_settings['company'] : '';
                $shipment_details['from_address']['street1'] = isset($easypost_settings['street1']) ? $easypost_settings['street1'] : '';
                $shipment_details['from_address']['street2'] = isset($easypost_settings['street2']) ? $easypost_settings['street2'] : '';
                $shipment_details['from_address']['city'] = isset($easypost_settings['city']) ? $easypost_settings['city'] : '';
                $shipment_details['from_address']['state'] = isset($easypost_settings['state']) ? $easypost_settings['state'] : '';
                $shipment_details['from_address']['zip'] = isset($easypost_settings['zip']) ? $easypost_settings['zip'] : '';
                $shipment_details['from_address']['email'] = isset($easypost_settings['email']) ? $easypost_settings['email'] : '';
                $shipment_details['from_address']['phone'] = isset($easypost_settings['phone']) ? $easypost_settings['phone'] : '';
                $shipment_details['from_address']['country'] = isset($easypost_settings['country']) ? $easypost_settings['country'] : '';
            }
        }
        if (1 == $this->bulk_label) {
            $shipment_details['from_address']['name'] = isset($easypost_settings['name']) ? $easypost_settings['name'] : '';
            $shipment_details['from_address']['company'] = isset($easypost_settings['company']) ? $easypost_settings['company'] : '';
            $shipment_details['from_address']['street1'] = isset($easypost_settings['street1']) ? $easypost_settings['street1'] : '';
            $shipment_details['from_address']['street2'] = isset($easypost_settings['street2']) ? $easypost_settings['street2'] : '';
            $shipment_details['from_address']['city'] = isset($easypost_settings['city']) ? $easypost_settings['city'] : '';
            $shipment_details['from_address']['state'] = isset($easypost_settings['state']) ? $easypost_settings['state'] : '';
            $shipment_details['from_address']['zip'] = isset($easypost_settings['zip']) ? $easypost_settings['zip'] : '';
            $shipment_details['from_address']['email'] = isset($easypost_settings['email']) ? $easypost_settings['email'] : '';
            $shipment_details['from_address']['phone'] = isset($easypost_settings['phone']) ? $easypost_settings['phone'] : '';
            $shipment_details['from_address']['country'] = isset($easypost_settings['country']) ? $easypost_settings['country'] : '';
        }
        $shipment_details['to_address']['name'] = isset($shipping_full_name) ? $shipping_full_name : '';
        $shipment_details['to_address']['street1'] = isset($order->shipping_address_1) ? $order->shipping_address_1 : '';
        $shipment_details['to_address']['street2'] = isset($order->shipping_address_2) ? $order->shipping_address_2 : '';
        $shipment_details['to_address']['city'] = isset($order->shipping_city) ? $order->shipping_city : '';
        $shipment_details['to_address']['company'] = isset($order->shipping_company) ? $order->shipping_company : '';
        $shipment_details['to_address']['state'] = isset($order->shipping_state) ? $order->shipping_state : '';
        $shipment_details['to_address']['zip'] = isset($order->shipping_postcode) ? $order->shipping_postcode : '';
        $shipment_details['to_address']['email'] = isset($order->billing_email) ? $order->billing_email : '';
        $shipment_details['to_address']['phone'] = isset($order->billing_phone) ? $order->billing_phone : '';
        $shipment_details['to_address']['country'] = isset($order->shipping_country) ? $order->shipping_country : '';
        $shipment_details['to_address']['residential'] = isset($easypost_settings['show_rates']) && 'residential' == $easypost_settings['show_rates'] ? true : '';
        if (isset($easypost_settings['return_address']) && 'yes' == $easypost_settings['return_address']) {
            $shipment_details['return_address']['name'] = isset($easypost_settings['return_name']) ? $easypost_settings['return_name'] : '';
            $shipment_details['return_address']['company'] = isset($easypost_settings['return_company']) ? $easypost_settings['return_company'] : '';
            $shipment_details['return_address']['street1'] = isset($easypost_settings['return_street1']) ? $easypost_settings['return_street1'] : '';
            $shipment_details['return_address']['street2'] = isset($easypost_settings['return_street2']) ? $easypost_settings['return_street2'] : '';
            $shipment_details['return_address']['city'] = isset($easypost_settings['return_city']) ? $easypost_settings['return_city'] : '';
            $shipment_details['return_address']['state'] = isset($easypost_settings['return_state']) ? $easypost_settings['return_state'] : '';
            $shipment_details['return_address']['zip'] = isset($easypost_settings['return_zip']) ? $easypost_settings['return_zip'] : '';
            $shipment_details['return_address']['email'] = isset($easypost_settings['return_email']) ? $easypost_settings['return_email'] : '';
            $shipment_details['return_address']['phone'] = isset($easypost_settings['return_phone']) ? $easypost_settings['return_phone'] : '';
            $shipment_details['return_address']['country'] = isset($easypost_settings['return_country']) ? $easypost_settings['return_country'] : '';
        }

        //  need to find some solution for intnat
        $international = false;
        $eligible_for_customs_details = $this->is_eligible_for_customs_details($shipment_details['from_address']['country'], $shipment_details['to_address']['country'], $shipment_details['to_address']['city']);
        if ($eligible_for_customs_details) {
            $international = true;

            $order_items = $order->get_items();
            $custom_line_array = [];
            foreach ($order_items as $order_item) {
                for ($i = 0; $i < $order_item['qty']; ++$i) {
                    $product_data = wc_get_product($order_item['variation_id'] ? $order_item['variation_id'] : $order_item['product_id']);
                    $title = $product_data->get_title();
                    if (WC()->version < '3.0') {
                        $weight = woocommerce_get_weight($product_data->get_weight(), 'lbs');
                    } else {
                        $weight = wc_get_weight($product_data->get_weight(), 'lbs');
                    }
                    $shipment_description = $title;
                    if (!empty($easypost_settings['customs_description'])) {
                        $shipment_description = $easypost_settings['customs_description'];
                    }
                    $shipment_description = (strlen($shipment_description) >= 50) ? substr($shipment_description, 0, 45).'...' : $shipment_description;
                    $quantity = $order_item['qty'];
                    $value = $order_item['line_subtotal'];

                    $custom_line = [];
                    $custom_line['description'] = $shipment_description;
                    $custom_line['quantity'] = 1;
                    $custom_line['value'] = $value / $quantity;
                    $custom_line['weight'] = (string) ($weight * 16);
                    $custom_line['origin_country'] = $shipment_details['from_address']['country'];
                    $wf_hs_code = get_post_meta($order_item['product_id'], '_wf_hs_code', 1);
                    if (!empty($wf_hs_code)) {
                        $custom_line['hs_tariff_number'] = $wf_hs_code;
                    }
                    if ($order_item['variation_id']) {
                        $product_id_customs = $order_item['variation_id'];
                    } else {
                        $product_id_customs = $order_item['product_id'];
                    }
                    $product_custom_declared_value = get_post_meta($product_id_customs, '_wf_easypost_custom_declared_value', true);
                    if ($product_custom_declared_value) {
                        $custom_line['value'] = $product_custom_declared_value;
                    } else {
                        $product_custom_declared_value = get_post_meta($order_item['product_id'], '_wf_easypost_custom_declared_value', true);
                        if ($product_custom_declared_value) {
                            $custom_line['value'] = $product_custom_declared_value;
                        }
                    }
                }
            }
            //dry_ice
            $dry_ices = $this->get_package_dry_ice($order);
            if ('yes' == $dry_ices) {
                $shipment_details['options']['dry_ice'] = 'true';
                $shipment_details['options']['dry_ice_weight'] = $custom_line['weight'];
            }
            //for International shipping only
            $shipment_details['customs_info']['customs_certify'] = true;
            $shipment_details['customs_info']['customs_signer'] = 'Customs Signer';
            $shipment_details['customs_info']['contents_type'] = 'merchandise';
            $shipment_details['customs_info']['contents_explanation'] = '';
            $shipment_details['customs_info']['restriction_type'] = 'none';
            $shipment_details['customs_info']['eel_pfc'] = 'NOEEI 30.37(a)';
        }
        if (!class_exists('EasyPost\EasyPost')) {
            require_once plugin_dir_path(dirname(__FILE__)).'/easypost.php';
        }
        if ('Live' == $this->settings['api_mode']) {
            \EasyPost\EasyPost::setApiKey($easypost_settings['api_key']);
        } else {
            \EasyPost\EasyPost::setApiKey($easypost_settings['api_test_key']);
        }
        $easypost_labels = [];
        $index = 0;
        $package_count = 0;
        $selected_flatrate_box = get_post_meta($post_id, 'wf_easypost_selected_flat_rate_service', true);
        $default_service_type = str_replace('[', '', $default_service_type);
        $default_service_type = str_replace(']', '', $default_service_type);
        $default_service_type = str_replace('"', '', $default_service_type);
        $default_service_type = explode(',', $default_service_type);
        $selected_flatrate_box = str_replace('[', '', $selected_flatrate_box);
        $selected_flatrate_box = str_replace(']', '', $selected_flatrate_box);
        $selected_flatrate_box = str_replace('"', '', $selected_flatrate_box);
        $selected_flatrate_box = explode(',', $selected_flatrate_box);
        $service_count = 0;
        $check_ups_service = [
            'Ground' => 'Ground (UPS)', '3DaySelect' => '3 Day Select (UPS)', '2ndDayAirAM' => '2nd Day Air AM (UPS)', '2ndDayAir' => '2nd Day Air (UPS)', 'NextDayAirSaver' => 'Next Day Air Saver (UPS)', 'NextDayAirEarlyAM' => 'Next Day Air Early AM (UPS)', 'NextDayAir' => 'Next Day Air (UPS)', 'Express' => 'Express (UPS)', 'Expedited' => 'Expedited (UPS)', 'ExpressPlus' => 'Express Plus (UPS)', 'UPSSaver' => 'UPS Saver (UPS)', 'UPSStandard' => 'UPS Standard (UPS)',
        ];

        foreach ($package_data_array as $package_data) {
            //For checking ups service to send thirdparty account details.

            $ups = false;
            $inc = 0;
            if (is_array($carrier_name) || !empty($carrier_name)) {
                if ('label_type' != $this->settings['elex_shipping_label_size']) {
                    if ('USPS' == $carrier_name[$service_count]) {
                        $shipment_details['options']['label_size'] = $this->settings['elex_shipping_label_size_usps'];
                    } elseif ('UPS' == $carrier_name[$service_count]) {
                        $shipment_details['options']['label_size'] = $this->settings['elex_shipping_label_size_ups'];
                    } else {
                        $shipment_details['options']['label_size'] = $this->settings['elex_shipping_label_size_fedex'];
                    }
                }
            }

            $ups_service = json_decode(stripslashes($wf_easypost_selected_service));

            if (array_key_exists($ups_service[$inc], $check_ups_service)) {
                if ('UPS' == $carrier_name[0]) {
                    $ups = true;
                } else {
                    $ups = false;
                }
            }

            ++$inc;

            //Multi-Vendor support
            if (isset($package_data['origin']) && 'yes' == get_option('wc_settings_wf_vendor_addon_allow_vedor_api_key')) {
                $easypost_api_key = get_user_meta($package_data['origin']['vendor_id'], 'vendor_easypost_api_key', true);
            } else {
                if ('Live' == $this->settings['api_mode']) {
                    $easypost_api_key = $easypost_settings['api_key'];
                } else {
                    $easypost_api_key = $easypost_settings['api_test_key'];
                }
            }
            //Third Party Billing Request options.
            if ('yes' == $this->settings['third_party_billing'] && $ups) {
                $shipment_details['options']['bill_third_party_account'] = json_decode(stripslashes(html_entity_decode($_GET['wf_elex_easypost_third_party_billing_api_str'])));
                $shipment_details['options']['bill_third_party_country'] = json_decode(stripslashes(html_entity_decode($_GET['wf_elex_easypost_third_party_billing_country_str'])));
                $shipment_details['options']['bill_third_party_postal_code'] = json_decode(stripslashes(html_entity_decode($_GET['wf_elex_easypost_third_party_billing_zipcode_str'])));
            }

            \EasyPost\EasyPost::setApiKey($easypost_api_key);
            if (isset($package_data['origin'])) {
                $shipment_details['from_address']['name'] = $package_data['origin']['first_name'];
                $shipment_details['from_address']['company'] = $package_data['origin']['company'];
                $shipment_details['from_address']['street1'] = $package_data['origin']['address_1'];
                $shipment_details['from_address']['street2'] = $package_data['origin']['address_2'];
                $shipment_details['from_address']['city'] = $package_data['origin']['city'];
                $shipment_details['from_address']['state'] = $package_data['origin']['state'];
                $shipment_details['from_address']['zip'] = $package_data['origin']['postcode'];
                $shipment_details['from_address']['email'] = $package_data['origin']['email'];
                $shipment_details['from_address']['phone'] = $package_data['origin']['phone'];
                $shipment_details['from_address']['country'] = $package_data['origin']['country'];
            }

            if ('yes' == $this->settings['third_party_billing'] && $ups) {
                $shipment_details['options']['payment']['type'] = 'THIRD_PARTY';
            } else {
                $shipment_details['options']['payment']['type'] = 'SENDER';
            }

            // Third Party payment details

            if ('yes' == $this->settings['third_party_billing'] && $ups) {
                $shipment_details['options']['payment']['account'] = isset($shipment_details['options']['bill_third_party_account']) ? $shipment_details['options']['bill_third_party_account'] : '';
                $shipment_details['options']['payment']['country'] = isset($shipment_details['options']['bill_third_party_country']) ? $shipment_details['options']['bill_third_party_country'] : '';
                $shipment_details['options']['payment']['postal_code'] = isset($shipment_details['options']['bill_third_party_postal_code']) ? $shipment_details['options']['bill_third_party_postal_code'] : '';
            }
            $default_service = $default_service_type[$service_count];
            if ($easypost_settings['weight_packing_process'] = 'pack_simple') {
                $custom_line['weight'] = $package_data['WeightOz'];
            }
            $custom_line_array[] = $custom_line;
            $tx_id = uniqid('wf_'.$order->id.'_');
            update_post_meta($order->id, 'wf_last_label_tx_id', $tx_id);
            if (!empty($selected_flatrate_box[$service_count])) {
                $shipment_details['parcel']['predefined_package'] = $selected_flatrate_box[$service_count];
            } else {
                unset($shipment_details['parcel']['predefined_package']);
                $shipment_details['parcel']['length'] = $package_data['Length'];
                $shipment_details['parcel']['width'] = $package_data['Width'];
                $shipment_details['parcel']['height'] = $package_data['Height'];
            }
            if (1 != $this->bulk_label) {
                ++$service_count;
            }
            $shipment_details['parcel']['weight'] = $package_data['WeightOz'];
            $shipment_details['options']['special_rates_eligibility'] = 'USPS.LIBRARYMAIL,USPS.MEDIAMAIL';
            //   $shipment_details['parcel']['predefined_package'] = 'letter';
            if (($shipment_details['from_address']['country'] != $shipment_details['to_address']['country']) && ('none' != $this->settings['ex_easypost_duty'])) {
                $shipment_details['options']['incoterm'] = $this->settings['ex_easypost_duty'];
            }
            // below lines for International shipping - + customs info
            if ($international) {
                $m = 0;
                $shipment_details['customs_info']['customs_items'] = [];
                //if multi-vendor
                if (isset($package_data['origin'])) {
                    $index = 0;
                }
                if (!empty($package_data['PackedItem'])) {
                    for ($m = 0; $m < sizeof($package_data['PackedItem']); ++$m) {
                        //In box packing algorithm the individual product details are stored in object named 'meta'
                        if ('weight_based_packing' == $this->packing_method) {//weight based packing don't need any dimentions.
                            $item = isset($package_data['PackedItem'][$index]->meta) ? $package_data['PackedItem'][$index]->meta : $package_data['PackedItem'][$index];
                            ++$index;
                        } else {
                            $item = isset($package_data['PackedItem'][$m]->meta) ? $package_data['PackedItem'][$m]->meta : $package_data['PackedItem'][$m];
                        }
                        $product_id_customs = $item->get_parent_id();
                        $item = $this->wf_load_product($item);

                        if (!empty($easypost_settings['customs_description'])) {
                            $prod_title = $easypost_settings['customs_description'];
                        } else {
                            $prod_title = $item->get_title();
                        }
                        $shipment_desc = (strlen($prod_title) >= 50) ? substr($prod_title, 0, 45).'...' : $prod_title;
                        $shipment_details['customs_info']['customs_items'][$m]['description'] = $shipment_desc;
                        $shipment_details['customs_info']['customs_items'][$m]['quantity'] = 1; //$quantity;
                        $shipment_details['customs_info']['customs_items'][$m]['value'] = $item->get_price();
                        $wf_hs_code = get_post_meta($item->id, '_wf_hs_code', 1);
                        $product_custom_declared_value = get_post_meta($item->id, '_wf_easypost_custom_declared_value', true);
                        if ($product_custom_declared_value) {
                            $shipment_details['customs_info']['customs_items'][$m]['value'] = $product_custom_declared_value;
                        } else {
                            $product_custom_declared_value = get_post_meta($product_id_customs, '_wf_easypost_custom_declared_value', true);
                            if ($product_custom_declared_value) {
                                $shipment_details['customs_info']['customs_items'][$m]['value'] = $product_custom_declared_value;
                            }
                        }

                        if (!empty($wf_hs_code)) {
                            $shipment_details['customs_info']['customs_items'][$m]['hs_tariff_number'] = $wf_hs_code;
                        }
                        if (WC()->version < '3.0') {
                            $weight_to_send = woocommerce_get_weight($item->weight, 'Oz');
                        } else {
                            $weight_to_send = wc_get_weight($item->weight, 'Oz');
                        }
                        $shipment_details['customs_info']['customs_items'][$m]['weight'] = $weight_to_send;
                        $shipment_details['customs_info']['customs_items'][$m]['origin_country'] = $shipment_details['from_address']['country'];
                    }
                } else { //if($this->packing_method == 'per_item'){ // PackedItem will be empty , also each item will be shipped separately
                    $shipment_details['customs_info']['customs_items'][0] = $custom_line_array[$package_count];
                }
            }
            if (1 == $this->bulk_label) {
                $this->wf_debug = false;
            }

            try {
                try {
                    if (isset($buttonType)) {
                        if ('return' == $buttonType) {
                            $shipment_details['is_return'] = true;
                        }
                    }
                    if ('return' == $label_type) {
                        $shipment_details['is_return'] = true;
                    }

                    $this->elex_ep_status_logger($shipment_details, $post_id, 'Request', $this->elex_ep_status_log);

                    $shipment = \EasyPost\Shipment::create($shipment_details);
                    $this->elex_ep_status_logger($shipment_details, $post_id, 'Response', $this->elex_ep_status_log);

                    $this->wf_debug("<h3>Debug mode is Enabled. Please Disable it in the <a href='".get_site_url()."/wp-admin/admin.php?page=wc-settings&tab=shipping&section=wf_easypost' tartget='_blank'>settings  page</a> if you do not want to see this.</h3>");
                    $this->wf_debug('EasyPost CREATE SHIPMENT REQUEST: <pre style="background: rgba(158, 158, 158, 0.30);width: 90%; display: block; margin: auto; padding: 15;">'.print_r($shipment_details, true).'</pre>');

                    $this->wf_debug('EasyPost CREATE SHIPMENT OBJECT: <pre style="background: rgba(158, 158, 158, 0.30);width: 90%; display: block; margin: auto; padding: 15;">'.print_r($shipment, true).'</pre>');
                    $this->check = 'verified';
                } catch (\Exception $e) {
                    if (!empty($shipment)) {
                        $shipment_obj_array = [
                            'rate' => $shipment->lowest_rate(array_keys($this->easypost_services), [$default_service]),
                        ];

                        if ($this->wf_insure && (float) $package_data['InsuredValue'] > 0) {
                            $shipment_obj_array['insurance'] = $package_data['InsuredValue'];
                        }
                        $this->wf_debug('<br><br>EasyPost REQUEST (Buy-shipment): <pre style="background: rgba(158, 158, 158, 0.15);width: 90%; display: block; margin: auto; padding: 15;">'.print_r($shipment_obj_array, true).'</pre>');

                        $this->elex_ep_status_logger($shipment_obj_array, $post_id, 'Service select Label', $this->elex_ep_status_log);

                        $response_obj = $shipment->buy($shipment_obj_array);

                        $this->elex_ep_status_logger($response_obj, $post_id, 'EasyPost Response But Label', $this->elex_ep_status_log);

                        if (isset($this->error_email) && true == $this->error_email) {
                            if (isset($response_obj)) {
                                $this->check = 'successfull';
                            } else {
                                $this->check = 'Failed';
                            }
                        }
                        if (isset($this->error_email) && 'verified' != $this->check) {
                            if (true == $this->error_email) {
                                if ('no' != $this->settings['enable_failed_email']) {
                                    echo $message;
                                    $to = get_option('admin_email');
                                    $email_subject = $this->settings['failed_email_subject'];
                                    $email_content = $this->settings['failed_email_content'];
                                    wp_mail($to, $email_subject.' ['.$post_id.']', $email_content, '', '');

                                    return;
                                }

                                return;
                            }
                        }
                        $message .= __('Create shipment failed. ', 'wf-easypost');
                        $message .= $e->getMessage().' ';
                        $wfeasypostmsg = 6;
                        update_post_meta($post_id, 'wfeasypostmsg', $message);
                        echo $message;

                        exit;
                    }
                }
                $srvc = $default_service;

                try {
                    if (!empty($shipment)) {
                        $shipment_obj_array = [
                            'rate' => $shipment->lowest_rate(array_keys($this->easypost_services), [$srvc]),
                        ];

                        if ($this->wf_insure && (float) $package_data['InsuredValue'] > 0) {
                            $shipment_obj_array['insurance'] = $package_data['InsuredValue'];
                        }
                        $this->wf_debug('<br><br>EasyPost REQUEST (Buy-shipment): <pre style="background: rgba(158, 158, 158, 0.15);width: 90%; display: block; margin: auto; padding: 15;">'.print_r($shipment_obj_array, true).'</pre>');

                        $this->elex_ep_status_logger($shipment_obj_array, $post_id, 'Service select Label', $this->elex_ep_status_log);

                        $response_obj = $shipment->buy($shipment_obj_array);

                        $this->elex_ep_status_logger($response_obj, $post_id, 'EasyPost Response But Label', $this->elex_ep_status_log);

                        if (isset($this->error_email) && true == $this->error_email) {
                            if (isset($response_obj)) {
                                $this->check = 'successfull';
                            } else {
                                $this->check = 'Failed';
                            }
                        }
                    } else {
                        $this->wf_debug('<pre style="background: rgba(158, 158, 158, 0.15);width: 90%; display: block; margin: auto; padding: 15;"> <center><font size="5">Seems like there is a connection problem. Please check your internet connection </center> </font></pre>');
                    }
                } catch (\Exception $e) {
                    if (isset($this->error_email) && 'Failed' == $this->check) {
                        if (true == $this->error_email) {
                            if ('no' != $this->settings['enable_failed_email']) {
                                echo $message;
                                $to = get_option('admin_email');
                                $email_subject = $this->settings['failed_email_subject'];
                                $email_content = $this->settings['failed_email_content'];
                                wp_mail($to, $email_subject.' ['.$post_id.']', $email_content, '', '');

                                return;
                            }

                            return;
                        }
                    }
                    $carrier_services = $this->easypost_services;

                    foreach ($carrier_services as $service => $code) {
                        if (array_key_exists($default_service, $code['services'])) {
                            $carrier_name = $service;
                        }
                    }
                    $message .= __('Something went wrong. ', 'wf-easypost');
                    $message .= $e->getMessage().' ';
                    //This error occurs while generating shipping label.
                    if ('SHIPMENT.POSTAGE.FAILURE' == $e->ecode && 'UPS' == $carrier_name) {
                        $message .= __('<br>The UPS account tied to the Shipper Number you are using is not yet fully set up. Please contact UPS.', 'wf-easypost');
                        if (isset($this->error_email)) {
                            if (true == $this->error_email) {
                                if ('no' != $this->settings['enable_failed_email']) {
                                    echo $message;
                                    $to = get_option('admin_email');
                                    $email_subject = $this->settings['failed_email_subject'];
                                    $email_content = $this->settings['failed_email_content'];
                                    wp_mail($to, $email_subject.' ['.$post_id.']', $email_content, '', '');

                                    return;
                                }

                                return;
                            }
                        }
                    } else {
                        $wfeasypostmsg = 6;
                        update_post_meta($post_id, 'wfeasypostmsg', $message);
                        if (isset($this->error_email)) {
                            if (true == $this->error_email) {
                                if ('no' != $this->settings['enable_failed_email']) {
                                    echo $message;
                                    $to = get_option('admin_email');
                                    $email_subject = $this->settings['failed_email_subject'];
                                    $email_content = $this->settings['failed_email_content'];
                                    wp_mail($to, $email_subject.' ['.$post_id.']', $email_content, '', '');

                                    return;
                                }

                                return;
                            }
                        } else {
                            echo $message;

                            return;
                        }
                    }
                }
            } catch (\Exception $e) {
                $message .= __('Unable to get information at this point of time. ', 'wf-easypost');
                $message .= $e->getMessage().' ';
            }

            if (isset($response_obj)) {
                $this->wf_debug('<br><br>EasyPost RESPONSE OBJECT(Buy-shipment): <pre style="background: rgba(158, 158, 158, 0.15);width: 90%; display: block; margin: auto; padding: 15;">'.print_r($response_obj, true).'</pre>');
                //$easypost_authenticator   = ( string ) $response_obj->Authenticator;
                $label_url = (string) $response_obj->postage_label->label_url;
                $tracking_link = (string) $response_obj->tracker->public_url;
                $carrier_selected = (string) $response_obj->selected_rate->carrier;

                if (!empty($label_url)) {
                    $easypost_label = [];
                    $easypost_label['url'] = $label_url;
                    $easypost_label['tracking_number'] = (string) $response_obj->tracking_code;
                    $easypost_label['integrator_txn_id'] = isset($shipment_details['IntegratorTxID']) ? $shipment_details['IntegratorTxID'] : ''; //(string) $response_obj->reference;
                    $easypost_label['easypost_tx_id'] = (string) $response_obj->tracker->id;
                    $easypost_label['shipment_id'] = $shipment->id;
                    $easypost_label['order_date'] = date('Y-m-d', strtotime((string) $response_obj->updated_at));
                    $easypost_label['carrier'] = $carrier_selected;
                    $easypost_label['link'] = $tracking_link;
                    $easypost_labels[] = $easypost_label;
                    if (isset($package_data['origin']) && 'yes' == get_option('wc_settings_wf_vendor_addon_email_labels_to_vendors')) {
                        $label_url_html = '<html><body>'.$label_url.'</body></html>';
                        wp_mail($package_data['origin']['email'], 'Shipment Label - '.$order->id, 'Label '.$label_url_html, '', '');
                    }
                }
            } else {
                $message .= __('Sorry. Something went wrong:', 'wf-easypost').'<br/>';
            }
            ++$package_count;
        }
        if (isset($carrier_selected)) {
            switch ($carrier_selected) {
                case 'UPS':
                    $carrier = 'ups';

                    break;

                case 'USPS':
                    $carrier = 'united-states-postal-service-usps';

                    break;

                case 'FedEx':
                    $carrier = 'fedex';

                    break;

                case 'CanadaPost':
                    $carrier = 'canada-post';

                    break;
            }
        } else {
            $carrier = 'united-states-postal-service-usps';
        }

        // if (isset($easypost_labels) && !empty($easypost_labels)) {
        if ($carrier !== 'united-states-postal-service-usps' && (isset($easypost_labels) && !empty($easypost_labels))) {
            // Update post \
            if (isset($buttonType) || 'return' == $label_type) {
                if ('return' == $label_type || 'return' == $buttonType) {
                    $previous_label = get_post_meta($post_id, 'wf_easypost_return_labels', true);
                    if (is_array($previous_label)) {
                        foreach ($previous_label as $key => $value) {
                            array_push($easypost_labels, $value);
                        }
                    } elseif (!empty($previous_label)) {
                        array_push($easypost_labels, $previous_label);
                    }
                    update_post_meta($post_id, 'wf_easypost_return_labels', $easypost_labels);
                } else {
                    update_post_meta($post_id, 'wf_easypost_labels', $easypost_labels);
                }
            } else {
                update_post_meta($post_id, 'wf_easypost_labels', $easypost_labels);
            }
            // Auto fill tracking info.
            $shipment_id_cs = '';
            foreach ($easypost_labels as $easypost_label) {
                $shipment_id_cs .= $easypost_label['tracking_number'].',';
            }
            // Shipment Tracking (Auto)
            //
            $admin_notice = '';

            try {
                $admin_notice = \WfTrackingUtil::update_tracking_data($post_id, $shipment_id_cs, $carrier, \WF_Tracking_Admin_EasyPost::SHIPMENT_SOURCE_KEY, \WF_Tracking_Admin_EasyPost::SHIPMENT_RESULT_KEY);
            } catch (\Exception $e) {
                $admin_notice = '';
                // Do nothing.
            }
            // Finished creating the label and moving on
        } else {
            delete_post_meta($post_id, 'wf_easypost_labels');
            delete_post_meta($post_id, 'wf_easypost_return_labels');
        }
    }

    private function get_results($package_request)
    {
        // Get rates.
        try {
            $payload = [];
            $payload['from_address'] = [
                'name' => $this->settings['name'],
                'company' => $this->settings['company'],
                'street1' => $this->settings['street1'],
                'street2' => $this->settings['street2'],
                'city' => $this->settings['city'],
                'state' => $this->settings['state'],
                'zip' => $package_request['Rate']['FromZIPCode'],
                //adding country
                'country' => $this->settings['country'],
            ];
            $payload['to_address'] = [
                // Name and Street1 are required fields for getting rates.
                // But, at this point, these details are not available.
                'name' => '-',
                'street1' => '-',
                'residential' => 'residential' == $this->settings['show_rates'] ? true : '',
                'zip' => $package_request['Rate']['ToZIPCode'],
                'country' => $package_request['Rate']['ToCountry'],
            ];
            if ('CA' == $payload['from_address']['country'] && 'CA' != $payload['to_address']['country']) {
                $payload['customs_info']['customs_certify'] = true;
                $payload['customs_info']['customs_signer'] = 'Customs Signer';
                $payload['customs_info']['contents_type'] = 'merchandise';
                $payload['customs_info']['contents_explanation'] = '';
                $payload['customs_info']['restriction_type'] = 'none';
                $payload['customs_info']['eel_pfc'] = 'NOEEI 30.37(a)';
            }

            if (!empty($package_request['Rate']['WeightOz'])) {
                $package_request['request']['Rate']['WeightOz'] = $package_request['Rate']['WeightOz'];
            }

            $payload['parcel'] = [
                'length' => $package_request['Rate']['Length'],
                'width' => $package_request['Rate']['Width'],
                'height' => $package_request['Rate']['Height'],
                'weight' => $package_request['Rate']['WeightOz'],
            ];

            $payload['options'] = [
                'special_rates_eligibility' => 'USPS.LIBRARYMAIL,USPS.MEDIAMAIL',
            ];
            $shipment = \EasyPost\Shipment::create($payload);
            $response = json_decode($shipment);
            $response_ele = [];
            $response_ele['response'] = $response;
        } catch (\Exception $e) {
            if (false !== strpos($e->getMessage(), 'Could not connect to EasyPost')) {
                echo __('Unable to get Auth information at this point of time. ', 'wf-easypost');
            }

            return false;
        }

        return $response_ele;
    }

    private function get_special_rates_eligibility($service)
    {
        if ('MediaMail' == $service) {
            $special_rates = 'USPS.MEDIAMAIL';
        } elseif ('LibraryMail' == $service) {
            $special_rates = 'USPS.LIBRARYMAIL';
        } else {
            $special_rates = false;
        }

        return $special_rates;
    }

    private function get_package_dry_ice($order)
    {
        $order_items = $order->get_items();
        $higher_signature_option = 0;
        foreach ($order_items as $order_item) {
            $dry_ice = get_post_meta($order_item['product_id'], '_wf_dry_ice_code');
        }

        return $dry_ice;
    }

    private function wf_load_product($product)
    {
        if (!$product) {
            return false;
        }
        if (!class_exists('wf_product')) {
            include_once 'class-wf-legacy.php';
        }
        if ($product instanceof \wf_product) {
            return $product;
        }

        return (WC()->version < '2.7.0') ? $product : new \wf_product($product);
    }

    private function wf_debug($message)
    {
        if ($this->wf_debug) {
            echo $message;
        }
    }
}
