<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.8.0
 */

defined( 'ABSPATH' ) || exit;

$prod_cat_args = array(
    'taxonomy'    => 'product_cat',
    'orderby'     => 'id',
    'hide_empty'  => false,
    'parent'      => 0
);

$woo_categories = get_categories( $prod_cat_args );
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

$wrapp_class  = ( is_cart() ) ? 'cart__col' : 'checkout__sidebar woocommerce-checkout-review-order-table'; //
$prefix_class = ( is_cart() ) ? 'cart' : 'checkout';
?>


<div class="<?php echo $wrapp_class; ?>">
    <div class="<?php echo $prefix_class; ?>__totals cart-totals">

        <?php do_action( 'woocommerce_before_cart_totals' ); ?>

        <div class="cart-totals__head">
            <p class="cart-totals__title">Order summary</p>

            <?php if ( ! is_cart() ) { ?>

                <a class="cart-totals__edit control-button" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
                    <svg class="control-button__icon" width="24" height="24" fill="#87898C">
                        <use href="#icon-edit"></use>
                    </svg>
                    Edit order
                </a>

            <?php } ?>

        </div>

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
                                    <th><?php echo _e('Taxes', 'cart'); ?></th>
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
                ?>

                <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

					<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

					<?php wc_cart_totals_shipping_html(); ?>

					<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

				<?php endif; ?>

                <?php
				if ( !empty( WC()->cart->get_coupons() ) ) {

					foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
					?>

						<tr class="cart-discount promo-code coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
							<td>
                                                                                        <span class="promo-code__title"><?php echo _e('Promo code:'); ?></span>
                                                                                        <span class="promo-code__value"><?php echo $coupon->get_data()['code'] ?></span>
                                                                                        <a href="<?php echo esc_url( add_query_arg( 'remove_coupon', rawurlencode( $coupon->get_code() ), (is_checkout()) ? wc_get_checkout_url() : wc_get_cart_url() ) ) ?>" class="woocommerce-remove-coupon link-2" data-coupon="<?php echo esc_attr( $coupon->get_code() ) ?>"><?php echo _e( 'Remove', 'woocommerce' ); ?></a>
                                                                                        <?php //var_dump(); ?>
                                                                                    </td>
							<td><?php echo '-' . wc_price(WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax )); ?></td>
						</tr>

					<?php
					}
				} else {
				?>

				<tr>
                    <td class="add-promo-code" colspan="2">
                        <svg class="add-promo-code__icon" width="24" height="24" fill="#34A34F">
                            <use href="#icon-discount"></use>
                        </svg>
                        <span class="add-promo-code__txt">Have a promocode?</span>
                        <a class="add-promo-code__trigger btn-modal" href="#js-promo-code">Apply here</a>
                    </td>
				</tr>

				<?php } ?>

				<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

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

        <?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

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
                    <p>Have a discount code? Apply  it during the checkout</p>
                </div>
            </li>
        </ul>

    </div>
</div>
