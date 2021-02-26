<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;


class SolutionFactoryTutorial {

	private static $_instance = null;

	// Number of tutorial steps on each page
    public $tutorials_numbers = [
        'cart' => 4,
        'single' => 2,
        'catalog' => 5
    ];

	private function __construct() {
	}

	protected function __clone() {
	}

	/**
	 * @return SolutionFactoryTutorial
	 */
	static public function getInstance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	function init() {

		add_action( 'sf_add_theme_suboption', [ $this, 'add_settings_subpage' ] );

		add_action( 'wp_ajax_save_tutorial_status', [ $this, 'save_tutorial_status' ] );
		add_action( 'wp_ajax_nopriv_save_tutorial_status', [ $this, 'save_tutorial_status' ] );

        add_action( 'wp_ajax_delete_tutorial_status', [ $this, 'tutorial_show_by_click' ] );
        add_action( 'wp_ajax_nopriv_delete_tutorial_status', [ $this, 'tutorial_show_by_click' ] );

		//return $this;
	}

	/**
     * Tutorial theme oprions fields
     *
     * @return
     */
	function add_settings_subpage( $main_page ) {

		$tutorials_labels = array(
			'plural_name'   => 'Tutorials',
			'singular_name' => 'Tutorial',
		);

		Container::make( 'theme_options', 'Tutorials' )
         ->set_page_parent( $main_page ) // reference to a top level container
         ->add_tab( __( 'Settings' ), [] )
         ->add_tab( __( 'Single page tutorials' ), [

         	Field::make( 'separator', 'op_single_tutorial_separator_1', 'Tutorial 1 step' )->set_width( 100 ),
         	Field::make( 'text', 'op_single_tutorial_heading_1', __( 'Heading' ) )->set_width( 40 )
         		->set_help_text( 'Single tutorial step 1 heading' ),
         	Field::make( 'textarea', 'op_single_tutorial_text_1', __( 'Text' ) )->set_width( 60 )
         		->set_help_text( 'Single tutorial step 1 text' ),

         	Field::make( 'separator', 'op_single_tutorial_separator_2', 'Tutorial 2 step' )->set_width( 100 ),
         	Field::make( 'text', 'op_single_tutorial_heading_2', __( 'Heading' ) )->set_width( 40 )
         		->set_help_text( 'Single tutorial step 2 heading' ),
         	Field::make( 'textarea', 'op_single_tutorial_text_2', __( 'Text' ) )->set_width( 60 )
         		->set_help_text( 'Single tutorial step 2 text' ),

         ] )

         ->add_tab( __( 'Cart page tutorials' ), [

         	Field::make( 'separator', 'op_cart_tutorial_separator_1', 'Tutorial 1 step' )->set_width( 100 ),
         	Field::make( 'text', 'op_cart_tutorial_heading_1', __( 'Heading' ) )->set_width( 40 )
         		->set_help_text( 'Single tutorial step 1 heading' ),
         	Field::make( 'textarea', 'op_cart_tutorial_text_1', __( 'Text' ) )->set_width( 60 )
         		->set_help_text( 'Single tutorial step 1 text' ),

         	Field::make( 'separator', 'op_cart_tutorial_separator_2', 'Tutorial 2 step' )->set_width( 100 ),
         	Field::make( 'text', 'op_cart_tutorial_heading_2', __( 'Heading' ) )->set_width( 40 )
         		->set_help_text( 'Single tutorial step 2 heading' ),
         	Field::make( 'textarea', 'op_cart_tutorial_text_2', __( 'Text' ) )->set_width( 60 )
         		->set_help_text( 'Single tutorial step 2 text' ),

         	Field::make( 'separator', 'op_cart_tutorial_separator_3', 'Tutorial 3 step' )->set_width( 100 ),
         	Field::make( 'text', 'op_cart_tutorial_heading_3', __( 'Heading' ) )->set_width( 40 )
         		->set_help_text( 'Single tutorial step 3 heading' ),
         	Field::make( 'textarea', 'op_cart_tutorial_text_3', __( 'Text' ) )->set_width( 60 )
         		->set_help_text( 'Single tutorial step 3 text' ),

         	Field::make( 'separator', 'op_cart_tutorial_separator_4', 'Tutorial 4 step' )->set_width( 100 ),
         	Field::make( 'text', 'op_cart_tutorial_heading_4', __( 'Heading' ) )->set_width( 40 )
         		->set_help_text( 'Single tutorial step 4 heading' ),
         	Field::make( 'textarea', 'op_cart_tutorial_text_4', __( 'Text' ) )->set_width( 60 )
         		->set_help_text( 'Single tutorial step 4 text' ),

         ] )

         ->add_tab( __( 'Catalog page tutorials' ), [

         	Field::make( 'separator', 'op_catalog_tutorial_separator_1', 'Tutorial 1 step' )->set_width( 100 ),
         	Field::make( 'text', 'op_catalog_tutorial_heading_1', __( 'Heading' ) )->set_width( 40 )
         		->set_help_text( 'Single tutorial step 1 heading' ),
         	Field::make( 'textarea', 'op_catalog_tutorial_text_1', __( 'Text' ) )->set_width( 60 )
         		->set_help_text( 'Single tutorial step 1 text' ),

         	Field::make( 'separator', 'op_catalog_tutorial_separator_2', 'Tutorial 2 step' )->set_width( 100 ),
         	Field::make( 'text', 'op_catalog_tutorial_heading_2', __( 'Heading' ) )->set_width( 40 )
         		->set_help_text( 'Single tutorial step 2 heading' ),
         	Field::make( 'textarea', 'op_catalog_tutorial_text_2', __( 'Text' ) )->set_width( 60 )
         		->set_help_text( 'Single tutorial step 2 text' ),

         	Field::make( 'separator', 'op_catalog_tutorial_separator_3', 'Tutorial 3 step' )->set_width( 100 ),
         	Field::make( 'text', 'op_catalog_tutorial_heading_3', __( 'Heading' ) )->set_width( 40 )
         		->set_help_text( 'Single tutorial step 3 heading' ),
         	Field::make( 'textarea', 'op_catalog_tutorial_text_3', __( 'Text' ) )->set_width( 60 )
         		->set_help_text( 'Single tutorial step 3 text' ),

         	Field::make( 'separator', 'op_catalog_tutorial_separator_4', 'Tutorial 4 step' )->set_width( 100 ),
         	Field::make( 'text', 'op_catalog_tutorial_heading_4', __( 'Heading' ) )->set_width( 40 )
         		->set_help_text( 'Single tutorial step 4 heading' ),
         	Field::make( 'textarea', 'op_catalog_tutorial_text_4', __( 'Text' ) )->set_width( 60 )
         		->set_help_text( 'Single tutorial step 4 text' ),

         	Field::make( 'separator', 'op_catalog_tutorial_separator_5', 'Tutorial 5 step' )->set_width( 100 ),
         	Field::make( 'text', 'op_catalog_tutorial_heading_5', __( 'Heading' ) )->set_width( 40 )
         		->set_help_text( 'Single tutorial step 5 heading' ),
         	Field::make( 'textarea', 'op_catalog_tutorial_text_5', __( 'Text' ) )->set_width( 60 )
         		->set_help_text( 'Single tutorial step 5 text' ),

         	Field::make( 'separator', 'op_catalog_tutorial_separator_survey', 'Tutorial survey' )->set_width( 100 ),
         	Field::make( 'text', 'op_catalog_tutorial_heading_survey', __( 'Heading' ) )->set_width( 40 )
         		->set_help_text( 'Single tutorial survey heading' ),
         	Field::make( 'textarea', 'op_catalog_tutorial_text_survey', __( 'Text' ) )->set_width( 60 )
         		->set_help_text( 'Single tutorial survey text' ),

         ] );
	}

