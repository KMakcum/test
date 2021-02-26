<?php

namespace ES\ServiceProviders;

use ES\Services\ElasticSearchClient;

class ElasticSearchUserSearchHandler
{
    private $ES;
    private $index;

    public function __construct()
    {
        $this->index = 'life-chef';
        $this->ES = new ElasticSearchClient();
    }

    public function get_results_from_ES($search_string)
    {
        $query =
            [
                'index' => $this->index,
                'size' => '54',
                'body' =>
                    [
                        'query' =>
                            [
                                'bool' =>
                                    [
                                        'must' =>
                                            [
                                                'bool' =>
                                                    [
                                                        'must' => [
                                                            [
                                                                'bool' => [
                                                                    'should' => [
                                                                        ['match' => ['description' => $search_string]],
                                                                        ['match' => ['part-1' => $search_string]],
                                                                        ['match' => ['part-2' => $search_string]],
                                                                        ['match' => ['part-3' => $search_string]],
                                                                        ['match' => ['ingredients' => $search_string]]
                                                                    ]
                                                                ]
                                                            ],
                                                            [
                                                                'bool' => [
                                                                    'should' => [
                                                                        'match' => ['title' => $search_string]
                                                                    ],
                                                                    'boost' => 5
                                                                ]
                                                            ]
                                                        ],

                                                    ]
                                            ],
                                    ]
                            ]
                    ]
            ];
        $search = $this->ES->client->search($query);
        return $search;
    }
}
