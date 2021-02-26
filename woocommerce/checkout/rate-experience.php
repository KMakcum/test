<?php $order = $args['order']; ?>

<section class="checkout-thanks__section rate-assessment">
    <div class="rate-assessment__head content">
        <h2><?php echo _e('Rate Your Experience', 'order-thank-you'); ?></h2>
        <p>
            <?php echo _e('We are constantly working on improvements to make your experience smooth and simple.
            Your feedback is so much important to help us become better. Please rate your overall experience
            and leave a feedback on what could be improved.', 'order-thank-you'); ?>
        </p>
    </div>
    <form class="rate-assessment__form form" action="#" method="post">
        <input type="hidden" name="rate-assessment[rating]">
        <input type="hidden" name="rate-assessment[order_id]" value="<?php echo $order->get_id(); ?>">
        <div class="form__rating feedback-rating">
            <p class="feedback-rating__title"><?php echo _e('How comfortable was the process?', 'order-thank-you'); ?></p>
            <div class="feedback-rating__stars rating rating--stroke js-rating--readonly--false"></div>
        </div>
        <p class="form__single-field field-box">
            <textarea class="field-box__field field-box__field--textarea js-auto-size" id="rate-assessment-message" name="rate-assessment[message]" required></textarea>
            <label class="field-box__label" for="rate-assessment-message"><?php echo _e('Share your feedback here', 'order-thank-you'); ?></label>
        </p>
        <button class="form__button button button--small"><?php echo _e('Send feedback', 'order-thank-you'); ?></button>
    </form>
</section>