<?php
global $wp, $sf_results_all_items, $sf_results_filtered_items;

$content = $args['products'];
$category_title = $args['category'];
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
$search_string = $args['search_string'];
?>
    <main class="site-main catalog-main search-results-main">
        <div class="search-results-main__back back-to-search-results">
            <div class="container">
                <a class="back-to-search-results__button control-button" href="#">
                    <svg class="control-button__icon" width="24" height="24" fill="#252728">
                        <use href="#icon-arrow-left"></use>
                    </svg>
                    <?php echo __('Back to search results') ?>
                </a>
            </div>
        </div><!-- / .back-to-search-results -->

        <section class="products">
            <div class="container">
                <div class="products__head">
                    <div class="products__title-and-page-info">
                        <h1 class="products__title"><?php echo $category_title ?></h1>&nbsp;<span
                                class="products__page-info"><span><?php
                                //TODO replace with filtered val
                                echo count($content) ?></span> /
                        <?php echo count($content); ?></span>
                    </div>

                    <?php
                    $use_survey_results = op_help()->sf_user->check_survey_default();
                    wc_get_template(
                        'catalog/recommended-switch.php',
                        [
                            'use_survey' => $use_survey_results,
                            'search_page' => true
                        ]
                    ); ?>

                </div>
                <div class="products__filter-and-sort">
                    <?php
                    if ($cat === 'meals') {
                        wc_get_template(
                            'loop/filters.php',
                            [
                                'use_survey' => $use_survey_results,
                            ]
                        );
                    }
                    op_help()->search_controller->get_sort_template();
                    ?>
                </div>

                <ul class="products__list product-list js-product-list">
                    <?php foreach ($content as $item) {
                        $product = op_help()->global_cache->get_cached_product($item['id']);
                        ?>
                        <li class="product-list__item product-item">
                            <a class="product-item__img-link"
                               href="<?php echo get_the_permalink($product['var_id']); ?>">
                                <picture>
                                    <source srcset="<?php echo $product['_thumbnail_id_url'] ?>" type="image/webp">
                                    <img class="product-item__img" src="<?php echo $product['_thumbnail_id_url'] ?>"
                                         alt="image">
                                </picture>
                            </a>

                            <div class="product-item__info">
                                <?php if ($cat === 'meals'):
                                    if (!empty($product['chef_score'])) {
                                        $count_hats = $product['chef_score'];
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
                                       href="<?php echo get_the_permalink($product['var_id']) ?>">
                                        <?php echo $product['op_post_title'] ?
                                            $product['op_post_title'] :
                                            $product['post_title'] ?></a>
                                </p>
                                <div class="product-item__review rating-and-review">
                                    <div class="rating-and-review__rating rating js-rating--readonly--true"
                                         data-rate-value="0"></div>
                                    <span class="rating-and-review__review">(0)</span>
                                </div>
                                <div class="product-item__actions">
                                    <p class="product-item__price price-box">
                                        <ins class="price-box__current">
                                            <?php echo $product['_sale_price'] ? get_woocommerce_currency_symbol() . number_format($product['_sale_price'], 2)
                                                : get_woocommerce_currency_symbol() . number_format($product['_regular_price'], 2) ?></ins>
                                        <?php {
                                            if (isset($product['_sale_price'])) { ?>
                                                <del class="price-box__old"><?php echo get_woocommerce_currency_symbol() . number_format($product['_regular_price'],
                                                            2) ?></del>
                                                <span class="price-box__discount">
                                                        <?php echo get_discount($product['_regular_price'],
                                                                $product['_sale_price']) . '% off'; ?>
                                                    </span>
                                            <?php }
                                        } ?>
                                    </p><!-- / .price-box -->
                                    <?php if ($cat === 'meals'): ?>
                                        <a class="product-item__button button button--small add-button add_to_cart_button ajax_add_to_cart product_type_variations meals-mpw"
                                           href="<?php echo get_the_permalink($content['var_id']) ?>"
                                           data-quantity="1"
                                           data-product_id="<?php echo $product['var_id'] ?>">
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
                                        <a class="product-item__button button button--plus ajax_add_to_cart add-button"
                                           href="<?php echo get_the_permalink($content['var_id']) ?>"
                                           data-quantity="1"
                                           data-product_id="<?php echo $product['var_id'] ?>">
                                            <span class="visually-hidden"><?php echo __('Add to cart') ?></span>
                                            <svg width="24" height="24" fill="#fff">
                                                <use href="#icon-plus"></use>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <?php if ($cat === 'meals'): ?>
                                    <ul class="product-item__badges badges">
                                        <?php
                                        foreach ($product['badges'] as $badge) { ?>
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
                        </li><!-- / .product-item -->
                    <?php } ?>
                </ul><!-- / .product-list -->
            </div>
        </section><!-- / .products -->
    </main><!-- / .site-main .catalog-main -->

<?php
if ($cat === 'meals') {
    get_template_part('template-parts/meal-plan-bottom-nav', '', ['display' => 'none']);
}

