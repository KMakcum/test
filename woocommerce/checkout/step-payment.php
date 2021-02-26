<?php
// Get subscription status
$subscribe_status = op_help()->subscriptions->get_subscribe_status();
?>

<li class="checkout-list__item checkout-item" id="Payment-Method" data-step="Payment-Method" data-step-number="3">
    <div class="checkout-item__head checkout-head">
        <span class="checkout-head__number">3</span>
        <p class="checkout-head__title">Payment Method</p>

        <button class="checkout-head__change control-button control-button--color--main-light sf_checkout_processing_step" type="button" style="display:none" data-mode="change" data-step="Payment-Method">
            <svg class="control-button__icon" width="24" height="24" fill="#34A34F">
                <use href="#icon-edit"></use>
            </svg>
            Edit
        </button>

    </div>
    <div class="checkout-item__body filled" style="display: none;">
        <div class="checkout-item__data-block data-block">
            <div class="data-block__card-data card-data">
                <span class="card-data__number">•••• <span class="card-data__number-inner"></span></span>
                <span class="card-data__exp">Exp. date: <span class="card-data__exp-inner"></span></span>
            </div>
            <div class="data-block__item">
                <div class="data-block__head">
                    <p class="data-block__title">Billing Details</p>
                </div>
                <p class="data-block__txt"></p>
            </div>
        </div>
    </div>
    <div class="checkout-item__body changing" data-type="shipping" style="display: none;">
        <fieldset class="checkout-item__fieldset fieldset">
            <div class="user-card-data">
                <?php wc_get_template( 'checkout/payment.php' ); ?>

                <div class="user-card-data__card-wr">
                    <div class="user-card-data__card js-card-wrapper"></div>
                </div>
            </div>
        </fieldset>
        <fieldset class="checkout-item__fieldset fieldset woocommerce-billing-fieldset">
            <?php do_action( 'woocommerce_checkout_shipping' ); ?>
        </fieldset>
        <fieldset class="checkout-item__fieldset fieldset">
            <ul class="fieldset__list fields-list">
                <li class="fields-list__item">
                    <p class="fields-list__checkbox checkbox">
                        <input class="checkbox__field visually-hidden" id="checkout-make-it-the-main-payment-method" type="checkbox" name="checkout[make_it_the_main_payment_method]" checked>
                        <label class="checkbox__label" for="checkout-make-it-the-main-payment-method"><?php _e('Set as my primary payment method.'); ?></label>
                    </p>
                    <!--<p class="fields-list__checkbox checkbox">
                        <input class="checkbox__field visually-hidden validate-checkbox" id="checkout-terms-and-conditions-agreements" type="checkbox" name="checkout[terms_and_conditions_agreements]">
                        <label class="checkbox__label" for="checkout-terms-and-conditions-agreements">
                            I have read and accepted <a href="<?php /*echo get_site_url().'/terms-conditions/' */?>" target="_blank"><?php /*_e('Terms of Service'); */?></a>
                            and <a href="<?php /*echo get_site_url().'/privacy-policy/' */?>" target="_blank"><?php /*_e('Privacy Policy'); */?></a>
                        </label>
                    </p>-->
                </li>
            </ul>
        </fieldset>
        <div class="checkout-item__foot">
            <?php if ( $subscribe_status['label'] != 'none-subscribe' ) { ?>
                <!-- TODO: return "validate-address" to  button after easypost will be complete -->
                <button class="checkout-item__button button sf_checkout_update_step" data-step="Confirmation" type="button">Save</button>
            <?php }else { ?>
                <!-- TODO: return "validate-address" to  button after easypost will be complete -->
                <button class="checkout-item__button button sf_checkout_processing_step create-user-payment-token" data-step="Confirmation" type="button" disabled="disabled">Save and Continue</button>
            <?php } ?>
        </div>
    </div>
</li>