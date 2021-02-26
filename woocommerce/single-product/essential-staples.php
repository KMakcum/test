<?php  
global $product;
$cross_sell_ids = $product->get_cross_sell_ids();

if ( empty( $cross_sell_ids ) ) {
    return false;
}

$args       = [
    'post_type'      => 'product',
    'posts_per_page' => 15,
    'post__in'       => $cross_sell_ids,
];
$query = new WP_Query( $args );

if ( $query->have_posts() ) :
?>

    <section class="product-group product-group--bg-gray">
        <div class="product-group__head head-product-group">
            <div class="container">
                <h2 class="head-product-group__title"><?php _e( 'Essential groceries' ); ?></h2>
            </div>
        </div>
        <div class="product-group__slider product-slider product-slider--staples swiper-container">
            <div class="swiper-wrapper">

                <?php 
                    while ( $query->have_posts() ) : 

                        $query->the_post();
                        get_template_part( 'woocommerce/content-slide-essential-product', null, [ 'product_id' => $post->ID ] );

                    endwhile;
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
        </div>
    </section>

<?php  
endif;
wp_reset_postdata();
?>
