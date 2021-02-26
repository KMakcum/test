<?php
$recommended_meals = op_help()->shop->get_recommended_meals(20);
?>
<section class="product-group product-group--bg-gray">
    <div class="product-group__head head-product-group">
        <div class="container">
            <h2 class="head-product-group__title"><?php _e('Recommended meals'); ?></h2>
        </div>
    </div><!-- / .head-product-group -->
    <div class="js-product-list product-group__slider product-slider product-slider--meals swiper-container swiper-container-initialized swiper-container-horizontal swiper-container-free-mode">
        <div class="swiper-wrapper" style="transform: translate3d(-3264px, 0px, 0px); transition: all 0ms ease 0s;">
            <?php foreach ( $recommended_meals as $product ) {
		            get_template_part( 'woocommerce/content-slide-product', null, [ 'product_data' => $product ] );
             } ?>
        </div>
        <!-- If we need navigation buttons -->
        <button class="product-slider__button product-slider__button--prev round-arrow" type="button" tabindex="0" role="button" aria-label="Previous slide" aria-disabled="false">
            <svg width="32" height="32" fill="#252728">
                <use href="#icon-angle-left-light"></use>
            </svg>
        </button><!-- / .round-arrow -->
        <button class="product-slider__button product-slider__button--next round-arrow" type="button" tabindex="0" role="button" aria-label="Next slide" aria-disabled="false">
            <svg width="32" height="32" fill="#252728">
                <use href="#icon-angle-rigth-light"></use>
            </svg>
        </button><!-- / .round-arrow -->
        <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span></div><!-- / .product-slider -->
</section>
