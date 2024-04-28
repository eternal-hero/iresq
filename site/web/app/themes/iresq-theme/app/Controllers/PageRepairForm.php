<?php

namespace App\Controllers;

use App\Classes\QueryBuilder;
use Sober\Controller\Controller;

class PageRepairForm extends Controller
{
    public static function getDevicesJson()
    {
        $devices = [];
        $terms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0]);
        foreach($terms as $term) {
            if($term->slug == 'uncategorized') {
                continue;
            }

            $brands = [];
            $brandTerms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => $term->term_id]);
            foreach($brandTerms as $brandTerm) {
                $models = [];
                $modelTerms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => $brandTerm->term_id]);
                foreach($modelTerms as $modelTerm) {
                    $models[] = [
                        'termId' => $modelTerm->term_id,
                        'slug' => $modelTerm->slug,
                        'name' => $modelTerm->name,
                        'url' => get_category_link($modelTerm->term_id),
                        'thumbnail' => wp_get_attachment_url(get_term_meta($modelTerm->term_id, 'thumbnail_id', true))
                    ];
                }

                $brands[] = [
                    'termId' => $brandTerm->term_id,
                    'slug' => $brandTerm->slug,
                    'name' => $brandTerm->name,
                    'models' => $models,
                    'url' => get_category_link($brandTerm->term_id),
                    'thumbnail' => wp_get_attachment_url(get_term_meta($brandTerm->term_id, 'thumbnail_id', true))
                ];
            }

            $devices[] = [
                'termId' => $term->term_id,
                'slug' => $term->slug,
                'name' => $term->name,
                'brands' => $brands,
                'url' => get_category_link($term->term_id),
                'thumbnail' => wp_get_attachment_url(get_term_meta($term->term_id, 'thumbnail_id', true))
            ];
        }
        return json_encode($devices);
    }

    /**
     * Get the devices via query and return them as an associative array.
     *
     * @return array
     */
    public static function getDevices()
    {
        $devices = [];
        foreach (QueryBuilder::getTermsByName('product_cat', true, 'count', 'desc') as $device) {
            $devices[$device->slug] = $device->name;
        }

        $devices['unsure'] = 'Browse All Devices & Repairs';

        return $devices;
    }

    /**
     *  Return URL of image.
     *
     * @return string
     */
    public static function getImage(string $name, string $device = '')
    {
        $name = strtolower($name);
        $device = strtolower($device);
        $url = '';
        if ('i\'m not sure' == $name) {
            return $url;
        }
        if ('ipod' == $name || 'ipod' == $device) {
            return '/app/uploads/2021/02/iresq-product-audio_player-generic.png';
        }

        if (empty($device)) {
            if ('tablet' == $name) {
                return '/app/uploads/2021/02/iresq-product-tablet-generic_with_case.png';
            }
            if ('laptop' == $name) {
                return '/app/uploads/2021/02/iresq-product-laptop-generic.png';
            }
            if ('desktop' == $name) {
                return '/app/uploads/2021/02/iresq-product-desktop-generic.png';
            }
            if ('watch' == $name) {
                return '/app/uploads/2021/03/iresq-device-apple-watch2.png';
            }
            if ('phone' == $name) {
                return '/app/uploads/2021/03/iresq-product-phone-android.png';
            }
        } else {
            if ('desktop' == $device) {
                if ('dell' == $name) {
                    return '/app/uploads/2021/02/iresq-product-desktop-generic.png';
                }
                if ('hp' == $name) {
                    return '/app/uploads/2021/02/iresq-product-desktop-generic.png';
                }
                if ('apple' == $name) {
                    return '/app/uploads/2021/02/iresq-product-desktop-imac.png';
                }
            } elseif ('laptop' == $device) {
                if ('dell' == $name) {
                    return '/app/uploads/2021/02/iresq-product-laptop-generic.png';
                }
                if ('hp' == $name) {
                    return '/app/uploads/2021/02/iresq-product-laptop-generic.png';
                }
                if ('apple' == $name) {
                    return '/app/uploads/2021/02/iresq-product-laptop-macbook.png';
                }
            } elseif ('phone' == $device) {
                if ('apple' == $name) {
                    return '/app/uploads/2021/02/iresq-product-phone-iphone.png';
                }
                if ('samsung' == $name) {
                    return '/app/uploads/2021/02/iresq-product-phone-android.png';
                }
            } elseif ('tablet' == $device) {
                if ('apple' == $name) {
                    return '/app/uploads/2021/02/iresq-product-tablet-ipad_with_pencil.png';
                }
                if ('samsung' == $name) {
                    return '/app/uploads/2021/02/iresq-product-tablet-generic_with_case.png';
                }
            }
        }

        return $url;
    }

    /**
     * Get the repairs via query and return them as an associative array.
     *
     * @return array
     */
    public static function getRepairs()
    {
        $repairs = [];
        foreach (QueryBuilder::getTermsByName('repairs', true, 'count', 'desc') as $repair) {
            $repairs[$repair->slug] = $repair->name;
        }

        $repairs['unsure'] = 'Browse All Repairs';

        return $repairs;
    }

    /**
     * Returns an array of the available brands for the device type.
     *
     * @param mixed $device
     */
    public static function getBrands($device)
    {
        $brands = [];
        $products_request = QueryBuilder::queryProductsByTaxonomy('product_cat', [$device]);

        if ($products_request->have_posts()) {
            while ($products_request->have_posts()) {
                $products_request->the_post();
                $brands_obj = wp_get_post_terms(get_the_ID(), 'pa_brand');
                if (!empty($brands_obj)) {
                    foreach ($brands_obj as $brand) {
                        $brands[$brand->slug] = $brand->name;
                    }
                }
            }
        }

        $titleDevice = ucwords($device);

        $brands['unsure'] = "Browse All {$titleDevice} Repairs";

        return $brands;
    }

    /**
     * Returns an array of models for the device and brand passed in.
     */
    public static function getModels(string $device = '', string $brand = '')
    {
        $brand_to_query = '';
        if ('apple' == $brand) {
            if ('phone' == $device) {
                $brand_to_query = 'pa_iphone-models';
            } elseif ('tablet' == $device) {
                $brand_to_query = 'pa_ipad-models';
            } elseif ('laptop' == $device) {
                $brand_to_query = 'pa_macbook-models';
            } elseif ('ipod' == $device) {
                $brand_to_query = 'pa_ipod-models';
            } elseif ($device = 'watch') {
                $brand_to_query = 'pa_watch-models';
            }
        } elseif ('samsung' == $brand) {
            if ('phone' == $device) {
                $brand_to_query = 'pa_samsung-phone-models';
            } elseif ('tablet' == $device) {
                $brand_to_query = 'pa_samsung-tablet-models';
            }
        } elseif ('dell' == $brand) {
            if ('laptop' == $device) {
                $brand_to_query = 'pa_dell-laptop-models';
            }
        } elseif ('hp' == $brand) {
            if ('laptop' == $device) {
                $brand_to_query = 'pa_hp-laptop-models';
            }
        }
        $models = [];
        if (!empty($brand_to_query)) {
            foreach (QueryBuilder::getTermsByName($brand_to_query) as $model) {
                $models[$model->slug] = $model->name;
            }
        }

        $titleBrand = ucwords($brand);

        $models['unsure'] = "Browse All {$titleBrand} Repairs";

        return $models;
    }
}
