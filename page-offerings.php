<?php
/**
 * Template Name: Offerings
 */

$subscribe_status = op_help()->subscriptions->get_subscribe_status();
$offerings_content = op_help()->offerings->get_offerings_fields(get_the_ID());
$offerings_staples_subcat = op_help()->subcategories->getAllSubcategories();
$offerings_staples_subcat_default_images =
    [
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-3-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-4-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-5-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-6-extra-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-7-extra-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-8-extra-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-9-extra-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-10-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-11-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-12-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-13-extra-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-14-extra-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-15-extra-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-16-extra-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-17-small.jpg',
        get_stylesheet_directory_uri() . '/assets/img/base/offerings-18-small.jpg',
    ];

$products = op_help()->offerings->get_offerings_products();
$offerings_products = $products['Meals'];
$offerings_vitamins = $products['Vitamins'];
if ( is_user_logged_in() ) {
    $current_user = wp_get_current_user();
    $zip_code     = trim( get_user_meta( $current_user->ID, 'sf_zipcode', true ) );
    $user_name    = get_user_meta( $current_user->ID, 'first_name', true );
} else {
    $zip_code = op_help()->sf_user::op_get_zip_cookie();
}
$is_not_zip_national = op_help()->sf_user::check_zip_group( $zip_code );

get_header();


$offer_meals_link = get_site_url() . $offerings_content['meals_catalog_url'];

if (intval(get_user_meta(get_current_user_id(), 'survey_default', true))) {
    $offer_meals_link = add_query_arg('use_survey', 'on', $offer_meals_link);
}

