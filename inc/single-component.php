<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class SingleComponentPageClass
{
    private static $_instance = null;

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function getURLSegments()
    {
        return explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    }

    private function getURLSegment($n)
    {
        $segs = $this->getURLSegments();
        return count($segs) > 0 && count($segs) >= ($n - 1) ? $segs[$n] : '';
    }

    public function init()
    {
        add_action('wp_ajax_single_component_qa_ajax_endpoint', [$this, 'single_component_qa_ajax_endpoint']);
        add_action('wp_ajax_nopriv_single_component_qa_ajax_endpoint', [$this, 'single_component_qa_ajax_endpoint']);
        add_action('carbon_fields_register_fields', [$this, 'set_single_component_fields']);
        add_action('wp_ajax_subscribe_handler', [$this, 'subscribe_handler']);
        add_action('wp_ajax_nopriv_subscribe_handler', [$this, 'subscribe_handler']);

        add_action('wp_enqueue_scripts', [$this, 'single_component_enqueue_scripts']);
    }

    public function single_component_enqueue_scripts()
    {
        $flag = false;
        if (($this->getURLSegment(1) === 'product') && ($this->getURLSegment(2)) !== 'main-meal-product') {
            wp_enqueue_script('single-component-zendesk-int', get_stylesheet_directory_uri() . '/assets/js/single-component.js', ['jquery']);
            wp_localize_script('single-component-zendesk-int', 'ajaxSettings', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('life-chef-action')
            ]);
            $flag = true;
        }
        if ($this->getURLSegment(1) === 'product') {
            wp_enqueue_script('modal-submit-question', get_stylesheet_directory_uri() . '/assets/js/faq-ajax.js', ['jquery',
                'dropzone-js-component']);
            wp_enqueue_script('dropzone-js-component', 'https://rawgit.com/enyo/dropzone/master/dist/dropzone.js', ['jquery']);
            if (!$flag) {
                wp_localize_script('modal-submit-question', 'ajaxSettings', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'ajax_nonce' => wp_create_nonce('life-chef-action')
                ]);
            }
            if (is_user_logged_in()) {
                wp_localize_script('modal-submit-question', 'userSettings',
                    [
                        'userName' => wp_get_current_user()->user_nicename,
                        'userEmail' => wp_get_current_user()->user_email,
                        'userPhone' => get_user_meta(wp_get_current_user()->ID, 'phone_number', true)
                    ]
                );
            }
        }
    }

    public function similar_meals_content($product_slug)
    {
        $variations = op_help()->global_cache->getAll();
        $variations = array_filter($variations, function ($arr) {
            return $result = $arr['type'] === 'variation' ? true : false;
        });

        $variation_ids = [];
        $i = 0;
        foreach ($variations as $variation) {
            if (in_array($product_slug, $variation['attributes']) && $i !== 59) {
                $variation_ids[$i] = $variation['variation_id'];
                $i++;
            }
        }
        $meals_items = array_map('wc_get_product', $variation_ids);
        return array_map(function ($product) {
            $image = wp_get_attachment_image_url($product->get_image_id(), 'op_archive_thumbnail');
            if ($product->is_on_sale()) {
                $price = [
                    'current' => $product->get_sale_price(),
                    'old' => $product->get_regular_price(),
                ];
            } else {
                $price = [
                    'current' => $product->get_regular_price()
                ];
            }

            return [
                "id" => $product->get_id(),
                "link" => get_the_permalink($product->get_id()),
                "title" => get_the_title($product->get_id()),
                "price" => $price,
                "image" => $image,
            ];
        }, $meals_items);
    }

    public function single_component_qa_ajax_endpoint()
    {
        $request = (object)$_POST;
        $request->data = json_decode(stripslashes($request->data), true);
        if (!$this->checkNonce($request->nonce, 'life-chef-action')) {
            self::returnError('wrong nonce');
        }
        if (!$request->data) {
            self::returnError('empty category');
        }
        self::returnData($this->get_category_qa_zendesk($request->data));
    }

    private function get_category_qa_zendesk($category)
    {
        $zendesk_category_id = '';
        switch ($category) {
            case 'vitamins':
                $zendesk_category_id = '360010596534';
                break;
            case 'meals':
                $zendesk_category_id = '360010596394';
                break;
            case 'staples':
                $zendesk_category_id = '360011717813';
                break;
            case 'checkout':
                $zendesk_category_id = '360011742613';
                break;
            case 'single-component':
                $zendesk_category_id = '360011922173';
                break;
        }
        $output_articles = [];
        $zendesk_api = new ZenDeskIntegration();
        $zendesk_api->init();
        $response_pages[0] = $zendesk_api->client->helpCenter->sections()->articles()->findAll();
        for ($i = 1; $i < $response_pages[0]->page_count; $i++) {
            $next_page = $response_pages[$i - 1]->next_page;
            $pages[$i] = json_decode(file_get_contents($next_page));
        }
        foreach ($response_pages as $response_page) {
            $i = 0;
            foreach ($response_page->articles as $article) {
                if ($article->section_id === (integer)$zendesk_category_id) {
                    $output_articles[$i] = $article;
                    $i++;
                }
            }
        }
        return $output_articles;
    }


    public function subscribe_handler()
    {

    }

    public function set_single_component_fields()
    {

    }

    public function get_single_component_fields($term_slug)
    {
        $term = get_term_by('slug', $term_slug, 'pa_part-1');
        return [
            'op_variations_component_description' =>
                $term->description ?
                    $term->description : '',
            'op_variations_component_sku' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component_sku') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component_sku') : '',
            'op_variations_component_thumb' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component_thumb') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component_thumb') : '',
            'op_variations_component__gallery' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component__gallery') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component__gallery') : '',
            'op_variations_component_cooking_steps' => carbon_get_term_meta($term->term_id, 'op_variations_component_cooking_steps') ?
                carbon_get_term_meta($term->term_id, 'op_variations_component_cooking_steps') : '',
            'op_variations_component_steps_header' => carbon_get_term_meta($term->term_id, 'op_variations_component_steps_header') ?
                carbon_get_term_meta($term->term_id, 'op_variations_component_steps_header') : '',
            'op_variations_component_instructions' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component_instructions') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component_instructions') : '',
            'op_variations_component_calories' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component_calories') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component_calories') : '',
            'op_variations_component_fats' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component_fats') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component_fats') : '',
            'op_variations_component_proteins' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component_proteins') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component_proteins') : '',
            'op_variations_component_carbohydrates' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component_carbohydrates') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component_carbohydrates') : '',
            'op_variations_component_allergens' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component_allergens') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component_allergens') : '',
            'op_variations_component_badges' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component_badges') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component_badges') : '',
            'op_variations_component_ingredients' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component_ingredients') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component_ingredients') : '',
            'op_variations_component_store_type' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component_store_type') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component_store_type') : '',
            'op_variations_component_sort_order' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component_sort_order') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component_sort_order') : '',
            'op_variations_component_nutrition_img' =>
                carbon_get_term_meta($term->term_id, 'op_variations_component_nutrition_img') ?
                    carbon_get_term_meta($term->term_id, 'op_variations_component_nutrition_img') : '',
        ];

    }

    private function checkNonce($nonce, $action)
    {
        return wp_verify_nonce($nonce, $action);
    }

    private static function returnData($data = [])
    {
        header('Content-Type:application/json');
        echo json_encode(['status' => true, 'data' => $data]);
        wp_die();
    }

    private static function returnError($message)
    {
        header('Content-Type:application/json');
        wp_send_json_error($message, 400);
        wp_die();
    }

}
