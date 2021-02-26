<?php
/**
 * Meal customize block
 *
 * @class   SFCustomize
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * SFGlobalCache class.
 */
class SFCustomize {

    private static $_instance = null;

    private function __construct() {
    }

    protected function __clone() {
    }

    /**
     * @return SFCustomize
     */
    static public function getInstance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Init all hooks.
     */
    public function init() {
        add_action( 'wp_ajax_nopriv_get_variation_link', [ $this, 'getVariationLink' ] );
        add_action( 'wp_ajax_get_variation_link', [ $this, 'getVariationLink' ] );
        add_action( 'wp_ajax_toggle_change', [ $this, 'regenerateModal' ] );

        add_action( 'wp', [ $this, 'on_init' ], 100 );
    }

    public function on_init() {
        global $variation;

        if ( $variation !== null ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'add_meal_customize_assets' ] );
            add_filter( 'script_loader_tag', [ $this, 'add_type_attribute' ], 10, 3 );
        }
    }

    /**
     * Enqueue scripts
     */
    public function add_meal_customize_assets() {
        wp_enqueue_script( 'meal-customizer-js', get_template_directory_uri() . '/assets/js/customize/customize.js', [ 'jquery' ] );
        wp_enqueue_script( 'meal-customizer-template-js', get_template_directory_uri() . '/assets/js/customize/customize-modal-template.js', [ 'jquery' ] );
        wp_localize_script( 'meal-customizer-js', 'settingsCustomizer',
            [
                'ajax_url'       => admin_url( 'admin-ajax.php' ),
                'ajax_nonce'     => wp_create_nonce( 'life-chef-customizer' ),
                'variation_info' => $this->getCurrentVariationInfo(),
            ]
        );
    }

    /**
     * Change template script tag type
     */
    public function add_type_attribute( $tag, $handle, $src ) {
        if ( 'meal-customizer-template-js' !== $handle && 'meal-customizer-js' !== $handle ) {
            return $tag;
        }
        $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';

        return $tag;
    }

    /**
     * Return script with customize components data in dom.
     */
    public function getCustomizeComponentsJson() {
        echo '<script type="application/json" id="customize-meal-json">' .
                    json_encode( op_help()->customize_cache->get_customize_json() )
            . '</script>' .
            '<script id="survey-exists">
                var surveyExists = ' . intval( get_user_meta( get_current_user_id(), 'survey_default', true ) ) .
            '</script>';
    }

    /**
     * Get current variation information
     */
    public function getCurrentVariationInfo() {
        $variation            = op_help()->variations->rules->getCurrentVariation();
        if ( is_array( $variation ) AND $variation['not_found'] ) {
            return [];
        }
        $variation_link       = op_help()->variations->rules->variationLink( $variation->get_id() );
        $variation_attributes = op_help()->variations->get_variation_attributes( $variation->get_id() );

        return [
            'var_id'         => $variation->get_id(),
            'var_link'       => $variation_link,
            'var_attributes' => $variation_attributes
        ];
    }

    /**
     * Get variation link by netsuit id's
     */
    public function getVariationLink() {
        $request = (object)$_POST;

        // new netsuit ids
        $new_checked_id = $request->newCheckedComponentId;
        $old_checked_id = $request->oldCheckedComponentId;

        //old netsuit ids
        $variation_start_components = [];

        foreach ( explode( ',', $request->allComponents ) as $component ) {
            $variation_start_components[] = get_term_meta( $component, '_op_variations_component_sku' )[0];
        }

        $index_for_replace = array_search( $old_checked_id, $variation_start_components );
        $variation_start_components[ $index_for_replace ] = $new_checked_id;

        // find meal with this components by id $variation_start_components
        $new_variation = op_help()->global_cache->get_product_by_netsuite_ids( $variation_start_components );

        wp_send_json_success( [
            'link' => op_help()->variations->rules->variationLink( $new_variation->var_id ),
            'price' => number_format( $new_variation->price, 2, '.', '' )
        ] );
    }

    public function regenerateModal() {
        if ( isset( $_POST['survey_status'] ) && ! empty( $_POST['survey_status'] ) ) {
            if ( $_POST['survey_status'] == 'true' ) {
                $recommended = 1;
                update_user_meta( get_current_user_id(), 'survey_default', 1 );
            } else {
                $recommended = 0;
                update_user_meta( get_current_user_id(), 'survey_default', 0 );
            }

            wp_send_json_success( json_encode( op_help()->customize_cache->get_customize_json( $recommended ) ) );
        }
    }

}
