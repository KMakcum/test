<?php


class SyncComponents {
	private static $_instance = null;
	public static $name_main_meal_product = 'Main Meal Product';
	public static $category_name = 'Components';
	public static $terms = [
		'pa_part-1',
		'pa_part-2',
		'pa_part-3'
	];

	static public function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function init() {
		add_action( 'init', [ $this, 'setup_cron_task' ] );
		add_action( 'sync_from_single_components', [ $this, 'sync_from_single_components' ] );
	}

	public function setup_cron_task() {
		if ( ! wp_next_scheduled( 'sync_from_single_components' ) ) {
			wp_schedule_event( time(), 'hourly', 'sync_from_single_components' );
		}
	}

	/**
	 * Sync Components and Meals from single components.
	 */
	public function sync_from_single_components() {
        \TB::start( 'sync' );
        \TB::m( "*start* cron `sync_from_single_components`." );
		$this->update_components();
        $result = $this->update_meals();
        \TB::m( "*stop* cron `sync_from_single_components`.\n Total: {$result['total']}\n Update: {$result['update']}", true, 'sync' );
//		do_action( 'plugins_finished_sync' );
	}

	/**
	 * Update Components (terms/WC attributes) from single products cat. by Components.
	 */
	public function update_components() {
		global $wpdb;

		$single_products = get_posts( array(
			'post_type'   => 'product',
			'numberposts' => -1,
			'post_status' => 'publish',
			'tax_query'   => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => 'components',
				)
			),
		) );

		foreach ( $single_products as $single_product ) {
			$price = get_post_meta( $single_product->ID, '_regular_price', true );
			$internalid = get_post_meta( $single_product->ID, 'internalid', true );

			// WC attributes (Components)
			$component_terms = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}termmeta` WHERE `meta_value` = %d AND `meta_key` = '_op_variations_component_sku'", $internalid ) );

			foreach ( $component_terms as $component_term ) {
				$wpdb->update( $wpdb->terms, ['name' => $single_product->post_title], ['term_id' => $component_term->term_id] );
				update_term_meta( $component_term->term_id, '_price', $price );
			}
		}
	}

	/**
	 * Update Meals by Components (terms/WC attributes).
	 */
	public function update_meals() {
		global $wpdb;

		$id_main_meal_product = \SfSync\ImportData::getPostByName( self::$name_main_meal_product );
		$meals = $wpdb->get_results( $wpdb->prepare( "SELECT `ID` FROM `{$wpdb->prefix}posts` WHERE `post_parent` = %d AND `post_status` = 'publish'", $id_main_meal_product ) );
        $result['total'] = count( $meals );
        $result['update'] = 0;

		foreach ( $meals as $meal ) {
//			$id_components = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}term_relationships` WHERE `object_id` = %d", $meal->ID ) );
			$id_components = $wpdb->get_results( $wpdb->prepare(
				"SELECT tr.* FROM `{$wpdb->prefix}term_relationships` as tr
				 LEFT JOIN `{$wpdb->prefix}term_taxonomy` as tt
				 ON tt.term_id = tr.term_taxonomy_id
				 WHERE tr.`object_id` = %d AND
				 (tt.taxonomy='pa_part-1' OR tt.taxonomy='pa_part-2' OR tt.taxonomy='pa_part-3')",
				$meal->ID )
			);
			$name = '';
			$price = 0;

			foreach ( $id_components as $id_component ) {
				$component_price = get_term_meta( $id_component->term_taxonomy_id, '_price', true );
				$component_name = $wpdb->get_var( $wpdb->prepare( "SELECT `name` FROM `{$wpdb->prefix}terms` WHERE `term_id` = %d", $id_component->term_taxonomy_id ) );
				if ( $component_price ) {
					$price += $component_price;
				}
				if ( $component_name ) {
					$name .= $component_name . ', ';
				}
			}

			// Multipliers from Meal
			$base_multiplier = get_post_meta( $meal->ID, 'base_multiplier', true );
			$base_multiplier = ( $base_multiplier ) ? $base_multiplier : 1;

			$MSRP_multiplier = SfSync\ImportData::getMSRPMultiplier( $meal->ID );
			$MSRP_multiplier = ( $MSRP_multiplier ) ? $MSRP_multiplier : 1;

			$sale_price = $price * $base_multiplier;
			$regular_price = $sale_price * $MSRP_multiplier;

			$name = substr( $name, 0, -2 );

			// Updates
			// Update Meal Price
            update_post_meta( $meal->ID, '_regular_price', $regular_price );
            if ( $sale_price < $regular_price ) {
                update_post_meta( $meal->ID, '_price', $sale_price );
                update_post_meta( $meal->ID, '_sale_price', $sale_price );
            } else {
                update_post_meta( $meal->ID, '_price', $regular_price );
                delete_post_meta( $meal->ID, '_sale_price' );
			}

            $result['update'] += 1;
			// Update Meal Name
//			update_post_meta( $meal->ID, 'op_post_title', $name );
//			$wpdb->update( $wpdb->posts, ['post_title' => $name], ['ID' => $meal->ID] ); // post_title
		}

		return $result;
	}
}