<?php
/**
 * Cart totals
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-totals.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 2.3.6
 */

defined( 'ABSPATH' ) || exit;

$prod_cat_args = array(
    'taxonomy'    => 'product_cat',
    'orderby'     => 'id',
    'hide_empty'  => false,
    'parent'      => 0
);

$woo_categories = get_categories( $prod_cat_args );
$total = 0;
$cart_woo_categories = [];
$separated_cart_woo_categories = [];
foreach ( $woo_categories as $key => $woo_cat ) {
    $new_products = op_help()->subscriptions->sort_items_by_category( WC()->cart->get_cart(), $woo_cat );
    if ( empty( $new_products ) ) continue;
    $total = array_reduce( $new_products, function( $total, $item ) {
        return $total + $item['data']->get_price()*$item['quantity'];
    }, 0 );
    $count = array_reduce( $new_products, function( $total, $item ){
        return $total + $item['quantity'];
    }, 0 );

    $cart_cat_data = [
        'name'      => $woo_cat->name,
        'frequency' => '',
        'count'     => $count,
        'total'     => wc_price($total),
    ];

    if ( carbon_get_term_meta( $woo_cat->term_id, "sf_separeted" ) ) {
        $separated_cart_woo_categories[] = $cart_cat_data;
    } else {
        $cart_woo_categories[] = $cart_cat_data;
    }
}
?>

