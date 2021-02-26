<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class SFMainPage {
	private static $_instance = null;

	static public function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function init() {
		add_action( 'carbon_fields_register_fields', [ $this, 'add_page_fields' ] );
	}

	public function add_page_fields() {
		// _wp_page_template = template.php
		Container::make( 'post_meta', 'Fron Page Settings' )
		         ->where( 'post_template', '=', 'front-page.php' )
		         ->add_tab( __( 'Slider' ), array(
			         Field::make( 'complex', 'op_slider', __( 'Slider' ) )
			              ->set_layout( 'tabbed-vertical' )
			              ->add_fields( 'slides', array(
				              Field::make( 'text', 'title', __( 'Slider Title' ) ),
				              Field::make( 'textarea', 'descr', __( 'Description' ) ),
				              Field::make( 'text', 'link', __( 'Link "Discover meals"' ) )
				                    ->set_default_value( '/product-category/meals/' ),
				              Field::make( 'text', 'link_redirect', __( 'Link redirect' ) )
				                   ->set_default_value( '/product-category/meals/' ),
				              Field::make( 'image', 'image', __( 'Slide Image' ) )
				                   ->set_value_type( 'url' )
			              ) )
			              ->set_header_template( '
											<% if (title) { %>
												<%- title %>
											<% } %>
										' )
		         ) )
		         ->add_tab( __( 'How it works' ), array(
			         Field::make( 'text', 'op_hiw_title', __( 'Block Title' ) ),
			         Field::make( 'complex', 'op_schema_work', __( 'Items' ) )
			              ->set_layout( 'tabbed-vertical' )
			              ->add_fields( 'work_item', array(
				              Field::make( 'image', 'image', __( 'Icon' ) ),
				              Field::make( 'text', 'title', __( 'Title' ) ),
				              Field::make( 'text', 'descr', __( 'Description' ) ),
			              ) )
			              ->set_header_template( '
											<% if (title) { %>
												<%- title %>
											<% } %>
										' ),
			         Field::make( 'text', 'op_hiw_redirect', __( 'Link redirect' ) )
			              ->set_default_value( '/how-it-works/' )
		         ) )
		         ->add_tab( __( 'About Us' ), array(
			         Field::make( 'image', 'op_about_image', __( 'Image Left' ) )
			              ->set_width( 8 )
			              ->set_value_type( 'url' ),
			         Field::make( 'text', 'op_about_title', __( 'Block Title' ) )->set_width( 40 ),
			         Field::make( 'rich_text', 'op_about_descr', __( 'Description' ) )->set_width( 40 ),
			         Field::make( 'separator', 'op_about_sep', '' )->set_width( 100 ),
			         Field::make( 'rich_text', 'op_about_descr_bottom', __( 'Description' ) )->set_width( 40 ),
			         Field::make( 'text', 'op_about_txt_link', __( 'Text link' ) )->set_width( 20 ),
			         Field::make( 'text', 'op_about_descr_link', __( 'Link' ) )->set_width( 20 ),
			         Field::make( 'image', 'op_about_image_bottom', __( 'Image Right' ) )
			              ->set_width( 8 )
			              ->set_value_type( 'url' ),
		         ) )
		         ->add_tab( __( 'On the menu' ), array(
			         Field::make( 'text', 'op_product_section_title', __( 'Title' ) ),
			         Field::make( 'textarea', 'op_product_section_descr', __( 'Description' ) ),
			         Field::make( 'association', 'op_product_section_association',
				         __( 'Association with Product & Variations' ) )
			              ->set_types( array(
				              	array(
					              	'type'      => 'post',
					              	'post_type' => 'product',
				              	),
				              	array(
					              	'type'      => 'post',
					              	'post_type' => 'product_variation',
				              	),
			              ) ),

		         ) )
		         ->add_tab( __( 'Reviews' ), array(
			         Field::make( 'text', 'op_reviews_title', __( 'Title' ) ),
			         Field::make( 'complex', 'op_reviews', __( 'Reviews' ) )
			              ->set_layout( 'tabbed-vertical' )
			              ->add_fields( 'review', array(
				              Field::make( 'rich_text', 'review', __( 'Content review' ) ),
				              Field::make( 'text', 'name', __( 'Name' ) ),
				              Field::make( 'text', 'position', __( 'Position' ) ),
			              ) )
			              ->set_header_template( '
											<% if (name) { %>
												<%- name %>
												<% if (position) { %>
													<%- position %>
												<% } %>
											<% } %>
										' )
		         ) )
		         ->add_tab( __( 'Call to action form' ), array(
			         Field::make( 'checkbox', 'op_cta_show_content', __( 'Show Block' ) )
			              ->set_option_value( 'yes' ),
			         Field::make( 'image', 'op_cta_bg', __( 'Background image' ) )
			              ->set_width( 100 )
			              ->set_value_type( 'url' ),
			         Field::make( 'text', 'op_cta_title', __( 'Title' ) ),
			         Field::make( 'text', 'op_cta_redirect', __( 'Link redirect (button)' ) )
			              ->set_default_value( '/offerings/#sf_open_survey' ),
			         Field::make( 'text', 'op_cta_link', __( 'Link to `Check your options`' ) ),
			         Field::make( 'checkbox', 'op_cta_link_modal', __( 'Show zip modal (for link)' ) )
			         	->set_option_value( 'yes' ),
		         ) );
	}

	public function get_slider( $id ) {
		return carbon_get_post_meta( $id, 'op_slider' );
	}

	public function get_block_hiw( $id ) {
		$title    = carbon_get_post_meta( $id, 'op_hiw_title' );
		$items    = carbon_get_post_meta( $id, 'op_schema_work' );
		$redirect = carbon_get_post_meta( $id, 'op_hiw_redirect' );

		return [
			$title,
			$items,
			$redirect
		];
	}

	public function get_product_section( $id ) {

		$products = carbon_get_post_meta( $id, 'op_product_section_association' );
		$products_ids = array_column( $products, 'id' );

		$data                 = [];
		$data['title']        = carbon_get_post_meta( $id, 'op_product_section_title' );
		$data['descr']        = carbon_get_post_meta( $id, 'op_product_section_descr' );

		$data['top_carousel'] = op_help()->global_cache->get( $products_ids );

		// $data['bottom_carousel'] = $this->get_product_posts(
		// 	carbon_get_post_meta( $id, 'op_product_section_straples_assoc' )
		// );

		return $data;
	}

//	private function get_product_posts( $array_ids, $args = [ 'product' ] ) {
//		$ids = array_column( $array_ids, 'id' );
//
//		$custom_query = new WP_Query;
//
//		return $custom_query->query( [
//			'post__in'  => $ids,
//			'post_type' => $args
//		] );
//	}

	public function get_reviews( $id ) {
		$data            = [];
		$data['title']   = carbon_get_post_meta( $id, 'op_reviews_title' );
		$data['reviews'] = carbon_get_post_meta( $id, 'op_reviews' );

		return $data;
	}

	public function get_cta_section( $id ) {
		$data               = [];
		$data['show']       = carbon_get_post_meta( $id, 'op_cta_show_content' );
		$data['title']      = carbon_get_post_meta( $id, 'op_cta_title' );
		$data['redirect']   = carbon_get_post_meta( $id, 'op_cta_redirect' );
		$data['link']       = carbon_get_post_meta( $id, 'op_cta_link' );
		$data['link_modal'] = carbon_get_post_meta( $id, 'op_cta_link_modal' );
		$data['bg']         = carbon_get_post_meta( $id, 'op_cta_bg' );

		return $data;
	}

	public function get_block_about( $id ) {
		$title      = carbon_get_post_meta( $id, 'op_about_title' );
		$desc_right = carbon_get_post_meta( $id, 'op_about_descr' );
		$img_left   = carbon_get_post_meta( $id, 'op_about_image' );
		$desc_left  = carbon_get_post_meta( $id, 'op_about_descr_bottom' );
		$txt_link   = carbon_get_post_meta( $id, 'op_about_txt_link' );
		$link       = carbon_get_post_meta( $id, 'op_about_descr_link' );
		$img_right  = carbon_get_post_meta( $id, 'op_about_image_bottom' );

		return [
			$title,
			$desc_right,
			$img_left,
			$desc_left,
			$txt_link,
			$link,
			$img_right,
		];
	}

}