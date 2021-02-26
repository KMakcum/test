<?php
global $product, $variation, $cached_product;

//$variation    = op_help()->variations->rules->getCurrentVariation();
//$is_variation = ! empty( $variation );
//$this_object  = function () use ( $is_variation, $product, $variation ) {
//
//	if ( $is_variation ) {
//		return $variation;
//	}
//
//	return $product;
//};

//$cached_data = op_help()->global_cache->get( $this_object()->get_id() );
//$badges      = $cached_data['data']['badges'];
$badges = $cached_product['badges'];

if ( $cached_product['type'] == 'variation' && ! empty( $badges ) ) :
	?>

    <ul class="nutrition__right badges">

		<?php foreach ( $badges as $badge ) { ?>
            <li class="badges__item" data-tippy-content="<?php echo $badge['title']; ?>">
                <?php echo '<img src="' . wp_get_attachment_image_url( $badge['icon_contains'], 'full' ) . '" alt="icon">'; ?>
            </li>
		<?php } ?>

    </ul>

<?php endif; ?>
