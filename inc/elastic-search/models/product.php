<?php

namespace ES\Models;

use WP_Query;

class Product
{

    public function get_all_products_and_variations()
    {
        $products = [];
        $vitamins_query = new WP_Query;
        $vitamins_items = $vitamins_query->query(
            [
                'post_type' => ['product_variation', 'product'],
                'posts_per_page' => -1,
                'post_status' => ['publish'],
                'fields' => 'ids',
                'tax_query' => [
                    [
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => ['vitamins'],
                        'operator' => 'IN'
                    ]
                ]
            ]);
        $products['vitamins'] = $vitamins_items;

        $staples_query = new WP_Query;
        $staples_items = $staples_query->query([
            'post_type' => ['product_variation', 'product'],
            'posts_per_page' => -1,
            'post_status' => ['publish'],
            'fields' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => ['staples'],
                    'operator' => 'IN'
                ]
            ]
        ]);
        $products['staples'] = $staples_items;
        $products_to_filter = op_help()->global_cache->getAll();
        $products_to_filter = array_filter($products_to_filter, function ($arr) {
            return $result = $arr['type'] === 'variation' ? true : false;
        });
        $variation_ids = array_column($products_to_filter, 'var_id');
        $products['meals'] = $variation_ids;
        return $products;
    }

}
