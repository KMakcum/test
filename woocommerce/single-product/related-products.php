<?php
global $product, $variation, $include_groceries;

$is_variation = ! empty( $variation );
$this_object  = function () use ( $is_variation, $product, $variation ) {
	if ( $is_variation ) {
		return $variation;
	}

	return $product;
};

if ( $include_groceries ) {
	$similar_meals = op_help()->shop->get_similar_groceries( $this_object()->get_id(), 20 );
} else {
	$available_items = op_help()->shop->get_similar_meals( $this_object()->get_id() );
	$similar_meals = $available_items;
}
if ( empty( $similar_meals ) ) {
	return;
}

$classes = 'js-product-list product-slider--meals';

if ( $include_groceries ) {
	$classes = 'js-product-list product-slider--staples';
}

$added_products = op_help()->meal_plan_modal->get_cart_all_items();
?>

<section class="product-group product-group--bg-gray">
    <div class="product-group__head head-product-group">
        <div class="container">
            <h2 class="head-product-group__title"><?php $include_groceries ? _e('Similar groceries') : _e('Similar meals'); ?></h2>
        </div>
    </div><!-- / .head-product-group -->


    <div class="product-group__slider product-slider swiper-container <?php echo $classes; ?>">
        <div class="swiper-wrapper">

        <?php
            if ( $include_groceries ) {
	            foreach ( $similar_meals as $item ) {
		            get_template_part( 'woocommerce/content-slide-grocceries', null, [ 'product_data' => $item, 'added_products' => $added_products ] );
	            }
            } else {
	            foreach ( $available_items as $item ) {
		            get_template_part( 'woocommerce/content-slide-product', null, [ 'product_data' => $item, 'similar' => true, 'added_products' => $added_products ] );
	            }
            }
        ?>

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
