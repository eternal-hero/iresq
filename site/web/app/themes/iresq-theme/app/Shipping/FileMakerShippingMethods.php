<?php

namespace App\Shipping;

class FileMakerShippingMethods extends \WC_Shipping_Method
{
    /**
     * Constructor for your shipping class.
     *
     * @param mixed $instance_id
     */
    public function __construct($instance_id = 0)
    {
        $this->id = 'iresq_filemaker';
        $this->instance_id = absint($instance_id);
        $this->supports = ['shipping-zones'];
        $this->title = __('iResQ - FileMaker Methods');
        $this->method_title = __('iResQ - FileMaker Methods');
        $this->method_description = __('List of available shipping methods synced with FileMaker');
        $this->enabled = 'yes';
        $this->init();
    }

    public function is_available($package)
    {
        return true;
    }

    /**
     * Init your settings.
     */
    public function init()
    {
        $this->shippingMethodActions();
        // Load the settings API
        $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
        $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
    }

    /**
     * calculate_shipping function.
     *
     * @param array $package
     */
    public function calculate_shipping($package = [])
    {
        $largestDeviceType = 'Handheld';
        foreach ($package['contents'] as $product) {
            $shippingOption = get_field('single_shipping_device_type', $product['product_id']);

            switch ($shippingOption) {
                case 'Laptop':
                    $largestDeviceType = 'Laptop';

                    break;

                case 'Tablet':
                    if ('Laptop' == $largestDeviceType) {
                        break;
                    }
                    $largestDeviceType = 'Tablet';

                    break;

                default:
                    if ('Laptop' == $largestDeviceType) {
                        break;
                    }
                    if ('Tablet' == $largestDeviceType) {
                        break;
                    }
                    $largestDeviceType = 'Handheld';

                    break;
            }
        }

        // If there are more than 5 products in the cart, hide all shipping methods
        if (count($package['contents']) >= 5) {
            return;
        }

        $methods = get_option('iresq_shipping_methods') ?: [];
        $methods = array_filter($methods, function ($k) use ($largestDeviceType) {
            return $k['device_type'] == $largestDeviceType;
        });

        if ('KS' === $package['destination']['state'] || 'MO' === $package['destination']['state']) {
            // First, add a Pick Up option for shipping
            $this->add_rate([
                'id' => 'pickup',
                'label' => 'Local Pickup in Kansas City',
                'cost' => '0.00',
                'taxes' => false,
                'meta_data' => [
                    'originalLabel' => 'Local Pickup in Kansas City',
                    'height' => 0,
                    'weight' => 0,
                    'length' => 0,
                    'width' => 0,
                    'code' => 0,
                    'sku' => 0,
                    'updateProductMeta' => false,
                ],
            ]);
        }

        foreach ($methods as $method) {
            $startParan = strpos($method['name'], "(");
            $endParan = strpos($method['name'], ")");
            $methodTitle = str_contains($method['name'], '(') && str_contains($method['name'], ')') ? substr($method['name'], $startParan+1, $endParan-$startParan-1) : $method['name'];
                    
            $this->add_rate([
                'id' => $method['device_type'].':'.$method['code'].':'.$method['sku'],
                'label' => $methodTitle,
                'cost' => $method['price'],
                'taxes' => false,
                'meta_data' => [
                    'originalLabel' => $method['name'],
                    'height' => $method['height'],
                    'weight' => $method['weight'],
                    'length' => $method['length'],
                    'width' => $method['width'],
                    'code' => $method['code'],
                    'sku' => $method['sku'],
                    'updateProductMeta' => true,
                ],
            ]);
        }
    }

    public function shippingMethodActions()
    {
        add_filter('woocommerce_shipping_methods', function ($methods) {
            $methods[$this->id] = 'App\Shipping\FileMakerShippingMethods';

            return $methods;
        });
    }
}
