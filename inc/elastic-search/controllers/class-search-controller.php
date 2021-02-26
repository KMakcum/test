<?php

namespace ES\Controllers;

use ES\ServiceProviders\ElasticSearchUserSearchHandler;

class SearchController
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
        add_action('wp_ajax_nopriv_show_all_products_handler', [$this, 'show_all_products_handler']);
        add_action('wp_ajax_show_all_products_handler', [$this, 'show_all_products_handler']);
        if ((!empty($_POST['action']) && $_POST['action'] === 'set_chose_filters' && $_POST['isSearchPage'] === 'true')
            || (!empty($_POST['action']) && $_POST['action'] === 'clear_selected_filters' && $_POST['isSearchPage'] === 'true')
            || ($this->getURLSegment(1) === 'search-results')) {
        add_filter('sf_do_score_products', [$this, 'filter_products_sort_cache'], 10, 4);
    }

        if (($this->getURLSegment(1) === 'search-results')) {
            if (!post_exists('Search results')) {
                $search_page = [
                    'post_title' => 'Search results',
                    'post_name' => 'search-results',
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_type' => 'page'
                ];
                wp_insert_post($search_page);
            }

            add_action('wp_enqueue_scripts', function () {
                wp_enqueue_script('ES-search-results-script', get_stylesheet_directory_uri() . '/inc/elastic-search/Views/assets/js/search-results.js', [
                    'jquery',
                    'ES-form-script',
                    'lodash'
                ]);
//				wp_enqueue_script( 'woocommerce-add-to-cart',
//					plugins_url() . '/woocommerce/' . 'assets/js/frontend/add-to-cart.js', [ 'jquery' ] );
                wp_enqueue_script('nice-number-js',
                    get_stylesheet_directory_uri() . '/assets/js/node_modules/jquery.nice-number/dist/jquery.nice-number.min.js',
                    ['jquery']);
                wp_enqueue_script('ui', 'https://code.jquery.com/ui/1.10.3/jquery-ui.js', ['jquery']);
                wp_enqueue_script('meal-plan-bottom-nav-offerings',
                    get_stylesheet_directory_uri() . '/assets/js/meal-plan-bottom-nav-offerings.js',
                    ['jquery', 'nice-number-js']);
                wp_localize_script('meal-plan-bottom-nav-offerings', 'ajaxSettingsMealPlan', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'site_url' => get_site_url(),
                    'stylesheet_dir' => get_stylesheet_directory_uri()
                ]);
                wp_localize_script('op_app_backend_dev', 'backendParams', [
                    'searchPage' => true
                ]);
                wp_enqueue_script('offerings-form-ajax',
                    get_stylesheet_directory_uri() . '/assets/js/offerings-form-ajax.js', ['jquery']);
                wp_localize_script('offerings-form-ajax', 'ajaxSettingsOfferingsForm', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'ajax_nonce' => wp_create_nonce('life-chef-action')
                ]);
            });
        }
    }

    public function get_sort_template()
    {
//            if ( ! wc_get_loop_prop( 'is_paginated' ) || ! woocommerce_products_will_display() ) {
//                return;
//            }
        $show_default_orderby = 'menu_order' === apply_filters('woocommerce_default_catalog_orderby', get_option('woocommerce_default_catalog_orderby', 'menu_order'));
        $catalog_orderby_options = apply_filters(
            'woocommerce_catalog_orderby',
            array(
                'menu_order' => __('Default sorting', 'woocommerce'),
                'popularity' => __('Sort by popularity', 'woocommerce'),
                'rating' => __('Sort by average rating', 'woocommerce'),
                'date' => __('Sort by latest', 'woocommerce'),
                'price' => __('Sort by price: low to high', 'woocommerce'),
                'price-desc' => __('Sort by price: high to low', 'woocommerce'),
            )
        );

        $default_orderby = wc_get_loop_prop('is_search') ? 'relevance' : apply_filters('woocommerce_default_catalog_orderby', get_option('woocommerce_default_catalog_orderby', ''));
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $orderby = isset($_GET['orderby']) ? wc_clean(wp_unslash($_GET['orderby'])) : $default_orderby;
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if (wc_get_loop_prop('is_search')) {
            $catalog_orderby_options = array_merge(array('relevance' => __('Relevance', 'woocommerce')), $catalog_orderby_options);

            unset($catalog_orderby_options['menu_order']);
        }

        if (!$show_default_orderby) {
            unset($catalog_orderby_options['menu_order']);
        }

        if (!wc_review_ratings_enabled()) {
            unset($catalog_orderby_options['rating']);
        }

        if (!array_key_exists($orderby, $catalog_orderby_options)) {
            $orderby = current(array_keys($catalog_orderby_options));
        }

        wc_get_template(
            'loop/orderby.php',
            array(
                'catalog_orderby_options' => $catalog_orderby_options,
                'orderby' => $orderby,
                'show_default_orderby' => $show_default_orderby,
            )
        );
    }

    public function filter_products_sort_cache($prod, $type, $product_cat, $tag)
    {

        $search_string = json_decode(stripslashes($_COOKIE['es_search_string']), true)['s'];
        $content = op_help()->search_controller->get_es_results($search_string);
        $cached_products = [];

        if ($type == 'variation') {
            foreach ($content['meals'] as $prod) {
                array_push($cached_products, op_help()->global_cache->getByType($type, $product_cat, $tag, $prod['id'])[0]);
            }
        } elseif ($type == 'simple' && $product_cat == 27) {
            foreach ($content['staples'] as $prod) {
                array_push($cached_products, op_help()->global_cache->getByType($type, $product_cat, $tag, $prod['id'])[0]);
            }
        } elseif ($type == 'simple' && $product_cat == 28) {
            foreach ($content['vitamins'] as $prod) {
                array_push($cached_products, op_help()->global_cache->getByType($type, $product_cat, $tag, $prod['id'])[0]);
            }
        }

        if (empty($cached_products))
            return null;
        return $cached_products;
    }

    public function get_es_results($search_string)
    {
        $es_search_handler = new ElasticSearchUserSearchHandler();
        $response = $es_search_handler->get_results_from_ES($search_string);
        $content = [];
        $content['vitamins'] = [];
        $content['meals'] = [];
        $content['staples'] = [];
        if ($response['hits']['hits']) {
            foreach ($response['hits']['hits'] as $document) {
                switch ($document['_source']['category']) {
                    case 'vitamins' :
                        $content['vitamins'][] =
                            [
                                'id' => $document['_source']['id'],
                                'title' => $document['_source']['title'],
                                'link' => $document['_source']['link'],
                                'description' => $document['_source']['description'],
                                'ingredients' => $document['_source']['ingredients'],
                                'image_url' => $document['_source']['image']
                            ];
                        break;
                    case 'staples' :
                        $content['staples'][] =
                            [
                                'id' => $document['_source']['id'],
                                'title' => $document['_source']['title'],
                                'link' => $document['_source']['link'],
                                'description' => $document['_source']['description'],
                                'ingredients' => $document['_source']['ingredients'],
                                'image_url' => $document['_source']['image']
                            ];
                        break;
                    case 'meals' :
                        $content['meals'][] =
                            [
                                'id' => $document['_source']['id'],
                                'title' => $document['_source']['title'],
                                'link' => $document['_source']['link'],
                                'description' => $document['_source']['description'],
                                'ingredients' => $document['_source']['ingredients'],
                                'image_url' => $document['_source']['image']
                            ];
                        break;
                }
            }
        }
        return $content;
    }

    public function show_all_products_handler()
    {
        $request = (object)$_POST;
        setcookie('es_search_string', json_encode(json_decode(stripslashes($request->search_string), true)), time() + 86400, '/');
        setcookie('selector', stripslashes($request->selector), time() + 86400, '/');
        self::returnData('seted');
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