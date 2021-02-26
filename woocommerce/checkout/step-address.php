<?php
// Get subscription status
$subscribe_status = op_help()->subscriptions->get_subscribe_status();
?>

<li class="checkout-list__item checkout-item" id="Delivery-Address" data-step="Delivery-Address" data-step-number="1">
    <div class="checkout-item__head checkout-head">
        <span class="checkout-head__number">1</span>
        <p class="checkout-head__title"><?php echo _e('Delivery Address', 'checkout'); ?></p>
        <!-- TMP show error message if address in invalid -->
        <div class="message message--full message--error" style="display: none;">
            <svg class="message__icon" width="24" height="24" fill="#690713">
                <use href="#icon-warning-3"></use>
            </svg>
            <div class="message__txt content">
                <p></p>
            </div>
        </div>

        <span class="pill pill--bg--main-very-lightest" style="display: none;">
            <svg class="pill__icon" width="24" height="24" fill="#0A6629">
                <use href="#icon-check-circle-stroke"></use>
            </svg>
            <?php echo _e('Email Verified!', 'checkout'); ?>
        </span>

        <button class="checkout-head__change control-button control-button--color--main-light sf_checkout_processing_step" type="button" style="display:none" data-mode="change" data-step="Delivery-Address">
            <svg class="control-button__icon" width="24" height="24" fill="#34A34F">
                <use href="#icon-edit"></use>
            </svg>
            <?php echo _e('Edit', 'checkout'); ?>
        </button>

    </div>
    <div class="checkout-item__body filled" style="display: none;">
        <div class="checkout-item__data-block data-block">
            <div class="data-block__head">
                <p class="data-block__title"><?php echo _e('Delivery Details', 'checkout'); ?></p>
            </div>
            <p class="data-block__txt"></p>
        </div>
    </div>
    <div class="checkout-item__body changing woocommerce-checkout" data-type="billing" style="display: none;">

        <?php do_action( 'woocommerce_checkout_billing' ); ?>

        <div class="checkout-item__foot">
            <?php if ( $subscribe_status['label'] != 'none-subscribe' ) { ?>
                <!-- TODO: return "validate-address" to  button after easypost will be complete -->
                <button class="checkout-item__button button sf_checkout_update_step" data-type="billing" data-step="Schedule-Your-First-Delivery" type="button">
                    <?php echo _e('Save', 'checkout'); ?>
                </button>
            <?php }else { ?>
                <!-- TODO: return "validate-address" to  button after easypost will be complete -->
                <button class="checkout-item__button button sf_checkout_processing_step" data-step="Schedule-Your-First-Delivery" type="button">
                    <?php echo _e('Save and Continue', 'checkout'); ?>
                </button>
            <?php } ?>
        </div>

    </div>
</li>