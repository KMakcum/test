<li class="checkout-list__item checkout-item" id="Confirmation" data-step="Confirmation" data-step-number="4">
    <div class="checkout-item__head checkout-head">
        <span class="checkout-head__number">4</span>
        <p class="checkout-head__title">Confirmation</p>

        <button class="checkout-head__change control-button control-button--color--main-light sf_checkout_processing_step" type="button" style="display:none" data-mode="change" data-step="Confirmation">
            <svg class="control-button__icon" width="24" height="24" fill="#34A34F">
                <use href="#icon-edit"></use>
            </svg>
            Edit
        </button>

    </div>
    <div class="checkout-item__body filled" style="display: none;"></div>
    <div class="checkout-item__body changing" style="display: none;">
        <p class="checkout-item__checkbox checkbox">
            <input class="checkbox__field visually-hidden" id="checkout-use-as-billing-address-2" type="checkbox" name="checkout[use_as_billing_address]">
            <label class="checkbox__label" for="checkout-use-as-billing-address-2">
                I confirm that I have read, understood and accepted LifeChef’s™
                <a href="<?php echo get_site_url().'/terms-conditions/' ?>" target="_blank">Terms and Conditions</a>
                and <a href="<?php echo get_site_url().'/privacy-policy/' ?>" target="_blank">Privacy Policy</a>
            </label>
        </p>

        <?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

        <?php
        $order_button_text = 'Place Order';
        echo apply_filters(
            'woocommerce_order_button_html',
            '<button type="submit" class="checkout-item__button button" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '" disabled="disabled">' . esc_html( $order_button_text ) . '</button>'
        ); // @codingStandardsIgnoreLine
        ?>

        <?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
    </div>
</li>
