<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {

	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<!-- Redirect page to cart if subscription exist and hash is wrong -->
<script>
    // Make redirect if hash wrong
    var is_subscription_exist = '<?php echo (op_help()->subscriptions->get_current_subscription()) ? 'true' : 'false' ?>';
    if (is_subscription_exist == 'true') {
        var checkout_steps = '<?php echo json_encode(checkout_steps_hashes()); ?>';
        if (!checkout_steps.includes(window.location.hash) || window.location.hash == '') {
            document.location.href = '<?php echo wc_get_cart_url(); ?>';
        }
    }else {
        // If page was reloaded - set "#Delivery-Address" step by default
        if (window.location.hash !== '#Delivery-Address') {
            document.location.href = document.URL.split('#')[0] + '#Delivery-Address';
        }
    }
</script>
<!-- \Redirect page to cart if subscription exist and hash is wrong -->


<main class="site-main checkout-page checkout-main checkout-main--short">
    <section class="checkout checkout--short">
        <div class="container">
            <h1 class="checkout__title h2">Checkout</h1>
            <ul class="checkout__nav step-nav">
                <li class="step-nav__item">
                    <a class="step-nav__link" data-step="Delivery-Address" id="Delivery-Address-mobile"></a>
                </li>
                <li class="step-nav__item">
                    <a class="step-nav__link" data-step="Schedule-Your-First-Delivery" id="Schedule-Your-First-Delivery-mobile"></a>
                </li>
                <li class="step-nav__item">
                    <a class="step-nav__link" data-step="Payment-Method" id="Payment-Method-mobile"></a>
                </li>
                <li class="step-nav__item">
                    <a class="step-nav__link" data-step="Confirmation" id="Confirmation-mobile"></a>
                </li>
            </ul>
            <div class="checkout__body">

                <form   class="checkout__form checkout"
                        name="checkout"
                        action="<?php echo esc_url( wc_get_checkout_url() ); ?>"
                        method="post"
                        enctype="multipart/form-data"
                >
                    <ul class="checkout__list checkout-list">

                        <?php wc_get_template('checkout/step-address.php'); ?>

                        <?php wc_get_template('checkout/step-delivery.php'); ?>

                        <?php wc_get_template('checkout/step-payment.php'); ?>

                        <?php wc_get_template('checkout/step-confirm.php'); ?>

                    </ul>
                </form>

                <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

                <?php remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 ); ?>

                <?php do_action( 'woocommerce_checkout_order_review' ); ?>

                <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

	            <?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

                <div class="checkout__other">
<!--                    --><?php //wc_get_template('checkout/questions.php'); ?>
                    <?php wc_get_template('checkout/zendesk-checkout-qa.php'); ?>
                </div>

            </div>
        </div>
    </section>
</main>
