<?php

namespace ES\ServiceProviders;

use ES\Models\Product;
use ES\Models\Component;
use ES\Services\ElasticSearchClient;

class ElasticSearchReindexer
{
    private $component;
    private $product;
    private $ES;
    private $index;

    public function __construct()
    {
        $this->product = new Product();
        $this->component = new Component();
        $this->index = 'life-chef';
        try {
            if (!$this->ES = new ElasticSearchClient()) {
                throw new \Exception();
            };
        } catch (\Exception $vi_kto_takie) {
            trigger_error('ElasticSearch Client didn\'t started', E_USER_NOTICE);
        }
    }

    public function get_components_for_ES()
    {
        $components_attrs = $this->component->get_all_components_attributes();
        $components_data = array_map(function ($term) {
//            $attr_meta = $this->component->get_single_component_attributes($term->slug);
            $single_prod = wc_get_product(get_page_by_title($term->name, 'OBJECT', 'product')->ID);
            return [
                "id" => $single_prod->get_id(),
                "link" => $single_prod->get_permalink(),
                "title" => $term->name,
                "description" => $term->description
            ];
            //todo mb add additional fields from CF
        }, $components_attrs);
        return $components_data;
    }

    private function get_posts_for_ES()
    {
        \TB::m('Getting all products and variations... this may take a while');
        $products_ids = $this->product->get_all_products_and_variations();
        \TB::m('Done...');
        \TB::m('Sorting vitamins and getting data about em... this may take a while');
        $vitamin_items = array_map('wc_get_product', $products_ids['vitamins']);
        $products['vitamins'] = array_map(function ($product) {
            $image = wp_get_attachment_image_url($product->get_image_id(), 'op_archive_thumbnail');
            return [
                "id" => $product->get_id(),
                "link" => parse_url(get_the_permalink($product->get_id()))['path'],
                "title" => get_the_title($product->get_id()),
                "description" => $product->get_description(),
                "image" => $image
            ];
        }, $vitamin_items);
        \TB::m('Done...');
        \TB::m('sorting groceries and getting data about em... this may take a while');
        $staples_items = array_map('wc_get_product', $products_ids['staples']);
        $products['staples'] = array_map(function ($product) {
            $image = wp_get_attachment_image_url($product->get_image_id(), 'op_archive_thumbnail');
            return [
                "id" => $product->get_id(),
                "link" => parse_url(get_the_permalink($product->get_id()))['path'],
                "title" => get_the_title($product->get_id()),
                "description" => $product->get_description(),
                "image" => $image
            ];
        }, $staples_items);
        \TB::m('Done...');
        \TB::m('sorting meals and getting data about em... this may take a while');
        $meals_items = array_map('wc_get_product', $products_ids['meals']);
        $products['meals'] = array_map(function ($product) {
            $component_1_term = get_term_by('name', $product->get_attribute('pa_part-1'), 'pa_part-1');
            $component_1_term_meta = $this->component->get_single_component_attributes($component_1_term->slug);
            $component_2_term = get_term_by('name', $product->get_attribute('pa_part-2'), 'pa_part-2');
            $component_2_term_meta = $this->component->get_single_component_attributes($component_2_term->slug);
            $component_3_term = get_term_by('name', $product->get_attribute('pa_part-3'), 'pa_part-3');
            $component_3_term_meta = $this->component->get_single_component_attributes($component_3_term->slug);
            $ingredients = $component_1_term_meta['op_variations_component_ingredients'] . ' ' . $component_2_term_meta['op_variations_component_ingredients'] . ' ' . $component_3_term_meta['op_variations_component_ingredients'];
            $image = wp_get_attachment_image_url($product->get_image_id(), 'op_archive_thumbnail');
            return [
                "id" => $product->get_id(),
                "link" => parse_url(get_the_permalink($product->get_id()))['path'],
                "title" => get_the_title($product->get_id()),
                "description" => $product->get_description(),
                "image" => $image,
                "part_1" => $component_1_term->name,
                "part_2" => $component_2_term->name,
                "part_3" => $component_3_term->name,
                "ingredients" => $ingredients
            ];
        }, $meals_items);
        return $products;
    }

