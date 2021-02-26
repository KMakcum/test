<?php
/**
 * Class for one-time data manipulation.
 *
 * @class   SFDataModify
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * SFDataModify class.
 */
class SFDataModify {
    private static $_instance = null;

    /**
     * SFDataModify constructor.
     */
    private function __construct() {
    }

    /**
     * @return SFDataModify
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
        add_action( 'init', [ $this, 'on_init' ] );
    }

    /**
     * On init.
     */
    public function on_init() {
        if ( isset( $_GET['regenerate_nsid'] ) AND $_GET['regenerate_nsid'] == 'start' ) {
            $this->change_old_nsid_to_new_nsid();
        }
    }

    protected function change_old_nsid_to_new_nsid() {
        global $wpdb;

        $r = [
            '3006' => '12016',
            '3402' => '12017',
            '3002' => '12011',
            '3200' => '12008',
            '3024' => '12040',
            '3001' => '12010',
            '3005' => '12015',
            '3016' => '12031',
            '3021' => '12037',
            '3102' => '12023',
            '3022' => '12038',
            '3106' => '12032',
            '3011' => '12022',
            '3026' => '12042',
            '3017' => '12033',
            '3000' => '12009',
            '3003' => '12013',
            '3013' => '12027',
            '3014' => '12028',
            '3100' => '12012',
            '3015' => '12030',
            '3010' => '12021',
            '3104' => '12026',
            '3018' => '12034',
            '3012' => '12024',
            '3019' => '12035',
            '3020' => '12036',
            '3008' => '12018',
            '3025' => '12041',
            '3009' => '12020',
            '3101' => '12019',
            '3105' => '12029',
            '3004' => '12014',
            '3023' => '12039',
            '3103' => '12025',
        ];

        $result = 0;

        foreach ( $r as $old_id => $new_id ) {
            $result += $wpdb->update(
                $wpdb->termmeta,
                [
                    'meta_value' => $new_id
                ],
                [
                    'meta_key'   => '_op_variations_component_sku',
                    'meta_value' => $old_id
                ]
            );
        }

        echo $result;

        die;
    }
}