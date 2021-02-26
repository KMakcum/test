<?php
$category_title = $args['category'];
$category_content = $args['category_content'];

$cat = '';
switch ($category_title) {
    case 'Meals':
        $cat = 'meals';
        break;
    case 'Groceries':
        $cat = 'staples';
        break;
    case 'Vitamins & Supplements':
        $cat = 'vitamins';
        break;
}
$products_count = $args['prod_count'];
?>

<section class="product-group product-group--extra js-product-list">
    <div class="container">
        <div class="product-group__head head-product-group head-product-group--extended">
            <h2 class="head-product-group__title">
                <?php echo $category_title ?>&nbsp;<sup
                        class="head-product-group__quantity"><?php echo $products_count; ?></sup>
            </h2>
            <a id="all<?php echo ucfirst($cat) ?>Link"
               class="head-product-group__button button button--extra-small button--rounded button--light see-all-button"
               href="#"><?php echo __('See all') ?></a>

        </div>
        <div class="product-group__slider product-slider-2 product-slider-2--large">
            <div class="product-slider-2__wr js-product-slider-2 swiper-container">
                <div class="swiper-wrapper">
                    <?php foreach ($category_content as $item) {
                        $content = op_help()->global_cache->get_cached_product($item['id']); ?>
                        <div class="swiper-slide">
                            <div class="product-item">
                                <a class="product-item__img-link"
                                   href="<?php echo get_the_permalink($content['var_id']); ?>">
                                    <picture>
                                        <source srcset="<?php echo $content['_thumbnail_id_url'] ?>"
                                                type="image/webp">
                                        <img class="product-item__img"
                                             src="<?php $content['_thumbnail_id_url'] ?>" alt="image">
                                    </picture>
                                </a>
                                <div class="product-item__info">
                                    <?php if ($cat === 'meals'):
                                        if (!empty($content['chef_score'])) {
                                            $count_hats = $content['chef_score'];
                                            $success_survey = op_help()->sf_user->check_survey_exist();
                                            $max_hats = op_help()->settings->min_hats_show();

                                            if ($success_survey && $count_hats < $max_hats) {
                                                ?>
                                                <div class="product-item__rating-extra rating-extra js-rating--readonly--true"
                                                     data-rate-value="<?php echo $count_hats; ?>">
                                                </div>
                                            <?php } elseif ($success_survey) { ?>
                                                <p class="product-item__label label label--best">
                                                    <svg class="label__icon" width="16" height="16"
                                                         fill="#34A34F">
                                                        <use href="#icon-cap"></use>
                                                    </svg>
                                                    <?php echo __('Best for you'); ?>
                                                </p>
                                            <?php }
                                        } ?>
                                    <?php endif; ?>
                                    <p class="product-item__name">
                                        <a class="product-item__name-link"
                                           href="<?php echo get_the_permalink($content['var_id']) ?>">
                                            <?php echo $content['op_post_title'] ?
                                                $content['op_post_title'] :
                                                $content['post_title'] ?></a>
                                    </p>
                                    <div class="product-item__review rating-and-review">
                                        <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="0"></div>
                                        <span class="rating-and-review__review">(0)</span>
                                    </div>
                                    <div class="product-item__actions">
                                        <p class="product-item__price price-box">
                                            <ins class="price-box__current">
                                                <?php echo $content['_sale_price'] ? get_woocommerce_currency_symbol() . number_format($content['_sale_price'], 2)
                                                    : get_woocommerce_currency_symbol() . number_format($content['_regular_price'], 2) ?></ins>
                                            <?php {
                                                if (isset($content['_sale_price'])) { ?>
                                                    <del class="price-box__old"><?php echo get_woocommerce_currency_symbol() . number_format($content['_regular_price'],
                                                                2) ?></del>
                                                    <span class="price-box__discount">
                                                        <?php echo get_discount($content['_regular_price'],
                                                                $content['_sale_price']) . '% off'; ?>
                                                    </span>
                                                <?php }
                                            } ?>
                                        </p><!-- / .price-box -->
                                        <?php if ($cat === 'meals'): ?>
                                            <a class="product-item__button
                                            button
                                            button--small
                                            add-button
                                            add_to_cart_button
                                            ajax_add_to_cart
                                            product_type_variations
                                            meals-mpw"
                                               href="<?php echo get_the_permalink($content['var_id']) ?>"
                                               data-quantity="1" data-product_id="<?php echo $content['var_id'] ?>">
                                                <span class="add-button__txt-1">
                                                    <?php echo __('Add to your plan') ?>
                                                </span>
                                                <span class="add-button__txt-2">
                                                    <?php echo __('1 Item Added') ?>
                                                </span>
                                                <svg class="add-button__icon" width="24" height="24" fill="#fff">
                                                    <use href="#icon-check-circle-stroke"></use>
                                                </svg>
                                            </a><!-- / .add-button -->
                                        <?php else: ?>
                                            <a class="
                                            product-item__button
                                            button
                                            button--plus
                                            ajax_add_to_cart
                                            add-button"
                                               href="<?php echo get_the_permalink($content['var_id']) ?>"
                                               data-quantity="1"
                                               data-product_id="<?php echo $content['var_id'] ?>">
                                                <span class="visually-hidden"><?php echo __('Add to cart') ?></span>
                                                <svg width="24" height="24" fill="#fff" class="icon-plus">
                                                    <use href="#icon-plus"></use>
                                                </svg>
                                                <svg class="add-button__icon" width="24" height="24"
                                                     fill="#fff">
                                                    <use href="#icon-check-circle-stroke"></use>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($cat === 'meals'): ?>
                                        <ul class="product-item__badges badges">
                                            <?php
                                            foreach ($content['badges'] as $badge) { ?>
                                                <li class="badges__item"
                                                    data-tippy-content="<?php echo $badge['title']; ?>">
                                                    <?php echo '<img src="' . wp_get_attachment_image_url($badge['icon_contains'],
                                                            'full') . '" alt="icon">';
                                                    ?>
                                                </li>
                                            <?php } ?>
                                        </ul><!-- / .badges -->
                                    <?php endif ?>
                                </div>
                            </div><!-- / .product-item -->
                        </div>
                    <?php } ?>
                </div>
                <button class="product-slider-2__button product-slider-2__button--prev round-arrow"
                        type="button">
                    <svg width="32" height="32" fill="#252728">
                        <use href="#icon-angle-left-light"></use>
                    </svg>
                </button><!-- / .round-arrow -->
                <button class="product-slider-2__button product-slider-2__button--next round-arrow"
                        type="button">
                    <svg width="32" height="32" fill="#252728">
                        <use href="#icon-angle-rigth-light"></use>
                    </svg>
                </button><!-- / .round-arrow -->
            </div><!-- / .product-slider -->
        </div>
</section><!-- / .product-group -->