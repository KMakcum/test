<?php
/**
 * Rest api for pick and pack service
 *
 * @class   SolutionFactoryStore
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SolutionFactoryStore
 */
class SolutionFactoryStore
{
	private static $_instance = null;

	private function __construct()
	{
	}

	protected function __clone()
	{
	}

	public $allZips;

	/**
	 * @return SFAddonSubscription
	 */
	static public function getInstance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	function init()
	{

		add_action('rest_api_init', [$this, 'add_endpoints']);
	}

	function add_endpoints()
	{

		$routes = [
			'get_orders' => '',
			'set_scan' => '/scan',
			'set_print' => '/print',
			'set_status' => '/status',
			'update_order' => '/update',
		];

		foreach ($routes as $method => $endpoint) {
			// TODO добавить permission
			register_rest_route('wc/v3', 'sf-order' . $endpoint, array(
				'methods' => 'POST',
				'callback' => [$this, $method],
				'permission_callback' => null,
			));
		}
	}

	public function get_orders($data)
	{

		if (!current_user_can('manage_options')) return new WP_Error("denied", "You haven't permissions", array('status' => 401));

		$from_date = new DateTime($_POST['from']);
		$to_date = new DateTime($_POST['to']);

		$orders_query = new WP_Query;
		//op_next_delivery
		$ready_orders = $orders_query->query([
			'post_type'   => 'shop_order',
			'post_status' => ['wc-processing', 'wc-picked', 'wc-packed'],
			'nopaging'    => true,
			'date_query'  => [
				'relation' => 'AND',
				[
					'column'     => 'post_date', // post_date | post_date_gmt | post_modified | post_modified_gmt
					'compare' => '>=',
					'after'   => $from_date->format("Y-m-d"),
					'inclusive' => true,
				],
				[
					'column'     => 'post_date',
					'compare' => '<=',
					'before'   => $to_date->format("Y-m-d"),
					'inclusive' => true,
				],
			],
			'meta_query' => [
				'relation' => 'AND',
				[
					'key' => 'op_next_delivery',
					//'value' => date("Y-m-d H:i:s"),
					'value' => $from_date->format("Y-m-d H:i:s"),
					'compare' => '>=',
					'type' => 'DATETIME'
				],
			],
			'orderby' => 'op_next_delivery',
			'order'   => 'DESC',
			'fields'      => 'ids'
		]);

		return array_map([$this, 'format_order_data'], $ready_orders);
	}

	public function set_scan()
	{
		if (!current_user_can('manage_options')) return new WP_Error("denied", "You haven't permissions", array('status' => 401));
		$order = wc_get_order($_POST['id']);
		if (is_null($order)) return new WP_Error("not-found", "Order not found", array('status' => 404));
		if (empty($_POST['sku'])) return new WP_Error("no-sku", "You must send sku", array('status' => 200));
		// mb sanitize SKU?
		$result = add_post_meta($order->get_id(), 'scan_list', $_POST['sku']);
		if ($result) {
			return ['message' => 'SKU have been added to scan list of order.'];
		} else {
			return new WP_Error("adding-error", "Unknown error with adding to DB", array('status' => 520));
		}
	}

	public function update_order()
	{
		$shippingFields = [
			'_pp_carrier',
			'_pp_tracking_number',
			'_pp_delivery_cost',
			'_pp_boxes',
			'_pp_insolation',
			'_pp_ice_packs'
		];

		if (!current_user_can('manage_options')) return new WP_Error("denied", "You haven't permissions", array('status' => 401));
		$order = wc_get_order($_POST['id']);
		if (is_null($order)) return new WP_Error("not-found", "Order not found", array('status' => 404));

		foreach($shippingFields as $shippingField) {
			if (isset($_POST[$shippingField])) {
				update_post_meta($order->get_id(), $shippingField, $_POST[$shippingField]);
			}
		}

		if (isset($_POST['status'])) {
			$order->set_status($_POST['status']);
			$order->save();
		}

		return ['message' => 'Order was updated'];
	}

