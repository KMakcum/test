<?php
/**
 * Import/Export meals, components, staples.
 *
 * @class   SFImport
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * SFImport class.
 */
class SFImport {
    private static $_instance = null;

    public static $ver = '1.0.1';

    /**
     * @var SFDataModify
     */
    public $data_modify;

    private function __construct() {
    }

    /**
     * @return SFImport
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
        $self = self::getInstance();
        $self->tableUp();
        $self->define_init();
        $self->import();

        SfSync\AdminPage::init();

        $this->data_modify = SFDataModify::getInstance();
        $this->data_modify->init();

//        add_action( 'init', [ $this, 'on_init' ] );
    }

    /**
     * Init defines.
     */
    public function define_init() {
        define( 'SF_IMPORT_PATH', str_replace( '\\', '/', dirname( __FILE__ ) ) );
    }

    /**
     * Import files.
     */
    public function import() {
        if ( ! class_exists( '\\SfSync\\AdminPage' ) ) {
            require "inc/class-admin-page.php";
            require "inc/class-update-product-table.php";
            require "inc/class-wp-middleware.php";
            require "inc/class-file-structure.php";
            require "inc/class-parse-file.php";
            require "inc/class-import-data.php";
            require "inc/class-export-csv.php";
            require "inc/class-data-modify.php";
        }
    }

    /**
     * Create cache table
     */
    public function tableUp() {
        global $wpdb;

        $table_name      = $wpdb->get_blog_prefix() . 'data_sync';
        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate}";

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id  bigint(20) unsigned NOT NULL auto_increment,
            type varchar(20),
            wp_id int unsigned,
            external_id int unsigned,
            portion_size int unsigned NOT NULL default 0,
            warehouse_location varchar(50),
            base_price int unsigned NOT NULL default 0,
            weight int unsigned NOT NULL default 0,
            weight_unit varchar(20),
            servings_size_weight int unsigned NOT NULL default 0,
            servings_per_container int unsigned NOT NULL default 0,
            facility_allergens varchar(255),
            kit_type varchar(45),
            keep_refrigerated tinyint NOT NULL default 0,
            reorder_frequency varchar(50),
            msrp_multiplier float NOT NULL default 0,
            display_multiplier tinyint NOT NULL default 0,
            PRIMARY KEY (id)
            ) {$charset_collate};";
        $wpdb->query( $sql );

        $wpdb->query( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sf_import` (
              `id` int NOT NULL auto_increment,
              `user_id` int NOT NULL,
              `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              `type` varchar(255) NOT NULL,
              `execution_time` varchar(255) NOT NULL,
              `status` varchar(255) NOT NULL,
              `return` longtext NOT NULL,
              PRIMARY KEY (id)
            ) {$charset_collate};"
        );
    }
}