	/**
     * Build fields with tutorials texts
     *
     * @return html
     */

	public function tutorial_texts() {
		$texts_html = '';

		if ( is_page( 'cart' ) || is_cart() ) { // Tutorial for Cart page
            $names = [ 'main' => 'cart' ];
        } elseif ( is_product() && op_help()->shop->is_meal_product() ) { // Tutorial for single Product page
            $names = [ 'main' => 'single' ];
        } elseif ( op_help()->shop->is_meals_category() ) { // Tutorial for Catalog page
    		$names = [ 'main' => 'catalog',
    				   'additional' => [ 'survey' ] ];
        }

        // Create html fields
        for ( $i = 1; $i <= $this->tutorials_numbers[ $names[ 'main' ] ]; $i ++ ) {
            // Heading field
            $texts_html .= '<input type="hidden" class="' . $names[ 'main' ] . '-tutorial-heading-' . $i . '" value="' . carbon_get_theme_option( 'op_' . $names[ 'main' ] . '_tutorial_heading_' . $i ) . '">';

            // Text field
            $texts_html .= '<input type="hidden" class="' . $names[ 'main' ] . '-tutorial-text-' . $i . '" value="' . carbon_get_theme_option( 'op_' . $names[ 'main' ] . '_tutorial_text_' . $i ) . '">';
        }

        // Cretae additional fields
        if ( isset( $names[ 'additional' ] ) ) {
            foreach ( $names[ 'additional' ] as $name ) {
                // Heading field
                $texts_html .= '<input type="hidden" class="' . $names[ 'main' ] . '-tutorial-heading-' . $name . '" value="' . carbon_get_theme_option( 'op_' . $names[ 'main' ] . '_tutorial_heading_' . $name ) . '">';

                // Text field
                $texts_html .= '<input type="hidden" class="' . $names[ 'main' ] . '-tutorial-text-' . $name . '" value="' . carbon_get_theme_option( 'op_' . $names[ 'main' ] . '_tutorial_text_' . $name ) . '">';
            }
        }

        return $texts_html;
	}

