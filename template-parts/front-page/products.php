<?php
//$allergens = carbon_get_theme_option( 'op_variations_allergens' );
$badges          = carbon_get_theme_option( 'op_variations_badges' );
$product_section = op_help()->mainpage->get_product_section( get_the_ID() );
?>

<section class="on-the-menu">
    <div class="on-the-menu__head">
        <div class="container">
            <div class="on-the-menu__head-txt content">

				<?php if ( $product_section['title'] ) : ?>
                    <h2><?php echo esc_html( $product_section['title'] ); ?></h2>
				<?php endif; ?>

				<?php if ( $product_section['descr'] ) : ?>
                    <p><?php echo esc_html( $product_section['descr'] ); ?></p>
				<?php endif; ?>

            </div>
        </div>
    </div>

	<?php if ( ! empty( $product_section['top_carousel'] ) ) : ?>

        <div class="on-the-menu__slider product-slider product-slider--bg--white product-slider--meals swiper-container">
            <div class="swiper-wrapper">

				<?php
				$products = $product_section['top_carousel'];
                if ( is_user_logged_in() ) {
                    $current_user = wp_get_current_user();
                    $zip_code     = trim( get_user_meta( $current_user->ID, 'sf_zipcode', true ) );
                } else {
                    $zip_code = op_help()->sf_user::op_get_zip_cookie();
                }
                $is_zip_national = op_help()->zip_codes->is_zip_zone_national( $zip_code );
                foreach ( $products as $product ) {
					$product_id = $product['var_id'];
					$badges     = $product['badges'];
					$link_class    = '';
					$link_data_r   = '';
					$product_title = ( $product['op_post_title'] ) ? $product['op_post_title'] : $product['post_title'];

                    if ( is_null( $zip_code ) ) {
                        $link_class  = 'btn-modal';
                        $link_href   = '#js-modal-zip-code';
                        $link_data_r = get_the_permalink( $product_id );
                    } else {
                        $link_href = ! $is_zip_national ? get_the_permalink($product_id) : '/offerings/';
                    }
					?>

                    <div class="swiper-slide">
                        <div class="product-item product-item--easy">
                            <a class="product-item__img-link <?php echo $link_class; ?>"
                               href="<?php echo $link_href; ?>"
                               data-redirect="<?php echo $link_data_r; ?>"
                               data-home-meal-slider="true">
                                <picture>
                                    <img src="<?php echo $product['_thumbnail_id_url']; ?>" class="product-item__img wp-post-image" alt="Beans, Navy – Dunya Harvest, 15 oz." loading="lazy">
                                </picture>
                            </a>
                            <div class="product-item__info">
                                <p class="product-item__name">
                                    <a class="product-item__name-link <?php echo $link_class; ?>"
                                       href="<?php echo $link_href; ?>"
                                       data-redirect="<?php echo $link_data_r; ?>"
                                       data-home-meal-slider="true">
										<?php echo $product_title; ?>
                                    </a>
                                </p>

                                <div class="product-item__review rating-and-review">
                                    <div class="rating-and-review__rating rating js-rating--readonly--true"
                                         data-rate-value="0"></div>
                                    <span class="rating-and-review__review">(0)</span>
                                </div>

								<?php if ( ! empty( $badges ) ) { ?>
                                    <ul class="product-item__badges badges">

										<?php foreach ( $badges as $badge ) { ?>
                                            <li class="badges__item"
                                                data-tippy-content="<?php echo $badge['title']; ?>">

												<?php if ( get_post_mime_type( $badge['icon_contains'] ) == 'image/svg+xml' ) :

													echo file_get_contents( get_attached_file( $badge['icon_contains'],
														'full' ) );

                                                elseif ( get_post_mime_type( $badge['icon_contains'] ) == 'image/png' ) :

													echo '<img src="' . wp_get_attachment_image_url( $badge['icon_contains'],
															'full' ) . '" alt="icon">';
												endif; ?>

                                            </li>
										<?php } ?>
                                    </ul>
								<?php } ?>

                            </div>
                        </div>
                    </div>

				<?php } ?>

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

	<?php endif; ?>

</section>