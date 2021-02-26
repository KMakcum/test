<?php
/**
 * Checkout coupon form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-coupon.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.4
 */

defined( 'ABSPATH' ) || exit;

if ( ! wc_coupons_enabled() ) { // @codingStandardsIgnoreLine.
	return;
}

?>

<form class="data__form form checkout_coupon" method="post">
    <ul class="form__list fields-list">
        <li class="fields-list__item field-box">
        	<input type="text" name="coupon_code" class="field-box__field" id="coupon_code" required autofocus>
            <label class="field-box__label" for="modal-promo-code-field">Promo Code</label>
        </li>
    </ul>
    <button type="submit" name="apply_coupon" class="form__button button">Apply</button>
</form>