    /**
     * Tutorial class for "body" element
     *
     * @return string
     */
    public function tutorial_body_class() {
        $class_name = '';
        $page_name = $this->tutorial_page_name();

        // Return tutorial class, if there are no any status yet (skipped/passed)
        if ( ! $this->get_tutorial_status( $page_name ) ) {

	        if ( is_page( 'cart' ) || is_cart() ) { // Tutorial for Cart page
	            $class_name = 'tutorial-cart';
	        } elseif ( is_product() && op_help()->shop->is_meal_product() ) { // Tutorial for single Product page
	            $class_name = 'tutorial-single';
	        } elseif ( op_help()->shop->is_meals_category() ) { // Tutorial for Catalog page
        		$class_name = 'tutorial-catalog';
	        }

    	}else { // Show additional tutorials

    		if ( ! $this->get_tutorial_status( $page_name, 'survey' ) ) {
	    		if ( op_help()->shop->is_meals_category() && op_help()->sf_user->check_survey_exist() && op_help()->sf_user->check_survey_default() ) { // Catalog tutorial for survey
	    			$class_name = 'tutorial-catalog-survey';
	    		}
    		}

    	}

        return $class_name;
    }

    /**
     * Show tutorial on button click
     *
     * @return
     */
    public function tutorial_show_by_click() {
        $tutorial_type = $_POST[ 'type' ];
        $tutorial_page = $_POST[ 'page_name' ];

        // Delete tutorial cookie
        op_help()->sf_user::op_delete_tutorial_cookie( $tutorial_page, $tutorial_type );

        // Delete tutorial user meta (if logged in)
        if ( is_user_logged_in() ) {
            op_help()->sf_user::op_delete_tutorial_meta( $tutorial_page, $tutorial_type );
        }
    }

    /**
     * Tutorial page name
     *
     * @return string
     */
    public function tutorial_page_name() {
        $page_name = '';

        if ( is_page( 'cart' ) || is_cart() ) { // Tutorial for Cart page
            $page_name = 'cart';
        } elseif ( is_product() && op_help()->shop->is_meal_product() ) { // Tutorial for single Product page
            $page_name = 'single';
        } elseif ( op_help()->shop->is_meals_category() ) { // Tutorial for Catalog page
            $page_name = 'catalog';
        }

        return $page_name;
    }

    /**
     * Save tutorials status if user skipped or passed it
     *
     * @return
     */
    public function save_tutorial_status() {
    	$status = $_POST[ 'state' ];
    	$tutorial_type = $_POST[ 'type' ];
        $tutorial_page = $_POST[ 'page_name' ];

    	// Save tutorial status to cookie
		op_help()->sf_user::op_set_tutorial_cookie( $status, $tutorial_page, $tutorial_type );

		// Save tutorial status to user meta (if logged in)
		if ( is_user_logged_in() ) {
			op_help()->sf_user::op_set_tutorial_meta( $status, $tutorial_page, $tutorial_type );
		}
    }

    /**
     * Get tutorials status
     *
     * @return bool - false if not skipped/passed
     */
    public function get_tutorial_status( $page_name, $tutorial_status = '' ) {
    	$status = false;

    	if ( is_user_logged_in() ) {
			$status = op_help()->sf_user::op_get_tutorial_meta( $page_name, $tutorial_status );
		} else {
			$status = op_help()->sf_user::op_get_tutorial_cookie( $page_name, $tutorial_status );
		}

		return $status;
    }

}