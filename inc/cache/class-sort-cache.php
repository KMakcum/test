<?php
/**
 * Sort cache for product loops.
 *
 * @class   SFSortCache
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * SFSortCache class.
 */
class SFSortCache extends SFCache {

    private static $_instance = null;

    /**
     * @return self
     */
    static public function getInstance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * init all hooks
     */
    public function init() {
        $self                  = self::getInstance();
        $self->global_cache_id = get_option( 'global_cache_id' );
        $self->tableUp();

        add_action( 'init', [ $this, 'on_init' ] );
    }

    /**
     * On init event.
     */
    public function on_init() {
        if ( isset( $_GET['hs'] ) ) {
//            $this->create_survey_filter_zone_cache( 'default' );
            $t = microtime( true );
            var_export( $this->get_sort_cache( $count = 11, $offset = 0, $sort_type = 'price', $user_id = false, $recommended = 0, $type = 'variation', $product_cat = false, $tag = false, $additional_filter = [ 'search' => 'hello' ] ) );
//            var_export( $this->get_sort_cache( 12, 0, 'default', false, 0, 'variation' ) );
//            var_export( $this->get_customize_json() );
            echo "total: " . ( microtime( true ) - $t ) . "\n";
//            $this->create_cache_for_zero_user();
            die;
        }
    }

    /**
     * Return sort product array.
     *
     * @param integer $count
     * @param integer $offset
     * @param string $sort_type
     * @param integer|bool $user_id
     * @param integer $recommended
     * @param string $type
     * @param string|bool $product_cat
     * @param string|bool $tag
     * @param array $additional_filter
     *
     * @return array
     */
    public function get_sort_cache( $count = 11, $offset = 0, $sort_type = 'default', $user_id = false, $recommended = 0, $type = 'variation', $product_cat = false, $tag = false, $additional_filter = [] ) {
        if ( false === $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            $user_id = 0;
        }

        $structure  = $this->generate_sort_structure( $sort_type, $user_id, $type, $this->zone(), $product_cat, $tag, $format = $recommended, $additional_filter );
        $cache_name = $this->hash( $structure );

        $sort_cache = $this->get_correct_sort( $cache_name );

        if ( ! $sort_cache ) {
            $sort_cache = $this->create_sort_cache( $sort_type, $user_id, $recommended, $type, $product_cat, $tag, $additional_filter );
        }
        $products = $sort_cache['products'];

//        $sort_cache_without_recommended = $sort_cache;
//        if ( $recommended ) {
//            $sort_cache_without_recommended = $this->get_correct_sort( $cache_name, 0 );
//        }

        if ( $count == - 1 OR $count > count( $products ) ) {
            $count = count( $products );
        } else {
            $count += $offset;
        }

        $result_sort = [];
        for ( $i = $offset; $i < $count; $i ++ ) {
            if ( isset( $products[ $i ] ) AND $products[ $i ] ) {
                $result_sort[] = $products[ $i ];
            }
        }

        return [
            'ids_with_chef_score' => $result_sort,
            'ids'                 => $this->flatten_array_ids_only( $result_sort ),
            'filtered_total'      => count( $products ),
            'total'               => $sort_cache['total'],
            'name'                => $cache_name,
        ];
    }

    /**
     * Create survey filter zone cache.
     *
     * @param string $sort_type
     * @param int $user_id
     * @param int $recommended
     * @param string $type
     * @param string $product_cat
     * @param string $tag
     * @param array $additional_filter
     *
     * @return array
     */
    public function create_survey_filter_zone_cache( $sort_type, $user_id, $recommended, $type, $product_cat, $tag, $additional_filter ) {
        $zone = $this->zone();

        $structure = $this->generate_sort_structure( $sort_type, $user_id, $type, $zone, $product_cat, $tag, $recommended, $additional_filter );

        $data = [
            'name'        => $this->hash( $structure ),
            'recommended' => $recommended,
            'type'        => $type,
            'structure'   => $structure,
            'zip_zone'    => $zone,
            'product_cat' => $product_cat,
            'tag'         => $tag,
        ];

        $products = $this->check_and_do_score( $data, $user_id );

        $data['data']  = $products['products'];
        $data['total'] = $products['total'];

        return $data;
    }

