<?php
/**
 * Global Cache products and variations.
 *
 * @class   SFSortCache
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * SFGlobalCache class.
 */
class SFGlobalCache {

    private static $_instance = null;

    private $table_name;

    private static $version;

    private static $post_meta = [];

    private static $all_products = [];

    /**
     * @var mixed $badges cache
     */
    private static $badges;

    private static $name_main_meal_product = 'Main Meal Product';

    /**
     * SFGlobalCache constructor.
     */
    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->get_blog_prefix() . 'op_global_cache';

        self::$post_meta = [
            'op_post_title',
            '_thumbnail_id',
            'op_variation_image_gallery',
            'op_variation_nutrition_image',
            'op_calories',
            'op_fats',
            'op_carbohydrates',
            'op_proteins',
            'internalid',
            'op_preparing_time',
            'op_prepare_instructions',
            'op_microwave',
            'op_meal_ingredients',
            '_regular_price',
            '_price',
            '_sale_price',
            'op_store_type',
            'op_order_by',
            '_company_name',
            'op_ingredients',
            'op_allergens',
        ];

        self::$version = $this->get_current_version();
    }

    /**
     * @return SFGlobalCache
     */
    static public function getInstance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * init all hooks.
     */
    public function init() {
        $self = self::getInstance();
        $self->tableUp();

        add_action( 'admin_menu', [ $self, 'add_theme_menu_item' ] );
        add_action( 'woocommerce_after_product_object_save', [ $self, 'updateProductCache' ], 20, 2 );
        add_action( 'admin_enqueue_scripts', [ $self, 'add_cache_page_assets' ] );

        //add_action( 'plugins_finished_sync', [ $self, 'cron_renew_cache' ] );
        add_action( 'plugins_finished_sync_2', [ $self, 'cron_renew_cache' ] );
        add_action( 'wp_ajax_regenerate_cache', [ $self, 'cron_renew_cache' ] );

        add_action( 'sf_cron_renew_cache', [ $self, 'regenerate_cache' ] );

        add_action( 'edit_post_product', [ $self, 'edit_post' ], 10, 1 );

        add_action( 'init', [ $self, 'on_init' ] );
    }

    /**
     * On init.
     */
    public function on_init() {
        if ( isset( $_GET['gc'] ) ) {
            self::regenerate_cache();
            print_r( self::get_cached_product( 99295 ) );
//            self::update_product( 99295 );

//            var_export($this->get_product_by_netsuite_ids( '302030193022' ));
            die;
        }
    }

    /**
     * Create cron single event.
     */
    public function cron_renew_cache() {
        if ( ! wp_next_scheduled( 'sf_cron_renew_cache' ) ) {
            TB::m( '*create* cron `sf_cron_renew_cache`' );
            wp_schedule_single_event( time(), 'sf_cron_renew_cache' );
        }
    }

    /**
     * Event on edit single product. Update product in global cache.
     *
     * @param int $post_ID
     */
    public function edit_post( $post_ID ) {
        $this->update_cache_for_single( $post_ID );
    }

    /**
     * Generate global cache. Main method.
     *
     * @return int|void
     */
    function regenerate_cache() {
        TB::m( '*start* cron `sf_cron_renew_cache`' );
        TB::start( 'global_cache' );

        $old_version = $this->get_current_version();
        $v           = $this->new_version();
//        echo "$v \n";

        // delete current version
        self::delete_cache_by_version( $v );

        $count = $this->create_cache();

        self::delete_cache_by_version( $old_version );
        $this->setup_new_version();
        $this->create_global_cache_id();

        op_help()->sort_cache->create_cache_for_zero_user();

        TB::m( "*end* cron `sf_cron_renew_cache`\n Total cache *{$count['total']}* items\n Meal *{$count['meals']}*\n Products *{$count['single_product']}*\n", true, 'global_cache' );
    }

    /**
     * Return all cached data of product.
     *
     * @param int|array $product_id
     *
     * @return array
     */
    public function get( $product_id ) {
        if ( is_array( $product_id ) ) {
            $pr = [];
            foreach ( $product_id as $item ) {
                array_push( $pr, $this->get_cached_product( $item ) );
            }

            return $pr;
        }

        return $this->get_cached_product( $product_id );
    }

    /**
     * Return all cached data of product.
     *
     * @param int $product_id
     *
     * @return array
     */
    public function get_cached_product( $product_id ) {
        $product_data = $this->get_product_by_id_from_all( $product_id );

        $data     = $product_data['data'];
        $data_ext = $product_data['data_ext'];
        unset( $product_data['data'], $product_data['data_ext'], $product_data['id'] );

        return array_merge( $product_data, $data, $data_ext );
    }

    /**
     * Insert product cache in DB.
     *
     * @param int $product_id
     *
     * @return array
     */
    public function get_product_by_id_from_all( $product_id ) {
        $all = $this->getAll();

        foreach ( $all as $item ) {
            if ( $item['var_id'] == $product_id ) {
                return $item;
            }
        }

        return [];
    }

    /**
     * Return all cached data of product.
     *
     * @param int $product_id
     * @param string $field
     *
     * @return mixed
     */
    public function get_cached_product_field( $product_id, $field ) {
        $product = $this->get_cached_product( $product_id );

        if ( isset( $product[ $field ] ) ) {
            return $product[ $field ];
        }

        return '';
    }

    /**
     * Create global_cache.
     *
     * @return array
     */
    protected function create_cache() {
        $meals           = self::get_products( 'product_variation' );
        $single_products = self::get_products( 'product' );

        $products = array_merge( $single_products, $meals );
        foreach ( $products as $product ) {
            self::update_product( $product );
        }

        return [
            'single_product' => count( $single_products ),
            'meals'          => count( $meals ),
            'total'          => count( $products ),
        ];
    }

    /**
     * Generate combinations of array.
     *
     * @param array $a
     *
     * @return string
     */
    public function combinations( $a ) {
        return "{$a[0]}{$a[1]}{$a[2]},{$a[0]}{$a[2]}{$a[1]},{$a[1]}{$a[0]}{$a[2]},{$a[1]}{$a[2]}{$a[0]},{$a[2]}{$a[0]}{$a[1]},{$a[2]}{$a[1]}{$a[0]}";
    }

    /**
     * Create global_cache.
     *
     * @param int $product_id
     */
    public function update_cache_for_single( $product_id ) {
        $this->delete_item_from_cache( $product_id );

        $this->update_product( $product_id );

        $this->create_global_cache_id();
        TB::m( $product_id );
    }

    /**
     * Insert product cache in DB.
     *
     * @param int|array $product
     */
    protected function update_product( $product ) {
        global $wpdb;

        $data = [
            'components' => [],
            'zones'      => [],
            'diets'      => [],
            'badges'     => [],
        ];

        if ( is_int( $product ) ) {
            $product = self::get_product_by_id( $product )[0];
        }

        if ( ! $product['ID'] ) {
            return;
        }

        $combinations = '';

        $data['zones'] = get_post_meta( $product['ID'], 'op_zones' );
        $data['diets'] = get_post_meta( $product['ID'], 'op_diet' );

        $data['facility_allergens'] = carbon_get_term_meta( (int) $product['product_cat'], 'facility_allergens' );

        if ( $product['type'] == 'variation' ) {
            $data['components'] = [
                'pa_part-1' => $product['part_1'],
                'pa_part-2' => $product['part_2'],
                'pa_part-3' => $product['part_3'],
            ];

            $combinations = $this->combinations( [
                $product['netSuite_id_1'],
                $product['netSuite_id_2'],
                $product['netSuite_id_3'],
            ] );

            $data['badges'] = self::get_badges( $data['components'] );
        }

        if ( $product['_thumbnail_id'] ) {
            $product['_thumbnail_id_url'] = $this->fix_img_url( wp_get_attachment_image_url( $product['_thumbnail_id'], 'full' ) );
        }

        if ( $product['op_variation_nutrition_image'] ) {
            $product['op_variation_nutrition_image_url'] = $this->fix_img_url( wp_get_attachment_image_url( $product['op_variation_nutrition_image'], 'full' ) );
        }

        if ( $product['op_variation_image_gallery'] ) {
            $img = [];
            if ( $product['_thumbnail_id_url'] ) {
                array_push( $img, $product['_thumbnail_id_url'] );
            }

            $ids = explode( ',', $product['op_variation_image_gallery'] );
            foreach ( $ids as $item ) {
                array_push( $img, $this->fix_img_url( wp_get_attachment_image_url( $item, 'full' ) ) );
            }

            $product['op_variation_image_gallery_url'] = $img;
        }

        $wpdb->insert( $this->table_name, [
            'var_id'       => $product['ID'],
            'cat_id'       => (int) $product['product_cat'],
            'type'         => $product['type'],
            'data'         => serialize( $data ),
            'data_ext'     => serialize( $product ),
            'price'        => $product['_price'],
            'order_by'     => $product['op_order_by'],
            'version'      => self::get_version(),
            'combinations' => $combinations,
        ], [ '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s' ] );
    }

    /**
     * Return cached product by netSuite ids.
     *
     * @param string|array $ids
     *
     * @return stdClass
     */
    public function get_product_by_netsuite_ids( $ids ) {
        global $wpdb;

        if ( is_array( $ids ) ) {
            $ids = implode( '', $ids );
        }

        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}op_global_cache` WHERE combinations LIKE '%%%s%'", $ids ) );
    }

    /**
     * Fix bug with img url.
     *
     * @param string $img_url
     *
     * @return string
     */
    public function fix_img_url( $img_url ) {
        if ( strrpos( $img_url, 'http' ) ) {
            return substr( $img_url, strrpos( $img_url, 'http' ) );
        }

        return $img_url;
    }

    /**
     * Insert product cache in DB.
     *
     * @param int $product_id
     *
     * @return array
     */
    public function get_product_by_id( $product_id ) {
        global $wpdb;

        $type = $wpdb->get_var( $wpdb->prepare( "SELECT post_type FROM $wpdb->posts WHERE ID = %s", $product_id ) );

        return self::get_products( $type, $product_id );
    }

    /**
     * Return product badges.
     *
     * @param int $id_components
     *
     * @return array
     */
    public static function get_badges( $id_components ) {
        if ( ! empty( $id_components ) ) {
            if ( ! self::$badges ) {
                self::$badges = carbon_get_theme_option( 'op_variations_badges' );
            }

            $components = array_map( function ( $item ) {
                return carbon_get_term_meta( $item, 'op_variations_component_badges' );
            }, (array) $id_components );

            // Если хоть один из компоннетов spicy, то meal = spicy
            $spicy = array_filter( $components, function ( $item_arr ) {
                return ( in_array( 'spicy', $item_arr ) );
            } );

            if ( ! empty( $spicy ) ) {
                $components = array_map( function ( $item ) {
                    $key = array_search( 'spicy', $item );

                    if ( is_numeric( $key ) ) {
                        unset( $item[ $key ] );
                    }

                    return $item;
                }, $components );

                $spicy_data = 'spicy';
            }
            $components = array_filter( $components, function ( $item ) {
                return array_filter( $item );
            } );

            $same_badges = array_intersect( $components['pa_part-1'], $components['pa_part-2'],
                $components['pa_part-3'] );

            if ( isset( $spicy_data ) ) {
                array_push( $same_badges, $spicy_data );
            }

            // return badges with info
            $meal_badges = array_map( function ( $item ) use ( $same_badges ) {
                foreach ( $same_badges as $slug ) {
                    if ( $item['slug'] === $slug ) {
                        return [
                            'title'         => $item['title'],
                            'slug'          => $item['slug'],
                            'icon_contains' => $item['icon_contains']
                        ];
                    }
                }

                return [];
            }, self::$badges );

            return array_filter( $meal_badges, function ( $item ) {
                return array_filter( $item );
            } );
        }

        return [];
    }

    /**
     * Return new version of global cache.
     *
     * @return int
     */
    public function new_version() {
        $current_version = self::get_current_version();
        self::$version   = ( $current_version ) ? ( $current_version + 1 ) : 1;

        return self::$version;
    }

    /**
     * Setup new version of global cache.
     */
    public function setup_new_version() {
        update_option( 'global_cache_version', self::get_version() );
    }

    /**
     * Return version of global cache.
     *
     * @return int
     */
    public function get_version() {
        return ( self::$version ) ? self::$version : self::new_version();
    }

    /**
     * Return current version of global cache.
     *
     * @return int
     */
    public function get_current_version() {
        $current_version = get_option( 'global_cache_version' );

        return ( $current_version ) ? $current_version : 0;
    }

    /**
     * Generate hash of global_cache.
     */
    public function create_global_cache_id() {
        $value = md5( json_encode( $this->getAll( $is_hard = true ) ) );
        update_option( 'global_cache_id', $value, false );
    }

    /**
     * Delete cache by version.
     *
     * @param int $product_id
     */
    public function delete_item_from_cache( $product_id ) {
        global $wpdb;

        $wpdb->query( $wpdb->prepare( "DELETE FROM {$this->table_name} WHERE `var_id` = %s", $product_id ) );
    }

    /**
     * Delete cache by version.
     *
     * @param string $version
     */
    public function delete_cache_by_version( $version ) {
        global $wpdb;

        $wpdb->query( $wpdb->prepare( "DELETE FROM {$this->table_name} WHERE `version` = %s", $version ) );
    }

    /**
     * Return al global cache
     *
     * @param bool $is_hard
     *
     * @return array|null
     */
    public function getAll( $is_hard = false ) {
        global $wpdb;

        if ( ! empty( self::$all_products ) AND ! $is_hard ) {
            return self::$all_products;
        }

        $all_data_raw = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE version = %s", self::get_version() ), ARRAY_A );

        self::$all_products = array_map( function ( $item ) {
            return [
                'var_id'   => intval( $item['var_id'] ),
                'cat_id'   => intval( $item['cat_id'] ),
                'price'    => floatval( $item['price'] ),
                'type'     => $item['type'],
                'data'     => unserialize( $item['data'] ),
                'data_ext' => unserialize( $item['data_ext'] ),
                'order_by' => intval( $item['order_by'] )
            ];
        }, $all_data_raw );

        return self::$all_products;
    }

    /**
     * Return products by type.
     *
     * @param string $type
     * @param string|bool $product_cat
     * @param string|bool $tag
     * @param int|bool $product_id
     *
     * @return array
     */
    function getByType( $type, $product_cat = false, $tag = false, $product_id = false ) {
        global $wpdb;

        if ( ! empty( $type ) ) {
            $additional = '';
            if ( $product_cat ) {
                $product_cat = intval( $product_cat );
                $additional  .= "AND cat_id = '$product_cat'";
            }

            if ( $tag ) {
                $additional .= "AND data_ext LIKE '%\"product_tag\";s%'";
            }

            if ( $product_id ) {
                $product_id = (int) $product_id;
                $additional .= "AND var_id = $product_id";
            }

            $row_products = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE `type`= %s AND version = %s $additional", $type, self::get_version() ), ARRAY_A );

            if ( $tag ) {
                $tag          = intval( $tag );
                $row_products = $this->filter_by_tag( $tag, $row_products );
            }

            $products = array_map( function ( $item ) {
                return [
                    'var_id'   => intval( $item['var_id'] ),
                    'cat_id'   => intval( $item['cat_id'] ),
                    'price'    => floatval( $item['price'] ),
                    'type'     => $item['type'],
                    'data'     => unserialize( $item['data'] ),
                    'order_by' => $item['order_by'],
                ];
            }, $row_products );

            return $products;
        }

        return [];
    }

    /**
     * Filter products by tag id.
     *
     * @param string $tag_id
     * @param array $products
     *
     * @return array
     */
    public function filter_by_tag( $tag_id, $products ) {
        $m = [];
        foreach ( $products as $product ) {
            $data_ext     = unserialize( $product['data_ext'] );
            $product_tags = explode( ',', $data_ext['product_tag'] );
            if ( in_array( $tag_id, $product_tags ) ) {
                array_push( $m, $product );
            }
        }

        return $m;
    }

    /**
     * @param $var_id
     * @param $cat_id
     * @param $data
     * @param $type
     * @param $price
     * @param $order_by
     *
     * @return int|false
     */
    function set( $var_id, $cat_id, $data, $type, $price, $order_by ) {
        global $wpdb;

        return $wpdb->replace( $this->table_name, [
            'var_id'   => $var_id,
            'cat_id'   => $cat_id,
            'type'     => $type,
            'data'     => serialize( $data ),
            'price'    => $price,
            'order_by' => $order_by,
        ], [ '%d', '%d', '%s', '%s', '%s', '%d' ] );
    }

    /**
     * Return products.
     *
     * @param string $post_type
     * @param int|bool $product_id
     *
     * @return array
     */
    public function get_products( $post_type, $product_id = false ) {
        global $wpdb;

        $post_type = esc_sql( $post_type );

        $sql = "SELECT DISTINCT p.post_title, p.ID, p.post_content, ";

        foreach ( self::$post_meta as $postmeta ) {
            $p   = esc_sql( $postmeta );
            $sql .= "(SELECT `meta_value` FROM `{$wpdb->prefix}postmeta` WHERE `post_id` = p.`ID` AND `meta_key` = '$p' LIMIT 1) as '$p',";
        }
        $sql = substr( $sql, 0, - 1 );

        $join = '';

        if ( $post_type == 'product_variation' ) {
            $id_main_meal_product = (int) \SfSync\ImportData::getPostByName( self::$name_main_meal_product );

            $sql .= ", 'variation' as type,
                {$this->sql_pa_part( 1, 'term_id' )},
                {$this->sql_pa_part( 2, 'term_id' )},
                {$this->sql_pa_part( 3, 'term_id' )},
                {$this->sql_pa_part( 1, 'netSuite_id' )},
                {$this->sql_pa_part( 2, 'netSuite_id' )},
                {$this->sql_pa_part( 3, 'netSuite_id' )},
                (SELECT tr.term_taxonomy_id FROM `{$wpdb->prefix}term_relationships` as tr
                LEFT JOIN `{$wpdb->prefix}term_taxonomy` as tt ON tt.term_id = tr.term_taxonomy_id 
                WHERE tr.`object_id` = $id_main_meal_product AND (tt.taxonomy='product_cat') LIMIT 1) as 'product_cat'";

            $where = "AND `post_parent` = '$id_main_meal_product' 
                AND `ID` NOT IN (SELECT tr.object_id FROM `{$wpdb->prefix}term_relationships` as tr
                LEFT JOIN `{$wpdb->prefix}termmeta` as tm ON tm.term_id = tr.term_taxonomy_id
                WHERE tr.`object_id` = p.`ID` AND tm.meta_key = 'is_published' AND tm.meta_value = '0')";

        } else {
            $sql .= ", 'simple' as type,
            (SELECT GROUP_CONCAT(tr.term_taxonomy_id SEPARATOR ',') FROM `{$wpdb->prefix}term_relationships` as tr
                LEFT JOIN `{$wpdb->prefix}term_taxonomy` as tt ON tt.term_id = tr.term_taxonomy_id 
                WHERE tr.`object_id` = p.`ID` AND (tt.taxonomy='product_cat')) as 'product_cat',
            (SELECT GROUP_CONCAT(tr.term_taxonomy_id SEPARATOR ',') FROM `{$wpdb->prefix}term_relationships` as tr
                LEFT JOIN `{$wpdb->prefix}term_taxonomy` as tt ON tt.term_id = tr.term_taxonomy_id 
                WHERE tr.`object_id` = p.`ID` AND (tt.taxonomy='product_tag')) as 'product_tag'";

            $join = "LEFT JOIN `{$wpdb->prefix}term_relationships` as trg ON trg.`object_id` = p.`ID`
                     LEFT JOIN `{$wpdb->prefix}terms` as tg ON tg.term_id = trg.term_taxonomy_id";

            $where = "AND tg.name='simple' AND p.ID NOT IN (SELECT tr.object_id FROM `{$wpdb->prefix}term_relationships` as tr
                WHERE tr.`object_id` = p.`ID` AND (tr.term_taxonomy_id = '225'))";
        }

        $sql .= "
        FROM `{$wpdb->prefix}posts` as p 
        $join
        WHERE `post_type` = '$post_type' $where AND `post_status` = 'publish'";

        if ( $product_id ) {
            $product_id = (int) $product_id;

            $sql .= " AND p.ID = '$product_id'";
        }

//        print_r( $wpdb->prepare( $sql ) );
//        die;

        return $wpdb->get_results( $wpdb->prepare( $sql ), ARRAY_A );
    }

    /**
     * Return SQL query with pa_part.
     *
     * @param int $id
     * @param string $type
     *
     * @return string
     */
    public function sql_pa_part( $id, $type = 'term_id' ) {
        global $wpdb;

        $id = (int) $id;

        $join  = '';
        $where = '';

        if ( 'term_id' == $type ) {
            $select      = 'tr.term_taxonomy_id';
            $where_title = 'part';

        } elseif ( 'netSuite_id' == $type ) {
            $select      = 'tm.meta_value';
            $join        = "LEFT JOIN `{$wpdb->termmeta}` as tm ON tm.term_id = tr.term_taxonomy_id";
            $where       = "AND tm.meta_key = '_op_variations_component_sku'";
            $where_title = 'netSuite_id';

        } else {
            return '';
        }

        return "(SELECT $select FROM `{$wpdb->prefix}term_relationships` as tr
                LEFT JOIN `{$wpdb->prefix}term_taxonomy` as tt ON tt.term_id = tr.term_taxonomy_id 
                $join
                WHERE tr.`object_id` = p.`ID` AND (tt.taxonomy='pa_part-{$id}') $where) as '{$where_title}_{$id}'";
    }

    /**
     * Create cache table
     */
    function tableUp() {
        global $wpdb;
        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate}";

        $created_table = $wpdb->query( "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            `id` bigint(20) unsigned NOT NULL auto_increment,
            `var_id` bigint(20) unsigned NOT NULL,
            `cat_id` varchar(64) NOT NULL default '0',
            `price` varchar(64) NOT NULL default '0',
            `type` varchar(64) NOT NULL default 'simple',
            `data` longtext NOT NULL default '',
    		`order_by` int(11) default '0',
    		`data_ext` longtext NOT NULL DEFAULT '',
    		`version` varchar(64) NOT NULL DEFAULT '',
    		`combinations` varchar(124) NOT NULL DEFAULT '',
            PRIMARY KEY (id)
        ) {$charset_collate};" );
    }

    public function add_cache_page_assets() {
        wp_enqueue_style( 'admin-cache-page-css', get_template_directory_uri() . '/inc/variations/assets/clear-cache.css' );
        wp_enqueue_script( 'admin-cache-page-js', get_template_directory_uri() . '/inc/variations/assets/clear-cache.js', [ 'jquery' ] );
        wp_localize_script( 'admin-cache-page-js', 'settingsCache',
            [
                'ajax_url'   => admin_url( 'admin-ajax.php' ),
                'ajax_nonce' => wp_create_nonce( 'life-chef-admin' )
            ]
        );
    }

    public function theme_settings_page() {
        ?>
        <body class="text-center">
        <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
            <main role="main" class="inner cover">
                <div class="container">
                    <div class="row">
                        <div class="col-12 justify-content-center d-flex">
                            <h2><?php echo __( 'Regenerate Cache' ) ?></h2>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-6 d-flex">
                            <button class="button button-primary button-clear-js" type="button">
                                <?php echo __( 'Regenerate Global Products Cache' ); ?>
                            </button>

                            <button class="button button-primary button-update-js" type="button">
                                <?php echo __( 'Update Zip codes' ); ?>
                            </button>
                        </div>
                        <div class="cache-result">
                            <div class="lds-spinner lds-spinner--hide">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>

                            <div class="response">

                            </div>
                        </div>
                    </div>
            </main>
        </div>
        </body>
        <?php
    }

    public function add_theme_menu_item() {
        add_menu_page(
            "Cache Panel",
            "Cache Panel",
            "manage_options",
            "cache-panel",
            [ $this, 'theme_settings_page' ],
            null,
            99
        );
    }
}