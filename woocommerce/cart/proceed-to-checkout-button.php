<?php
/**
 * Proceed to checkout button
 *
 * Contains the markup for the proceed to checkout button on the cart.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/proceed-to-checkout-button.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( op_help()->subscriptions->is_has_subscribe() ) {
    return;
}
?>
<!-- <a class="cart-totals__button button" href="#">Place your order</a> -->
<?php if(! is_user_logged_in()) {
    echo '<a href="#js-modal-sign-up" class="user-list__link btn-modal cart-totals__button button button--medium  wc-forward" disabled="disabled"';
} else {
    echo '<a href="' . esc_url( wc_get_checkout_url() ) . '" class="cart-totals__button button button--medium wc-forward" disabled="disabled"' ;
} ?>><?php esc_html_e( 'Start your plan', 'woocommerce' ); ?>
</a>