    /**
     * Generate sort structure.
     *
     * @param string $sort_type
     * @param integer|bool $user_id
     * @param string $type
     * @param integer|bool $zone
     * @param string|bool $product_cat
     * @param string|bool $tag
     * @param string|bool $format
     * @param array $additional_filter
     *
     * @return array
     */
    public function generate_sort_structure( $sort_type = 'default', $user_id = false, $type = 'variation', $zone = false, $product_cat = false, $tag = false, $format = 'all', $additional_filter = [] ) {
        $user_id = $this->user_id( $user_id );

        $structure              = $this->general_structure( $sort_type, $user_id, $type, $zone, $product_cat, $tag, $additional_filter );
        $structure_with_combine = $structure;

        $survey_answers = [];
        if ( $user_id ) {
            $survey_answers = get_user_meta( $user_id, 'survey_answers', 1 );
        }

        $filter = op_help()->sf_filters->get_selected_filters();

        if ( $type == 'variation' ) {
            if ( $format OR 'all' == $format ) {
                $structure_with_combine['answers'] = $this->sort_answer( $this->combine_answers( $survey_answers, $filter ) );
            }

            if ( ! $format OR 'all' == $format ) {
                $structure['answers'] = $this->sort_answer( $filter );
            }
        }

        if ( 'all' === $format ) {
            return [
                'combine_survey_filters' => $structure_with_combine,
                'only_filters'           => $structure,
            ];
        }

        if ( ! $format ) {
            return $structure;
        }

        return $structure_with_combine;
    }

    /**
     * Generate general part of sort structure.
     *
     * @param string $sort_type
     * @param integer|bool $user_id
     * @param string $type
     * @param integer|bool $zone
     * @param string|bool $product_cat
     * @param string|bool $tag
     * @param array $additional_filter
     *
     * @return array
     */
    protected function general_structure( $sort_type, $user_id, $type, $zone, $product_cat, $tag, $additional_filter ) {
        if ( false === $zone ) {
            $zone = $this->zone_default;
            if ( $user_id ) {
                $zone = op_help()->zip_codes->get_current_user_zone();
            }
        }

        $structure = [
            'type'     => $type,
            'sort'     => $sort_type,
            'zip_zone' => $zone,
        ];

        if ( $product_cat ) {
            $structure['product_cat'] = $product_cat;
        }

        if ( $tag ) {
            $structure['tag'] = $tag;
        }

        if ( ! empty( $additional_filter ) ) {
            $structure += $additional_filter;
        }

        return $structure;
    }

    /**
     * Return filtered user_id with 0 for zero user.
     *
     * @param int $user_id
     *
     * @return int
     */
    protected function user_id( $user_id ) {
        if ( false === $user_id AND ! $this->is_zero_user ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            $user_id = 0; // for an unauthorized user
        }

        return $user_id;
    }

    /**
     * Create sort cache from user cache.
     *
     * @param string $sort_type
     * @param integer|bool $user_id
     * @param integer $recommended
     * @param string $type
     * @param string|bool $product_cat
     * @param string|bool $tag
     * @param array $additional_filter
     *
     * @return array
     */
    public function create_sort_cache( $sort_type = 'default', $user_id = false, $recommended = 0, $type = 'variation', $product_cat = false, $tag = false, $additional_filter = [] ) {
        $user_id = $this->user_id( $user_id );

        $scoring = $this->create_survey_filter_zone_cache( $sort_type, $user_id, $recommended, $type, $product_cat, $tag, $additional_filter );

        $params = [
            'data'        => $scoring['data'],
            'user_id'     => $user_id,
            'sort_type'   => $sort_type,
            'name'        => $scoring['name'],
            'recommended' => $scoring['recommended'],
            'structure'   => $scoring['structure'],
            'product_cat' => $product_cat,
            'tag'         => $tag,
            'total'       => $scoring['total'],
        ];

        $scoring['data'] = $this->sort_by_type( $params );

        return [
            'products' => $scoring['data'],
            'total'    => $scoring['total'],
        ];
    }

