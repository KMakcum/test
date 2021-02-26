<?php
global $product, $variation;

$is_variation               = ! empty( $variation );
$this_object                = function () use ( $is_variation, $product, $variation ) {
	if ( $is_variation ) {
		return $variation;
	}
	return $product;
};

if ( $is_variation ) :

	$product_id = $this_object()->get_id();
	$product_has_attributes = $product->get_variation_attributes();

	$sort_cache = op_help()->sort_cache->get_sort_cache( -1, 0, 'default', false, (bool) op_help()->sf_user->check_survey_default());
    $chef_hats = array_column( $sort_cache['ids_with_chef_score'], 'cs', 'id' );

	if ( isset( $chef_hats[$product_id] ) ) {
		$count_hats     = $chef_hats[$product_id];
    } else {
		$count_hats = 2;
    }

	$success_survey = op_help()->sf_user->check_survey_exist();
	$survey_default = op_help()->sf_user->check_survey_default();
	$max_hats       = op_help()->settings->min_hats_show();
	?>

    <section class="product-details__section customize">
        <div class="customize__head">
            <h2 class="customize__title">Customize</h2>

			<?php
			if ( $survey_default && $success_survey && $count_hats < $max_hats ) { ?>
                <div class="customize__label product-item__rating-extra rating-extra js-rating--readonly--true"
                     data-rate-value="<?php echo $count_hats; ?>">
                </div>
			<?php } elseif ( $survey_default && $success_survey ) { ?>
                <span class="customize__label label-best">Best for you</span>
			<?php } ?>

        </div>
        <ul class="customize__variations variations">

			<?php
			$attrs = $this_object()->get_attributes();

			foreach ( $product_has_attributes as $attr_slug => $attr_variation ) :
				$this_variation_term = get_term_by( 'slug', $attrs[ $attr_slug ],
					$attr_slug );
				?>

                <li class="variations__item">
                    <a class="variations__link btn-modal"
                       href="#meals-variation-group-<?php echo esc_attr( $attr_slug ); ?>">

						<?php
						$attr_term_image = carbon_get_term_meta( $this_variation_term->term_id,
							'op_variations_component_thumb' );

						if ( ! empty( $attr_term_image ) ) : ?>
                            <figure class="variations__figure">
                                <picture>
									<?php
									echo wp_get_attachment_image(
										$attr_term_image,
										'thumbnail', false,
										[ 'class' => 'variations__img' ]
									);
									?>
                                </picture>
                            </figure>
						<?php endif; ?>

                        <div class="variations__info content">
                            <h3><?php echo esc_html( $this_variation_term->name ); ?></h3>
                            <p><?php echo esc_html( $this_variation_term->description ); ?></p>
                        </div>

                    </a>
                </li>

			<?php
			endforeach;
			?>

        </ul>
    </section>

<?php
endif;
?>