<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Subcategories {
	private static $_instance = null;

//	public static $subcategories = [
//		'Drinks',
//		'Water',
//		'Juices',
//		'Dressings & Condiments',
//		'Spread',
//		'Deli',
//		'Milk And Dairy',
//		'Non-Dairy',
//		'Cheese',
//		'Eggs',
//		'Yogurt',
//		'Oil',
//		'Pancake',
//		'Pasta And Beans',
//		'Oatmeal',
//		'Snacks',
//		'Tortilla',
//		'Tea And Coffee',
//		'Misc',
//		'Mac & Cheese',
//		'Salt',
//		'Pepper',
//		'Spices',
//		'Honey'
//	];

	static public function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function init() {

//		add_action( 'init', [ $this, 'createSubcategories' ] );
//		add_action( 'init', [ $this, 'getAllSubcategories' ], 25 );
		add_action( 'carbon_fields_register_fields', [ $this, 'addFields' ] );

	}

//	public function createSubcategories() {
//
//		foreach ( self::$subcategories as $tag ) {
//			if ( ! term_exists( $tag, 'product_tag' ) ) {
//				wp_insert_term(
//					$tag,
//					'product_tag',
//				);
//			}
//		}
//
//	}

	public function getAllSubcategories() {

		$products       = op_help()->global_cache->getAll();
		$staples_cat_id = 27;

		$staples = array_filter( $products, function ( $item ) use ( $staples_cat_id ) {
			return ( $item['cat_id'] == $staples_cat_id );
		} );

		if ( ! empty( $staples ) ) {
			$available_items = op_help()->variations->filtered_products( $staples, false );

			if ( ! empty( $available_items ) ) {
				$filtered_ids = array_column( $available_items, 'var_id' );

				$terms_args = [
					'taxonomy'   => 'product_tag',
					'object_ids' => $filtered_ids,
					'hide_empty' => false,
				];

				$terms = get_terms( $terms_args );

				return array_map( function ( $item ) {
					return [
						'name'     => $item->name,
						'slug'     => $item->slug,
						'img_url'  => carbon_get_term_meta( $item->term_id, 'op_subcategory_image' ),
//						'products' => $this->getItems( $item )
					];
				}, $terms );
			}
		}

		return false;

	}

	public function getItems( $item ) {

		$products_ids_from_db = $this->getSubcategoriesProducts( $item );

		if ( ! empty( $products_ids_from_db ) ) {
			$products_from_cache = op_help()->global_cache->get( $products_ids_from_db );
			$shown_products      = op_help()->variations->zipCheck( $products_from_cache );

			return array_column( $shown_products, 'var_id' );
		}

		return [];

	}


	public function getSubcategoriesProducts( $term ) {
		return get_posts( array(
			'post_type'   => 'product',
			'numberposts' => - 1,
			'post_status' => 'publish',
			'fields'      => 'ids',
			'tax_query'   => array(
				array(
					'taxonomy' => 'product_tag',
					'field'    => 'slug',
					'terms'    => $term->slug,
					'operator' => 'IN',
				)
			),
		) );
	}

	public function addFields() {
		Container::make( 'term_meta', __( 'Additional Fields' ) )
		         ->where( 'term_taxonomy', 'IN', [ 'product_tag' ] )
		         ->add_fields( array(
			         Field::make( 'image', 'op_subcategory_image', 'Tag Image' )
			              ->set_type( [ 'image' ] )
			              ->set_value_type( 'url' )
		         ) );
	}
}