<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
// do_action( 'woocommerce_before_main_content' );

global $wp, $wp_query, $sf_results_all_items, $sf_results_filtered_items;

$taxonomy = $wp_query->get_queried_object();
if ( is_user_logged_in() ) {
	$current_user = wp_get_current_user();
	$zip_code     = trim( get_user_meta( $current_user->ID, 'sf_zipcode', true ) );
} else {
	$zip_code = op_help()->sf_user::op_get_zip_cookie();
}
$is_zip_national = op_help()->zip_codes->is_zip_zone_national( $zip_code );

$use_survey_results = op_help()->sf_user->check_survey_default();

if ( empty( $sf_results_all_items ) and $sf_results_all_items == 0 ) {
	$sf_results_all_items = wc_get_loop_prop( 'total' );
}
$sf_results_filtered_items = empty( $sf_results_filtered_items ) ? $sf_results_all_items : $sf_results_filtered_items;
$sf_results_filtered_items = ( $sf_results_filtered_items > $sf_results_all_items ) ? $sf_results_all_items : $sf_results_filtered_items;
// fast solution to be refactored
$product_tags = [
	'Everyday Essentials'    => 'everyday-essentials',
	'Meats And Cheese'       => 'meats-and-cheese',
	'Dairy And Alternatives' => 'dairy-and-alternatives',
	'Snacks And Cookies'     => 'snacks-and-cookies',
	'Tea And Coffee'         => 'tea-and-coffee',
	'Water And Beverages'    => 'water-and-beverages',
];
add_filter( 'kama_breadcrumbs_filter_elements', function ( $elms, $class, $ptype ) use ( $taxonomy, $product_tags ) {
	if ( $taxonomy->slug === 'vitamins' || $taxonomy->slug === 'meals' ) {
		$elms['home'] = [];
		unset( $elms['tax_hierar'] );
	} elseif ( in_array( $taxonomy->slug, $product_tags ) ) {
		$grocery_group = true;
		$elms['home']  = [
			$class->makelink( '/offerings', 'Offerings' ),
			$class->makelink( '/groceries', 'Groceries' ),
		];
		unset( $elms['tax_tag'] );
	}

	return $elms;
}, 10, 3 );


if ( in_array( $taxonomy->slug, $product_tags ) ) {
	$offerings_staples_subcat                = op_help()->subcategories->getAllSubcategories();
	$offerings_staples_subcat_default_images =
		[
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-3-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-4-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-5-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-6-extra-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-7-extra-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-8-extra-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-9-extra-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-10-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-11-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-12-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-13-extra-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-14-extra-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-15-extra-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-16-extra-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-17-small.jpg',
			get_stylesheet_directory_uri() . '/assets/img/base/offerings-18-small.jpg',
		];
}

