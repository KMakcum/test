<?php
require_once 'zendesc-int.php';

class ZenDeskHelpCenterIntegration
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
        add_action('wp_ajax_category_qa_ajax_endpoint', [$this, 'get_category_qa_ajax_endpoint']);
        add_action('wp_ajax_nopriv_category_qa_ajax_endpoint', [$this, 'get_category_qa_ajax_endpoint']);
//        add_action('wp_ajax_checkout_feedback_form_handler', [$this, 'checkout_feedback_form_handler']);
//        add_action('wp_ajax_nopriv_checkout_feedback_form_handler', [$this, 'checkout_feedback_form_handler']);
        add_action('wp_enqueue_scripts', function () {
            if ($this->getURLSegment(1) === 'product' && $this->getURLSegment(2) === 'main-meal-product') {
                wp_enqueue_script('single-product-zendesk-int', get_stylesheet_directory_uri() . '/assets/js/single-product-zendesk-ajax.js', ['jquery']);
                wp_localize_script('single-product-zendesk-int', 'ajaxSettings', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'ajax_nonce' => wp_create_nonce('life-chef-action')
                ]);
            }
            if ($this->getURLSegment(1) === 'checkout') {
                wp_enqueue_script('single-product-zendesk-int', get_stylesheet_directory_uri() . '/assets/js/checkout-zendesk-ajax.js', ['jquery']);
                wp_localize_script('single-product-zendesk-int', 'ajaxSettings', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'ajax_nonce' => wp_create_nonce('life-chef-action')
                ]);
            }
        });
    }

//    public function checkout_feedback_form_handler()
//    {
//        $request = (object)$_POST;
//        $request->data = json_decode(stripslashes($request->data), true);
//        if (!$this->checkNonce($request->nonce, 'life-chef-action')) {
//            self::returnError('wrong nonce');
//        }
//
//
//
//    }

    public function get_category_qa_ajax_endpoint()
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

    public function get_category_qa_zendesk($category)
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