    /**
     * Check sort on exist and return scoring products.
     *
     * @param array $data
     * @param int $user_id
     *
     * @return array
     */
    public function check_and_do_score( $data, $user_id ) {
        $sort = $this->get_correct_sort( $data['name'] );
        if ( $sort AND ! empty( $sort ) ) {
            return $sort;
        }

        return $this->do_score( $data, $user_id );
    }

    /**
     * Return scoring products.
     *
     * @param array $data
     * @param int $user_id
     *
     * @return array
     */
    public function do_score( $data, $user_id ) {

        $products = apply_filters( 'sf_do_score_products', [], $data['type'], $data['product_cat'], $data['tag'] );

        if ( empty( $products ) && is_array( $products ) ) {
            $products = op_help()->global_cache->getByType( $data['type'], $data['product_cat'], $data['tag'] );
        }

        if ( $data['type'] != 'variation' ) {
            return [
                'products' => $products,
                'total'    => count( $products ),
            ];
        }

        $filter_score     = op_help()->survey->calculate_survey_score( $data['structure']['answers'], $user_id );
        $items_with_score = op_help()->survey->calculate_score_for_items( $products, $filter_score );
        $items_with_score = op_help()->variations->zipCheck( $items_with_score, 1, $data['zip_zone'] );
        $items_with_score = $this->filter_items_by_score_remove( $items_with_score );

        return [
            'products' => $items_with_score,
            'total'    => count( $products ),
        ];
    }

    /**
     * Filter items by score 'remove'.
     *
     * @param array $data
     *
     * @return array
     */
    public function filter_items_by_score_remove( $data ) {
        $n = [];
        foreach ( $data as $datum ) {
            if ( (string) $datum['score'] !== 'remove' ) {
                $n[] = $datum;
            }
        }

        return $n;
    }

    /**
     * Check sort to exist and control_id == global_cache_id.
     *
     * @param string $name
     *
     * @return bool|array
     */
    public function get_correct_sort( $name ) {
        global $wpdb;

        $sort_cache = $wpdb->get_row(
            $wpdb->prepare( /** @lang sql */ "SELECT * FROM `{$wpdb->prefix}op_sort_cache` WHERE `name` = %s AND `control_id` = %s",
                $name, $this->global_cache_id
            )
        );

        if ( empty( $sort_cache ) OR ! $sort_cache->data ) {
            return false;
        }
        $data = unserialize( $sort_cache->data );

        return ( empty( $data ) ) ? false : [
            'products' => $data,
            'total'    => $sort_cache->total,
        ];
    }

    /**
     * Sort products by type.
     *
     * @param array $data
     *
     * @return array
     */
    public function sort_by_type( $data ) {
        $this->remove_sort_cache( [
            'name' => $data['name'],
        ] );

        switch ( $data['sort_type'] ) {
            case 'price-desc' :
                $products = $this->sort_price_desc( $data, true );
                break;
            case 'price' :
                $products = $this->sort_price_desc( $data, false );
                break;
            case 'default' :
            default:
                $products = $this->sort_default( $data );
                break;
        }

        return $products;
    }

    /**
     * Set cache for new users (or survey not exist).
     */
    public function create_cache_for_zero_user() {
        $user_id = 0;

        $this->is_zero_user = true;

        $this->zone_default = 'national';
        $this->create_sort_cache( 'default', $user_id );
        $this->create_sort_cache( 'price-desc', $user_id );
        $this->create_sort_cache( 'price', $user_id );

        $this->zone_default = 'local';
        $this->create_sort_cache( 'default', $user_id );
        $this->create_sort_cache( 'price-desc', $user_id );
        $this->create_sort_cache( 'price', $user_id );

        $this->is_zero_user = false;
    }

    /**
     * Sort products by default (chef_score and OrderBy).
     *
     * @param array $data
     *
     * @return array
     */
    public function sort_default( $data ) {
        $scores = [];

        foreach ( $data['data'] as $product ) {
            $scores[ $product['chef_score'] ][] = $product;
        }
        $data['data'] = $scores;

        return $this->prepare_sort( $data );
    }

