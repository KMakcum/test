<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class SFCategoryPage {
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
		Container::make( 'term_meta', 'Category settings' )
	         ->where( 'term_taxonomy', 'product_cat' )
	         ->add_fields( array(
		         Field::make( 'text', 'facility_allergens', __( 'facility allergens' ) ),
             ) );
	}
}