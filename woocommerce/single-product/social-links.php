<ul class="product-card__social social-links-2">
    <li class="social-links-2__item">
        <a class="social-links-2__link social-links-2__link--facebook"
           onclick="window.open('http://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>&amp;t=<?php the_title(); ?>', 'sharer', 'toolbar=0,status=0,width=548,height=325');"
           href="javascript:void(0);"
           target="_parent"
           rel="noopener nofollow"
           title="Share on Facebook">
            <svg class="social-links-2__icon" width="16" height="16" fill="#87898C">
                <use href="#icon-facebook-circle"></use>
            </svg>
            <span class="social-links-2__tooltip">Facebook</span>
        </a>
    </li>

    <li class="social-links-2__item">

        <a class="social-links-2__link social-links-2__link--twitter"
           onclick="window.open('http://twitter.com/share?text=<?php the_title(); ?>&amp;url=<?php the_permalink(); ?>');"
           href="javascript:void(0);"
           target="_parent"
           rel="noopener nofollow"
           title="Share on Twitter">
            <svg class="social-links-2__icon" width="16" height="16" fill="#87898C">
                <use href="#icon-twitter"></use>
            </svg>
            <span class="social-links-2__tooltip">Twitter</span>
        </a>
    </li>

	<?php
	global $product, $variation;

	//if simple or variation product
	$is_variation = ! empty( $variation );

	$this_object = function () use ( $is_variation, $product, $variation ) {

		if ( $is_variation ) {
			return $variation;
		}

		return $product;
	};

	//if product's category is "Components" (id = 181)
	$term_list = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) );

	$cat_id = (int) $term_list[0];

	if ( $cat_id == 181 ) {
		$component_content = op_help()->single_component->get_single_component_fields( $this_object()->get_slug() );
		$gallery_ids       = $component_content['op_variations_component__gallery'];

		$pinterest_image = wp_get_attachment_image_url( $gallery_ids[0], 'full' );
	} else {
		$main_image = $this_object()->get_image_id();
		$pinterest_image_arr = wp_get_attachment_image_src( $main_image, 'full' );
		$pinterest_image = $pinterest_image_arr[0];
	}

	// not to show pinterest button if there's no product image
	if ( ! empty( $pinterest_image ) ) :
		?>
        <li class="social-links-2__item">
            <a class="social-links-2__link social-links-2__link--pinterest"
               onclick="window.open('https://www.pinterest.com/pin/create/button/?url=<?php the_permalink(); ?>&media=<?php echo $pinterest_image; ?>&description=<?php the_title(); ?>');"
               href="javascript:void(0);"
               target="_parent"
               data-pin-do="buttonPin"
               data-pin-custom="true"
               rel="noopener nofollow"
               title="Share on Pinterest">
                <svg class="social-links-2__icon" width="16" height="16" fill="#87898C">
                    <use href="#icon-pinterest"></use>
                </svg>
                <span class="social-links-2__tooltip">Pinterest</span>
            </a>

        </li>
	<?php endif; ?>
    <li class="social-links-2__item">
        <a class="social-links-2__link social-links-2__link--email" href="#" target="_blank" rel="noopener nofollow"
           title="Send by email">
            <svg class="social-links-2__icon" width="16" height="16" fill="#87898C">
                <use href="#icon-envelope"></use>
            </svg>
            <span class="social-links-2__tooltip">Email</span>
        </a>
    </li>
    <!-- <li class="social-links-2__item">
        <a class="social-links-2__link social-links-2__link--print" href="javascript:(print());" title="Print">
            <svg class="social-links-2__icon" width="16" height="16" fill="#87898C">
                <use href="#icon-pritner"></use>
            </svg>
            <span class="social-links-2__tooltip">Print</span>
        </a>
    </li> -->
</ul>