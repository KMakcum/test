<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product, $wp_query, $sf_custom_filter_items;
global $added_products;

$current_taxonomy = $wp_query->get_queried_object();

// for ajax pagination
//if ( empty( $product ) ) {
//    $product_item = $args;
//    print_r($product_item);
//
//    if ( $product_item['id'] ) {
//        $product = wc_get_product( $product_item['id'] );
//    }
//}

$product_id = $product->get_id();
//print_r($product_id);

// Ensure visibility.
if ( !$product_id /*|| ! $product->is_visible()*/ ) {
	return;
}
$cached_product = op_help()->global_cache->get_cached_product( $product_id );
$badges = $cached_product['badges'];

//if ( ! empty( $sf_custom_filter_items ) ) {
//	$filtered_product_from_cache = array_filter( $sf_custom_filter_items, function ( $item ) use ( $product_id ) {
//		return $item['var_id'] == $product_id;
//	} );
//	$cached_product = $filtered_product_from_cache[ array_key_last( $filtered_product_from_cache ) ];
//	$badges             = $cached_product['data']['badges'];
//}

$in_cart = false;
$added_products_ids = array_column( $added_products, 'id' );

if ( in_array( $product_id, $added_products_ids ) ) {
    $in_cart       = true;
    $quantity_cart = array_filter( $added_products, function ( $item ) use ( $product_id ) {
        return ( $item['id'] === $product_id );
    } );
}

if ($in_cart) {
	$added_classes = 'add-button--added';
	$quantity_in_cart = $quantity_cart[ array_key_last( $quantity_cart ) ]['quantity'];
	$data_attr = 'data-added="'. $quantity_in_cart .'"';
//	$text = ( $quantity_in_cart > 1 ) ? 'Meals' : 'Meal';
}

?>

<li <?php wc_product_class( 'product-list__item product-item', $product ); ?>>
	<?php $link = apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product ); ?>

    <a class="product-item__img-link" href="<?php echo esc_url( $link ); ?>">

		<?php
		/**
		 * Hook: woocommerce_before_shop_loop_item_title.
		 *
		 * @hooked woocommerce_show_product_loop_sale_flash - 10
		 * @hooked woocommerce_template_loop_product_thumbnail - 10
		 */
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
		do_action( 'woocommerce_before_shop_loop_item_title' );
		?>
		<?php if ( $cached_product['_thumbnail_id_url'] ) { ?>
            <picture>
                <img src="<?php echo $cached_product['_thumbnail_id_url']; ?>"
                     alt="<?php echo ( $cached_product['op_post_title'] ) ? $cached_product['op_post_title'] : $cached_product['post_title']; ?>">
            </picture>
		<?php } ?>
    </a>

    <div class="product-item__info">
		<?php
		if ( $cached_product['type'] == 'variation' && 'staples' != $current_taxonomy->slug ) {

			$count_hats     = op_help()->sort_cache->get_chef_score( $product_id );
			$survey_default = op_help()->sf_user->check_survey_default();
			$success_survey = op_help()->sf_user->check_survey_exist();
			$max_hats       = op_help()->settings->min_hats_show();

			if ( $survey_default && $success_survey && $count_hats < $max_hats ) {
				?>
                <div class="product-item__rating-extra rating-extra js-rating--readonly--true"
                     data-rate-value="<?php echo $count_hats; ?>">
                </div>

			<?php } elseif ( $survey_default && $success_survey ) { ?>
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
				<?php
				// hooked by SFAddonVariations::change_title_for_variations
                if ( $cached_product['op_post_title'] ) {
                    echo $cached_product['op_post_title'];
                } else {
                    echo $cached_product['post_title'];
                }
				?>
            </a>

			<?php if ( !empty( $company_name = $cached_product['_company_name'] ) ) : ?>
                <a class="product-item__name-add" href="<?php echo esc_url( $link ); ?>">
					<?php echo $company_name; ?>
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

		$btn_class = ( isset($cached_product) && $cached_product['type'] === 'variation' ) ? 'button--small' : 'button--plus';
		?>

        <div class="product-item__actions">
            <p class="product-item__price price-box">
				<?php
                $price_suffix = $product->get_price_suffix();
				if ( empty( $cached_product['_price'] ) ) {
					$price = apply_filters( 'woocommerce_empty_price_html', '', $product );
					echo '<ins class="price-box__current">' . $price . '</ins>';
				} elseif ( $product->is_on_sale() ) { ?>
					<?php
					echo '<ins class="price-box__current">' . wc_price( $cached_product['_sale_price'] ) . $price_suffix . '</ins>';
					echo '<del class="price-box__old">' . wc_price( $cached_product['_regular_price'] ) . $price_suffix . '</del>';
					echo '<span class="price-box__discount">' . get_discount( $cached_product['_regular_price'],
							$cached_product['_sale_price'] ) . '% off</span>';
				} else {
					$price = wc_price( wc_get_price_to_display( $product ) ) . $price_suffix;
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
               href="<?php esc_url( the_permalink() ) ?>"
               data-quantity="1"
				<?php echo ( ( op_help()->subscriptions->get_subscribe_status() )['label'] == 'locked' ) ? 'disabled' : ''; ?>
               data-product_id="<?php echo $product->get_id(); ?>"
	            <?php echo ( $in_cart ) ? $data_attr : ''; ?>
            >
				<?php if ( isset($cached_product) && $cached_product['type'] === 'variation' ) { ?>
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

						<?php
						echo '<img src="' . wp_get_attachment_image_url( $badge['icon_contains'],
								'full' ) . '" alt="icon">';
						?>

                    </li>
				<?php } ?>
            </ul>
		<?php } ?>

    </div>

	<?php
	/**
	 * Hook: woocommerce_before_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_open - 10
	 */
	// do_action( 'woocommerce_before_shop_loop_item' );


	/**
	 * Hook: woocommerce_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_product_title - 10
	 */
	// do_action( 'woocommerce_shop_loop_item_title' );


	/**
	 * Hook: woocommerce_after_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_close - 5
	 * @hooked woocommerce_template_loop_add_to_cart - 10
	 */
	// do_action( 'woocommerce_after_shop_loop_item' );
	?>

</li>
