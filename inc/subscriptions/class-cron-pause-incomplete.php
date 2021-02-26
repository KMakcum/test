<?php
/**
 * Work with Incomplete and Pause statuses of subscribe-orders
 *
 * @class   SF_Pause_Incomplete
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SF_Pause_Incomplete
 */
class SF_Pause_Incomplete {

	public function init () {
		add_action( 'init', array( $this, 'pause_incomplete_cron' ), 10 );
		add_action( 'pause_incomplete_event', array($this, 'pause_incomplete_handler'), 1 );
	}

	public function pause_incomplete_cron () {
		if( ! wp_next_scheduled( 'pause_incomplete_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'pause_incomplete_event');
		}
	}

	public function pause_incomplete_handler () {
		$orders_query = new WP_Query;
		$args = [
			'post_type' => 'shop_order',
			'post_status' => ['wc-op-paused', 'wc-op-incomplete'],
			'nopaging' => true,
			'meta_query' => [
				[
					'key' => 'op_next_order_creation',
					'compare' => '<=',
					'value' => (new DateTime())->format("Y-m-d H:00:00"),
					'type' => 'DATETIME'
				],
			],
			'fields' => 'ids'
		];

		$ready_orders = $orders_query->query( $args );
		foreach ( $ready_orders as $key => $subscription_id ) {
			$subscription = wc_get_order( $subscription_id );
			try {
				$current_time = new DateTime();
				$creation_time = new DateTime( $subscription->get_meta( "op_next_order_creation" ) );
				$delivery_time = new DateTime( $subscription->get_meta( "op_next_delivery" ) );
			} catch ( Exception $e ) {
				continue;
			}

			if ( $current_time < $creation_time ) {
				continue;
			}

			$delivery_date_offset = carbon_get_theme_option( 'op_subscription_order_offset' ) ? (integer) carbon_get_theme_option( 'op_subscription_order_offset' ) + 1 : 49;
			$op_next_week = intval( $subscription->get_meta("op_next_week") ) + 1;

			$subscription->update_meta_data( "op_next_week", $op_next_week );
			$subscription->update_meta_data( "op_next_delivery", $delivery_time->modify( "+1 weeks" )->format( "Y-m-d h:m:i" ) );
			$subscription->update_meta_data( "op_next_order_creation",
				$delivery_time->modify( '- ' . $delivery_date_offset . ' hour' )->format( "Y-m-d h:m:i" ) );

			if ( $subscription->post_status != 'wc-op-paused' ) {
				$subscription->set_status( 'wc-op-paused', 'Order is paused.' );
			}
			$subscription->save();
		}
	}
}