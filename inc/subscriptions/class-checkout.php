<?php
/**
 * класс для проверки чекаута перед созданием подписки, мы не можем использовать приватные методы
 *
 * @class   SF_Checkout
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SF_Checkout
 */
class SF_Checkout extends WC_Checkout
{

	/**
	 * проверяет переданные данные из чекаута на ошибки и готовность к созданию подписки
	 */
	function sf_get_errors(){
		$errors = new WP_Error;
		$posted_data = $this->get_posted_data();
		$this->validate_checkout( $posted_data, $errors );
		return $errors;
	}

	/**
	 * метод для получение условного заказа чтобы создать токен для оплаты
	 */
	function sf_get_order(){

		$posted_data = $this->get_posted_data();
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

		$abstract_order = new WC_Order();
		// mb need
		/* $fields_prefix = array(
		  'shipping' => true,
		  'billing'  => true,
		);
		$shipping_fields = array(
		  'shipping_method' => true,
		  'shipping_total'  => true,
		  'shipping_tax'    => true,
		);
		foreach ( $posted_data as $key => $value ) {
		  if ( is_callable( array( $abstract_order, "set_{$key}" ) ) ) {
			$abstract_order->{"set_{$key}"}( $value );
			// Store custom fields prefixed with wither shipping_ or billing_. This is for backwards compatibility with 2.6.x.
		  } elseif ( isset( $fields_prefix[ current( explode( '_', $key ) ) ] ) ) {
			if ( ! isset( $shipping_fields[ $key ] ) ) {
			  $abstract_order->update_meta_data( '_' . $key, $value );
			}
		  }
		} */

		$abstract_order->set_payment_method( isset( $available_gateways[ $posted_data['payment_method'] ] ) ? $available_gateways[ $posted_data['payment_method'] ] : $posted_data['payment_method'] );

		return $abstract_order;
	}
}