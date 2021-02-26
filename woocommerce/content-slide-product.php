<?php
defined( 'ABSPATH' ) || exit;

$product_data = $args['product_data'];
$is_similar   = $args['similar'];
$product_id   = $product_data['var_id'];

// Ensure visibility.
if ( empty( $product_data ) ) {
	return;
}

$is_variation = ( $product_data['type'] === 'variation' );

if ( $is_variation ) {
	$link = op_help()->variations->rules->variationLink( $product_id );
}

// different arrays
if ( $is_similar ) {
	$image = $product_data['data_ext']['_thumbnail_id_url'];
	$title = $product_data['data_ext']['op_post_title'];
	$badges = $product_data['data']['badges'];
	$is_sale_price = ! empty( $product_data['data_ext']['_sale_price'] );
	$price = wc_price( $product_data['data_ext']['_regular_price'] );
	$sale_price = wc_price( $product_data['data_ext']['_sale_price'] );
	$discount_percentage = ( $is_sale_price ) ? get_discount( $product_data['data_ext']['_regular_price'], $product_data['data_ext']['_sale_price'] ) : '';
} else {
	$image = $product_data['_thumbnail_id_url'];
	$title = $product_data['op_post_title'];
	$badges = $product_data['badges'];
	$is_sale_price = ! empty( $product_data['_sale_price'] );
	$price = wc_price( $product_data['_regular_price'] );
	$sale_price = wc_price( $product_data['_sale_price'] );
	$discount_percentage = ( $is_sale_price ) ? get_discount( $product_data['_regular_price'], $product_data['_sale_price'] ) : '';
}

$in_cart            = false;
$added_products     = op_help()->meal_plan_modal->get_cart_all_items();
$added_products_ids = array_column( $added_products, 'id' );

if ( $is_variation ) {
	if ( in_array( $product_id, $added_products_ids ) ) {
		$in_cart       = true;
		$quantity_cart = array_filter( $added_products, function ( $item ) use ( $product_id ) {
			return ( $item['id'] === $product_id );
		} );
	}
}

$added_classes = '';

if ( $in_cart ) {
	$added_classes    = 'add-button--added';
	$quantity_in_cart = $quantity_cart[ array_key_last( $quantity_cart ) ]['quantity'];
	$data_attr        = 'data-added="' . $quantity_in_cart . '"';
}

?>

    <div class="swiper-slide">
        <div class="product-item">
            <a class="product-item__img-link" href="<?php echo esc_url( $link ); ?>">
                <picture>
                    <img class="product-item__img" src="<?php echo $image ?>" alt="">
                </picture>
            </a>
            <div class="product-item__info">

                <p class="product-item__name">
                    <a class="product-item__name-link" href="<?php echo esc_url( $link ); ?>">
						<?php echo esc_html( $title ) ?>
                    </a>
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
				?>

				<?php
				if ( $is_variation ) {

					$count_hats     = $product_data['chef_score'];
					$success_survey = op_help()->sf_user->check_survey_exist();
					$survey_default = op_help()->sf_user->check_survey_default();
					$max_hats       = op_help()->settings->min_hats_show();

					if ( $survey_default && $success_survey && $count_hats < $max_hats ) {?>
                        <div class="product-item__rating-extra rating-extra js-rating--readonly--true"
                             data-rate-value="<?php echo $count_hats; ?>">
                        </div>
					<?php } elseif ( $survey_default && $success_survey ) { ?>
                        <p class="product-item__label label label--best">
                            <svg class="label__icon" width="16" height="16" fill="#34A34F">
                                <use href="#icon-cap"></use>
                            </svg>
							<?php echo __( 'Best for you' ); ?>
                        </p>
					<?php } ?>

				<?php } ?>

                <div class="product-item__actions">
                    <p class="product-item__price price-box">

						<?php
						if ( $is_sale_price ) { ?>
							<?php
							echo '<ins class="price-box__current">' . $price . '</ins>';
							echo '<del class="price-box__old">' . $sale_price . '</del>';
							echo '<span class="price-box__discount">' . $discount_percentage . '% off</span>';
						} else {
							echo '<ins class="price-box__current">' . $price . '</ins>';
						}
						?>

                    </p>

                    <a class="product-item__button button button--small ajax_add_to_cart add-button <?php echo $added_classes; ?>"
                       href="<?php echo esc_url( $link ) ?>"
                       data-quantity="1"
	                    <?php echo ( $in_cart ) ? $data_attr : ''; ?>
						<?php //echo ( ( op_help()->subscriptions->get_subscribe_status() )['label'] == 'locked' ) ? 'disabled' : ''; ?>
                       data-product_id="<?php echo $product_data['var_id']?>"
                    >
						<?php if ( $is_variation ) { ?>
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

				<?php if ( $is_variation && ! empty( $badges ) ) : ?>

                    <ul class="product-item__badges badges">

						<?php foreach ( $badges as $badge ) { ?>
                            <li class="badges__item" data-tippy-content="<?php echo $badge['title']; ?>">
								<?php
                                echo '<img src="' . wp_get_attachment_image_url( $badge['icon_contains'],
                                        'full' ) . '" alt="icon">';
								?>
                            </li>
						<?php } ?>

                    </ul>

				<?php endif; ?>

            </div>
        </div>
    </div>