    /**
     * Sort products by default (chef_score and OrderBy).
     *
     * @param array $data
     * @param bool $is_revers
     *
     * @return array
     */
    public function sort_price_desc( $data, $is_revers = true ) {
        $scores = [];

        foreach ( $data['data'] as $product ) {
            $scores[ ( $product['price'] * 10 ) ][] = $product;
        }
        $data['data'] = $scores;

        return $this->prepare_sort( $data, $is_revers );
    }

    /**
     * Common part for sorting.
     *
     * @param array $data
     * @param bool $is_revers
     *
     * @return array
     */
    protected function prepare_sort( $data, $is_revers = true ) {
        if ( $is_revers ) {
            krsort( $data['data'] );
        } else {
            ksort( $data['data'] );
        }

        $data['data'] = $this->extra_sort_order_by( $data['data'] );
        $data['data'] = $this->flatten_array( $data['data'] );
        $this->insert_sort_cache( $data );

        return $data['data'];
    }

    /**
     * Remove sort cache from op_sort_cache.
     *
     * @param array $where
     *
     * @return bool
     */
    public function remove_sort_cache( $where = [] ) {
        global $wpdb;

        $where_arr = [];

        if ( ! empty( $where ) ) {
            $where_arr += $where;
        }

        return $wpdb->delete( "{$wpdb->prefix}op_sort_cache", $where_arr );
    }

    /**
     * Insert sort cache.
     *
     * @param array $data
     *
     * @return int
     */
    public function insert_sort_cache( $data ) {
        global $wpdb;

        if ( ! $data['user_id'] AND ! $this->is_zero_user ) {
            return 0; // for zero user
        }

        $wpdb->insert(
            "{$wpdb->prefix}op_sort_cache",
            [
                'data'        => serialize( $data['data'] ),
                'control_id'  => $this->global_cache_id,
                'sort_type'   => $data['sort_type'],
                'name'        => $data['name'],
                'recommended' => $data['recommended'],
                'user_id'     => $data['user_id'],
                'structure'   => serialize( $data['structure'] ),
                'total'       => $data['total'],
            ]
        );

        return (int) $wpdb->insert_id;
    }

    /**
     * Flatten the array with ids only.
     *
     * @param array $arr
     *
     * @return array
     */
    protected function flatten_array_ids_only( $arr ) {
        $r = [];
        foreach ( $arr as $item ) {
            array_push( $r, $item['id'] );
        }

        return $r;
    }

    /**
     * Return chef score from global $sf_sort_cache by product_id.
     *
     * @param int $product_id
     * @param array|bool $data
     *
     * @return int|bool
     */
    public function get_chef_score( $product_id, $data = false ) {
        if ( ! $data ) {
            global $sf_sort_cache;
            $data = $sf_sort_cache;
        }

        foreach ( $data['ids_with_chef_score'] as $item ) {
            if ( $item['id'] == $product_id ) {
                return $item['cs'];
            }
        }

        return false;
    }

    /**
     * CMP by OrderBy for usort().
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function cmp_order_by( $a, $b ) {
        $a = ( $a['order_by'] !== null ) ? $a['order_by'] : PHP_INT_MAX;
        $b = ( $b['order_by'] !== null ) ? $b['order_by'] : PHP_INT_MAX;
        if ( $a == $b ) {
            return 0;
        }

        return ( $a < $b ) ? - 1 : 1;
    }

    /**
     * Create cache table
     */
    function tableUp() {
        global $wpdb;

        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate}";

        $wpdb->query( /** @lang sql */ "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}op_sort_cache` (
            `id` bigint(20) unsigned NOT NULL auto_increment,
            `user_id` bigint(20) NOT NULL,
            `name` varchar(125) NOT NULL,
            `sort_type` varchar(125) NOT NULL,
            `recommended` int(3) NOT NULL,
            `data` longtext NOT NULL default '',
            `control_id` varchar(125) NOT NULL,
            `structure` text NOT NULL,
            `total` bigint(20) NOT NULL,
            PRIMARY KEY (id) 
        ) {$charset_collate};" );
    }
}
