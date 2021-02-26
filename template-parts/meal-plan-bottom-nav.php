<?php
$meal_plan_class = op_help()->meal_plan_modal;
$cart_items = $meal_plan_class->get_cart_items();
$items_quantity = 0;
$subscribe_status = op_help()->subscriptions->get_subscribe_status();

foreach ($cart_items as $cart_item) {
    $items_quantity += $cart_item['quantity'];
}
$next_delivery = op_help()->meal_plan_modal->get_delivery_date_from_previous_order();
if (!$next_delivery) {
    $next_delivery = date('D, F j', strtotime('next thursday'));
} else {
    $next_delivery = new DateTime($next_delivery);
    $next_delivery = date('D, F j', $next_delivery->getTimestamp());
}

$hide = (isset($args['display']) && $args['display']) ? 'visibility:hidden;' : '';

?>
    <!-- Meal plan -->
    <div class="meal-plan" id="meal-plan-modal" style=" <?php echo $hide; ?>">
        <div class="meal-plan__body body-meal-plan">
            <button class="body-meal-plan__close-mobile control-button control-button--no-txt control-button--close"
                    type="button">
                <svg class="control-button__icon" width="24" height="24" fill="#252728">
                    <use href="#icon-arrow-left"></use>
                </svg>
            </button>
            <button class="body-meal-plan__close control-button control-button--no-txt control-button--close"
                    type="button">
                <svg class="control-button__icon" width="24" height="24" fill="#252728">
                    <use href="#icon-times"></use>
                </svg>
            </button>

            <div class="body-meal-plan__container">

                <div class="body-meal-plan__head head-body-meal-plan">
                    <p class="head-body-meal-plan__title">Your meal plan</p>
                    <span class="head-body-meal-plan__counter meal-plan-counter meal-plan-counter--medium-filled"></span>
                </div>
