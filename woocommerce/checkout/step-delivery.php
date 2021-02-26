<?php
// Get subscription status
$subscribe_status = op_help()->subscriptions->get_subscribe_status();
$subscribe_delivery_date = op_help()->subscriptions->get_delivery_date();

$cart_delivery_date = op_help()->shop->get_session_cart('delivery_date', (new DateTime())->format("Y-m-d"));

// Set current date for datepicker
if ($subscribe_delivery_date) {
    $current_date = $subscribe_delivery_date->format("Y-m-d");
    $cart_delivery = $subscribe_delivery_date;
}else {
    $current_date = $cart_delivery_date;
    $cart_delivery = new DateTime( $cart_delivery_date );
}

?>

<li class="checkout-list__item checkout-item" id="Schedule-Your-First-Delivery" data-step="Schedule-Your-First-Delivery" data-step-number="2">
    <div class="checkout-item__head checkout-head">
        <span class="checkout-head__number">2</span>
        <p class="checkout-head__title">Schedule Your First Delivery</p>

        <button class="checkout-head__change control-button control-button--color--main-light sf_checkout_processing_step" type="button" style="display:none" data-mode="change" data-step="Schedule-Your-First-Delivery">
            <svg class="control-button__icon" width="24" height="24" fill="#34A34F">
                <use href="#icon-edit"></use>
            </svg>
            Edit
        </button>

    </div>
    <div class="checkout-item__body filled" style="display: none;">
        <div class="checkout-item__data-block data-block">
            <div class="data-block__head">
                <p class="data-block__title">Every <span class="dynamic-delivery-day"><?php echo esc_html($cart_delivery->format("l")); ?></span></p>
            </div>
            <p class="data-block__txt">Shipping out on: <span class="dynamic-delivery-date"><?php echo esc_html($cart_delivery->format("l, F j, Y")); ?></span></p>
        </div>
    </div>
    <div class="checkout-item__body changing" style="display: none;">
        <div class="checkout-item__datepicker datepicker js-datepicker" data-current="<?php echo esc_attr($current_date); ?>"></div>
        <div class="checkout-item__message-datepicker message message--vertical message--full message--info">
            <svg class="message__icon" width="40" height="40" fill="#0482CC">
                <use href="#icon-info-2"></use>
            </svg>
            <div class="message__txt content">
                <p>Choose your first shipment date. You will get your delivery same day every week. <br>
                You can edit, skip or cancel anytime.</p>
                <p>Your shipment should arrive within 24 to 48 hours after being shipped out</p>
            </div>
        </div>
        <div class="checkout-item__foot">
            <?php if ( $subscribe_status['label'] != 'none-subscribe' ) { ?>
                <button class="checkout-item__button button sf_checkout_update_step" data-step="Payment-Method" type="button">
                    Save
                </button>
            <?php }else { ?>
                <button class="checkout-item__button button sf_checkout_processing_step" data-step="Payment-Method" type="button">
                    Save and Continue
                </button>
            <?php  } ?>
        </div>
        <p class="checkout-item__delivery-info">
            First ship date:
            <span id="sf_first_delivery"><?php echo esc_html( $cart_delivery->format("l, F j, Y") ) ?></span>
        </p>
    </div>
</li>
