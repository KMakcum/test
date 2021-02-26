<?php
global $product, $variation, $cached_product;

$is_variation = ( ! empty( $variation ) ) ? true : false;
$this_object  = function () use ( $is_variation, $product, $variation ) {

	if ( $is_variation ) {
		return $variation;
	}

	return $product;
};

$about_dish = '';
if ( $cached_product['type'] == 'variation' ) {
	$about_dish = $cached_product['op_meal_ingredients'];
} else {
	$about_dish = $cached_product['op_ingredients'];
}

$facility_allergens = $cached_product['facility_allergens'];

$allergens       = carbon_get_theme_option( 'op_variations_allergens' );
$allergens_slugs = [];
foreach ( (array) $cached_product['components'] as $component_id ) {
	$allergens_slugs = array_merge( $allergens_slugs, explode( ',', $cached_product['op_allergens'] ) );
}

$allergens_show = array_map( function ( $allergen ) use ( $allergens_slugs ) {
	if ( in_array( $allergen['slug'], $allergens_slugs ) ) {
		return $allergen['title'];
	}
}, $allergens );

$allergens_show = array_filter( $allergens_show );
$allergens_string = implode( ', ', $allergens_show );

if ( ! empty( $about_dish ) ) :
	?>
    <section class="product-card__section info-box content content--small" data-id="<?php echo $cached_product['var_id']; ?>">
        <h2>Ingredients</h2>
		<?php echo apply_filters( "the_content", $about_dish ); ?>

		<?php if ( ! empty( $allergens_string ) ) { ?>
            <p><strong>Allergens: <?php echo $allergens_string; ?>.</strong></p>
		<?php } ?>

		<?php if ( $facility_allergens ) { ?>
            <p><strong>Facility allergens: <?php echo $facility_allergens; ?>.</strong></p>
		<?php } ?>
    </section>

<?php endif;