<?php
global $product, $variation, $cached_product;

$imgs = ( !empty( $cached_product['op_variation_image_gallery_url'] ) ) ? $cached_product['op_variation_image_gallery_url'] : [$cached_product['_thumbnail_id_url']];

//$cached_product = op_help()->global_cache->get_cached_product( $product->get_id() );

//$is_variation = ( ! empty( $variation ) ) ? true : false;
//$this_object  = function () use ( $is_variation, $product, $variation ) {
//
//	if ( $is_variation ) {
//		return $variation;
//	}
//
//	return $product;
//};

//$main_image  = $this_object()->get_image_id();
//$gallery_ids = $this_object()->get_gallery_image_ids();
//
//if ( $is_variation ) {
//	$variation_image_ids = get_post_meta( $variation->get_id(), 'op_variation_image_gallery', true );
//	$gallery_ids         = array_filter( explode( ',', $variation_image_ids ) );
//}
//
//array_unshift( $gallery_ids, $main_image );

if ( empty($cached_product['op_variation_image_gallery_url']) ) {
	$cached_product['op_variation_image_gallery_url'][] = $cached_product['_thumbnail_id_url'];
}

?>

<div class="product-card__gallery">
    <div class="product-card__slider-big product-slider-big">
        <div class="product-slider-big__wr swiper-container">
            <div class="swiper-wrapper">
				<?php foreach ( $imgs as $img_url ) { ?>
                    <div class="product-slider-big__item swiper-slide">
                        <a class="product-slider-big__link"
                           href="<?php echo $img_url; ?>"
                           data-fancybox="gallery">
                            <picture>
                                <img width="1" height="1" src="<?php echo $img_url; ?>" class="product-slider-big__img" alt="" loading="lazy" />
                            </picture>
                        </a>
                    </div>
				<?php } ?>
            </div>
        </div>
        <!-- Add Arrows -->
        <button class="product-slider-big__button product-slider-big__button--prev round-arrow" type="button">
            <svg width="32" height="32" fill="#252728">
                <use href="#icon-angle-left-light"></use>
            </svg>
        </button><!-- / .round-arrow -->
        <button class="product-slider-big__button product-slider-big__button--next round-arrow" type="button">
            <svg width="32" height="32" fill="#252728">
                <use href="#icon-angle-rigth-light"></use>
            </svg>
        </button><!-- / .round-arrow -->
    </div>
    <div class="product-card__slider-small product-slider-small swiper-container">
        <div class="swiper-wrapper">
			<?php foreach ( $imgs as $img_url ) { ?>
                <div class="product-slider-small__item swiper-slide">
                    <figure class="product-slider-small__figure">
                        <picture>
                            <img width="1" height="1" src="<?php echo $img_url; ?>" class="product-slider-small__img" alt="" loading="lazy" />
                        </picture>
                    </figure>
                </div>
			<?php } ?>
        </div>
    </div>
</div>