<?php

namespace ES\Services;

use ES\Controllers\controller;

class ElasticSearchCLI
{
    public function __construct()
    {
        $options = getopt('p:');
        if ($options['p'] === '{JgfPOnb*@345}') {
            include dirname(__FILE__, 7) . '/wp-load.php';
            $es_controller = controller::getInstance();
            $es_controller->init();
            return $es_controller->es_reindex();
        } else {
            self::returnError('unauthorized');
        }
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

new ElasticSearchCLI();


