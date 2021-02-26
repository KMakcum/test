<?php
/**
 * Checkout Payment Section
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/payment.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.3
 */

defined( 'ABSPATH' ) || exit;
?>

<!-- <ul class="fieldset__payment-methods payment-methods">
    <li class="payment-methods__item">
        <input class="payment-methods__field visually-hidden" id="checkout-apple-pay" type="radio" value="apple-pay" name="checkout[payment_methods]">
        <label class="payment-methods__label" for="checkout-apple-pay">
            <img class="payment-methods__icon" src="<?php // echo get_template_directory_uri(); ?>/assets/img/base/apple-pay--white.svg" width="61" height="24" alt="">
        </label>
    </li>
    <li class="payment-methods__item">
        <input class="payment-methods__field visually-hidden" id="checkout-g-pay" type="radio" value="g-pay" name="checkout[payment_methods]">
        <label class="payment-methods__label" for="checkout-g-pay">
            <img class="payment-methods__icon" src="<?php // echo get_template_directory_uri(); ?>/assets/img/base/g-pay--white.svg" width="61" height="24" alt="">
        </label>
    </li>
</ul>

<p class="fieldset__separator separator">Or pay with card</p> -->

<?php if ( $cards = op_help()->payment->getCards( get_current_user_id() ) ) { ?>

  <?php $card = op_help()->payment->getLatestDefaultCard( $cards ) ?>

  <?php if ($card) { ?>
    
  	<?php $card_data = op_help()->payment->getCardMeta( $card->token_id ); ?>

    <ul class="fieldset__list fields-list fields-list--payment-methods">
        <li class="fields-list__item field-box field-box--card-number filled-validate-required">
            <input class="field-box__field card-exist <?php echo strtolower($card_data['type']); ?>"
                   id="checkout-card-number" 
                   data-name="checkout"
                   type="text"
                   name="checkout[card_number]"
                   required
                   value="**** **** **** ****"
                   placeholder="**** **** **** <?php echo $card_data['last4']; ?>"
                   data-last4="<?php echo $card_data['last4']; ?>"
                   data-type="<?php echo strtolower($card_data['type']); ?>">
            <label class="field-box__label" for="checkout-card-number">Card Number</label>
        </li>
        <li class="fields-list__item field-box validate-required">
            <input class="field-box__field"
                   id="checkout-name-on-card"
                   data-name="checkout"
                   name="checkout[name_on_card]"
                   type="text"
                   required
                   value="<?php echo $card_data['cardholder'] ?>">
            <label class="field-box__label" for="checkout-name-on-card">Name on Card</label>
        </li>
        <li class="fields-list__item fields-list__item--half field-box validate-required">
            <input class="field-box__field"
                   id="checkout-exp-date"
                   data-name="checkout"
                   name="checkout[exp_date]"
                   type="text"
                   inputmode="numeric"
                   required
                   value="<?php echo $card_data['month'] ?>/<?php echo $card_data['year'] ?>">
            <label class="field-box__label" for="checkout-exp-date">Exp. Date</label>
        </li>
        <li class="fields-list__item fields-list__item--half field-box filled-validate-required">
            <input class="field-box__field cvv-exist"
                   type="text"
                   id="checkout-cvc"
                   data-name="checkout"
                   name="checkout[cvv]"
                   maxlength="3" 
                   required
                   value="***"
                   placeholder="***">
            <label class="field-box__label" for="checkout-cvc">CVV</label>
        </li>
    </ul>

    <input type="hidden" name="checkout_saved_card" value="1">

  <?php } ?>

<?php } else { ?>

    <ul class="fieldset__list fields-list fields-list--payment-methods">
        <li class="fields-list__item field-box field-box--card-number validate-required">
            <input class="field-box__field" id="checkout-card-number" data-name="checkout" type="text"
                   name="checkout[card_number]" inputmode="numeric" required>
            <label class="field-box__label" for="checkout-card-number">Card Number</label>
        </li>
        <li class="fields-list__item field-box validate-required">
            <input class="field-box__field" id="checkout-name-on-card" data-name="checkout" type="text"
                   name="checkout[name_on_card]" required>
            <label class="field-box__label" for="checkout-name-on-card">Name on Card</label>
        </li>
        <li class="fields-list__item fields-list__item--half field-box validate-required">
            <input class="field-box__field" id="checkout-exp-date" data-name="checkout" type="text"
                   name="checkout[exp_date]" inputmode="numeric" required>
            <label class="field-box__label" for="checkout-exp-date">Exp. Date</label>
        </li>
        <li class="fields-list__item fields-list__item--half field-box validate-required">
            <input class="field-box__field" id="checkout-cvc" type="text" data-name="checkout" name="checkout[cvv]"
                   inputmode="numeric" maxlength="3" required>
            <label class="field-box__label" for="checkout-cvc">CVV</label>
        </li>
    </ul>

    <input type="hidden" name="checkout_saved_card" value="0">

<?php } ?>