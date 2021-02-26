<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $sf_results_filtered_items;

$filter_score = op_help()->survey->calculate_survey_score();
$filters_data = unserialize( get_option( 'filters_data_cache' ) );

if ( empty( $filters_data ) ) {
	return;
}
?>

<ul class="products__filter-list filter-list">
	<?php foreach (
		$filters_data

		as $filter
	) {

		if ( $filter['type'] === 'allergies' ) {
			$allergies_options = $filter['answers'];

			if ( ! empty( $allergies_options ) ) {
				$allergies_filter = op_help()->sf_filters->get_selected_allergens( $filter['question_title'] );
				$chosen_allergies = is_array( $allergies_filter ) ? $allergies_filter : [];

				$survey_allergies        = [];
				$survey_allergies_filter = empty( $filter_score['allergen_filter'] ) ? [] : $filter_score['allergen_filter'];

//        if ($use_survey && !empty($filter_score['allergen_scoring'])) {
//            foreach ($allergies_options as $allergen) {
//                if (!empty($filter_score['allergen_scoring'][$allergen['slug']])) {
//                    if ($filter_score['allergen_scoring'][$allergen['slug']] === 'remove') {
//                        $survey_allergies[] = $allergen['slug'];
//                    }
//                }
//            }
//        }

				if ( $use_survey ) {
					$same_filters   = array_intersect( $chosen_allergies, $survey_allergies_filter );
					$chosen_filters = count( $chosen_allergies ) + count( $survey_allergies_filter );

					if ( ! empty( $same_filters ) ) {
						$chosen_filters = $chosen_filters - count( $same_filters );
					}

				} else {
					$chosen_filters = count( $chosen_allergies );
				}

				$add_recommend_class = $chosen_filters > 0 ? 'filter-list__button--recommended' : '';
				?>
                <li class="filter-list__item">
                    <button class="filter-list__button <?php echo esc_attr( $add_recommend_class ); ?>" type="button">
						<?php echo $filter['show_title']; ?>
						<?php if ( $chosen_filters > 0 ) { ?>
                            <span class="filter-list__button-counter filter-list__button-counter--recommended"><?php echo esc_html( $chosen_filters ); ?></span>
						<?php } ?>
                    </button>
                    <form class="filter-list__dropdown products-filter products-filter--default ajax_filter_products"
                          action="<?php //echo home_url(add_query_arg(array(), $wp->request)); ?>" method="post">
                        <div class="header-products-filter">
                            <div class="header-products-filter__actions">
                                <button class="header-products-filter__clear products-filter__clear"
                                        type="button"><?php _e( 'Clear' ); ?></button>
                                <p class="header-products-filter__title"><?php _e( 'Allergies' ); ?></p>
                                <button class="header-products-filter__close control-button control-button--no-txt control-button--close"
                                        type="button">
                                    <svg class="control-button__icon" width="24" height="24" fill="#252728">
                                        <use href="#icon-times"></use>
                                    </svg>
                                </button>
                            </div>

							<?php if ( is_user_logged_in() ) { ?>
								<?php if ( $use_survey ) { ?>
                                    <div class="header-products-filter__txt content">
                                        Applied based on your survey results. If you would like to
                                        modify this list <a href="#" class="sf_open_survey">re-take the survey</a>
                                        again.
                                    </div>
								<?php } else { ?>
                                    <a class="header-products-filter__offer offer-card-2 sf_open_survey" href="#">
                                        <div class="offer-card-2__body">
                                            <p class="offer-card-2__title"><?php _e( 'Personalize your experience' ); ?></p>
                                            <span class="offer-card-2__button control-button control-button--small control-button--invert control-button--color--main">
                                    <?php _e( 'Take a Survey' ); ?>
                                    <svg class="control-button__icon" width="24" height="24" fill="#0A6629">
                                        <use href="#icon-angle-rigth-light"></use>
                                    </svg>
                                </span>
                                        </div>
                                        <picture>
                                            <source srcset="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/base/personalize-your-experience-2.webp"
                                                    type="image/webp">
                                            <img class="offer-card-2__bg"
                                                 src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/base/personalize-your-experience-2.jpg"
                                                 alt="">
                                        </picture>
                                    </a>
								<?php } ?>
							<?php } ?>
                        </div>
                        <div class="body-products-filter">
                            <ul class="body-products-filter__checkbox-list checkbox-list">
								<?php foreach ( $allergies_options as $allergen ) {
									$is_checked  = '';
									$is_disabled = '';
									if ( in_array( $allergen['slug'], $chosen_allergies ) ) {
										$is_checked = ' checked';
									}
									if ( $use_survey && in_array( $allergen['slug'], $survey_allergies ) ) {
										$is_disabled = ' disabled';
										$is_checked  = ' checked';
									}
									if ( $use_survey && in_array( $allergen['slug'], $survey_allergies_filter ) ) {
										$container_disabled = 'checkbox-2--disabled';
										$is_disabled        = ' disabled';
										$is_checked         = ' checked';
									}
									?>
                                    <li class="checkbox-list__item">
                                        <label class="checkbox-item checkbox-item--type--2">
                                            <input
                                                    class="checkbox-item__field visually-hidden"
                                                    type="checkbox"
                                                    name="allergies[]"
                                                    value="<?php echo esc_attr( $allergen['slug'] ) ?>"
												<?php
												echo $is_disabled;
												echo $is_checked;
												?>
                                            >
											<?php
											if ( ! empty( $allergen['icon'] ) ) {
												$image_object = get_post( $allergen['icon'] );
												?>
                                                <span class="checkbox-item__box">
                                            <?php
                                            echo wp_get_attachment_image( $allergen['icon'], 'op_single_thumbnail',
	                                            false, [ 'class' => "checkbox-item__icon style-svg" ] );
                                            //                                            if ( $image_object->post_mime_type === "image/svg+xml" ) {
                                            //	                                            echo file_get_contents( wp_get_attachment_image_url( $allergen['icon'],
                                            //		                                            'full' ) );
                                            //                                            } else {
                                            //	                                            echo wp_get_attachment_image( $allergen['icon'], 'op_single_thumbnail',
                                            //		                                            false, [ 'class' => "checkbox-item__icon style-svg" ] );
                                            //                                            }
                                            ?>
                                          </span>

											<?php } ?>

                                            <span class="checkbox-item__txt"><?php echo esc_html( $allergen['title'] ) ?></span>
                                        </label><!-- / .checkbox-item -->
                                    </li>
								<?php } ?>
                            </ul><!-- / .checkbox-list -->
                        </div>

                        <div class="footer-products-filter">
                            <button class="footer-products-filter__clear products-filter__clear" type="button">Clear
                            </button>
                            <span class="footer-products-filter__count">
                                <span><?php echo esc_html( $sf_results_filtered_items ); ?></span> Results
                            </span>
                            <button class="footer-products-filter__button button">Apply</button>
                        </div>
                        <input type="hidden" name="question_id" value="<?php echo $filter['question_id'] ?>">
						<?php //wc_query_string_form_fields(null, array('allergies')); ?>
                    </form>
                </li>
			<?php }
		} ?>

		<?php

		if ( $filter['type'] === 'diets' ) {
			$diets_options = $filter['answers'];

			if ( ! empty( $diets_options ) ) {
				$diets_filter = op_help()->sf_filters->get_selected_allergens( $filter['question_title'] );
				$chosen_diets = is_array( $diets_filter ) ? $diets_filter : [];

				$survey_diets       = [];
				$survey_diet_filter = empty( $filter_score['diet_filter'] ) ? [] : $filter_score['diet_filter'];

				if ( $use_survey ) {
					$chosen_filters = count( $chosen_diets ) + count( $survey_diet_filter );
				} else {
					$chosen_filters = count( $chosen_diets );
				}

				$add_recommend_class = $chosen_filters > 0 ? 'filter-list__button--recommended' : '';
				?>
                <li class="filter-list__item">
                    <button class="filter-list__button <?php echo esc_attr( $add_recommend_class ); ?>" type="button">
                        Diet
						<?php if ( $chosen_filters > 0 ) { ?>
                            <span class="filter-list__button-counter filter-list__button-counter--recommended"><?php echo esc_html( $chosen_filters ); ?></span>
						<?php } ?>
                    </button>
                    <form class="filter-list__dropdown products-filter products-filter--default ajax_filter_products"
                          action="<?php //echo home_url( add_query_arg( array(), $wp->request ) ); ?>" method="post">
                        <div class="header-products-filter">
                            <div class="header-products-filter__actions">
                                <button class="header-products-filter__clear products-filter__clear" type="button">Clear
                                </button>
                                <p class="header-products-filter__title"><?php _e( 'Preferred diet' ); ?></p>
                                <button class="header-products-filter__close control-button control-button--no-txt control-button--close"
                                        type="button">
                                    <svg class="control-button__icon" width="24" height="24" fill="#252728">
                                        <use href="#icon-times"></use>
                                    </svg>
                                </button>
                            </div>
							<?php if ( $use_survey ) { ?>
                                <div class="header-products-filter__txt content">
                                    Applied based on your survey results. If you would like to
                                    modify this list <a href="#" class="sf_open_survey">re-take the survey</a> again.
                                </div>
							<?php } ?>
                        </div><!-- / .header-products-filter -->
                        <div class="body-products-filter">
                            <ul class="body-products-filter__checkboxes checkboxes checkboxes--columns--2">
								<?php foreach ( $diets_options as $diet ) {
									$is_checked         = '';
									$is_disabled        = '';
									$container_disabled = '';
									if ( in_array( $diet['slug'], $chosen_diets ) ) {
										$is_checked = ' checked';
									}
									if ( $use_survey ) {
										if ( in_array( $diet['slug'], $survey_diets ) ) {
											$is_disabled        = ' disabled';
											$container_disabled = 'checkbox-2--disabled';
											$is_checked         = '';
										}
										if ( in_array( $diet['slug'], $survey_diet_filter ) ) {
											$container_disabled = 'checkbox-2--disabled';
											$is_disabled        = ' disabled';
											$is_checked         = ' checked';
										}
									}
									?>
                                    <li class="checkboxes__item">
                                        <label class="checkbox-2 <?php echo esc_attr( $container_disabled ); ?>">
                                            <input
                                                    class="checkbox-2__field visually-hidden"
                                                    type="checkbox"
                                                    name="diets[]"
                                                    value="<?php echo esc_attr( $diet['slug'] ) ?>"
												<?php
												echo $is_disabled;
												echo $is_checked;
												?>
                                            >
                                            <span class="checkbox-2__txt"><?php echo esc_html( $diet['title'] ) ?></span>
                                        </label><!-- / .checkbox-2 -->
                                    </li>
								<?php } ?>
                            </ul><!-- / .checkboxes -->
                        </div><!-- / .body-products-filter -->
                        <div class="products-filter__bottom">
                            <button class="products-filter__clear" type="button">Clear</button>
                            <!-- <span class="products-filter__count">458 Results</span> -->
                            <button class="products-filter__button button button--medium">Apply</button>
                        </div>
                        <input type="hidden" name="question_id" value="<?php echo $filter['question_id'] ?>">
						<?php //wc_query_string_form_fields(null, array('diets')); ?>

                    </form>
                </li>

			<?php }
		}

	} ?>
</ul>