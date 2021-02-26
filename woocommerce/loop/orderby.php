<?php
/**
 * Show options for ordering
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/orderby.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce/Templates
 * @version     3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp;
?>
<form class="products__sort-form" action="<?php echo home_url( add_query_arg( array(), $wp->request ) ); ?>" method="get">
		<p class="products__sort-select select select--no-border">
				<label class="visually-hidden" for="products-filter-sort-by"><?php esc_attr_e( 'Sort by', 'woocommerce' ); ?></label>
				<select class="select__field" id="products-filter-sort-by" name="orderby">
					<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
						<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php wc_query_string_form_fields( null, array( 'orderby' ) ); ?>
		</p><!-- / .select -->
</form>
<script>document.querySelector("#products-filter-sort-by").addEventListener("change", function(e){ e.target.closest("form.products__sort-form").submit() });</script>