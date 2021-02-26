<div class="product-card__gallery">
    <div class="product-card__slider-big product-slider-big">
        <div class="product-slider-big__wr swiper-container">
            <div class="swiper-wrapper">
                <?php foreach ( $args['gallery_ids'] as $img_id ) {?>
                    <div class="product-slider-big__item swiper-slide">
                        <a class="product-slider-big__link"
                           href="<?php echo wp_get_attachment_image_url( $img_id, 'full' ); ?>"
                           data-fancybox="gallery">
                            <picture>
                                <?php echo wp_get_attachment_image( $img_id, 'op_single_thumbnail',
                                    false, [ 'class' => 'product-slider-big__img' ] ); ?>
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
            <?php foreach ( $args['gallery_ids'] as $img_id ) { ?>
                <div class="product-slider-small__item swiper-slide">
                    <figure class="product-slider-small__figure">
                        <picture>
                            <?php echo wp_get_attachment_image( $img_id, 'thumbnail', false,
                                [ 'class' => 'product-slider-small__img' ] ); ?>
                        </picture>
                    </figure>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
