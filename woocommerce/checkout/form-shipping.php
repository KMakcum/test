<?php
/**
 * Checkout shipping information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-shipping.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;

$subscription = op_help()->subscriptions->get_current_subscription();

?>
<div class="woocommerce-shipping-fields">
	<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>

		<h3 id="ship-to-different-address">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" /> <span><?php esc_html_e( 'Ship to a different address?', 'woocommerce' ); ?></span>
			</label>
		</h3>

		<div class="shipping_address">

			<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

			<div class="woocommerce-shipping-fields__field-wrapper">
				<?php $fields = $checkout->get_checkout_fields( 'shipping' );

				// Unset excess fileds
				unset($fields['shipping_company']);
				?>

				<legend class="fieldset__legend"><?= _e('Billing details', 'checkout'); ?></legend>
				<ul class="fieldset__list fields-list">
					<?php
					foreach ( $fields as $key => $field ) {
						$field_status = ($key == 'shipping_state') ? true : false; //|| $key == 'shipping_country' TODO: Do we need to disable country for billing?
						if (!$subscription) {
							op_help()->shop->woocommerce_form_field( $key, $field, $checkout->get_value( $key ), $field_status );
						}else {
							$method = 'get_' . str_replace('shipping', 'billing', $key);
							op_help()->shop->woocommerce_form_field( $key, $field, $subscription->$method(), $field_status );
						}
					} ?>
				</ul>

				<fieldset class="checkout-item__fieldset fieldset">
					<ul class="fieldset__list fields-list">
							<li class="fields-list__item">
								<div class="field-wr">
										<span class="field-wr__message message message--inline message--color--main-light">
												<svg class="message__icon" width="24" height="24" fill="#34A34F">
														<use href="#icon-info"></use>
												</svg>
												<span class="message__txt"><?php _e('We will contact you by phone only in case if urgent action on your order is required'); ?></span>
										</span>
										<p class="field-wr__intl-phone intl-phone validate-required" data-name="billing_phone">
												<input class="intl-phone__field js-intl-phone field-box__field" id="checkout-shipping-phone" type="tel" required name="shipping_phone" value="<?php echo esc_attr( (!$subscription) ? $checkout->get_value( 'shipping_phone' ) : $subscription->get_billing_phone() ); ?>"><!-- / .intl-phone -->
												<label class="intl-phone__label" for="checkout-phone">Cell phone</label>
										</p><!-- / .intl-phone -->
								</div><!-- / .field-wr -->
							</li>
					</ul>
				</fieldset>

			</div>

			<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>

		</div>

	<?php endif; ?>
</div>
