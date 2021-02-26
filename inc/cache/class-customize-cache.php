<?php
/**
 * Sort cache for product loops.
 *
 * @class   SFCustomizeCache
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * SFCustomizeCache class.
 */
class SFCustomizeCache extends SFCache  {

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
        if ( isset( $_GET['cs'] ) ) {
//            $this->create_survey_filter_zone_cache( 'default' );
            $t = microtime( true );
//            var_export( $this->get_sort_cache( $count = 11, $offset = 0, $sort_type = 'price-desc' ) );
//            var_export( $this->get_sort_cache( 12, 0, 'default', false, 0, 'variation' ) );
            var_export( $this->get_customize_json() );
            echo "total: " . ( microtime( true ) - $t ) . "\n";
//            $this->create_cache_for_zero_user();
            die;
        }
    }

    /**
     * Return user zone.
     *
     * @return string
     */
    public function zone() {
        $zone = op_help()->zip_codes->get_current_user_zone();
        if ( ! $zone ) {
            $zone = $this->zone_default;
        }

        return $zone;
    }

    /**
     * Return hash of structure.
     *
     * @param array $structure
     *
     * @return string
     */
    public function hash( $structure ) {
        return md5( json_encode( $structure ) );
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
     * Get customize components data from db.
     *
     * @param int|bool $recommended
     * @param integer|bool $user_id
     *
     * @return string
     */
    public function get_customize_json( $recommended = false, $user_id = false ) {
        $user_id = $this->user_id( $user_id );

        if ( $recommended === false ) {
            $recommended = (bool) op_help()->sf_user->check_survey_default();
        }

        $structure  = $this->generate_customize_structure( $recommended, $user_id );
        $cache_name = $this->hash( $structure );

        $customize_cache = $this->get_correct_sort( $cache_name );
        if ( ! $customize_cache ) {
            $customize_cache = $this->create_customize_cache( $structure, $cache_name, $user_id, $recommended );
        }

        return $customize_cache;
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

        $customize_cache = $wpdb->get_row(
            $wpdb->prepare( /** @lang sql */ "SELECT * FROM `{$wpdb->prefix}op_customize_cache` WHERE `name` = %s AND `control_id` = %s",
                $name, $this->global_cache_id
            )
        );

        if ( empty( $customize_cache ) OR ! $customize_cache->data ) {
            return false;
        }
        $data = unserialize( $customize_cache->data );

        return ( empty( $data ) ) ? false : $data;
    }

    /**
     * Create customize components data.
     *
     * @param array $structure
     * @param string $cache_name
     * @param int $user_id
     * @param bool $recommended
     *
     * @return array
     */
    public function create_customize_cache( $structure, $cache_name, $user_id, $recommended ) {
        $components = $this->get_components();
        $components = op_help()->survey->calculate_customize_components( $components, $structure );

        $this->insert_customize_cache( [
            'data'        => $components,
            'name'        => $cache_name,
            'structure'   => $structure,
            'user_id'     => $user_id,
            'recommended' => $recommended,
        ] );

        return $components;
    }

    /**
     * Generate sort structure.
     *
     * @param int|bool $recommended
     * @param integer|bool $user_id
     *
     * @return array
     */
    public function generate_customize_structure( $recommended, $user_id = false ) {
        $user_id = $this->user_id( $user_id );

        $survey_answers = [];

        if ( $user_id AND $recommended ) {
            $survey_answers = get_user_meta( $user_id, 'survey_answers', 1 );
        }

        $filter = op_help()->sf_filters->get_selected_filters();

        return $this->sort_answer( $this->combine_answers( $survey_answers, $filter ) );
    }

    /**
     * Insert sort cache.
     *
     * @param array $data
     *
     * @return int
     */
    public function insert_customize_cache( $data ) {
        global $wpdb;

        $wpdb->insert(
            "{$wpdb->prefix}op_customize_cache",
            [
                'data'        => serialize( $data['data'] ),
                'control_id'  => $this->global_cache_id,
                'name'        => $data['name'],
                'user_id'     => $data['user_id'],
                'structure'   => serialize( $data['structure'] ),
            ]
        );

        return (int) $wpdb->insert_id;
    }

    /**
     * Get customize components data from db.
     */
    public function get_components() {
        global $wpdb;

        $all_components = $wpdb->get_results( /** @lang sql */
            "SELECT * FROM $wpdb->termmeta as term
                LEFT JOIN $wpdb->term_taxonomy as tax 
                ON term.term_id=tax.term_id
                LEFT JOIN $wpdb->terms as terms
                ON terms.term_id=term.term_id
             WHERE tax.taxonomy LIKE 'pa_part-1'
             AND terms.term_id NOT IN (SELECT tm.term_id FROM `$wpdb->termmeta` as tm
                WHERE tm.meta_key = 'is_published' AND tm.meta_value = '0')
             ORDER BY term.term_id", ARRAY_A
        );

        foreach ( $all_components as $component ) {
            if ( empty( $components[ $component['term_id'] ] ) ) {
                $components[ $component['term_id'] ]['meta_id'] = $component['meta_id'];
            }

            if ( empty( $components[ $component['name'] ] ) ) {
                $components[ $component['term_id'] ]['name'] = $component['name'];
            }

            if ( empty( $components[ $component['slug'] ] ) ) {
                $components[ $component['term_id'] ]['slug'] = $component['slug'];
            }
            $components[ $component['term_id'] ][ $component['meta_key'] ] = $component['meta_value'];
        }

        // get images
        foreach ( $components as $key => $value ) {
            $components[ $key ]['_op_variations_component_thumb'] = wp_get_attachment_image( $value['_op_variations_component_thumb'], 'thumbnail', false, [ 'class' => 'option-item__img' ] );
        }

        return $components;
    }

    /**
     * Create cache table
     */
    function tableUp() {
        global $wpdb;

        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate}";

        $wpdb->query( /** @lang sql */ "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}op_customize_cache` (
            `id` bigint(20) unsigned NOT NULL auto_increment,
            `user_id` bigint(20) NOT NULL,
            `name` varchar(125) NOT NULL,
            `recommended` int(3) NOT NULL,
            `data` longtext NOT NULL default '',
            `control_id` varchar(125) NOT NULL,
            `structure` text NOT NULL,
            PRIMARY KEY (id) 
        ) {$charset_collate};" );
    }
}