	public function set_print()
	{
		if (!current_user_can('manage_options')) return new WP_Error("denied", "You haven't permissions", array('status' => 401));
		$order = wc_get_order($_POST['id']);
		if (is_null($order)) return new WP_Error("not-found", "Order not found", array('status' => 404));
		if (empty($_POST['sku'])) return new WP_Error("no-sku", "You must send sku", array('status' => 200));
		// mb sanitize SKU?
		$result = add_post_meta($order->get_id(), 'print_list', $_POST['sku']);
		if ($result) {
			return ['message' => 'SKU have been added to print list of order.'];
		} else {
			return new WP_Error("adding-error", "Unknown error with adding to DB", array('status' => 520));
		}
	}

	public function set_status()
	{
		if (!current_user_can('manage_options')) return new WP_Error("denied", "You haven't permissions", array('status' => 401));
		$new_status = $_POST['status'];
		$message = '';
		$order = wc_get_order($_POST['id']);
		if (!$order) return new WP_Error("not-found", "Order not found", array('status' => 404));
		if (empty($new_status)) return new WP_Error("no-status", "You must send new status", array('status' => 200));
		if ($new_status == 'wc-' . $order->get_status()) return new WP_Error("same-status", "You tried change to same status", array('status' => 200));
		//if (in_array($new_status, ['wc-processing', 'wc-await-ship', 'wc-shipped'])) return new WP_Error("no-status", "You must send sku", array('status' => 200));
		if (empty($_POST['message'])) $message = esc_html($_POST['message']);
		$order->set_status($_POST['status'], $message);
		$order->save();
		return ['message' => 'Status have been changed'];
	}

	public function get_order_zone($user_zip) {
		if (empty($this->allZips)) {
			$this->allZips = carbon_get_theme_option( 'op_zones' );
		}

		$orderZone = 'National';

		$codes = array_column($this->allZips, 'zip_op_zones', 'title_op_zones');

		foreach ( (array) $codes as $area => $code ) {
			$zip_codes_in_db = array_column($code, 'code_zip_op_zones');

			if ( in_array($user_zip, $zip_codes_in_db) ) {
				$orderZone = $area;
				return $orderZone;
				break;
			}

		}

		return $orderZone;
	}


