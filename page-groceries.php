<?php
/**
 * Template Name: Groceries
 */

$success_survey           = op_help()->sf_user->check_survey_exist();
$subscribe_status         = op_help()->subscriptions->get_subscribe_status();
$offerings_staples_subcat = op_help()->subcategories->getAllSubcategories();

add_filter( 'kama_breadcrumbs_filter_elements', function ( $elms, $class, $ptype ) {

	$elms['home'] = [
		$class->makelink( '/offerings', 'Offerings' ),
	];
	unset( $elms['singular_hierar'] );

	return $elms;
}, 10, 3 );

$grocery_items       = new WP_Query;
$grocery_items       = $grocery_items->query( [
	'post_type'      => [ 'product' ],
	'posts_per_page' => 50,
	'post_status'    => [ 'publish' ],
	'fields'         => 'ids',
	'tax_query'      => [
		[
			'taxonomy' => 'product_tag',
			'field'    => 'slug',
			'terms'    => array_column( $offerings_staples_subcat, 'slug' ),
		]
	]
] );
$products_from_cache = op_help()->global_cache->get( $grocery_items );
$hide_products       = empty( $offerings_staples_subcat );

$added_products = op_help()->meal_plan_modal->get_cart_all_items();

get_header(); ?>

<main class="site-main catalog-main <?php if ( ! isset( $_COOKIE['user_offer_banner'] ) && ! $success_survey && ! $hide_products ) {
	echo 'catalog-main--padding-top--no';
} ?>">
	<?php if ( ! $hide_products ) { ?>
        <section id="survey-not-exists"
                 class="catalog-main__user-offer user-offer" <?php if ( isset( $_COOKIE['user_offer_banner'] ) || $success_survey )
			echo 'style="display: none;"' ?>>
            <div class="user-offer__breadcrumbs breadcrumbs-box breadcrumbs-box--white">
                <div class="container">
					<?php do_action( 'echo_kama_breadcrumbs' ) ?>
                </div>
            </div><!-- / .breadcrumbs-box -->
            <div class="container">
                <div class="user-offer__lead lead lead--small lead--small-title">
                    <button class="lead__btn-hide link link--color--gray"
                            type="button"><?php _e( 'Don’t show for a while' ); ?></button>
                    <div class="lead__txt content">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/base/icon-planning.svg"
                             width="64" height="64" alt="">
                        <h2><?php _e( 'Personalize your experience' ); ?></h2>
                        <p><?php _e( 'Take a few minutes to complete our nutritional survey to build your personalized menu.' ); ?></p>
                    </div>
					<?php if ( is_user_logged_in() ) { ?>
                        <a class="lead__button button sf_open_survey" href="#">
							<?php echo __( 'Take a Survey' ) ?>
                        </a>
					<?php } else { ?>
                        <a class="lead__button button btn-modal" href="#js-modal-sign-up">
							<?php echo __( 'Take a Survey' ) ?>
                        </a>
					<?php } ?>
                </div><!-- / .lead -->
            </div>
            <picture>
                <source srcset="<?php echo get_template_directory_uri(); ?>/assets/img/bg/personalize-your-experience-6.webp"
                        type="image/webp">
                <img class="user-offer__bg"
                     src="<?php echo get_template_directory_uri(); ?>/assets/img/bg/personalize-your-experience-6.jpg"
                     alt="">
            </picture>
        </section><!-- / .user-offer -->
	<?php } ?>
    <div id="survey-exists"
         class="breadcrumbs-box" <?php if ( ! isset( $_COOKIE['user_offer_banner'] ) && ! $success_survey && ! $hide_products )
		echo 'style="display: none;"' ?>>
        <div class="container">
			<?php do_action( 'echo_kama_breadcrumbs' ) ?>
        </div>
    </div><!-- / .breadcrumbs-box -->
    <h1 class="visually-hidden"><?php the_title(); ?></h1>

    <section class="offerings">
        <div class="container">
            <div class="offerings__box">
                <h1 class="offerings__title h2"><?php the_title(); ?></h1>
                <ul class="offerings__list offerings-list">
					<?php if ( $offerings_staples_subcat ) { ?>
					<?php
					$i            = 0;
					$render_state = 'small';
					$render_index = 1;
					foreach ( $offerings_staples_subcat as $staples_cat ) {
						?>
                        <li class="offerings-list__item offering-item offering-item--small offering-item--small-high">
                            <a class="offering-item__link"
                               href="<?php echo get_site_url() . '/product-tag/' . $staples_cat['slug']; ?>">
                                <div class="offering-item__txt content">
                                    <h3><?php echo $staples_cat['name']; ?></h3>
                                </div>
                                <picture>
                                    <source srcset="<?php echo $staples_cat['img_url'] ? $staples_cat['img_url']
										: '' ?>" type="image/webp">
                                    <img class="offering-item__bg"
                                         src="<?php echo $staples_cat['img_url'] ? $staples_cat['img_url']
										     : '' ?>"
                                         alt="staples category image">
                                </picture>
                            </a>
                        </li>
						<?php
						$render_index ++;
						$i ++;
						if ( $render_index % 5 === 0 && $render_state === 'extra-small' ) {
							$render_state = 'small';
							$render_index = 1;
						} elseif ( $render_index % 4 === 0 && $render_state === 'small' ) {
							$render_state = 'extra-small';
							$render_index = 1;
						}
					}
					?>
                </ul>
				<?php } else {
					echo '<p>' . __( 'We don\'t have products for you' ) . '</p>';
				} ?>
                </ul><!-- / .offerings-list -->
            </div>
        </div>
    </section><!-- / .offerings -->
	<?php if ( ! $hide_products ) { ?>
        <section class="product-group product-group--extra">
            <div class="container">
                <div class="product-group__head head-product-group head-product-group--extended">
                    <h2 class="head-product-group__title"><?php _e( 'All items' ); ?></h2>
                </div>

                <ul class="products__list product-list js-product-list product-list--columns--4">
					<?php foreach ( $products_from_cache as $item ) {
						$product_id = $item['var_id'];

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
						}

					    $product = wc_get_product( $item['var_id'] );
						$product_link  = $product->get_permalink();
						if ( $product ) { ?>
                            <li <?php wc_product_class( 'product-list__item product-item', $product ); ?>>

                                <a class="product-item__img-link" href="<?php echo $product_link; ?>">
									<?php if ( ! empty( $product->get_image_id() ) ) { ?>
                                        <picture>
                                            <img src="<?php echo wp_get_attachment_image_url( $product->get_image_id(),
												'op_single_thumbnail' ) ?>"
                                                 alt="">
                                        </picture>
									<?php } else { ?>
                                        <picture>
                                            <img src="<?php echo wc_placeholder_img_src() ?>" alt="">
                                        </picture>
									<?php } ?>
                                </a>

                                <div class="product-item__info">
                                    <p class="product-item__name">
                                        <a class="product-item__name-link" href="<?php echo $product_link; ?>">
											<?php echo $product->get_title(); ?>
                                        </a>

										<?php if ( ! empty( $company_name = $product->get_meta( '_company_name' ) ) ) : ?>
                                            <a class="product-item__name-add" href="<?php echo $product_link; ?>">
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
									remove_action( 'woocommerce_after_shop_loop_item_title',
										'woocommerce_template_loop_price', 10 );
									do_action( 'woocommerce_after_shop_loop_item_title' );

									$btn_class = 'button--plus';
									?>

                                    <div class="product-item__actions">
                                        <p class="product-item__price price-box">
											<?php
											if ( empty( $product->get_price() ) ) {
												$price = apply_filters( 'woocommerce_empty_price_html', '', $product );
												echo '<ins class="price-box__current">' . $price . '</ins>';
											} elseif ( $product->is_on_sale() ) { ?>
												<?php
												echo '<ins class="price-box__current">' . wc_price( $product->get_sale_price() ) . $product->get_price_suffix() . '</ins>';
												echo '<del class="price-box__old">' . wc_price( $product->get_regular_price() ) . $product->get_price_suffix() . '</del>';
												echo '<span class="price-box__discount">' . get_discount( $product->get_regular_price(),
														$product->get_sale_price() ) . '% off</span>';
											} else {
												$price = wc_price( wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
												echo '<ins class="price-box__current">' . $price . '</ins>';
											}
											?>
                                        </p>
										<?php
										$added_classes = '';
										if ( $in_cart ) {
											$added_classes = 'product-item__button--added add-button--added';
										}
										?>
                                        <a class="product-item__button button button--plus add-button ajax_add_to_cart <?php echo $added_classes; ?>"
                                           href="<?php esc_url( the_permalink() ) ?>"
                                           data-quantity="1"
											<?php echo ( $in_cart ) ? $data_attr : ''; ?>
											<?php echo ( $subscribe_status['label'] == 'locked' ) ? 'disabled' : ''; ?>
                                           data-product_id="<?php echo $product->get_id(); ?>">
                                            <span class="visually-hidden"><?php echo __( 'Add to cart' ) ?></span>
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

                            </li>
						<?php } ?>
					<?php } ?>

                </ul>

            </div>
        </section>
	<?php } ?>
</main><!-- / .site-main .catalog-main  -->

<?php get_footer(); ?>

