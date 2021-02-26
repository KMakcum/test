<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;


class SolutionFactoryZones {

	private static $_instance = null;

	private function __construct() {
	}

	protected function __clone() {
	}

	/**
	 * @return SolutionFactoryZones
	 */
	static public function getInstance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	function init() {

		add_action( 'sf_add_theme_suboption', [ $this, 'add_settings_subpage' ] );

		add_action( 'wp_ajax_update_zip_codes', [ $this, 'cron_update_zip_codes' ] );
		add_action( 'wp_ajax_nopriv_update_zip_codes', [ $this, 'cron_update_zip_codes' ] );

		add_action( 'sf_cron_update_zip_codes', [ $this, 'helper_add_zips' ] );

		//return $this;
	}

	public function cron_update_zip_codes() {
		if ( ! wp_next_scheduled( 'sf_cron_update_zip_codes' ) ) {
			wp_schedule_single_event( time(), 'sf_cron_update_zip_codes' );
		}
	}

	public function create_zips_array( $zip_codes ) {
		return array_map( function ( $zip ) {
			return [ 'code_zip_op_zones' => $zip ];
		}, $zip_codes );
	}

	public function helper_add_zips() {
		$json_url  = get_template_directory_uri() . '/inc/Zones/zips.json';
		$json_data = file_get_contents( $json_url );
		$data      = json_decode( $json_data );

		$areas = [];

		foreach ( $data as $area ) {
			$areas[] =
				[
					'title_op_zones' => $area->title,
					'slug_op_zones'  => $area->slug,
					'zip_op_zones'   => ( $area->zips ) ? $this->create_zips_array( $area->zips ) : ''
				];
		}

		carbon_set_theme_option( 'op_zones', $areas );
	}

	function add_settings_subpage( $main_page ) {

		$zones_labels = array(
			'plural_name'   => 'Zones',
			'singular_name' => 'Zone',
		);

		Container::make( 'theme_options', 'Zones Addon' )
		         ->set_page_parent( $main_page ) // reference to a top level container
		         ->add_tab( __( 'List of zones' ), [
				Field::make( 'complex', 'op_zones', 'Zones' )
				     ->set_collapsed( true )
				     ->set_layout( 'tabbed-horizontal' )
				     ->setup_labels( $zones_labels )
				     ->add_fields( array(
					     Field::make( 'text', 'title_op_zones', __( 'Title' ) ),
					     Field::make( 'text', 'slug_op_zones', __( 'Unique slug' ) )->set_required( true ),

					     Field::make( 'complex', 'zip_op_zones', 'Zone Codes ZIP' )
					          ->set_collapsed( true )
					          ->set_layout( 'tabbed-vertical' )
					          ->setup_labels( $zones_labels )
					          ->add_fields( array(
						          Field::make( 'text', 'code_zip_op_zones', __( 'Code ZIP' ) )->set_attribute( 'type',
							          'number' ),
						          //Field::make( 'text', 'pr_cities_op_zones', __( 'City' ) ),
						          //Field::make( 'text', 'state_op_zones', __( 'State' ) )
					          ) )
					          ->set_header_template( '
								                  <% if (code_zip_op_zones) { %>
								                    <%- code_zip_op_zones %>
								                  <% } %>
								                ' )

				     ) )
				     ->set_header_template( '
						            <% if (title_op_zones) { %>
						              <%- title_op_zones %> <%- slug_op_zones ? " (" + slug_op_zones + ")" : " (You must type slug)" %>
						            <% } %>
						          ' )
			] );
	}
}