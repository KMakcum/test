<?php

namespace ES\Services;
require get_template_directory() . '/inc/vendor/autoload.php';

use Elasticsearch\ClientBuilder;

class ElasticSearchClient
{
    private $cluster;
    public $client;

    public function __construct()
    {
        $this->cluster = [
            'https://' . ES_USERNAME . ':' . ES_PASSWORD . '@' . ES_INSTANCE_URL . ':' . ES_INSTANCE_PORT . ''
        ];
        $this->client = ClientBuilder::create()
            ->setHosts($this->cluster)
            ->build();
    }
}
