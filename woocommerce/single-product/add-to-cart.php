<?php
global $product, $variation;

$is_variation = ! empty( $variation );

$product_categories = $product->get_category_ids();
$tags = $product->get_tag_ids();
if ( ! empty( $tags ) ) {
    $tag_id  = $tags[ array_key_last( $tags ) ];
    $tag_obj = get_term_by( 'id', $tag_id, 'product_tag' );
}

$in_cart            = false;
$added_products     = op_help()->meal_plan_modal->get_cart_all_items();
$added_products_ids = array_column( $added_products, 'id' );

if ( $is_variation ) {
	if ( in_array( $variation->get_id(), $added_products_ids ) ) {
		$in_cart       = true;
		$quantity_cart = array_filter( $added_products, function ( $item ) use ( $variation ) {
			return ( $item['id'] === $variation->get_id() );
		} );
	}
} else {
	if ( in_array( $product->get_id(), $added_products_ids ) ) {
		$in_cart       = true;
		$quantity_cart = array_filter( $added_products, function ( $item ) use ( $product ) {
			return ( $item['id'] === $product->get_id() );
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

<form class="footer-product-details__form form-add-to-cart" method="POST">
    <input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
    <input class="nice-number__field js-nice-number" type="number" value="1" min="1" name="quantity">
	<?php if ( $is_variation ) : ?>

        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $variation->get_id() ); ?>">
        <input type="hidden" name="variation_id" class="variation_id"
               value="<?php echo esc_attr( $variation->get_id() ); ?>">

        <?php if ( intval( get_user_meta( get_current_user_id(), 'survey_default', true )) ) { ?>
            <input type="hidden" name="category_redirect"
                   value="<?php echo get_category_link( $product_categories[0] ); ?>?use_survey=on">
        <?php } else { ?>
            <input type="hidden" name="category_redirect"
                   value="<?php echo get_category_link( $product_categories[0] ); ?>">
        <?php } ?>
        <button
                type="submit"
                class="form-add-to-cart__button button add-button <?php echo $added_classes; ?>"
			<?php echo ( $in_cart ) ? $data_attr : ''; ?>
			<?php echo ( ( op_help()->subscriptions->get_subscribe_status() )['label'] == 'locked' ) ? 'disabled' : ''; ?>
        >
            <span class="add-button__txt-1">
                Add <span class="only-more-sm">to your plan</span>
            </span>
			<?php if ( $in_cart ) { ?>
                <span class="add-button__txt-2">Added</span>
			<?php } else { ?>
                <span class="add-button__txt-2">Added</span>
			<?php } ?>

            <svg class="add-button__icon" width="24" height="24"
                 fill="#fff">
                <use href="#icon-check-circle-stroke"></use>
            </svg>
        </button>

	<?php else : ?>
        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
        <input type="hidden" name="category_redirect"
               value="<?php echo ! empty( $tags ) ? get_site_url().'/product-tag/'.$tag_obj->slug : get_category_link( $product_categories[0] ); ?>">
        <button
                class="form-add-to-cart__button button add-button <?php echo $added_classes; ?>"
			<?php echo ( $in_cart ) ? $data_attr : ''; ?>
			<?php echo ( ( op_help()->subscriptions->get_subscribe_status() )['label'] == 'locked' ) ? 'disabled' : ''; ?>
        >
            <span class="add-button__txt-1">
                Add <span class="only-more-sm">to your plan</span>
            </span>
			<?php if ( $in_cart ) { ?>
                <span class="add-button__txt-2">Added</span>
			<?php } else { ?>
                <span class="add-button__txt-2">Added</span>
			<?php } ?>
            <svg class="add-button__icon" width="24" height="24"
                 fill="#fff">
                <use href="#icon-check-circle-stroke"></use>
            </svg>
        </button>

	<?php endif; ?>

</form>
