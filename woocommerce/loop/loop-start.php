<?php
/**
 * Product Loop Start
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/loop-start.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// you can use wc_get_loop_prop( 'columns' ) for using columns

$variation_cats_options = carbon_get_theme_option('op_shop_variationcat');
$variation_cats_ids = array_column( $variation_cats_options, 'id' );

$container_classes = ['products__list', 'product-list', 'js-product-list'];
if( !in_array( get_queried_object_id(), $variation_cats_ids ) ) {
	$container_classes[] = 'product-list--columns--4';
}

global $added_products;
$added_products = op_help()->meal_plan_modal->get_cart_all_items();
?>


<ul class="<?php echo esc_attr( implode(" ", $container_classes ) ); ?>">