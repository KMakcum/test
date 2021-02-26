<?php
$products = $args;

$added_products = op_help()->meal_plan_modal->get_cart_all_items();

foreach ( $products as $product_ ) {

    $product_item = op_help()->global_cache->get_cached_product( $product_['id'] );

	$in_cart = false;

	$added_products_ids = array_column( $added_products, 'id' );
	$pro_id             = $product_item['var_id'];

	if ( in_array( $product_item['var_id'], $added_products_ids ) ) {
		$in_cart       = true;
		$quantity_cart = array_filter( $added_products, function ( $item ) use ( $pro_id ) {
			return ( $item['id'] === $pro_id );
		} );
	}

	$added_classes = '';

	if ($in_cart) {
		$added_classes = 'product-item__button--added add-button--added';
		$quantity_in_cart = $quantity_cart[ array_key_last( $quantity_cart ) ]['quantity'];
		$data_attr = 'data-added="'. $quantity_in_cart .'"';
	}

	$product = wc_get_product( $product_item['var_id'] );
	if ( empty( $product ) ) {
	    continue;
    }
	$badges  = $product_item['badges'];

	if ( $product_item['type'] == 'variation' ) {
		$link = op_help()->variations->rules->variationLink( $product_item['var_id'] );
	} else {
//		$link = apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product );
		$link = get_the_permalink( $product_item['var_id'] );
	}
	?>
    <li <?php wc_product_class( 'product-list__item product-item', $product ); ?>>
        <a class="product-item__img-link" href="<?php echo esc_url( $link ); ?>">

			<?php
			/**
			 * Hook: woocommerce_before_shop_loop_item_title.
			 *
			 * @hooked woocommerce_show_product_loop_sale_flash - 10
			 * @hooked woocommerce_template_loop_product_thumbnail - 10
			 */
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail',
				10 );
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
			do_action( 'woocommerce_before_shop_loop_item_title' );
			?>
			<?php if ( $product_item['_thumbnail_id_url'] ) { ?>
                <picture>
                    <img src="<?php echo $product_item['_thumbnail_id_url']; ?>"
                         alt="<?php echo ( $product_item['op_post_title'] ) ? $product_item['op_post_title'] : $product_item['post_title']; ?>">
                </picture>
			<?php } else { ?>
                <picture>
                    <img src="<?php echo wc_placeholder_img_src() ?>" alt="">
                </picture>
			<?php } ?>
        </a>

        <div class="product-item__info">
			<?php
			if ( $product_item['type'] == 'variation' ) {

				$product_id = $product->get_id();

				$count_hats     = $product_['cs'];
				$survey_default = op_help()->sf_user->check_survey_default();
				$success_survey = op_help()->sf_user->check_survey_exist();
				$max_hats       = op_help()->settings->min_hats_show();

				if ( $survey_default && $success_survey && $count_hats < $max_hats ) {
					?>
                    <div class="product-item__rating-extra rating-extra js-rating--readonly--true"
                         data-rate-value="<?php echo $count_hats; ?>">
                    </div>

				<?php } elseif ($survey_default && $success_survey ) { ?>
                    <p class="product-item__label label label--best">
                        <svg class="label__icon" width="16" height="16" fill="#34A34F">
                            <use href="#icon-cap"></use>
                        </svg>
                        Best for you
                    </p>
				<?php } ?>

			<?php } ?>

            <p class="product-item__name">
                <a class="product-item__name-link" href="<?php echo esc_url( $link ); ?>">
                    <?php echo ( $product_item['op_post_title'] ) ? $product_item['op_post_title'] : $product_item['post_title']; ?>
                </a>

				<?php /*if ( ! empty( $company_name = $product->get_meta( '_company_name' ) ) ) : ?>
					<a class="product-item__name-add" href="<?php echo esc_url( $link ); ?>">
						<?php echo $company_name; ?>
					</a>
				<?php endif;*/ ?>
            </p>

			<?php
			$btn_class = ( $product_item['type'] == 'variation' ) ? 'button--small' : 'button--plus';
			?>

            <div class="product-item__review rating-and-review">
                <div class="rating-and-review__rating rating js-rating--readonly--true" data-rate-value="0"></div>
                <span class="rating-and-review__review">(0)</span>
            </div>

            <div class="product-item__actions">
                <p class="product-item__price price-box">
					<?php
                    if ( empty( $product_item['_price'] ) ) {
                        $price = apply_filters( 'woocommerce_empty_price_html', '', $product );
                        echo '<ins class="price-box__current">' . $price . '</ins>';
                    } elseif ( $product->is_on_sale() ) { ?>
                        <?php
                        echo '<ins class="price-box__current">' . wc_price( $product_item['_sale_price'] ) . $price_suffix . '</ins>';
                        echo '<del class="price-box__old">' . wc_price( $product_item['_regular_price'] ) . $price_suffix . '</del>';
                        echo '<span class="price-box__discount">' . get_discount( $product_item['_regular_price'],
                                $product_item['_sale_price'] ) . '% off</span>';
                    } else {
                        $price = wc_price( wc_get_price_to_display( $product ) ) . $price_suffix;
                        echo '<ins class="price-box__current">' . $price . '</ins>';
                    }
					?>
                </p>
                <a class="product-item__button button add-button meals-mpw <?php echo $btn_class; ?> ajax_add_to_cart <?php echo $added_classes; ?>"
                   href="<?php esc_url( get_the_permalink( $product_item['var_id'] ) ) ?>"
                   data-quantity="1"
					<?php echo ( ( op_help()->subscriptions->get_subscribe_status() )['label'] == 'locked' ) ? 'disabled' : ''; ?>
                   data-product_id="<?php echo $product->get_id(); ?>"
					<?php echo ( $in_cart ) ? $data_attr : ''; ?>
                >
					<?php if ( $product_item['type'] == 'variation' ) { ?>
                        <span class="add-button__txt-1">Add to your plan</span>
						<?php if ( $in_cart ) { ?>
                            <span class="add-button__txt-2">Added</span>
						<?php } else { ?>
                            <span class="add-button__txt-2">Added</span>
						<?php } ?>

                        <svg class="add-button__icon" width="24" height="24" fill="#fff">
                            <use href="#icon-check-circle-stroke"></use>
                        </svg>
					<?php } else { ?>
                        <svg class="icon-plus" width="24" height="24" fill="#fff">
                            <use href="#icon-plus"></use>
                        </svg>
                        <svg class="add-button__icon" width="24" height="24" fill="#fff">
                            <use href="#icon-check-circle-stroke"></use>
                        </svg>
					<?php } ?>
                </a>
            </div>

			<?php if ( ! empty( $badges ) ) { ?>
                <ul class="product-item__badges badges">
					<?php foreach ( $badges as $badge ) { ?>
                        <li class="badges__item" data-tippy-content="<?php echo $badge['title']; ?>">
							<?php echo '<img src="' . wp_get_attachment_image_url( $badge['icon_contains'],
									'full' ) . '" alt="icon">'; ?>
                        </li>
					<?php } ?>
                </ul>
			<?php } ?>

        </div>
    </li>

	<?php
}