<!--                <ul class="body-meal-plan__shipping-info shipping-info">-->
<!--                    <li class="shipping-info__item">-->
<!--                        <p class="shipping-info__term">Next shipment</p>-->
<!--                        <p class="shipping-info__value sign sign--color--main-light">-->
<!--                            <svg class="sign__icon" width="24" height="24" fill="#34A34F">-->
<!--                                <use href="#icon-delivery"></use>-->
<!--                            </svg>-->
<!--                            --><?php //echo $next_delivery ?>
<!--                        </p>-->
<!--                    </li>-->
<!--                </ul>-->

                <ul class="body-meal-plan__cards plan-cards">
                    <li class="plan-cards__item" data-meal-count="6">
                        <label class="plan-card__label plan-card">
                            <input class="plan-card__field visually-hidden" type="radio"
                                   name="meal_plan" <?php echo $items_quantity <= 6 ? 'checked' : '' ?>>
                            <span class="plan-card__box">
                                <span class="plan-card__inner">
                                    <span class="plan-card__title">6 meals</span>
                                    <span class="plan-card__time">per week</span>
                                </span>
                            </span>
                        </label><!-- / .plan-card-->
                    </li>
                    <li class="plan-cards__item" data-meal-count="10">
                        <label class="plan-card__label plan-card">
                            <input class="plan-card__field visually-hidden" type="radio"
                                   name="meal_plan" <?php echo ($items_quantity > 6 && $items_quantity <= 10) ? 'checked' : '' ?>>
                            <span class="plan-card__box">
                                <span class="plan-card__inner">
                                    <span class="plan-card__title">10 meals</span>
                                    <span class="plan-card__time">per week</span>
                                </span>
                            </span>
                        </label><!-- / .plan-card-->
                    </li>
                    <li class="plan-cards__item" data-meal-count="14">
                        <label class="plan-card__label plan-card">
                            <input class="plan-card__field visually-hidden" type="radio"
                                   name="meal_plan"<?php echo ($items_quantity > 10 && $items_quantity <= 14) ? 'checked' : '' ?>>
                            <span class="plan-card__box">
                                <span class="plan-card__inner">
                                    <span class="plan-card__title">14 meals</span>
                                    <span class="plan-card__time">per week</span>
                                </span>
                            </span>
                        </label><!-- / .plan-card-->
                    </li>
                </ul><!-- / .plan-cards -->
                <div class="body-meal-plan__custom-scrollbar">
                    <div class="body-meal-plan__custom-scrollbar-wr js-perfect-scrollbar">
                        <ul class="body-meal-plan__list meal-plan-list">
                            <div class="message message--full message--warning" style="display: none;">
                                <svg class="message__icon" width="24" height="24" fill="#5C3700">
                                    <use href="#icon-warning-2"></use>
                                </svg>
                                <p class="message__txt">To switch to <span class="meals-count-span">6</span> meals plan
                                    you
                                    should remove item(s) from your current selection</p>
                            </div>
                            <?php foreach ($cart_items as $cart_item): ?>
                                <li class="meal-plan-list__item meal-plan-item"
                                    data-product_id=<?php echo $cart_item['id'] ?>>
                                    <figure class="meal-plan-item__img-box">
                                        <a class="meal-plan-item__img-link" href="<?php echo $cart_item['permalink'] ?>"
                                           target="_blank">
                                            <picture>
                                                <source srcset="<?php echo $cart_item['image_url'] ?>"
                                                        type="image/webp">
                                                <img class="meal-plan-item__img"
                                                     src="<?php echo $cart_item['image_url'] ?>"
                                                     alt="">
                                            </picture>
                                        </a>
                                    </figure>
                                    <div class="meal-plan-item__center">
                                        <p class="meal-plan-item__title"><a style="text-transform: capitalize"
                                                                            href="<?php echo $cart_item['permalink'] ?>"
                                                                            target="_blank"><?php echo $cart_item['title'] ?></a>
                                        </p>
                                        <div class="nice-number">
                                            <?php if ($subscribe_status['label'] != 'locked') { ?>
                                                <input class="nice-number__field js-nice-number" type="number"
                                                       value="<?php echo $cart_item['quantity'] ?>"
                                                       min="1" name="quantity" readonly>
                                            <?php } else { ?>
                                                <div style="display: none"><input class="js-nice-number" type="hidden"
                                                                                  value="<?php echo $cart_item['quantity'] ?>"
                                                                                  name="quantity" readonly></div>
                                                <b><?php echo $cart_item['quantity'] ?>
                                                    × <?php echo get_woocommerce_currency_symbol() . number_format(($cart_item['price'] * $cart_item['quantity']), 2) ?></b>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="meal-plan-item__right">
                                        <?php if ($subscribe_status['label'] != 'locked') { ?>
                                            <p class="meal-plan-item__price"
                                               data-price="<?php echo $cart_item['price'] ?>"><?php echo get_woocommerce_currency_symbol() . number_format(($cart_item['price'] * $cart_item['quantity']), 2) ?></p>
                                        <?php } ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul><!-- / .meal-plan-list -->
                    </div>
                </div>
            </div>
        </div><!-- / .body-meal-plan -->

        <div class="meal-plan__head head-meal-plan">
            <div class="head-meal-plan__left">
                <p class="head-meal-plan__title">Meal plan</p>
                <span class="head-meal-plan__counter meal-plan-counter meal-plan-counter--medium-filled"></span>
                <button class="head-meal-plan__mobile-trigger control-button control-button--invert" type="button">
                    <span class="meals-left-mobile">
                        <span class="meals-filled"></span> out of
                        <span class="meals-plan-count"></span> selected
                    </span>
                    <svg class="control-button__icon" width="24" height="24" fill="#87898C">
                        <use href="#icon-angle-rigth-light"></use>
                    </svg>
                </button>
            </div>
            <div class="head-meal-plan__center">
                <div class="head-meal-plan__steps-and-switch">
                    <div class="steps-checker"></div>
                    <button class="head-meal-plan__mobile-switch link-2" type="button">Switch to <span
                                class="meals-plan-next"></span> meals
                    </button>
                </div>
                <p class="head-meal-plan__txt">Add <span class="meals-left"></span> meals to your weekly plan</p>
            </div>
            <div class="head-meal-plan__right">
                <a href="<?php echo get_site_url() . '/cart' ?>"
                   style="display: none;"
                   class="head-meal-plan__btn-start-plan button button--small">
                    <?php echo __('Start your plan', ''); ?>
                </a>
                <a class="head-meal-plan__mobile-explore-staples link-2"
                   href="<?php echo get_site_url() . '/groceries' ?>"
                   style="text-align: center"
                   type="button">Explore groceries</a>
                <button class="head-meal-plan__trigger" type="button">
                    <svg width="24" height="24" fill="#252728">
                        <use href="#icon-angle-up-light"></use>
                    </svg>
                </button>
            </div>
        </div>
    </div>

<?php
get_template_part('template-parts/modals/meals-filled', '', []);