?>
    <main class="site-main catalog-main catalog-loader">
        <div class="loader-container">
            <div class="loader-image"></div>
            <div class="loader-text"><?php echo carbon_get_theme_option( 'op_loaders_catalog_text' ); ?></div>
        </div>
        <input type="hidden" id="taxonomy" value="<?php echo $taxonomy->slug; ?>">
		<?php if ( in_array( $taxonomy->slug, $product_tags ) ) { ?>
            <section class="offerings offerings--padding-bottom--small">
                <div class="container">
                    <ul class="offerings__list offerings-list">
						<?php if ( $offerings_staples_subcat ): ?>
						<?php
						$i            = 0;
						$render_state = 'small';
						$render_index = 1;
						foreach ( $offerings_staples_subcat as $staples_cat ) {
							?>
                            <li class="offerings-list__item offering-item offering-item--very-small">
                                <a class="offering-item__link"
                                   href="<?php echo get_site_url() . '/product-tag/' . $staples_cat['slug']; ?>">
                                    <div class="offering-item__txt content">
                                        <h3><?php echo $staples_cat['name']; ?></h3>
                                    </div>
                                    <picture>
                                        <source srcset="<?php echo $staples_cat['img_url'] ? $staples_cat['img_url']
											: $offerings_staples_subcat_default_images[ $i ] ?>" type="image/webp">
                                        <img class="offering-item__bg"
                                             src="<?php echo $staples_cat['img_url'] ? $staples_cat['img_url']
											     : $offerings_staples_subcat_default_images[ $i ] ?>"
                                             alt="groceries category image">
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
					<?php endif; ?>
                </div>
            </section><!-- / .offerings -->
		<?php } ?>
        <div class="breadcrumbs-box">
            <div class="container">
				<?php do_action( 'echo_kama_breadcrumbs' ); ?>
            </div>
        </div>

        <section class="products">
            <div class="container">

				<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>

                    <div class="products__head">

                        <div class="products__title-and-page-info">
                            <h1 class="products__title"><?php woocommerce_page_title(); ?></h1>
                            <span class="products__page-info">
								<span><?php echo esc_html( $sf_results_filtered_items ); ?></span>
								/
								<?php echo esc_html( $sf_results_all_items ); ?>
							</span>
                        </div>

						<?php
                            if ( op_help()->shop->is_meals_category() ) {
                                wc_get_template(
                                    'catalog/recommended-switch.php',
                                    [
                                        'use_survey' => $use_survey_results,
                                    ]
                                );
                            }
                        ?>

                    </div>

				<?php endif; ?>

				<?php
				/**
				 * Hook: woocommerce_archive_description.
				 *
				 * @hooked woocommerce_taxonomy_archive_description - 10
				 * @hooked woocommerce_product_archive_description - 10
				 */
				// do_action( 'woocommerce_archive_description' );


				/**
				 * Hook: woocommerce_sidebar.
				 *
				 * @hooked woocommerce_get_sidebar - 10
				 */
				// do_action( 'woocommerce_sidebar' );
				?>

                <div class="products__filter-and-sort">


					<?php

					if ( op_help()->shop->is_meals_category() ) {
						wc_get_template(
							'loop/filters.php',
							[
								'use_survey' => $use_survey_results,
							]
						);
					}

					/**
					 * Hook: woocommerce_before_shop_loop.
					 *
					 * @hooked woocommerce_output_all_notices - 10
					 * @hooked woocommerce_result_count - 20
					 * @hooked woocommerce_catalog_ordering - 30
					 */
					remove_action( 'woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10 );
					remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

					do_action( 'woocommerce_before_shop_loop' );
					?>
                </div>

				<?php
				//( woocommerce_product_loop() && $taxonomy->slug === 'vitamins' ) ||
				//( woocommerce_product_loop() && ! $is_zip_national && ( $taxonomy->slug === 'meals' || in_array($taxonomy->slug, $product_tags) ) )

				if ( ( woocommerce_product_loop() && op_help()->shop->check_zone_for_catalog( $taxonomy ) ) ) {

					woocommerce_product_loop_start();

					do_action( 'woocommerce_offer_card' );

					if ( wc_get_loop_prop( 'total' ) ) {
						while ( have_posts() ) {
							the_post();

							/**
							 * Hook: woocommerce_shop_loop.
							 */
							do_action( 'woocommerce_shop_loop' );
							wc_get_template_part( 'content', 'product' );
						}
					}

					woocommerce_product_loop_end();

					/**
					 * Hook: woocommerce_after_shop_loop.
					 *
					 * @hooked woocommerce_pagination - 10
					 */
					do_action( 'woocommerce_after_shop_loop' );
				} else {
					/**
					 * Hook: woocommerce_no_products_found.
					 *
					 * @hooked wc_no_products_found - 10
					 */
					do_action( 'woocommerce_no_products_found' );
				}

				/**
				 * Hook: woocommerce_after_main_content.
				 *
				 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
				 */
				do_action( 'woocommerce_after_main_content' );
				?>

            </div>
        </section>
    </main>

<?php

get_footer( 'shop' );
if ( stripos( $_SERVER['REQUEST_URI'], 'product-category/meals' ) ) {
	get_template_part( 'template-parts/meal-plan-bottom-nav' );
}
