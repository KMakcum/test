<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

?>

	<main class="site-main checkout-page checkout-main">
        <section class="checkout-thanks">
            <div class="container">

                <div class="checkout-thanks__top-message message message--full message--error" style="display: none;">
                    <svg class="message__icon" width="24" height="24" fill="#690713">
                        <use href="#icon-warning-3"></use>
                    </svg>
                    <div class="message__txt content">
                        <p><?php echo _e('Please verify your email so we can ship your order', 'order-thank-you'); ?></p>
                    </div>
                </div>

                <h1 class="checkout-thanks__title h2"><?php echo _e('Thank you!', 'order-thank-you'); ?></h1>
                <div class="checkout-thanks__box">

                    <?php get_template_part( 'woocommerce/checkout/thankyou-orders', null, [ 'order' => $order ] ); ?>

                    <?php wc_get_template( 'checkout/rate-experience.php', [ 'order' => $order ] ); ?>

                    <?php wc_get_template( 'checkout/questions.php' ); ?>

                    <?php wc_get_template( 'checkout/share.php' ); ?>

                </div>
            </div>
        </section>
    </main>

    <?php do_action( 'sf_woo_thankyou', $order->get_id() ); ?>
    <?php //do_action( 'woocommerce_thankyou', $order->get_id() ); ?>