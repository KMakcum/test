<?php
$product_data = $args['product_data'];
$added_products =  $args['added_products'];
$product_id   = $product_data['var_id'];
$image        = $product_data['_thumbnail_id_url'];
$title        = $product_data['post_title'];
$company_name = $product_data['_company_name'];

$is_sale_price       = ! empty( $product_data['_sale_price'] );
$price               = wc_price( $product_data['_regular_price'] );
$sale_price          = wc_price( $product_data['_sale_price'] );
$discount_percentage = ( $is_sale_price ) ? get_discount( $product_data['_regular_price'],
	$product_data['_sale_price'] ) : '';
$product_link        = get_permalink( $product_id );

$in_cart = false;
$added_products_ids = array_column($added_products, 'id');

if (in_array($product_id, $added_products_ids)) {
	$in_cart = true;
	$quantity_cart = array_filter($added_products, function ($item) use ($product_id) {
		return ($item['id'] === $product_id);
	});
}

if ($in_cart) {
	$added_classes = 'product-item__button--added add-button--added';
	$quantity_in_cart = ( !empty( $quantity_cart )) ? $quantity_cart[array_key_last($quantity_cart)]['quantity'] : 0;
	$data_attr = 'data-added="' . $quantity_in_cart . '"';
}

?>

<div class="swiper-slide">
    <div class="product-item">
        <a class="product-item__img-link" href="<?php echo $product_link; ?>">
			<?php if ( ! empty( $image ) ) { ?>
                <picture>
                    <img class="product-item__img"
                         src="<?php echo $image; ?>"
                         alt="">
                </picture>
			<?php } else { ?>
                <picture>
                    <img class="product-item__img" src="<?php echo wc_placeholder_img_src() ?>" alt="">
                </picture>
			<?php } ?>
        </a>
        <div class="product-item__info">
            <p class="product-item__name">
                <a class="product-item__name-link" href="<?php echo $product_link; ?>">
					<?php echo $title; ?>
                </a>

				<?php if ( ! empty( $company_name ) ) { ?>
                    <a class="product-item__name-add" href="<?php echo $product_link; ?>">
						<?php echo $company_name; ?>
                    </a>
				<?php } else { ?>
                    <a class="product-item__name-add" href="#">
						<?php _e( 'Brand name' ); ?>
                    </a>
				<?php } ?>
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

			$btn_class = 'button--plus';
			?>

            <div class="product-item__actions">
                <p class="product-item__price price-box">
					<?php
					if ( $is_sale_price ) { ?>
						<?php
						echo '<ins class="price-box__current">' . $sale_price . '</ins>';
						echo '<del class="price-box__old">' . $price . '</del>';
						echo '<span class="price-box__discount">' . $discount_percentage . '% off</span>';
					} else {
						echo '<ins class="price-box__current">' . $price . '</ins>';
					}
					?>
                </p>
	            <?php
	            $added_classes = '';
	            if ($in_cart) {
		            $added_classes = 'product-item__button--added add-button--added';
	            }
	            ?>
                <a class="product-item__button button add-button <?php echo $btn_class; ?> ajax_add_to_cart <?php echo $added_classes; ?>"
                   href="<?php esc_url( $product_link ) ?>"
	                <?php echo ($in_cart) ? $data_attr : ''; ?>
					<?php echo ( ( op_help()->subscriptions->get_subscribe_status() )['label'] == 'locked' ) ? 'disabled' : ''; ?>
                   data-product_id="<?php echo $product_id; ?>"
                >
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