    public function reindex_ES()
    {
        \TB::m('*start* ElasticSearch reindex');
        \TB::start('get-products');
        \TB::start('total-reindex-time');
        $products = $this->get_posts_for_ES();
        \TB::m('All products prepared... 😊😊');
        \TB::show_message(true, 'get-products');
        $query = [];
//        $components = $this->get_components_for_ES();
        \TB::m('Creating ElasticSearch Index..');
        try {
            $this->ES->client->indices()->delete(['index' => $this->index]);
        } catch (\Exception $e) {
            $this->ES->client->indices()->create(['index' => $this->index]);
        }
        \TB::m('Done...');
        \TB::start('reindex-es');
        \TB::m('downloading data to ElasticSearch');
        $prev_counter_value = 1;
        for ($i = 1; $i <= count($products['vitamins']); $i++) {
            $query['body'][] =
                [
                    'index' =>
                        [
                            '_index' => $this->index,
                        ]
                ];
            $query['body'][] =
                [
                    'id' => $products['vitamins'][$i - 1]['id'],
                    'category' => 'vitamins',
                    'title' => $products['vitamins'][$i - 1]['title'],
                    'description' => $products['vitamins'][$i - 1]['description'],
                    'link' => $products['vitamins'][$i - 1]['link'],
                    'image' => $products['vitamins'][$i - 1]['image'] ? $products['vitamins'][$i]['image'] : ''
                ];

            if ($prev_counter_value % 1000 === 0) {
                $responses = $this->ES->client->bulk($query);
                $query = ['body' => []];
                $prev_counter_value = 0;
                unset($responses);
            }
            $prev_counter_value++;
        }
        for ($i = 0; $i < count($products['staples']); $i++) {
            $query['body'][] =
                [
                    'index' =>
                        [
                            '_index' => $this->index,

                        ],
                ];
            $query['body'][] =
                [
                    'id' => $products['staples'][$i]['id'],
                    'category' => 'staples',
                    'title' => $products['staples'][$i]['title'],
                    'description' => $products['staples'][$i]['description'],
                    'link' => $products['staples'][$i]['link'],
                    'image' => $products['staples'][$i]['image'] ? $products['staples'][$i]['image'] : ''
                ];
            $prev_counter_value++;
            if ($prev_counter_value % 1000 === 0) {
                $responses = $this->ES->client->bulk($query);
                $prev_counter_value = 0;
                $query = ['body' => []];
                unset($responses);
            }
        }
        for ($i = 0; $i < count($products['meals']); $i++) {
            $query['body'][] =
                [
                    'index' =>
                        [
                            '_index' => $this->index,

                        ],
                ];
            $query['body'][] = [
                'id' => $products['meals'][$i]['id'],
                'category' => 'meals',
                'title' => $products['meals'][$i]['title'],
                'description' => $products['meals'][$i]['description'],
                'image' => $products['meals'][$i]['image'] ? $products['meals'][$i]['image'] : '',
                'link' => $products['meals'][$i]['link'],
                'part - 1' => $products['meals'][$i]['part_1'],
                'part - 2' => $products['meals'][$i]['part_2'],
                'part - 3' => $products['meals'][$i]['part_3'],
                'ingredients' => $products['meals'][$i]['ingredients']
            ];
            $prev_counter_value++;
            if ($prev_counter_value % 1000 === 0) {
                $responses = $this->ES->client->bulk($query);
                $query = ['body' => []];
                $prev_counter_value = 0;
                unset($responses);
            }
        }

        if (!empty($query['body'])) {
            $response = $this->ES->client->bulk($query);
        }
        \TB::m('Done...');
        \TB::show_message(true, 'es-reindex');
        \TB::m('----------------------');
        \TB::m('*end* ElasticSearch reindex', true, 'total-reindex-time');
        return $response;
    }

}
