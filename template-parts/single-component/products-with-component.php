<?php
$offerings_products = $args['products'];
$title = $args['product_name']

?>

<section class="product-group product-group--bg-gray">
    <div class="product-group__head head-product-group">
        <div class="container">
            <h2 class="head-product-group__title"><?php echo __('Popular meals with ') . __($title) ?></h2>
        </div>
    </div><!-- / .head-product-group -->
    <div class="product-group__slider product-slider product-slider--meals swiper-container">
        <div class="swiper-wrapper">
            <?php
            foreach ($offerings_products as $key => $offerings_product) {
                $product = wc_get_product($offerings_product['id']);
                ?>
                <div class="swiper-slide">
                    <div class="product-item">
                        <a class="product-item__img-link" href="<?php echo $offerings_product['link']; ?>">
                            <picture>
                                <source srcset="<?php echo $offerings_product['image']; ?>" type="image/webp">
                                <img class="product-item__img" src="<?php echo $offerings_product['image']; ?>" alt="">
                            </picture>
                        </a>
                        <div class="product-item__info">

                            <?php
                            if ($product->is_type('variation')) {
	                            $count_hats     = op_help()->shop->show_chef_hats( $product->get_id() );
	                            $survey_default = op_help()->sf_user->check_survey_default();
	                            $success_survey = op_help()->sf_user->check_survey_exist();
	                            $max_hats = op_help()->settings->min_hats_show();

                                if ($survey_default && $success_survey && $count_hats < $max_hats) { ?>
                                    <div class="product-item__rating-extra rating-extra js-rating--readonly--true"
                                         data-rate-value="<?php echo $count_hats; ?>">
                                    </div>
                                <?php } else if ( $survey_default && $success_survey) { ?>
                                    <p class="product-item__label label label--best">
                                        <svg class="label__icon" width="16" height="16" fill="#34A34F">
                                            <use href="#icon-cap"></use>
                                        </svg>
                                        <?php echo __('Best for you'); ?>
                                    </p>
                                    <?php
                                }

                            }
                            ?>

                            <p class="product-item__name">
                                <a class="product-item__name-link" href="<?php echo $offerings_product['link']; ?>">
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
                            remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
                            do_action('woocommerce_after_shop_loop_item_title');
                            ?>
                            <div class="product-item__actions">
                                <p class="product-item__price price-box">
                                    <ins class="price-box__current"><?php echo $offerings_product['price']['current'] . get_woocommerce_currency_symbol() ?></ins>
                                    <?php {
                                        if ($offerings_product['price']['old']) { ?>
                                            <del class="price-box__old"><?php echo $offerings_product['price']['old'] . get_woocommerce_currency_symbol() ?></del>
                                            <span class="price-box__discount">
                                                        <?php echo floor((($offerings_product['price']['old'] - $offerings_product['price']['current']) / $offerings_product['price']['current']) * 100) . '% off'
                                                        ?>
                                                    </span>
                                        <?php }
                                    } ?>
                                </p><!-- / .price-box -->
                                <a class="product-item__button button button--small ajax_add_to_cart add_to_cart_button product_type_variations"
                                   href="<?php echo $offerings_product['link']; ?>"
                                   data-quantity="1"
	                                <?php echo ( ( op_help()->subscriptions->get_subscribe_status() )['label'] == 'locked' ) ? 'disabled' : ''; ?>
                                   data-product_id="<?php echo $offerings_product['id']; ?>">
                                    <?php echo __('Add to your plan') ?>
                                </a>
                            </div>
                            <ul class="product-item__badges badges">
                                <?php
                                $badges = carbon_get_theme_option('op_variations_badges');
                                $cached_data = op_help()->global_cache->get($offerings_product['id']);
                                $components = array_map(function ($item) {
                                    return carbon_get_term_meta($item, 'op_variations_component_badges');
                                }, (array)$cached_data['data']['components']);
                                if (!empty($spicy)) {
                                    $components = array_map(function ($item) {
                                        $key = array_search('spicy', $item);
                                        if (is_numeric($key)) {
                                            unset($item[$key]);
                                        }
                                        return $item;
                                    }, $components);
                                    $spicy_data = array_filter($badges, function ($item) {
                                        return ($item['slug'] == 'spicy');
                                    });
                                    $spicy_data = $spicy_data[array_key_last($spicy_data)];
                                }
                                $same_badges = array_intersect($components['pa_part-1'], $components['pa_part-2'], $components['pa_part-3']);
                                foreach ($badges as $badge) { ?>
                                    <?php if (in_array($badge['slug'], $same_badges)) : ?>
                                        <li class="badges__item"
                                            data-tippy-content="<?php echo $badge['title']; ?>">
                                            <?php
                                            if (get_post_mime_type($badge['icon_contains']) == 'image/svg+xml') :
                                                echo file_get_contents(get_attached_file($badge['icon_contains'], 'full'));
                                            elseif (get_post_mime_type($badge['icon_contains']) == 'image/png') :
                                                echo '<img src="' . wp_get_attachment_image_url($badge['icon_contains'],
                                                        'full') . '" alt="icon">';
                                            endif;
                                            ?>
                                        </li>
                                    <?php endif; ?>
                                <?php } ?>
                                <?php if (isset($spicy_data) && !empty($spicy_data)) { ?>
                                    <li class="badges__item"
                                        data-tippy-content="<?php echo $spicy_data['title'] ?>">
                                        <?php
                                        if (get_post_mime_type($spicy_data['icon_contains']) == 'image/svg+xml') :
                                            echo file_get_contents(get_attached_file($spicy_data['icon_contains'], 'full'));
                                        elseif (get_post_mime_type($spicy_data['icon_contains']) == 'image/png') :
                                            echo '<img src="' . wp_get_attachment_image_url($spicy_data['icon_contains'],
                                                    'full') . '" alt="icon">';
                                        endif;
                                        ?>
                                    </li>
                                <?php } ?>
                            </ul><!-- / .badges -->
                        </div>
                    </div><!-- / .product-item -->
                </div>

            <?php } ?>
        </div>
        <!-- If we need navigation buttons -->
        <button class="product-slider__button product-slider__button--prev round-arrow" type="button">
            <svg width="32" height="32" fill="#252728">
                <use href="#icon-angle-left-light"></use>
            </svg>
        </button><!-- / .round-arrow -->
        <button class="product-slider__button product-slider__button--next round-arrow" type="button">
            <svg width="32" height="32" fill="#252728">
                <use href="#icon-angle-rigth-light"></use>
            </svg>
        </button><!-- / .round-arrow -->
    </div><!-- / .product-slider -->
</section><!-- / .product-group -->
