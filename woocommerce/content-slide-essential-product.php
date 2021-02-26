<?php  
defined( 'ABSPATH' ) || exit;

global $product;
$old_product = $product;
$product_id  = $args['product_id']; 
$product     = wc_get_product( $product_id );

if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

$link = apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product );
?>

<div class="swiper-slide">
    <div class="product-list__item product-item">
        <a class="product-item__img-link" href="<?php echo esc_url( $link ); ?>">
            <picture>
                <?php echo $product->get_image( 'op_single_thumbnail' ); ?>
            </picture>
        </a>
        <div class="product-item__info">
            <p class="product-item__name">
                <a class="product-item__name-link" href="<?php echo esc_url( $link ); ?>"><?php echo get_the_title(); ?></a>

                <?php if ( ! empty( $product->get_meta('_company_name') ) ) : ?>
                    <a class="product-item__name-add" href="<?php echo esc_url( $link ); ?>">
                        <?php echo $product->get_meta('_company_name'); ?>
                    </a>
                <?php endif; ?>

            </p>

            <?php
            /**
             * Hook: woocommerce_after_shop_loop_item_title.
             *
             * @hooked woocommerce_template_loop_rating - 5
             * @hooked woocommerce_template_loop_price - 10
             */
            remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
            do_action( 'woocommerce_after_shop_loop_item_title' );
            $product = $old_product;
            ?>

        </div>
    </div>
</div> 