	/**
	 * @param int|WC_Order $order - needed order
	 *
	 * @return array Data for REST
	 */
	private function format_order_data($order)
	{

		if (is_numeric($order)) $order = wc_get_order($order);
		if (is_null($order)) return [];

		$order_post_meta = get_post_meta($order->get_id());
		$ship_date = isset($order_post_meta['op_next_delivery'][0]) ? $order_post_meta['op_next_delivery'][0] : '';

		$customer = new WC_Customer($order->get_customer_id());

		$scan_list = isset($order_post_meta['scan_list']) ? $order_post_meta['scan_list'] : [];
		$print_list = isset($order_post_meta['print_list']) ? $order_post_meta['print_list'] : [];
		$scan_list = empty($scan_list) ? [] : $scan_list;
		$print_list = empty($print_list) ? [] : $print_list;

		$orderZone = $this->get_order_zone($customer->get_billing_postcode());

		$address1 = isset($order_post_meta['_shipping_address_1'][0]) ? $order_post_meta['_shipping_address_1'][0] : '';
		$address2 = isset($order_post_meta['_shipping_address_2'][0]) ? $order_post_meta['_shipping_address_2'][0] : '';

		$shippingAddress = [
			'name' => $order_post_meta['_shipping_first_name'][0] . ' ' . $order_post_meta['_shipping_last_name'][0],
			'street1' => $address1 . ', ' . $address2,
			'city' => $order_post_meta['_shipping_city'][0],
			'state' => $order_post_meta['_shipping_state'][0],
			'zip' => $order_post_meta['_shipping_postcode'][0],
			'country' => $order_post_meta['_shipping_country'][0],
			'phone' => $order_post_meta['_billing_phone'][0],
		];

		$order_data = [
			'id' => $order->get_id(),
			'name' => 'Order#' . $order->get_id(),
			'status' => 'wc-' . $order->get_status(),
			'customer' => $customer->get_first_name() . ' ' . $customer->get_last_name(),
			'zipCode' => $customer->get_billing_postcode(),
			'zone' => $orderZone,
			'to' => $shippingAddress,
			'scanList' => $scan_list,
			'printList' => $print_list,
			'_pp_carrier' => isset($order_post_meta['_pp_carrier'][0]) ? $order_post_meta['_pp_carrier'][0] : '',
			'_pp_tracking_number' => isset($order_post_meta['_pp_tracking_number'][0]) ? $order_post_meta['_pp_tracking_number'][0] : '',
			'_pp_delivery_cost' => isset($order_post_meta['_pp_delivery_cost'][0]) ? $order_post_meta['_pp_delivery_cost'][0] : '',
			'_pp_boxes' => isset($order_post_meta['_pp_boxes'][0]) ? $order_post_meta['_pp_boxes'][0] : '',
			'_pp_insolation' => isset($order_post_meta['_pp_insolation'][0]) ? $order_post_meta['_pp_insolation'][0] : '',
			'_pp_ice_picks' => isset($order_post_meta['_pp_ice_picks'][0]) ? $order_post_meta['_pp_ice_picks'][0] : '',
			'ship_date' => $ship_date,
			// "created_by": {
			//   "id": 1,
			//   "firstname": "root",
			//   "lastname": "toor",
			//   "username": null
			// },
			// "updated_by": {
			//   "id": 1,
			//   "firstname": "root",
			//   "lastname": "toor",
			//   "username": null
			// },
			"created_at" => $order->get_date_created(),
			// "updated_at": "2020-09-15T15:32:09.842Z",
		];

		$items_by_category = op_help()->subscriptions->get_items_n_categories_for_order($order);
		foreach ($items_by_category as $category_key => $category_data) {
			$category_items = [];

			foreach ($category_data['products'] as $cart_product) {
				$product_temp_data = [
					"id" => $cart_product->get_id(),
					"sku" => $cart_product->get_product()->get_sku(),
					"qty" => $cart_product->get_quantity(),
					"name" => get_the_title( $cart_product->get_product()->get_id() ),
					"storeType" => get_post_meta( $cart_product->get_product()->get_id() , 'op_store_type', true),
				];

				if (!empty($cart_product->get_product()->get_attributes())) {

					$product_components = [];
					foreach ($cart_product->get_product()->get_attributes() as $attr_type => $attr_name) {
						$attr_data = get_term_by('slug', $attr_name, $attr_type);
						$product_components[] = [
							'id' => $attr_data->term_id,
							'name' => $attr_data->name,
							'sku' => get_term_meta( $attr_data->term_id, '_op_variations_component_sku', true ),
							"storeType" => "Refregirator"
						];
					}

					$product_temp_data['components'] = $product_components;
				}

				$category_items[] = $product_temp_data;
				// {
				//   "name": "Meal 1",
				//   "sku": "MEAL SKU1",
				//   "storeType": "warehouse",
				//   "created_by": {
				//     "id": 1,
				//     "firstname": "root",
				//     "lastname": "toor",
				//     "username": null
				//   },
				//   "updated_by": {
				//     "id": 1,
				//     "firstname": "root",
				//     "lastname": "toor",
				//     "username": null
				//   },
				//   "created_at": "2020-09-15T13:12:41.006Z",
				//   "updated_at": "2020-09-15T13:46:57.897Z",
				//   "components": [
				//     {
				//       "id": 1,
				//       "name": "Component 1",
				//       "sku": "SKU1",
				//       "storeType": "fridge",
				//       "created_by": 1,
				//       "updated_by": 1,
				//       "created_at": "2020-09-15T13:11:43.199Z",
				//       "updated_at": "2020-09-15T13:46:41.205Z"
				//     },
				//     {
				//       "id": 2,
				//       "name": "Component 2",
				//       "sku": "SKU2",
				//       "storeType": "fridge",
				//       "created_by": 1,
				//       "updated_by": 1,
				//       "created_at": "2020-09-15T13:11:51.847Z",
				//       "updated_at": "2020-09-15T13:46:44.211Z"
				//     },
				//     {
				//       "id": 3,
				//       "name": "Component 3",
				//       "sku": "SKU3",
				//       "storeType": "fridge",
				//       "created_by": 1,
				//       "updated_by": 1,
				//       "created_at": "2020-09-15T13:12:05.960Z",
				//       "updated_at": "2020-09-15T13:46:47.281Z"
				//     }
				//   ]
				// },
			}

			$order_data[$category_data['category']->slug] = $category_items;
		}

		return $order_data;
	}
}


//SolutionFactoryStore::getInstance()->init();
