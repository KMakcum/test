<?php
/**
 * Cross-sells
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cross-sells.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( $cross_sells ) : 
?>

	<section class="cart-main__product-group product-group">
        <div class="product-group__head head-product-group">
            <div class="container">
                <h2 class="head-product-group__title"><?php esc_html_e( 'You may be interested in&hellip;', 'woocommerce' ); ?></h2>
            </div>
        </div>
        <div class="product-group__slider product-slider product-slider--staples swiper-container">
            <div class="swiper-wrapper">

                <?php woocommerce_product_loop_start(); ?>

                    <?php foreach ( $cross_sells as $cross_sell ) : ?>

                        <?php
                            $post_object = get_post( $cross_sell->get_id() );

                            setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited, Squiz.PHP.DisallowMultipleAssignments.Found

                            wc_get_template_part( 'content', 'product' );
                        ?>

                    <?php endforeach; ?>

                <?php woocommerce_product_loop_end(); ?>

                <!-- <div class="swiper-slide">
                    <div class="product-list__item product-item">
                        <a class="product-item__img-link" href="product.html">
                            <picture>
                                <source srcset="<?php echo get_template_directory_uri(); ?>/assets/img/base/staples-1.webp" type="image/webp">
                                <img class="product-item__img" src="<?php echo get_template_directory_uri(); ?>/assets/img/base/staples-1.jpg" alt="">
                            </picture>
                        </a>
                        <div class="product-item__info">
                            <p class="product-item__label label label--best">
                                <svg class="label__icon" width="16" height="16" fill="#34A34F">
                                    <use href="#icon-cap"></use>
                                </svg>
                                Best for you
                            </p>
                            <p class="product-item__name">
                                <a class="product-item__name-link" href="product.html">Beef Bone Broth</a>
                                <a class="product-item__name-add" href="product.html">Brand name</a>
                            </p>
                            <div class="product-item__review rating-and-review">
                                <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="4.5"></div>
                                <p class="rating-and-review__review">4.5/5 <a href="#">(38 reviews)</a></p>
                            </div>
                            <div class="product-item__actions">
                                <p class="product-item__price price-box">
                                    <ins class="price-box__current">$11.60</ins>
                                    <del class="price-box__old">$13.90</del>
                                    <span class="price-box__discount">40% off</span>
                                </p>
                                <a class="product-item__button button button--plus" href="product.html">
                                    <span class="visually-hidden">Add to cart</span>
                                    <svg width="24" height="24" fill="#fff">
                                        <use href="#icon-plus"></use>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div> -->

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
        </div>
    </section>

<?php
endif;

wp_reset_postdata();
