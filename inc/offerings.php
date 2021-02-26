<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class OfferingsPageClass {
	private static $_instance = null;

	static public function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function getURLSegments() {
		return explode( "/", parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
	}

	private function getURLSegment( $n ) {
		$segs = $this->getURLSegments();

		return count( $segs ) > 0 && count( $segs ) >= ( $n - 1 ) ? $segs[ $n ] : '';
	}

	public function init() {
		add_action( 'wp_ajax_offerings_form_handler', [ $this, 'offerings_form_handler' ] );
		add_action( 'wp_ajax_nopriv_offerings_form_handler', [ $this, 'offerings_form_handler' ] );
		add_action( 'carbon_fields_register_fields', [ $this, 'set_offerings_fields' ] );
		if ( ( $this->getURLSegment( 1 ) === 'offerings' ) ) {
			if ( ! post_exists( 'Offerings' ) ) {
				$offerings_page = [
					'post_title'  => 'Offerings',
					'post_name'   => 'offerings',
					'post_status' => 'publish',
					'post_author' => 1,
					'post_type'   => 'page'
				];
				wp_insert_post( $offerings_page );
			}

			add_action( 'wp_enqueue_scripts', function () {
				wp_enqueue_script( 'nice-number-js',
					get_stylesheet_directory_uri() . '/assets/js/node_modules/jquery.nice-number/dist/jquery.nice-number.min.js',
					[ 'jquery' ] );
				wp_enqueue_script( 'ui', 'https://code.jquery.com/ui/1.10.3/jquery-ui.js', [ 'jquery' ] );
				wp_enqueue_script( 'meal-plan-bottom-nav-offerings',
					get_stylesheet_directory_uri() . '/assets/js/meal-plan-bottom-nav-offerings.js',
					[ 'jquery', 'nice-number-js' ] );
				wp_localize_script( 'meal-plan-bottom-nav-offerings', 'ajaxSettingsMealPlan', [
					'ajax_url'       => admin_url( 'admin-ajax.php' ),
					'site_url'       => get_site_url(),
					'stylesheet_dir' => get_stylesheet_directory_uri()
				] );
				wp_enqueue_script( 'offerings-form-ajax',
					get_stylesheet_directory_uri() . '/assets/js/offerings-form-ajax.js', [ 'jquery' ] );
				wp_localize_script( 'offerings-form-ajax', 'ajaxSettingsOfferingsForm', [
					'ajax_url'   => admin_url( 'admin-ajax.php' ),
					'ajax_nonce' => wp_create_nonce( 'life-chef-action' )
				] );
			} );
		} elseif ( ( $this->getURLSegment( 1 ) === 'groceries' ) ) {
			if ( ! post_exists( 'Groceries' ) ) {
				$groceries = [
					'post_title'  => 'Groceries',
					'post_name'   => 'groceries',
					'post_status' => 'publish',
					'post_author' => 1,
					'post_type'   => 'page'
				];
				wp_insert_post( $groceries );
			}
		}
	}

	public function offerings_form_handler() {
		$request       = (object) $_POST;
		$request->data = json_decode( stripslashes( $request->data ), true );
		if ( ! $this->checkNonce( $request->nonce, 'life-chef-action' ) ) {
			self::returnError( 'wrong nonce' );
		}
		if ( ! $request->data['email'] ) {
			self::returnError( 'empty email field' );
		}
		$user_zip_code = [];
		if ( $request->zip_code ) {
			$user_zip_code = [
				'id'    => '1500000014962',
				'value' => preg_replace( '/\s+/', '', $request->zip_code )
			];
		}
		$zendesk_api = new ZenDeskIntegration();
		$zendesk_api->init();
		$ticket_data =
			[
				'requester'     =>
					[
						'name'  => $request->data['email'],
						'email' => $request->data['email']
					],
				'subject'       => 'User ' . $request->data['email'] . ' ask to notifications',
				'comment'       =>
					[
						'body' => 'Please notify me when some meals would be available',
					],
				'type'          => 'question',
				'priority'      => 'normal',
				'status'        => 'new',
				'custom_fields' =>
					[
						[
							'id'    => '360051983373',
							'value' => 'notify_me'
						],
						$user_zip_code
					],
			];
		self::returnData( $zendesk_api->client->tickets()->create( $ticket_data ) );
	}

	public function get_offerings_products( $meals_count = 20 ) {

		$offerings = [];
		$vitamins = op_help()->shop->get_offerings_vitamins( $meals_count );

		foreach ( $vitamins as $vitamin ) {
			$image = $vitamin['_thumbnail_id_url'];
			$is_sale = ! empty( $vitamin['_sale_price'] );
			$regular_price = $vitamin['_regular_price'];
			$sale_price = $vitamin['_sale_price'];

			if ( $is_sale ) {
				$price = [
					'current' => $sale_price,
					'old'     => $regular_price,
				];
			} else {
				$price = [
					'current' => $regular_price
				];
			}

			$offerings['Vitamins'][] = [
				"id"    => $vitamin['var_id'],
				"link"  => get_the_permalink( $vitamin['var_id'] ),
				"title" => get_the_title( $vitamin['var_id'] ),
				"price" => $price,
				"image" => $image,
			];
		}

		$meals = op_help()->shop->get_recommended_meals( $meals_count );
		$survey_default = op_help()->sf_user->check_survey_default();

		foreach ( $meals as $product ) {
			$product_id = $product['var_id'];
			$image = $product['_thumbnail_id_url'];
			$is_sale = ! empty( $product['_sale_price'] );
			$regular_price = $product['_regular_price'];
			$sale_price = $product['_sale_price'];

			if ( $is_sale ) {
				$price = [
					'current' => $regular_price,
					'old'     => $sale_price,
				];
			} else {
				$price = [
					'current' => $regular_price
				];
			}

			$offerings['Meals'][] = [
				"id"         => $product_id,
				"link"       => $survey_default ? add_query_arg( 'use_survey', 'true', get_the_permalink( $product_id ) ) : get_the_permalink( $product_id ),
				"title"      => $product['op_post_title'],
				"price"      => $price,
				"image"      => $image,
				"chef_score" => $product['chef_score'],
				"badges"     => $product['badges']
			];
		}

		return $offerings;
	}

	public function set_offerings_fields() {
		Container::make( 'post_meta', 'Offerings page settings' )
		         ->show_on_page( 'offerings' )
		         ->add_fields( [
			         Field::make( 'text', 'meals_catalog_title', __( 'Meals catalog title' ) ),
			         Field::make( 'text', 'meals_catalog_text', __( 'Meals catalog text' ) ),
			         Field::make( 'text', 'meals_catalog_link', __( 'Meals catalog button text' ) ),
			         Field::make( 'text', 'meals_catalog_url', __( 'Meals catalog url' ) ),

			         Field::make( 'text', 'vitamins_n_supplements_title', __( 'Vitamins/supplements title' ) ),
			         Field::make( 'text', 'vitamins_n_supplements_text', __( 'Vitamins/supplements text' ) ),
			         Field::make( 'text', 'vitamins_n_supplements_link', __( 'Vitamins/supplements button text' ) ),
			         Field::make( 'text', 'vitamins_n_supplements_url', __( 'Vitamins/supplements url' ) ),

			         Field::make( 'text', 'meals_carousel_section_title', __( 'Meals carousel section title' ) ),
			         Field::make( 'text', 'meals_carousel_section_count',
				         __( 'Meals carousel section count products' ) )
			              ->set_attribute( 'type', 'number' ),
			         Field::make( 'text', 'meals_carousel_section_link', __( 'Meals carousel section link' ) ),
			         Field::make( 'text', 'vitamins_supplements_carousel_section_title',
				         __( 'Vitamins/supplements section title' ) ),
			         Field::make( 'text', 'vitamins_supplements_carousel_section_link',
				         __( 'Vitamins/supplements section link' ) ),
		         ] );
	}

	public function get_offerings_fields( $id ) {
		return [
			'meals_catalog_title'                         => carbon_get_post_meta( $id, 'meals_catalog_title' ) ?
				carbon_get_post_meta( $id, 'meals_catalog_title' ) :
				__( 'Chef Crafted Healthy Meals Delivered to you' ),
			'meals_catalog_text'                          => carbon_get_post_meta( $id, 'meals_catalog_text' ) ?
				carbon_get_post_meta( $id, 'meals_catalog_text' ) :
				__( 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.' ),
			'meals_catalog_link'                          => carbon_get_post_meta( $id, 'meals_catalog_link' ) ?
				carbon_get_post_meta( $id, 'meals_catalog_link' ) :
				__( 'Pick your meal plan' ),
			'meals_catalog_url'                           => carbon_get_post_meta( $id, 'meals_catalog_url' ) ?
				carbon_get_post_meta( $id, 'meals_catalog_url' ) :
				__( '/product-category/meals' ),
			'vitamins_n_supplements_title'                => carbon_get_post_meta( $id,
				'vitamins_n_supplements_title' ) ?
				carbon_get_post_meta( $id, 'vitamins_n_supplements_title' ) :
				__( 'Nutritionist Suggested Vitamins and Supplements' ),
			'vitamins_n_supplements_text'                 => carbon_get_post_meta( $id,
				'vitamins_n_supplements_text' ) ?
				carbon_get_post_meta( $id, 'vitamins_n_supplements_text' ) :
				__( 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.' ),
			'vitamins_n_supplements_link'                 => carbon_get_post_meta( $id,
				'vitamins_n_supplements_link' ) ?
				carbon_get_post_meta( $id, 'vitamins_n_supplements_link' ) :
				__( 'Shop vitamins' ),
			'vitamins_n_supplements_url'                  => carbon_get_post_meta( $id, 'vitamins_n_supplements_url' ) ?
				carbon_get_post_meta( $id, 'vitamins_n_supplements_url' ) :
				__( '/product-category/vitamins' ),
			'meals_carousel_section_title'                => carbon_get_post_meta( $id,
				'meals_carousel_section_title' ) ?
				carbon_get_post_meta( $id, 'meals_carousel_section_title' ) :
				__( 'Meals' ),
			'meals_carousel_section_count'                => carbon_get_post_meta( $id,
				'meals_carousel_section_count' ) ?
				carbon_get_post_meta( $id, 'meals_carousel_section_count' ) :
				5,
			'meals_carousel_section_link'                 => carbon_get_post_meta( $id,
				'meals_carousel_section_link' ) ?
				carbon_get_post_meta( $id, 'meals_carousel_section_link' ) :
				__( '' ),
			'vitamins_supplements_carousel_section_title' => carbon_get_post_meta( $id,
				'vitamins_supplements_carousel_section_title' ) ?
				carbon_get_post_meta( $id, 'vitamins_supplements_carousel_section_title' ) :
				__( 'Vitamins & Supplements' ),
			'vitamins_supplements_carousel_section_link'  => carbon_get_post_meta( $id,
				'vitamins_supplements_carousel_section_link' ) ?
				carbon_get_post_meta( $id, 'vitamins_supplements_carousel_section_link' ) :
				__( '' )
		];
	}

	private function checkNonce( $nonce, $action ) {
		return wp_verify_nonce( $nonce, $action );
	}

	private static function returnData( $data = [] ) {
		header( 'Content-Type:application/json' );
		echo json_encode( [ 'status' => true, 'data' => $data ] );
		wp_die();
	}

	private static function returnError( $message ) {
		header( 'Content-Type:application/json' );
		wp_send_json_error( $message, 400 );
		wp_die();
	}
}
