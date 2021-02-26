<?php

namespace ES\Controllers;
require_once get_template_directory() . '/inc/elastic-search/services/class-elastic-search-client.php';
require_once get_template_directory() . '/inc/elastic-search/service-providers/class-elastic-search-reindexer.php';
require_once get_template_directory() . '/inc/elastic-search/service-providers/class-elastic-search-user-search-handler.php';
require_once get_template_directory() . '/inc/elastic-search/models/product.php';
require_once get_template_directory() . '/inc/elastic-search/models/component.php';
require_once get_template_directory() . '/inc/elastic-search/controllers/class-admin-form-render.php';


use ES\ServiceProviders\ElasticSearchReindexer;
use ES\ServiceProviders\ElasticSearchUserSearchHandler;


class ElasticSearchDataController
{

    private static $_instance = null;

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function init()
    {
        if (!(defined('ES_USERNAME')
            && defined('ES_PASSWORD')
            && defined('ES_INSTANCE_URL')
            && defined('ES_INSTANCE_PORT'))) {
            return;
        }

        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_script('debounce', 'https://cdn.jsdelivr.net/npm/lodash@4.17.20/lodash.min.js', ['jquery']);
            wp_enqueue_script('ES-form-script', get_stylesheet_directory_uri() . '/inc/elastic-search/Views/assets/js/es-form.js', [
                'jquery',
                'lodash'
            ]);
            wp_localize_script('ES-form-script', 'AjaxSettingsES',
                [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'ajax_nonce' => wp_create_nonce('life-chef-action'),
                    'site_url' => get_site_url(),
                    'stylesheet_dir' => get_stylesheet_directory_uri()
                ]);
        });
        add_action('wp_ajax_es_form_handler', [$this, 'es_form_handler']);
        add_action('wp_ajax_nopriv_es_form_handler', [$this, 'es_form_handler']);
        add_action('plugins_finished_sync', [$this, 'es_reindex']);
        add_action('admin_menu', [$this, 'elastic_search_options_page']);
        add_action('wp_ajax_reindex_es_handler', [$this, 'reindex_es_handler']);
        add_action('admin_enqueue_scripts', function ($hook_suffix) {
            if ($hook_suffix === 'toplevel_page_es-options') {
                wp_enqueue_script('admin-script-es', get_stylesheet_directory_uri() . '/inc/elastic-search/Views/assets/js/admin-script-es.js');
                wp_enqueue_script('admin-style-es', get_stylesheet_directory_uri() . '/inc/elastic-search/Views/assets/css/admin-style-es.css');
                wp_localize_script('admin-script-es', 'settingsES',
                    [
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'ajax_nonce' => wp_create_nonce('life-chef-admin'),
                    ]
                );
                wp_enqueue_style('bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css');
                wp_enqueue_script('boot1', 'https://code.jquery.com/jquery-3.3.1.slim.min.js', array('jquery'), '', true);
                wp_enqueue_script('boot2', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array('jquery'), '', true);
                wp_enqueue_script('boot3', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js', array('jquery'), '', true);
            }
        });
        add_action('es_reindex_event', [$this, 'es_reindex']);
    }

    public function elastic_search_options_page()
    {
        add_menu_page('Elastic Search Options',
            'Elastic Search Options',
            'manage_options',
            'es-options',
            [new AdminFormRender(), 'render']);
    }


    public function reindex_es_handler()
    {
        $request = (object)$_POST;
        $request->data = json_decode(stripslashes($request->data), true);
        if (!$this->checkNonce($request->nonce, 'life-chef-admin')) {
            self::returnError('wrong nonce');
        }
        if (!wp_next_scheduled('es_reindex_event')) {
            self::returnData(wp_schedule_single_event(time() + 1, 'es_reindex_event'));
        }
    }

    public function es_form_handler()
    {
        $request = (object)$_POST;
        $request->data = json_decode(stripslashes($request->data), true);
//        if (!$this->checkNonce($request->nonce, 'life-chef-action')) {
//            self::returnError('wrong nonce');
//        }
        if (!$request->data['s']) {
            self::returnError('empty search string');
        }
        $es_search_handler = new ElasticSearchUserSearchHandler();
        $es_results = $es_search_handler->get_results_from_ES($request->data['s']);
        if ($es_results) {
            self::returnData($es_results);
        } else {
            self::returnData('no_results');
        }
    }

    public function es_reindex()
    {
        $reindexer = new ElasticSearchReindexer();

        return $reindexer->reindex_ES();

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