$success_survey = op_help()->sf_user->check_survey_exist();
?>
    <?php if ( ! $success_survey ) { ?>
        <section class="offer-card-line offer-card-line--only-mobile offer-card-line--no-icon">
            <div class="container">
                <div class="offer-card-line__body">
                    <div class="offer-card-line__left">
                        <div class="offer-card-line__txt content">
                            <h2><?php _e( 'Personalize Your Experience' ); ?></h2>
                            <p><?php _e( 'Take a few minutes to complete our nutritional survey to build your personalized menu.' ); ?></p>
                        </div>
                    </div>
                    <a class="offer-card-line__button button button--small <?php echo is_user_logged_in() ? ' sf_open_survey' : 'btn-modal'; ?>" href="<?php echo is_user_logged_in() ? '#' : '#js-modal-sign-up'; ?>">Take a Survey</a>
                </div>
            </div>
            <picture>
                <source srcset="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/base/personalize-your-experience--offerings.webp" type="image/webp">
                <img class="offer-card-line__bg" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/base/personalize-your-experience--offerings.jpg" alt="">
            </picture>
        </section>
    <?php } ?>
    <main class="site-main offerings-main">
        <h1 class="visually-hidden"></h1>
        <section class="offerings">
            <div class="container">
                <ul class="offerings__list offerings-list">
                    <?php if ($offerings_products): ?>
                        <li class="offerings-list__item offering-item offering-item--extra-large">
                            <a class="offering-item__link"
                               href="<?php echo $offer_meals_link; ?>">
                                <div class="offering-item__txt content">
                                    <h2><?php echo $offerings_content['meals_catalog_title']; ?></h2>
                                    <p>
                                        <?php echo $offerings_content['meals_catalog_text']; ?>
                                    </p>
                                </div>
                                <span class="offering-item__button button button--light"><?php echo $offerings_content['meals_catalog_link']; ?></span>
                                <picture>
                                    <source srcset="<?php echo get_template_directory_uri(); ?>/assets/img/base/offerings-1.webp"
                                            type="image/webp">
                                    <img class="offering-item__bg"
                                         src="<?php echo get_template_directory_uri(); ?>/assets/img/base/offerings-1.jpg"
                                         alt="">
                                </picture>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="offerings-list__item offering-item offering-item--extra-large offering-item--extra-large-less-high">
                            <div class="offering-item__link">
                                <div class="offering-item__txt content">
                                    <h2><?php echo __('Chef Crafted Healthy Meals Coming soon') ?></h2>
                                    <p>
                                        <?php echo __('We are working hard to launch healthy meals delivery to your area.
                                        We will inform you when meal delivery is available') ?>
                                    </p>
                                </div>
                                <form class="offering-item__form form-row" action="#" method="post">
                                    <p class="form-row__box">
                                        <label class="visually-hidden"
                                               for="subs-notify"><?php echo __('Subscribe to notification') ?></label>
                                        <input class="form-row__field" id="subs-notify" type="email" name="email"
                                               placeholder="Your Email" required>
                                        <button class="form-row__button button button--light"><?php echo __('Notify me!') ?></button>
                                    </p>
                                </form><!-- / .form-row -->
                                <picture>
                                    <source srcset="<?php echo get_template_directory_uri(); ?>/assets/img/bg/offerings-1-dark.webp"
                                            type="image/webp">
                                    <img class="offering-item__bg"
                                         src="<?php echo get_template_directory_uri(); ?>/assets/img/bg/offerings-1-dark.jpg"
                                         alt="">
                                </picture>
                            </div>
                        </li><!-- / .offering-item -->
                    <?php endif; ?>
                    <li class="offerings-list__item offering-item offering-item--extra-large
                        <?php if (!$offerings_products): ?>
                            offering-item--extra-large-less-high
                        <?php endif; ?>">
                        <a class="offering-item__link"
                           href="<?php echo get_site_url() . $offerings_content['vitamins_n_supplements_url']; ?>">
                            <div class="offering-item__txt content">
                                <h2><?php echo $offerings_content['vitamins_n_supplements_title']; ?></h2>
                                <p>
                                    <?php echo $offerings_content['vitamins_n_supplements_text']; ?>
                                </p>
                            </div>
                            <span class="offering-item__button button button--light"><?php echo $offerings_content['vitamins_n_supplements_link']; ?></span>
                            <picture>
                                <source srcset="<?php echo get_template_directory_uri(); ?>/assets/img/base/offerings-2.webp"
                                        type="image/webp">
                                <img class="offering-item__bg"
                                     src="<?php echo get_template_directory_uri(); ?>/assets/img/base/offerings-2.jpg"
                                     alt="">
                            </picture>
                        </a>
                    </li>
                </ul>
                <?php if ($offerings_staples_subcat): ?>
                    <h2 class="offerings__title only-less-sm"><?php _e( 'Shop by categorie' ); ?></h2>
                    <ul class="offerings__list offerings-list">
                        <?php
                        $i = 0;
                        $render_state = 'small';
                        $render_index = 1;
                        foreach ($offerings_staples_subcat as $staples_cat) {
                            ?>
                            <li <?php echo $i === 0 ? 'id="subcategories-anchor"' : ''; ?>
                                    class="offerings-list__item offering-item offering-item--<?php echo /*$render_state*/
                                    'small'; //remove comment for 3/4 rows ?>">
                                <a class="offering-item__link"
                                   href="<?php echo get_site_url() . '/product-tag/' . $staples_cat['slug'] ?>">
                                    <div class="offering-item__txt content">
                                        <h2><?php echo $staples_cat['name'] ?></h2>
                                    </div>
                                    <span class="offering-item__button link link--color--white">Explore</span>
                                    <picture>
                                        <source srcset="<?php echo $staples_cat['img_url'] ? $staples_cat['img_url']
                                            : $offerings_staples_subcat_default_images[$i] ?>" type="image/webp">
                                        <img class="offering-item__bg"
                                             src="<?php echo $staples_cat['img_url'] ? $staples_cat['img_url']
                                                 : $offerings_staples_subcat_default_images[$i] ?>"
                                             alt="staples category image">
                                    </picture>
                                </a>
                            </li>
                            <?php
                            $render_index++;
                            $i++;
                            if ($render_index % 5 === 0 && $render_state === 'extra-small') {
                                $render_state = 'small';
                                $render_index = 1;
                            } elseif ($render_index % 4 === 0 && $render_state === 'small') {
                                $render_state = 'extra-small';
                                $render_index = 1;
                            }
                        }
                        ?>
                    </ul>
            <?php endif; ?>
            </div>
        </section>

        <?php
        //$offerings_content['meals_carousel_section_link']
        $meals_link = get_site_url() . '/product-category/meals';

        if (intval(get_user_meta(get_current_user_id(), 'survey_default', true))) {
            $meals_link = add_query_arg('use_survey', 'on', $meals_link);
        }
        ?>

        <?php if ( $offerings_products && $is_not_zip_national ): ?>
            <section class="product-group product-group--extra">
                <div class="container">
                    <div class="product-group__head product-group__head--has-margin-bottom head-product-group head-product-group--extended">
                        <h2 class="head-product-group__title"><?php echo $offerings_content['meals_carousel_section_title'] ?></h2>
                        <a class="head-product-group__button button button--extra-small button--rounded button--light"
                           href="<?php echo $meals_link; ?>"><?php echo __('See all'); ?></a>
                    </div>
                    <div class="product-group__slider product-slider-2 product-slider-2--large">
                        <div class="product-slider-2__wr js-product-slider-2 swiper-container">
                            <div class="swiper-wrapper">
                                <?php
                                // show survey
                                get_template_part('template-parts/survey', 'card');

                                $added_products = op_help()->meal_plan_modal->get_cart_all_items();

                                foreach ($offerings_products as $key => $offerings_product) {
                                    $product_id = $offerings_product['id'];

                                    $in_cart = false;
                                    $added_products_ids = array_column($added_products, 'id');

                                    if (in_array($product_id, $added_products_ids)) {
                                        $in_cart = true;
                                        $quantity_cart = array_filter($added_products, function ($item) use ($product_id) {
                                            return ($item['id'] === $product_id);
                                        });
                                    }

                                    if ($in_cart) {
                                        $added_classes = 'add-button--added';
                                        $quantity_in_cart = $quantity_cart[array_key_last($quantity_cart)]['quantity'];
                                        $data_attr = 'data-added="' . $quantity_in_cart . '"';
//										$text = ( $quantity_in_cart > 1 ) ? 'Meals' : 'Meal';
                                    }

                                    $product = wc_get_product($offerings_product['id']);
                                    ?>

                                    <div class="swiper-slide">
                                        <div class="product-item">
                                            <a class="product-item__img-link"
                                               href="<?php echo $offerings_product['link']; ?>">
                                                <picture>
                                                    <source srcset="<?php echo $offerings_product['image']; ?>"
                                                            type="image/webp">
                                                    <img class="product-item__img"
                                                         src="<?php echo $offerings_product['image']; ?>"
                                                         alt="">
                                                </picture>
                                            </a>
                                            <div class="product-item__info">
                                                <?php
                                                if (!empty($offerings_product['chef_score'])) {
//	                                                $count_hats = op_help()->shop->show_chef_hats($product->get_id());
                                                    $count_hats = $offerings_product['chef_score'];
	                                                $survey_default = op_help()->sf_user->check_survey_default();
                                                    $max_hats = op_help()->settings->min_hats_show();

                                                    if ($survey_default && $count_hats < $max_hats) {
                                                        ?>
                                                        <div class="product-item__rating-extra rating-extra js-rating--readonly--true"
                                                             data-rate-value="<?php echo $count_hats; ?>">
                                                        </div>
                                                    <?php } elseif ($survey_default) { ?>
                                                        <p class="product-item__label label label--best">
                                                            <svg class="label__icon" width="16" height="16"
                                                                 fill="#34A34F">
                                                                <use href="#icon-cap"></use>
                                                            </svg>
                                                            <?php echo __('Best for you'); ?>
                                                        </p>
                                                    <?php }
                                                } ?>
                                                <p class="product-item__name">
                                                    <a class="product-item__name-link"
                                                       href="<?php echo $offerings_product['link']; ?>">
                                                        <?php echo $offerings_product['title']; ?>
                                                    </a>
                                                </p>
                                                <?php
                                                /**
                                                 * Hook: woocommerce_after_shop_loop_item_title.
                                                 *
                                                 * @hooked woocommerce_template_loop_rating - 5
                                                 * @hooked woocommerce_template_loop_price - 10
                                                 */
                                                remove_action('woocommerce_after_shop_loop_item_title',
                                                    'woocommerce_template_loop_price', 10);
                                                do_action('woocommerce_after_shop_loop_item_title');
                                                ?>
                                                <div class="product-item__actions">
                                                    <p class="product-item__price price-box">
                                                        <ins class="price-box__current"><?php echo get_woocommerce_currency_symbol() . number_format($offerings_product['price']['current'],
                                                                    2) ?></ins>
                                                        <?php {
                                                            if (isset($offerings_product['price']['old'])) { ?>
                                                                <del class="price-box__old"><?php echo get_woocommerce_currency_symbol() . number_format($offerings_product['price']['old'],
                                                                            2) ?></del>
                                                                <span class="price-box__discount">
                                                        <?php echo get_discount($offerings_product['price']['old'],
                                                                $offerings_product['price']['current']) . '% off'; ?>
                                                    </span>
                                                            <?php }
                                                        } ?>
                                                    </p><!-- / .price-box -->
                                                    <?php
                                                    $added_classes = '';
                                                    if ($in_cart) {
                                                        $added_classes = 'product-item__button--added add-button--added';
                                                    }
                                                    ?>
                                                    <a class="meals-mpw product-item__button button add-button button--small ajax_add_to_cart add_to_cart_button product_type_variations <?php echo $added_classes; ?>"
                                                       href="<?php echo $offerings_product['link']; ?>"
                                                       data-quantity="1"
                                                        <?php echo ($in_cart) ? $data_attr : ''; ?>
                                                        <?php echo ($subscribe_status['label'] == 'locked') ? 'disabled' : ''; ?>
                                                       data-product_id="<?php echo $offerings_product['id']; ?>">
                                                        <span class="add-button__txt-1"><?php echo __('Add to your plan') ?></span>
                                                        <?php if ($in_cart) { ?>
                                                            <span class="add-button__txt-2">Added</span>
                                                        <?php } else { ?>
                                                            <span class="add-button__txt-2">Added</span>
                                                        <?php } ?>

                                                        <svg class="add-button__icon" width="24" height="24"
                                                             fill="#fff">
                                                            <use href="#icon-check-circle-stroke"></use>
                                                        </svg>
                                                    </a>
                                                </div>
                                                <ul class="product-item__badges badges">
                                                    <?php
                                                    foreach ($offerings_product['badges'] as $badge) { ?>
                                                        <li class="badges__item"
                                                            data-tippy-content="<?php echo $badge['title']; ?>">
                                                            <?php echo '<img src="' . wp_get_attachment_image_url($badge['icon_contains'],
                                                                    'full') . '" alt="icon">';
                                                            ?>
                                                        </li>
                                                    <?php } ?>
                                                </ul><!-- / .badges -->
                                            </div>
                                        </div><!-- / .product-item -->
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <!-- If we need navigation buttons -->
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
                    </div><!-- / .product-slider-2 -->
                </div>
            </section><!-- / .product-group -->
        <?php endif; ?>
        <?php if ($offerings_vitamins): ?>
            <section class="product-group product-group--extra js-product-list">
                <div class="container">
                    <div class="product-group__head product-group__head--has-margin-bottom head-product-group head-product-group--extended">

                        <h2 class="head-product-group__title"><?php echo $offerings_content['vitamins_supplements_carousel_section_title'] ?></h2>
                        <a class="head-product-group__button button button--extra-small button--rounded button--light"
                           href="<?php echo $offerings_content['vitamins_supplements_carousel_section_link'] ?
                               $offerings_content['vitamins_supplements_carousel_section_link'] : get_site_url() . '/product-category/vitamins' ?>">
                            <?php echo __('See all'); ?></a>
                    </div>
                    <div class="product-group__slider product-slider-2 product-slider-2--medium">
                        <div class="product-slider-2__wr js-product-slider-2 swiper-container">
                            <div class="swiper-wrapper">
                                <?php
                                //show survey
                                if (!$offerings_products) {
                                    get_template_part('template-parts/survey', 'card-vitamins');
                                }

                                $added_products = op_help()->meal_plan_modal->get_cart_all_items();
                                foreach ($offerings_vitamins as $offerings_vitamin) {

                                    $product_id = $offerings_vitamin['id'];

                                    $in_cart = false;
                                    $added_products_ids = array_column($added_products, 'id');

                                    if (in_array($product_id, $added_products_ids)) {
                                        $in_cart = true;
                                        $quantity_cart = array_filter($added_products, function ($item) use ($product_id) {
                                            return ($item['id'] === $product_id);
                                        });
                                    }

                                    if ($in_cart) {
                                        $added_classes = 'add-button--added';
                                        $quantity_in_cart = ( !empty( $quantity_cart )) ? $quantity_cart[array_key_last($quantity_cart)]['quantity'] : 0;
                                        $data_attr = 'data-added="' . $quantity_in_cart . '"';
//										$text = ( $quantity_in_cart > 1 ) ? 'Meals' : 'Meal';
                                    }

                                    $product = wc_get_product($offerings_vitamin['id']);
                                    ?>
                                    <div class="swiper-slide">
                                        <div class="product-item">
                                            <a class="product-item__img-link"
                                               href="<?php echo $offerings_vitamin['link']; ?>">
                                                <picture>
                                                    <source srcset="<?php echo $offerings_vitamin['image']; ?>"
                                                            type="image/webp">
                                                    <img class="product-item__img"
                                                         src="<?php echo $offerings_vitamin['image']; ?>"
                                                         alt="Vitamin image">
                                                </picture>
                                            </a>
                                            <div class="product-item__info">
                                                <p class="product-item__name">
                                                    <a class="product-item__name-link"
                                                       href="<?php echo $offerings_vitamin['link']; ?>"><?php echo $offerings_vitamin['title']; ?></a>
                                                    <?php //TODO Implement company to product ?>
                                                    <!--                                        <a class="product-item__name-add"-->
                                                    <!--                                           href="#">-->
                                                    <?php //echo __('Supplements company') ?><!--</a>-->
                                                </p>
                                                <?php
                                                /**
                                                 * Hook: woocommerce_after_shop_loop_item_title.
                                                 *
                                                 * @hooked woocommerce_template_loop_rating - 5
                                                 * @hooked woocommerce_template_loop_price - 10
                                                 */
                                                remove_action('woocommerce_after_shop_loop_item_title',
                                                    'woocommerce_template_loop_price', 10);
                                                do_action('woocommerce_after_shop_loop_item_title');
                                                ?>

                                                <div class="product-item__actions">
                                                    <p class="product-item__price price-box">
                                                        <ins class="price-box__current"><?php echo get_woocommerce_currency_symbol() . number_format($offerings_vitamin['price']['current'],
                                                                    2) ?></ins>
                                                        <?php {
                                                            if (!empty($offerings_vitamin['price']['old'])) { ?>
                                                                <del class="price-box__old"><?php echo get_woocommerce_currency_symbol() . number_format($offerings_vitamin['price']['old'],
                                                                            2) ?></del>
                                                                <span class="price-box__discount">
                                                        <?php echo 100 - round(number_format((float)$offerings_vitamin['price']['current'],
                                                                    2) / number_format((float)$offerings_vitamin['price']['old'],
                                                                    2) * 100) . '% off'
                                                        ?>
                                                    </span>
                                                            <?php }
                                                        } ?>
                                                    </p><!-- / .price-box -->
                                                    <?php
                                                    $added_classes = '';
                                                    if ($in_cart) {
                                                        $added_classes = 'product-item__button--added add-button--added';
                                                    }
                                                    ?>
                                                    <a class="product-item__button button button--plus add-button ajax_add_to_cart <?php echo $added_classes; ?>"
                                                       href="<?php echo $offerings_vitamin['link']; ?>"
                                                       data-quantity="1"
                                                        <?php echo ($in_cart) ? $data_attr : ''; ?>
                                                        <?php echo ($subscribe_status['label'] == 'locked') ? 'disabled' : ''; ?>
                                                       data-product_id="<?php echo $offerings_vitamin['id']; ?>">
                                                        <span class="visually-hidden"><?php echo __('Add to cart') ?></span>
                                                        <svg class="icon-plus" width="24" height="24" fill="#fff">
                                                            <use href="#icon-plus"></use>
                                                        </svg>
                                                        <svg class="add-button__icon" width="24" height="24"
                                                             fill="#fff">
                                                            <use href="#icon-check-circle-stroke"></use>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div><!-- / .product-item -->
                                    </div>
                                <?php } ?>
                            </div>
                            <!-- If we need navigation buttons -->
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
                        </div>
                    </div>
                </div>
            </section><!-- / .product-group -->
        <?php endif; ?>
    </main><!-- / .site-main .offerings-main  -->
<?php
get_template_part('template-parts/meal-plan-bottom-nav', '', ['display' => 'none']);
get_footer();