<div class="cart__col">
    <div class="cart__totals cart-totals">

        <?php do_action( 'woocommerce_before_cart_totals' ); ?>

	    <?php
	    $subscribe_status = op_help()->subscriptions->get_subscribe_status();
	    $delivery_date = op_help()->subscriptions->get_delivery_date();
	    ?>
        <div class="cart-totals__head">
            <p class="cart-totals__title">Order summary</p>
	        <?php if ( $subscribe_status['label'] == 'wc-op-paused' ) { ?>
                <div class="cart-totals__head-sign sign">
                    <svg class="sign__icon" width="24" height="24" fill="#DB4827">
                        <use href="#icon-critical-error"></use>
                    </svg>
                    <p class="sign__txt">Paused</p>
                </div>
                <button class="cart-totals__menu manage-order-button control-button control-button--no-txt control-button--color--main-light" type="button" data-manage-order="play" data-tippy-content="Resume">
                    <svg class="control-button__icon" width="24" height="24" fill="#34A34F">
                        <use href="#icon-play"></use>
                    </svg>
                </button>
	        <?php } elseif ( $subscribe_status['label'] == 'locked' ) { ?>
                <div class="cart-totals__head-sign sign">
                    <svg class="sign__icon" width="24" height="24" fill="#252728">
                        <use href="#icon-lock"></use>
                    </svg>
                    <p class="sign__txt">Locked</p>
                </div>
	        <?php
	        } elseif ( $subscribe_status['label'] != 'none-subscribe' ) {
                if ( $delivery_date ) {
                ?>
                <div class="cart-totals__head-sign sign sign--color--main-light">
                    <svg class="sign__icon" width="24" height="24" fill="#34A34F">
                        <use href="#icon-change"></use>
                    </svg>
                    <?php
                    $now = new DateTime();
                    $dif = $now->diff( $delivery_date );
                    $dif_f = $dif->format( '%a' );
                    ?>
                    <p class="sign__txt"><?php echo ( $subscribe_status['label'] == 'wc-op-subscription' ) ? 'Open' : 'Incomplete'; ?> <span><?php echo ( $dif_f > 1 OR $dif_f == 0 ) ? "$dif_f days" : "$dif_f day"; ?> left</span></p>
                </div><!-- / .sign -->
                <?php } ?>
                <button class="cart-totals__menu manage-order-button control-button control-button--no-txt control-button--color--error" type="button" data-manage-order="pause" data-tippy-content="Pause">
                    <svg class="control-button__icon" width="24" height="24" fill="#DB4827">
                        <use href="#icon-pause"></use>
                    </svg>
                </button>
                <?php if ( !is_cart() ) { ?>
                    <a class="cart-totals__edit control-button" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
                        <svg class="control-button__icon" width="24" height="24" fill="#87898C">
                            <use href="#icon-edit"></use>
                        </svg>
                        Edit order
                    </a>
                <?php
                }
            }
            ?>
        </div>
	    <?php
        if ( $subscribe_status['label'] == 'wc-op-paused' ) { ?>
            <p class="cart-totals__notice">Your order was paused.</p>
	        <?php
	    } else {
            if ( $subscribe_status['label'] == 'wc-op-incomplete' ) { ?>
                <p class="cart-totals__notice">Your order was paused because it was incomplete.</p>
                <?php
            }
            if ( $delivery_date ) {
            ?>
            <div class="cart-totals__sign sign">
                <svg class="sign__icon" width="24" height="24" fill="#34A34F">
                    <use href="#icon-delivery"></use>
                </svg>
                <p class="sign__txt"><?php echo $delivery_date->format( 'D, F d' ); ?></p>
                <?php if ( $subscribe_status['label'] != 'locked' ) { ?>
                    <a class="control-button control-button--round"
                       href="/checkout/#Schedule-Your-First-Delivery"
                       data-tippy-content="Edit">
                        <svg class="control-button__icon" width="16" height="16" fill="#252728">
                            <use href="#icon-edit"></use>
                        </svg>
                    </a>
                <?php } ?>
            </div><!-- / .sign -->
            <?php } ?>
        <?php } ?>

        <table class="cart-totals__table shop-table-1">
            <tbody>
                <tr>
                    <td><strong>Meal plan</strong></td>
                    <td class="shop-table-1__time">Weekly</td>
                </tr>

                <?php foreach ( $cart_woo_categories as $woo_cat ) { ?>

                    <tr>
                        <td><?php echo $woo_cat['count'] . ' ' . $woo_cat['name']; ?></td>
                        <td><?php echo $woo_cat['total'];  ?></td>
                    </tr>

                <?php } ?>

            </tbody>
        </table>

        <?php
        if( ! empty( $separated_cart_woo_categories ) ) {
            foreach ( $separated_cart_woo_categories as $woo_cat) {
            ?>

                <table class="cart-totals__table shop-table-1">
                    <tbody>
                        <tr>
                            <td><strong><?php echo $woo_cat['name']; ?></strong></td>
                            <td class="shop-table-1__time"><?php echo $woo_cat['frequency']; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $woo_cat['count'] . ' '; echo $woo_cat['count'] > 1 ? 'items' : 'item'; ?></td>
                            <td><?php echo $woo_cat['total'];  ?></td>
                        </tr>
                    </tbody>
                </table>

            <?php
            }
        }
        ?>

        <table class="cart-totals__table shop-table-2">
            <tbody>

                <tr>
                    <td>Subtotal</td>
                    <td><?php echo WC()->cart->get_cart_subtotal(); ?></td>
                </tr>

                <?php
                if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
                    $taxable_address = WC()->customer->get_taxable_address();
                    $estimated_text  = '';

                    if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
                        /* translators: %s location. */
                        $estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
                    }

                    if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
                        if (!empty(WC()->cart->get_tax_totals())) {
                            foreach ( WC()->cart->get_tax_totals() as $code => $tax ) {
                            ?>

                                <tr>
                                    <td><?php echo _e('Taxes', 'cart'); ?></td>
                                    <td data-title="<?php echo _e('Taxes', 'cart'); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
                                </tr>

                            <?php
                            }
                        }else { ?>
                             <tr>
                                <td><?php echo _e('Taxes', 'cart'); ?></td> <!--  ' (' . $subscriptions_taxes['tax-rate'] . '%)' -->
                                <?php
                            // Get subscription taxes
                                $subscriptions_taxes = calc_subscription_taxes();

                                if ($subscriptions_taxes) { ?>
                                    <td data-title="<?php echo _e('Taxes', 'cart'); ?>"><?php echo wc_price($subscriptions_taxes['tax-total']) ?></td>
                                <?php }else {
                                    $tax_rate = op_help()->shop->get_tax_rates_by_zip(); ?>
                                    <td data-title="<?php echo _e('Taxes', 'cart'); ?>"><?php echo wc_price(op_help()->shop->calc_total_tax($tax_rate)) ?></td>
                                <?php } ?>
                            </tr>
                        <?php }
                    } else {
                    ?>

                        <tr>
                            <th><?php echo _e('Taxes', 'cart'); ?></th>
                            <td data-title="<?php echo _e('Taxes', 'cart'); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
                        </tr>

                    <?php
                    }
                }

                if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) {
                    do_action( 'woocommerce_cart_totals_before_shipping' );
                    wc_cart_totals_shipping_html();
                    do_action( 'woocommerce_cart_totals_after_shipping' );
                } elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) {
                ?>
                    <tr>
                        <th><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></th>
                        <td data-title="<?php esc_attr_e( 'Shipping', 'woocommerce' ); ?>"><?php //woocommerce_shipping_calculator(); ?></td>
                    </tr>
                <?php } ?>

                <?php
                if ( !empty( WC()->cart->get_coupons() ) ) {
                    foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
                        ?>
                        <tr class="cart-discount promo-code coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                            <td>
                                <span class="promo-code__title"><?php echo _e('Promo code:'); ?></span>
                                <span class="promo-code__value"><?php echo $coupon->get_data()['code'] ?></span>
                                <a href="<?php echo esc_url( add_query_arg( 'remove_coupon', rawurlencode( $coupon->get_code() ), (is_checkout()) ? wc_get_checkout_url() : wc_get_cart_url() ) ) ?>" class="woocommerce-remove-coupon link-2" data-coupon="<?php echo esc_attr( $coupon->get_code() ) ?>"><?php echo _e( 'Remove', 'woocommerce' ); ?></a>
                            </td>
                            <td><?php echo '-' . wc_price(WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax )); ?></td>
                        </tr>
                        <?php
                    }
                } ?>

                <?php foreach ( WC()->cart->get_fees() as $fee ) { ?>
                    <tr>
                        <th><?php echo esc_html( $fee->name ); ?></th>
                        <td data-title="<?php echo esc_attr( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></td>
                    </tr>
                <?php } ?>

                <tr class="shop-table-2__order-total">
                    <th>Today’s Total</th>
                    <td><?php wc_cart_totals_order_total_html(); ?></td>
                </tr>

            </tbody>
        </table>

        <?php do_action( 'woocommerce_proceed_to_checkout' ); ?>

        <ul class="cart-totals__informer cart-totals__informer--no-separator informer">
            <li class="informer__item informer-item">
                <svg class="informer-item__icon" width="32" height="32" fill="#34A34F">
                    <use href="#icon-reccuring"></use>
                </svg>
                <div class="informer-item__txt content">
                    <p><strong>Recurring order</strong></p>
                    <p>
                        You will receive your meals on selected day every week. Shipment of groceries and vitamins will
                        depend on selected frequency.
                    </p>
                </div>
            </li>
            <li class="informer__item informer-item">
                <svg class="informer-item__icon" width="32" height="32" fill="#34A34F">
                    <use href="#icon-bank-card"></use>
                </svg>
                <div class="informer-item__txt content">
                    <p><strong>Easily Customized</strong></p>
                    <p>
                        You can edit, pause or cancel your order anytime. Your card will be charged after delivery
                        editing deadline has passed.
                    </p>
                </div>
            </li>
            <li class="informer__item informer-item">
                <svg class="informer-item__icon" width="32" height="32" fill="#34A34F">
                    <use href="#icon-discount"></use>
                </svg>
                <div class="informer-item__txt content">
                    <p><strong>Discount</strong></p>
                    <p>Have a promo code? Apply it during the checkout</p>
                </div>
            </li>
        </ul>
    </div>
	<?php wc_get_template('cart/cart-shipping-block.php'); ?>
	<?php wc_get_template('cart/cart-payment-billing-block.php'); ?>
</div>
