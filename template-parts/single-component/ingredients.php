<?php
global $product;

$term_list = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) );
$cat_id = (int) $term_list[0];
$facility_allergens = carbon_get_term_meta( $cat_id, 'facility_allergens' );
?>
<section class="product-card__section info-box content content--small">
    <h2><?php echo __('Ingredients'); ?></h2>
    <p>
        <?php echo $args['ingredients'] ?>
    </p>
	<?php if ( ! empty( $args['allergens'] ) ) { ?>
        <p><strong>Allergens: <?php echo $args['allergens']; ?>.</strong></p>
	<?php } ?>
	<?php if ( ! empty( $facility_allergens ) ) { ?>
        <p><strong>Facility allergens: <?php echo $facility_allergens; ?>.</strong></p>
	<?php } ?>
</section><!-- / .info-box -->
