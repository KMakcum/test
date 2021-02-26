<?php
/**
 * Checkout billing information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-billing.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;

$subscription = op_help()->subscriptions->get_current_subscription();

?>
<div class="woocommerce-billing-fields">

	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

	<div class="woocommerce-billing-fields__field-wrapper">
		<?php
		$fields = $checkout->get_checkout_fields( 'billing' );

		unset( $fields['billing_phone'] );
		unset( $fields['billing_email'] );

		?>

		<fieldset class="checkout-item__fieldset fieldset">
			<legend class="fieldset__legend">Delivery Details</legend>
			<ul class="fieldset__list fields-list">
			<?php
			foreach ( $fields as $key => $field ) {
				$field_status = ($key == 'billing_state' || $key == 'billing_country') ? true : false;
				if (!$subscription) {
					op_help()->shop->woocommerce_form_field( $key, $field, $checkout->get_value( $key ), $field_status );
				}else {
					$method = 'get_' . str_replace('billing', 'shipping', $key);
					op_help()->shop->woocommerce_form_field( $key, $field, $subscription->$method(), $field_status );
				}
			} ?>
			</ul><!-- / .fields-list -->
		<?php

		// unset( $fields['billing_email'] );


		?>
		<p class="fieldset__checkbox checkbox">
			<input class="checkbox__field visually-hidden" id="checkout-use-as-billing-address" type="checkbox" name="checkout[use_as_billing_address]" checked>
			<label class="checkbox__label" for="checkout-use-as-billing-address">Use this as my billing address</label>
		</p>
		</fieldset><!-- / .fieldset -->

		<fieldset class="checkout-item__fieldset fieldset">
				<legend class="fieldset__legend">Personal details</legend>
				<ul class="fieldset__list fields-list">
						<li class="fields-list__item">
								<div class="field-wr">
										<span class="field-wr__message message message--inline message--color--main-light">
												<svg class="message__icon" width="24" height="24" fill="#34A34F">
														<use href="#icon-info"></use>
												</svg>
												<span class="message__txt"><?php _e('We ask for your email to send you order updates'); ?></span>
										</span>
										<p class="fieldset__single-field field-box validate-required" data-name="billing_email">
												<input class="field-box__field" id="checkout-email" type="email" name="billing_email" value="<?php echo esc_attr( $checkout->get_value( 'billing_email' ) ); ?>" required>
												<label class="field-box__label" for="checkout-email">Your Email</label>
										</p><!-- / .field-box -->
								</div><!-- / .field-wr -->
								<p class="fields-list__checkbox checkbox">
										<input class="checkbox__field visually-hidden" id="checkout-receive-offers" type="checkbox" name="billing_email_offers" <?php // echo $checkout->get_value( 'billing_email_offers' ) ? 'checked' : ''; ?> checked>
										<label class="checkbox__label" for="checkout-receive-offers">I would like to recieve offers from LifeChef™</label>
								</p><!-- / .checkbox -->
						</li>
						<li class="fields-list__item">
								<div class="field-wr">
										<span class="field-wr__message message message--inline message--color--main-light">
												<svg class="message__icon" width="24" height="24" fill="#34A34F">
														<use href="#icon-info"></use>
												</svg>
												<span class="message__txt"><?php _e('We will contact you by phone only in case if urgent action on your order is required'); ?></span>
										</span>
										<p class="field-wr__intl-phone intl-phone validate-required" data-name="billing_phone">
												<input class="intl-phone__field js-intl-phone field-box__field" id="checkout-phone" type="tel" name="billing_phone" required value="<?php echo esc_attr( (!$subscription) ? $checkout->get_value( 'billing_phone' ) : get_post_meta($subscription->get_id(), '_shipping_phone', true) ); ?>"><!-- / .intl-phone -->
												<label class="intl-phone__label" for="checkout-phone">Cell phone</label>
										</p><!-- / .intl-phone -->
								</div><!-- / .field-wr -->
								<p class="fields-list__checkbox checkbox">
										<input class="checkbox__field visually-hidden" id="checkout-sms-on-my-orders" type="checkbox" name="billing_phone_sms" <?php // echo $checkout->get_value( 'billing_phone_sms' ) ? 'checked' : ''; ?> checked>
										<label class="checkbox__label" for="checkout-sms-on-my-orders">Receive SMS text message updates on my orders</label>
								</p><!-- / .checkbox -->
						</li>
				</ul>
		</fieldset>
		<fieldset class="checkout-item__fieldset fieldset">
				<legend class="fieldset__legend">Delivery Instructions</legend>
				<div class="field-wr">
						<p class="field-wr__field field-box">
								<textarea class="field-box__field field-box__field--textarea js-auto-size" id="checkout-delivery-instructions" name="order_comments"><?php
									echo esc_attr( $subscription->customer_message );
								?></textarea>
								<label class="field-box__label" for="checkout-delivery-instructions">Delivery instructions</label>
						</p><!-- / .field-box -->
						<span class="field-wr__credits">Optional</span>
				</div><!-- / . field-wr -->
		</fieldset>

		<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>

	</div>

</div>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
	<div class="woocommerce-account-fields">
		<?php if ( ! $checkout->is_registration_required() ) : ?>

			<p class="form-row form-row-wide create-account">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'woocommerce' ); ?></span>
				</label>
			</p>

		<?php endif; ?>

		<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

		<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>

			<div class="create-account">
				<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
					<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>

		<?php endif; ?>

		<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
	</div>
<?php endif; ?>
