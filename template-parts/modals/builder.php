<?php
global $wp, $product, $variation;

$is_variation = ! empty( $variation );
$this_object  = function () use ( $is_variation, $product, $variation ) {
	if ( $is_variation ) {
		return $variation;
	}

	return $product;
};

$center_title = 'title-center';

if ( $is_variation ) {
    $product_can_be = op_help()->global_cache->getAll();
	$survey       = false;

	if ( is_user_logged_in() ) {
		$center_title = '';
		if ( isset( $_GET['use_survey'] ) && $_GET['use_survey'] == 'true' ) {
			$survey = true;
		}
	}

	$sort_cache = op_help()->sort_cache->get_sort_cache( -1, 0, 'default', false, $survey, 'variation' );
	$chef_score = array_column( $sort_cache['ids_with_chef_score'], 'cs', 'id' );
	$product_can_be = op_help()->variations->get_sort_order($product_can_be, $sort_cache['ids']);

	$product_has_attributes       = $product->get_variation_attributes(); // все компоненты
	$this_variation_attributes_db = $this_object()->get_attributes(); // компоненты текущие
	$this_variation_attributes    = [];

	foreach ( $this_variation_attributes_db as $key => $value ) {
		$term                              = get_term_by( 'slug', $value, $key );
		$this_variation_attributes[ $key ] = $term->term_id;
	}

	$other_variations           = [];
	$other_variations_items_ids = [ $this_object()->get_id() ];
	$this_variation_term_ids    = [];

	foreach ( $product_has_attributes as $attr_slug => $attr_variation ) {

		$this_variation_attr_value = $this_object()->get_attribute( $attr_slug );
		$this_variation_term       = get_term_by( 'slug', $this_variation_attr_value, $attr_slug );

		$this_variation_term_ids[] = $this_variation_term->term_id;
		// find all variations with other option for this component group
		$other_variations[ $attr_slug ] = array_filter( $product_can_be,
			function ( $variation ) use ( $attr_slug, $this_variation_attributes ) {

				foreach ( $variation['data']['components'] as $other_variation_attr_slug => $other_variation_attr_value ) {

					// if current group different - its ok
					if ( $other_variation_attr_slug == $attr_slug ) {
						continue;
					}

					// but other attributes must be same
					if ( $this_variation_attributes[ $other_variation_attr_slug ] != $other_variation_attr_value ) {
						return false;
					}
				}

				return true;
			} );

		$other_variations[ $attr_slug ] = array_map( function ( $item ) use (
			$attr_slug,
			&$other_variations_items_ids
		) {
			$this_variation_attr_value    = $item['data']['components'][ $attr_slug ];
			$other_variations_items_ids[] = $item['var_id'];

			return [
				'item'     => $item,
				'term'     => get_term_by( 'id', $this_variation_attr_value, $attr_slug ),
				'termmeta' => get_term_meta( $this_variation_attr_value )
			];

		}, $other_variations[ $attr_slug ] );

	}

	if ( ! empty( $other_variations ) ) {
		foreach ( $other_variations as $attributes_group => $attributes_variations ) {
			?>

            <div class="modal-builder modal-common"
                 id="meals-variation-group-<?php echo esc_attr( $attributes_group ); ?>"
                 style="display: none">
                <form class="modal-builder__form" action="#"
                      method="post"
                      data-product="<?php echo $product->get_id();  ?>"
                      data-variation="<?php echo $this_object()->get_id();  ?>"
                      data-form="<?php echo $attributes_group ?>">
					<?php count( $product_can_be ) ?>
                    <header class="modal-builder__header builder-header">
						<?php if ( op_help()->sf_user->check_survey_exist() ) { ?>
                            <div class="builder-header__toggle toggle toggle--easy">
                                <!--  id="products-filter-disable-survey" -->
                                <input class="js-toggle-switch visually-hidden"
                                       type="checkbox" name="use_survey"
									<?php echo ( intval( get_user_meta( get_current_user_id(), 'survey_default',
										true ) ) ) ? 'checked' : ''; ?>>

                                <span class="toggle__txt">Show recommended only</span>
                            </div>
						<?php } ?>
                        <h3 class="builder-header__title <?php echo $center_title; ?>">Select replacement</h3>
                    </header>

                    <div class="modal-builder__body builder-body js-perfect-scrollbar">
                        <ul class="builder-body__options option-list option-list-<?php echo $attributes_group ?>">
							<?php
							$data_term                    = [];
							foreach ( $attributes_variations as $group_variation_key => $group_variation ) :

								$group_variation_item = $group_variation['item'];
								$group_variation_term     = $group_variation['term'];
								$group_variation_termmeta = $group_variation['termmeta'];


								$checked = ( in_array( $group_variation_term->term_id,
									$this_variation_term_ids ) ) ? 'checked' : '';
								// get nutrition value
								$cals_val     = $group_variation_termmeta['_op_variations_component_calories'][0];
								$carbs_val    = $group_variation_termmeta['_op_variations_component_carbohydrates'][0];
								$fats_val     = $group_variation_termmeta['_op_variations_component_fats'][0];
								$proteins_val = $group_variation_termmeta['_op_variations_component_proteins'][0];

								$nutrition_data_total = $fats_val + $proteins_val + $carbs_val;

								if ( $nutrition_data_total > 0 ) {

									$nutrition_data_percentage = [
										'calories'      => round( $cals_val / 2500, 2 ) * 100,
										'fats'          => round( $fats_val / $nutrition_data_total, 2 ) * 100,
										'proteins'      => round( $proteins_val / $nutrition_data_total, 2 ) * 100,
										'carbohydrates' => round( $carbs_val / $nutrition_data_total, 2 ) * 100,
									];

								} else {
									$nutrition_data_percentage = [
										'calories'      => round( $cals_val / 2500, 2 ),
										'fats'          => 0,
										'proteins'      => 0,
										'carbohydrates' => 0,
									];
								}

								if ( in_array( $group_variation_term->term_id, $this_variation_term_ids ) ) {
									$data_term['name']               = $group_variation_term->name;
									$data_term['desc']               = $group_variation_term->description;
									$data_term['price']              = $group_variation_item['price'];
									$data_term['cal']                = $cals_val;
									$data_term['carbs']              = $carbs_val;
									$data_term['fat']                = $fats_val;
									$data_term['protein']            = $proteins_val;
									$data_term['carbs_percentage']   = $nutrition_data_percentage['carbohydrates'];
									$data_term['fat_percentage']     = $nutrition_data_percentage['fats'];
									$data_term['protein_percentage'] = $nutrition_data_percentage['proteins'];
								}

								$product_link = get_the_permalink( $group_variation_item['var_id'] );

								if ( intval( get_user_meta( get_current_user_id(), 'survey_default', true ) ) ) {
									$product_link = add_query_arg( 'use_survey', 'true', $product_link );
								}
								?>

                                <li class="option-list__item">
                                    <label class="option-item"
                                           data-link="<?php echo $product_link; ?>"
                                           data-name="<?php echo $group_variation_term->name; ?>"
                                           data-desc="<?php echo $group_variation_term->description; ?>"
                                           data-price="<?php echo number_format( $group_variation_item['price'],
										       2 ); ?>"

                                           data-cal="<?php echo $cals_val; ?>"
                                           data-carbs="<?php echo $carbs_val; ?>"
                                           data-fat="<?php echo $fats_val; ?>"
                                           data-protein="<?php echo $proteins_val; ?>"

                                           data-carbs-percentage="<?php echo esc_attr( $nutrition_data_percentage['carbohydrates'] ); ?>"
                                           data-fat-percentage="<?php echo esc_attr( $nutrition_data_percentage['fats'] ); ?>"
                                           data-protein-percentage="<?php echo esc_attr( $nutrition_data_percentage['proteins'] ); ?>"
                                    >
                                        <input class="option-item__field visually-hidden"
                                               type="radio"
                                               name="moroccan-chicken-stew"
											<?php echo $checked; ?>

                                        >
                                        <span class="option-item__box">

                                    <?php
                                    $attr_term_image = $group_variation_termmeta['_op_variations_component_thumb'][0];

                                    if ( ! empty( $attr_term_image ) ) :
	                                    ?>

                                        <span class="option-item__img-box">
                                            <picture>
                                                <?php
                                                echo wp_get_attachment_image(
	                                                $attr_term_image,
	                                                'thumbnail', false,
	                                                [ 'class' => 'option-item__img' ]
                                                );
                                                ?>
                                            </picture>
                                        </span>

                                    <?php
                                    endif;
                                    ?>

                                    <span class="option-item__info">
                                        <span class="option-item__name"><?php echo esc_html( $group_variation_term->name ); ?></span>
                                        <?php
                                        $count_rating = ( isset( $chef_score ) && $survey ) ? $chef_score[$group_variation_item['var_id']] : NULL;

                                        if ( isset( $count_rating ) ) {
	                                        if ( $count_rating < op_help()->settings->min_hats_show() ) { ?>
                                                <div class="option-item__rating rating-extra js-rating--readonly--true"
                                                     data-rate-value="<?php echo $count_rating; ?>">
                                            </div>
	                                        <?php } else { ?>
                                                <span class="option-item__label label-best">Best for you</span>
	                                        <?php } ?>
                                        <?php } ?>


                                    </span>
                                </span>


                                    </label>
                                </li>

							<?php
							endforeach;
							?>

                        </ul>
                    </div>

                    <footer class="modal-builder__footer builder-footer">
                        <div class="builder-footer__left content js-perfect-scrollbar">

                            <h4><?php echo $data_term['name']; ?></h4>
                            <p><?php echo $data_term['desc']; ?></p>

                        </div>
                        <div class="builder-footer__right">
                            <ul class="builder-footer__nutrition-list nutrition-list nutrition-list--small">
                                <li class="nutrition-list__item nutrition-item">

                                    <div class="nutrition-item__progress-bar progress-bar progress-bar--small summ_cal"
                                         data-value-grams="<?php echo $data_term['cal']; ?>"
                                         data-value-percent="<?php echo $data_term['cal']; ?>"

                                         data-color="#fff">
                                    </div>
                                    <p class="nutrition-item__title">Cal</p>
                                </li>
                                <li class="nutrition-list__item nutrition-item">

                                    <div class="nutrition-item__progress-bar progress-bar progress-bar--small summ_carbs"
                                         data-value-grams="<?php echo $data_term['carbs']; ?> g"
                                         data-value-percent="<?php echo $data_term['carbs_percentage']; ?>"

                                         data-color="#0482CC">
                                    </div>
                                    <p class="nutrition-item__title">Carbs</p>
                                </li>
                                <li class="nutrition-list__item nutrition-item">

                                    <div class="nutrition-item__progress-bar progress-bar progress-bar--small summ_fat"
                                         data-value-grams="<?php echo $data_term['fat']; ?> g"
                                         data-value-percent="<?php echo $data_term['fat_percentage']; ?>"
                                         data-color="#F2AE04">
                                    </div>
                                    <p class="nutrition-item__title">Fat</p>
                                </li>
                                <li class="nutrition-list__item nutrition-item">

                                    <div class="nutrition-item__progress-bar progress-bar progress-bar--small summ_protein"
                                         data-value-grams="<?php echo $data_term['protein']; ?> g"
                                         data-value-percent="<?php echo $data_term['protein_percentage']; ?>"
                                         data-color="#34A34F">
                                    </div>
                                    <p class="nutrition-item__title">Protein</p>
                                </li>
                            </ul>

                            <button class="builder-footer__button button" type="button">
                                Apply
								<?php echo get_woocommerce_currency_symbol(); ?>
                                <span><?php echo number_format( $data_term['price'], 2 ); ?></span>
                            </button>
                        </div>

                    </footer>

                </form>

            </div>
		<?php }
	}
}
?>