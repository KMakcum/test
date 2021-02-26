<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class SolutionFactorySurvey {
	private static $_instance = null;
	private $survey_score = [];
	private $components_in_groups;

	private function __construct() {
	}

	protected function __clone() {
	}

	/**
	 * @return SolutionFactorySurvey
	 */
	static public function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	function init() {
		add_action( 'init', [ $this, 'register_survey_steps_posttype' ] );

		add_action( 'sf_add_theme_suboption', [ $this, 'add_settings_subpage' ] );

		add_action( 'carbon_fields_register_fields', [ $this, 'add_fields_to_steps' ] );

		add_filter( 'wp_ajax_sf_survey_init', [ $this, 'ajax_survey_init' ] );
		add_filter( 'wp_ajax_nopriv_sf_survey_init', [ $this, 'ajax_survey_init' ] );

		add_filter( 'wp_ajax_sf_survey_get_step', [ $this, 'ajax_survey_get_step' ] );
		add_filter( 'wp_ajax_nopriv_sf_survey_get_step', [ $this, 'ajax_survey_get_step' ] );

		add_filter( 'wp_ajax_sf_survey_get_result', [ $this, 'ajax_survey_get_result' ] );
		add_filter( 'wp_ajax_nopriv_sf_survey_get_result', [ $this, 'ajax_survey_get_result' ] );

		add_filter( 'wp_ajax_sf_survey_get_offerings', [ $this, 'ajax_survey_get_offering' ] );
		add_filter( 'wp_ajax_nopriv_sf_survey_get_offerings', [ $this, 'ajax_survey_get_offering' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'add_variables_to_script' ] );
	}

	// Todo Ask `DK` maybe he has this
	public function filtering_products( $global_cache_items ) {
		// remove components
		$cached_products = array_filter( $global_cache_items, function ( $item ) {
			return ( $item['cat_id'] !== 225 );
		} );

		// score products
		$filter_score     = op_help()->survey->calculate_survey_score();
		$items_with_score = op_help()->survey->calculate_score_for_items( $cached_products, $filter_score );

		op_help()->sort_cache->get_sort_cache( -1 );

		// remove products
		$prepared_items = array_filter( $items_with_score, function ( $item ) {
			return $item['score'] !== 'remove';
		} );

		return $prepared_items;
	}

	function add_variables_to_script() {

		wp_localize_script( 'op_app_bundle', 'sf_survey_ajax', admin_url( 'admin-ajax.php' ) );
		wp_localize_script( 'op_app_bundle', 'sf_survey_theme', get_template_directory_uri() );

	}

	function ajax_survey_get_offering() {

		$survey_results = carbon_get_theme_option( "sf_survey_results" );

		if ( ! empty( $survey_results ) ) {
			foreach ( $survey_results as $data ) {
				switch ( $data['_type'] ) {
					case 'banner':
						$banner_data = $data;
						break;
					case 'products':
						$products_data = $data;
						break;
				}
			}
		}

		$items_to_show_ids = op_help()->sort_cache->get_sort_cache( 20, 0, 'default', false, 1 );
		$offerings         = [];

		foreach ( $items_to_show_ids['ids_with_chef_score'] as $item ) {
			$product            = wc_get_product( $item['id'] );
			$product_from_cache = op_help()->global_cache->get_cached_product( $item['id'] );
			$image              = wp_get_attachment_image_url( $product_from_cache['_thumbnail_id'],
				'op_archive_thumbnail' );

			if ( $product->is_on_sale() ) {
				$price = [
					'current' => number_format( $product_from_cache['_price'], 2 ),
					'old'     => number_format( $product_from_cache['_regular_price'], 2 ),
				];
			} else {
				$price = [
					'current' => number_format( $product_from_cache['_regular_price'], 2 )
				];
			}

			$badges_for_variation = [];
			if ( ! empty( $product_from_cache['badges'] ) ) {
				foreach ( $product_from_cache['badges'] as $badge ) {
					if ( ! empty( $badge['icon_contains'] ) ) {
						$badges_for_variation[] = [
							'title' => $badge['title'],
							'image' => wp_get_attachment_image_url( $badge['icon_contains'], 'full' ),
						];
					}
				}
			}

			$offerings[] = [
				"id"         => $item['id'],
				"link"       => get_the_permalink( $item['id'] ),
				"title"      => $product_from_cache['op_post_title'],
				"price"      => $price,
				"image"      => $image,
				"chef_score" => (int) $item['cs'],
				"badges"     => $badges_for_variation
			];
		}

		$banner = null;

		// TODO collect banner with results of survey
		if ( ! empty( $banner_data ) ) {
			if ( ! empty( $banner_data['background'] ) ) {
				$background_image     = wp_get_attachment_image_url( $banner_data['background'], 'full' );
				$banner['background'] = $background_image;
			}
			if ( ! empty( $banner_data['title'] ) ) {
				$banner['title'] = $banner_data['title'];
			}
			if ( ! empty( $banner_data['description'] ) ) {
				$banner['description'] = explode( "\r\n", $banner_data['description'] );
			}
		}

		// set customer meta for success survey
		$currentUser = wp_get_current_user();
		if ( $currentUser !== 0 ) {
			update_user_meta( $currentUser->ID, 'customer_success_survey', true );
			// add activecampaign customer meta
			$apiUrl = 'https://frontrowlabs.api-us1.com/api/3/contactTags';
			$apiKey = 'd7692ca0146eddda45220525bc08e7855e79c96dbd30930b0dff426cd114755b490e0838';
			$params = [
				'contactTag' => [
					'contact' => get_user_meta( $currentUser->ID, 'activecampaign_user_id', true ),
					'tag'     => get_option( 'active_campaign_survey_passed' )
				]
			];

			wp_remote_post( $apiUrl, array(
				'headers'     => [ 'Content-Type' => 'application/json; charset=utf-8', 'Api-Token' => $apiKey ],
				'body'        => json_encode( $params ),
				'method'      => 'POST',
				'data_format' => 'body',
			) );

			update_user_meta( $currentUser->ID, 'survey_default', 1 );

			do_action( 'make_survey_default' );
		}

		wp_send_json_success( [
			"banner"        => $banner,
			"offeringItems" => $offerings,
			'offeringLink'  => $products_data['offerings_link'],
		] );

	}

	function filter_products_by_category() {
		$global_cache_items = op_help()->global_cache->getAll();
		$prepared_items     = $this->filtering_products( $global_cache_items );

		$categories = get_terms(
			[
				'taxonomy' => [ 'product_cat' ],
				'fields'   => 'id=>name',
				'include'  => array_unique( array_column( $prepared_items, 'cat_id' ) )
			]
		);

		if ( ! empty( $prepared_items ) ) {
			$products = $prepared_items;
		} else {
			$products = $global_cache_items;
		}

		$available_items = [];
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $cat_id => $cat_name ) {
				$this_cat_items    = array_filter( $products, function ( $i ) use ( $cat_id ) {
					return $i['cat_id'] === $cat_id;
				} );
				$items_count       = count( $this_cat_items );
				$available_items[] = [
					'title' => $cat_name,
					'value' => $items_count,
					//'debug' => $this_cat_items,
				];
			}
		}

		return $available_items;
	}

	function ajax_survey_get_result() {

		$user_answers = json_decode( stripslashes( $_POST['answers'] ) );

		$steps_data = [];
		$users_data = [];

		foreach ( $user_answers as $step_key => $step_answers ) {
			if ( $step_key === 'personal' ) {
				foreach ( $step_answers as $question_key => $question_answer ) {
					if ( empty( $question_answer ) ) {
						continue;
					}
					$users_data[ $question_key ] = $question_answer;
				}
			} else {
				foreach ( $step_answers as $question_key => $question_answer ) {
					if ( empty( $question_answer ) ) {
						continue;
					}
					$steps_data[ $step_key ][ $question_key ] = $question_answer;
				}
			}
		}


		// needed answers
		$sub_answers = [];

		foreach ( $steps_data as $key => $answer ) {
			if ( count( $steps_data[$key] ) > 1 ) {
				$get_question = carbon_get_post_meta( $key, 'sf_survey' );

				foreach ( $get_question as $question ) {
					$types = array_column( $question['components'], '_type' );

					if ( in_array( 'condition', $types ) ) {
						$current_sub_question = $question['components'][ array_search('condition', $types) ];
						$value_to_compare = sanitize_title_with_dashes( $current_sub_question['c_value'] );
						$target_question = sanitize_title_with_dashes($current_sub_question['target']);

						if ( ! in_array( $target_question, $sub_answers[$key] ) ) {
							$sub_answers[$key][] = $target_question;
						}

						if ( $current_sub_question['enabled'] && in_array( $value_to_compare ,  $answer[$target_question] )) {
							$sub_answers[$key][] = sanitize_title_with_dashes($question['question_title']);
						}
					}
				}
			}
		}

		foreach ( $sub_answers as $key => $question_id ) {
			$res = array_diff( array_keys( $steps_data[$key] ) , $sub_answers[$key] );

			foreach ( $res as $id ) {
				unset($steps_data[$key][$id]);
			}
		}

		//error_log(print_r(  $steps_data , true));


		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			foreach ( $users_data as $field_key => $field_value ) {
				if ( $field_key === 'name' ) {
					$name = explode( " ", trim( $field_value ) );
					if ( ! empty( $name[0] ) || ! empty( $name[1] ) ) {
						$first_name = empty( $name[0] ) ? '' : $name[0];
					}
					$last_name = empty( $name[1] ) ? '' : $name[1];
					wp_update_user( [
						'ID'         => $current_user->ID,
						'first_name' => $first_name,
						'last_name'  => $last_name,
					] );
					continue;
				}
				update_user_meta( $current_user->ID, 'sf_' . $field_key, $field_value );
			}
			update_user_meta( $current_user->ID, 'survey_answers', $steps_data );
		} else {
			$_SESSION['user_data']  = $users_data;
			$_SESSION['steps_data'] = $steps_data;
		}

		$available_items = $this->filter_products_by_category();

		$strength = $this->calculate_strength( $steps_data );

		$survey_first_state['steps'] = [
			[
				'id'    => "personal",
				'title' => 'Personal Details',
			]
		];
		$active_steps                = carbon_get_theme_option( 'sf_survey_steps' );
		$step_list                   = get_posts( [
			'post_type' => 'sf_survey_step',
			'post__in'  => array_column( $active_steps, 'id' ),
			'orderby'   => 'post__in'
		] );

		foreach ( $step_list as $key => $step_post ) {
			// $step_post = get_post( $step_data['id'] );
			$step_state          = [];
			$step_state['id']    = $step_post->ID;
			$step_state['title'] = $step_post->post_title;

			$survey_first_state['steps'][] = $step_state;
		}

		$change_status = array_map( function ( $item ) use ( $users_data, $steps_data ) {

			if ( $item['id'] === 'personal' ) {

				// fix error update survey user
				if ( $users_data['gender'] === 'male' ) {
					if ( isset( $users_data['pregnant'] ) && $users_data['pregnant'] === 'skipped' ) {
						$users_data['pregnant'] = 'not need';
					}
				}

				foreach ( $users_data as $key => $value ) {
					if ( ! empty( $value ) && $value !== 'skipped' ) {
						$item['success'] = true;
					} else {
						$item['success'] = false;
						$item['error']   = true;
					}
				}
			} else {
				foreach ( $steps_data as $key => $value ) {
					foreach ( $value as $answer ) {
						if ( ! empty( $answer ) ) {
							if ( $item['id'] === $key ) {
								if ( $answer === 'skipped' ) {
									$item['error']   = true;
									$item['success'] = false;
								} else {
									$item['success'] = true;
									$item['error']   = false;
								}
							}
						} else {
							if ( $item['id'] === $key ) {
								$item['error'] = true;
							}
						}
					}

				}
			}

			return $item;

		}, $survey_first_state['steps'] );

		$survey_first_state['steps'] = $change_status;

		wp_send_json_success( [
			'status' => [
				'strength' => $strength['title'], // Weak | average | Good | Strong
				'products' => $available_items,
			],
			'steps'  => $survey_first_state,
			/////// DEBUG /////////
			//'current_user' => $current_user,
//			'users_dat' => $users_data,
//			'steps_dat' => $steps_data,
//			'SESSION_user_data'  => $_SESSION['user_data'],
//			'SESSION_steps_data' => $_SESSION['steps_data'],
		] );

	}

	public function find_by_id( $array, $haystack ) {
		return array_filter( $array, function ( $item ) use ( $haystack ) {
			return ( $item['id'] === $haystack );
		} );
	}

	function calculate_strength( $answers = false ) {

		$strenght_title = 'Unknown';

		if ( is_user_logged_in() ) {

			$current_user = wp_get_current_user();
			$gender       = get_user_meta( $current_user->ID, 'sf_gender', true );
			if ( empty( $answers ) ) {
				$answers = get_user_meta( $current_user->ID, 'survey_answers', true );
			}

		} else {
			$gender = empty( $_SESSION['user_data']['gender'] ) ? 'female' : $_SESSION['user_data']['gender'];

			if ( empty( $answers ) ) {
				$answers = isset( $_SESSION['steps_data'] ) ?? $_SESSION['steps_data'];
			}
		}

		$total_score = 100;

		if ( $gender !== 'male' ) {
			$gender = 'female';
		}

		$active_steps = carbon_get_theme_option( 'sf_survey_steps' );
		$steps_ids    = array_column( $active_steps, 'id' );
		$steps        = array_map( function ( $q_id ) {
			return [ 'id' => $q_id, 'questions' => carbon_get_post_meta( $q_id, 'sf_survey' ) ];
		}, $steps_ids );

		foreach ( $steps as $step ) {
			$step_id = $step['id'];
			foreach ( $step['questions'] as $question ) {
				$condition_data = array_filter( $question['components'], function ( $part ) {
					return $part['_type'] === 'condition';
				} );
				if ( ! empty( $condition_data ) ) {
					if ( isset( $condition_data[0] ) && $condition_data[0]['enabled'] ) {

						if ( ! empty( $answers ) ) {
							if ( ! empty( $answers[ $step_id ] ) ) {
								if ( ! empty( $answers[ $step_id ][ sanitize_title_with_dashes( $condition_data[0]['target'] ) ] ) ) {

									$actual_answer = $answers[ $step_id ][ sanitize_title_with_dashes( $condition_data[0]['target'] ) ];
									switch ( $condition_data[0]['compare'] ) {
										case "not-equal":
											if ( $actual_answer !== $condition_data[0]['c_value'] ) {
												continue 2;
											}
										case "equal":
											if ( $actual_answer === $condition_data[0]['c_value'] ) {
												continue 2;
											}
										case "include":
											if ( empty( $actual_answer ) ) {
												$actual_answer = [];
											}
											if ( $actual_answer === 'skipped' ) {
												$actual_answer = [];
											}
											if ( in_array( $condition_data[0]['c_value'], $actual_answer ) ) {
												continue 2;
											}
										case "not-include":
											if ( empty( $actual_answer ) ) {
												$actual_answer = [];
											}
											if ( $actual_answer === 'skipped' ) {
												$actual_answer = [];
											}
											if ( ! in_array( $condition_data[0]['c_value'], $actual_answer ) ) {
												continue 2;
											}
									}
								}
							}
						}
					}
				}

				$penalty_data = array_filter( $question['components'], function ( $part ) {
					return $part['_type'] === 'question';
				} );


				if ( empty( $penalty_data ) ) {
					continue;
				}
				$penalty_data = $penalty_data[ array_key_last( $penalty_data ) ];
				if ( empty( $penalty_data['strength'] ) ) {
					continue;
				}

				$penalty_score = intval( $penalty_data[ 'strength_' . $gender . '_score' ] );


				$do_penalty = false;
				if ( empty( $answers ) ) {
					$do_penalty = true;
				}
				if ( empty( $answers[ $step_id ] ) ) {
					$do_penalty = true;
				}
				if ( empty( $answers[ $step_id ][ sanitize_title_with_dashes( $question['question_title'] ) ] ) ) {
					$do_penalty = true;
				}
				if ( $answers[ $step_id ][ sanitize_title_with_dashes( $question['question_title'] ) ] === 'skipped' ) {
					$do_penalty = true;
				}

				if ( $do_penalty ) {

					$total_score -= $penalty_score;

				}
			}
		}

		$strenght_limits = carbon_get_theme_option( "sf_survey_strenght" );

		foreach ( $strenght_limits as $limit ) {
			if ( $total_score > intval( $limit['points'] ) ) {
				$strenght_title = $limit['title'];
			}
		}

		return [
			'points' => $total_score,
			'title'  => $strenght_title,
		];

	}

	/**
	 * @param array $items
	 * @param array $score_data
	 *
	 * @return array items with new col - score
	 */
	function calculate_score_for_items( $items, $score_data ) {
		$scored_items = array_map( function ( $item ) use ( $score_data ) {
			$scored_item               = $item;
			$scored_item['score']      = 0;
			$scored_item['chef_score'] = 2;


			if ( empty( $score_data ) ) {
				return $scored_item;
			}

			if ( ! empty( $score_data['component_scoring'] ) ) {
				if ( ! empty( $item['data']['components'] ) ) {
					foreach ( $item['data']['components'] as $component_slug => $component_data ) {
						if ( is_array( $component_data ) ) {
							foreach ( $component_data as $key => $component_id ) {
								if ( ! empty( $score_data['component_scoring'][ $component_id ] ) ) {
									if ( $score_data['component_scoring'][ $component_id ] === 'remove' ) {
										$scored_item['score']      = 'remove';
										$scored_item['chef_score'] = 0;
										break 2; // no need next calculating if remove
									} else {
										$scored_item['score'] += $score_data['component_scoring'][ $component_id ];
										if ( intval( $score_data['component_scoring'][ $component_id ] ) >= 5 ) {
											$scored_item['chef_score'] += 1;
										}
									}
								}
							}
						} else {
							if ( ! empty( $score_data['component_scoring'][ $component_data ] ) ) {
								if ( $score_data['component_scoring'][ $component_data ] === 'remove' ) {
									$scored_item['score']      = 'remove';
									$scored_item['chef_score'] = 0;
									break; // no need next calculating if remove
								} else {
									$scored_item['score'] += $score_data['component_scoring'][ $component_data ];
									if ( intval( $score_data['component_scoring'][ $component_data ] ) >= 5 ) {
										$count_hats                = intval( $score_data['component_scoring'][ $component_data ] ) / 5;
										$scored_item['chef_score'] += floor( $count_hats );
									}
								}
							}
						}
					}
				}
			}

			// todo отрефакторить
			if ( ! empty( $score_data['diet_scoring'] ) ) {
				if ( ! empty( $item['data']['diets'] ) ) {

					foreach ( $item['data']['diets'] as $component_slug => $diet_data ) {
						if ( is_array( $diet_data ) ) {
							foreach ( $diet_data as $key => $diet_slug ) {
								if ( ! empty( $score_data['diet_scoring'][ $diet_slug ] ) ) {
									if ( $score_data['diet_scoring'][ $diet_slug ] === 'remove' ) {
										$scored_item['score'] = 'remove';
										break 2; // no need next calculating if remove
									} else {
										$scored_item['score'] += $score_data['diet_scoring'][ $diet_slug ];
										if ( intval( $score_data['diet_scoring'][ $diet_slug ] ) >= 5 ) {
											$scored_item['chef_score'] += 1;
										}
									}
								}
							}
						} else {
							if ( ! empty( $score_data['diet_scoring'][ $diet_data ] ) ) {

								if ( $score_data['diet_scoring'][ $diet_data ] === 'remove' ) {
									$scored_item['score'] = 'remove';
									break; // no need next calculating if remove
								} else {
									$scored_item['score'] += $score_data['diet_scoring'][ $diet_data ];

									if ( intval( $score_data['diet_scoring'][ $diet_data ] ) >= 5 ) {
										$count_hats                = intval( $score_data['diet_scoring'][ $diet_data ] ) / 5;
										$scored_item['chef_score'] += floor( $count_hats );
									}
								}
							}
						}
					}
				}
			}

			return $scored_item;

		}, $items );

		return $scored_items;

	}

	/**
	 * Exclude components and add score
	 *
	 * @param $components
	 * @param $structure | answers list
	 *
	 * @return mixed
	 */
	public function modify_customize_components( $components, $structure ) {
		$survey_score = $this->calculate_survey_score( $structure );

		$components_update = [];

		foreach ( $components as $id => $data ) {
			if ( ! empty( $survey_score['component_scoring'][ $id ] ) ) {
				$data['score'] = $survey_score['component_scoring'][ $id ];
				$data['chef_score'] = $survey_score['component_scoring'][ $id ] / 5;
			} else {
				$data['score'] = 0;
				$data['chef_score'] = 0;
			}
			$components_update[ $id ] = $data;
		}

		// remove allergic
        return array_filter( $components_update, function ( $item ) {
            return $item['score'] !== 'remove';
        } );
	}

	/**
	 * Filter components for customize by survey and catalog filters.
	 *
	 * @param array $components
	 * @param array $structure | answers list
	 *
	 * @return array
	 */
	function calculate_customize_components( $components, $structure ) {

		if ( empty( $structure ) ) {
			return $components;
		}

		return $this->modify_customize_components( $components, $structure );
	}

	function calculate_survey_score( $answers = false, $user = false ) {
		if ( ! empty( $this->survey_score ) ) {
			return $this->survey_score;
		}

		if ( is_user_logged_in() ) {

			if ( $user === false ) {
				$current_user = wp_get_current_user();
			}

			if ( empty( $answers ) ) {
				$answers = get_user_meta( $current_user->ID, 'survey_answers', true );
			}

		} else {

			if ( empty( $answers ) ) {
				$answers = isset( $_SESSION['steps_data'] ) ? $_SESSION['steps_data'] : '';
			}

		}

		$active_steps = carbon_get_theme_option( 'sf_survey_steps' );
		$steps_ids    = array_column( $active_steps, 'id' );
		$steps        = array_map( function ( $q_id ) {
			return [ 'id' => $q_id, 'questions' => carbon_get_post_meta( $q_id, 'sf_survey' ) ];
		}, $steps_ids );

		$component_scoring  = [];
		$diet_scoring       = [];
		$diet_filter        = [];
		$allergen_scoring   = [];
		$allergen_filter    = [];
		$ingredient_scoring = [];

		if ( empty( $this->components_in_groups ) ) {
			$this->components_in_groups = carbon_get_theme_option( "sf_additional_component_groups" );
		}
		$component_additional_group = $this->components_in_groups;

		$components_group = [];
		foreach ( $component_additional_group as $key => $group_data ) {
			$components_group[ $group_data['slug'] ] = array_map( function ( $component ) {
				return $component['id'];
			}, $group_data['components'] );
		}

		foreach ( $steps as $step ) {
			$step_id = $step['id'];
			foreach ( $step['questions'] as $question ) {
				$condition_data = array_filter( $question['components'], function ( $part ) {
					return $part['_type'] === 'condition';
				} );
				if ( ! empty( $condition_data ) ) {
					if ( $condition_data[ array_key_last( $condition_data ) ]['enabled'] ) {

						if ( ! empty( $answers ) ) {
							if ( ! empty( $answers[ $step_id ] ) ) {
								if ( ! empty( $answers[ $step_id ][ sanitize_title_with_dashes( $condition_data[ array_key_last( $condition_data ) ]['target'] ) ] ) ) {

									$actual_answer = $answers[ $step_id ][ sanitize_title_with_dashes( $condition_data[ array_key_last( $condition_data ) ]['target'] ) ];

									switch ( $condition_data[ array_key_last( $condition_data ) ]['compare'] ) {
										case "not-equal":
											if ( $actual_answer !== $condition_data[ array_key_last( $condition_data ) ]['c_value'] ) {
												continue;
											}
										case "equal":
											if ( $actual_answer === $condition_data[ array_key_last( $condition_data ) ]['c_value'] ) {
												continue;
											}
										case "include":
											if ( empty( $actual_answer ) ) {
												$actual_answer = [];
											}
											if ( $actual_answer === 'skipped' ) {
												$actual_answer = [];
											}
											if ( in_array( $condition_data[ array_key_last( $condition_data ) ]['c_value'],
												$actual_answer ) ) {
												continue;
											}
										case "not-include":
											if ( empty( $actual_answer ) ) {
												$actual_answer = [];
											}
											if ( $actual_answer === 'skipped' ) {
												$actual_answer = [];
											}
											if ( ! in_array( $condition_data[ array_key_last( $condition_data ) ]['c_value'],
												$actual_answer ) ) {
												continue;
											}
									}
								}
							}
						}
					}
				}

				$answers_data = array_filter( $question['components'], function ( $part ) {
					return $part['_type'] === 'answer_list';
				} );
				if ( empty( $answers_data ) ) {
					continue;
				}
				$answers_data = $answers_data[ array_key_last( $answers_data ) ];
				if ( empty( $answers_data['answer_list'] ) ) {
					continue;
				}

				if ( empty( $answers ) ) {
					continue;
				}
				if ( empty( $answers[ $step_id ] ) ) {
					continue;
				}
				if ( empty( $answers[ $step_id ][ sanitize_title_with_dashes( $question['question_title'] ) ] ) ) {
					continue;
				}
				if ( $answers[ $step_id ][ sanitize_title_with_dashes( $question['question_title'] ) ] === 'skipped' ) {
					continue;
				}
				$current_question_answer = $answers[ $step_id ][ sanitize_title_with_dashes( $question['question_title'] ) ];


				foreach ( $answers_data['answer_list'] as $key => $answer ) {

					if ( is_array( $current_question_answer ) ) {
						if ( ! in_array( sanitize_title_with_dashes( $answer['title'] ), $current_question_answer ) ) {
							continue;
						}
					} else {
						if ( sanitize_title_with_dashes( $answer['title'] ) != $current_question_answer ) {
							continue;
						}
					}

					if ( ! empty( $answer['allergens'] ) ) {
						foreach ( $answer['allergens'] as $key => $allergens_scoring_data ) {
							if ( $allergens_scoring_data['mode'] === 'remove' ) {
								$do_score = 'remove';
							} elseif ( $allergens_scoring_data['mode'] === 'pick' ) {
								$do_score = 'pick';
							} else {
								$do_score = intval( $allergens_scoring_data['score'] );
							}
							if ( ! empty( $allergens_scoring_data['items'] ) ) {
								foreach ( $allergens_scoring_data['items'] as $key => $score_item ) {
									$scoring_elems = [ $score_item ];

									foreach ( $scoring_elems as $key => $score_el ) {
										if ( $do_score === 'remove' ) {
											$allergen_scoring[ $score_el ] = 'remove';
										} elseif ( $do_score === 'pick' ) {
											$allergen_filter[] = $score_el;
										} else {
											if ( empty( $component_scoring[ $score_el ] ) ) {
												$allergen_scoring[ $score_el ] = $do_score;
											} else {
												$allergen_scoring[ $score_el ] += $do_score;
											}
										}
									}
								}
							}
						}
					}

					if ( ! empty( $answer['diets'] ) ) {
						foreach ( $answer['diets'] as $key => $diet_scoring_data ) {
							if ( $diet_scoring_data['mode'] === 'remove' ) {
								$do_score = 'remove';
							} elseif ( $diet_scoring_data['mode'] === 'pick' ) {
								$do_score = 'pick';
							} else {
								$do_score = intval( $diet_scoring_data['score'] );
							}
							if ( ! empty( $diet_scoring_data['items'] ) ) {
								foreach ( $diet_scoring_data['items'] as $key => $score_item ) {
									$scoring_elems = [ $score_item ];

									foreach ( $scoring_elems as $key => $score_el ) {
										if ( $do_score === 'remove' ) {
											$diet_scoring[ $score_el ] = 'remove';
										} elseif ( $do_score === 'pick' ) {
											$diet_filter[] = $score_el;
										} else {
											if ( empty( $component_scoring[ $score_el ] ) ) {
												$diet_scoring[ $score_el ] = $do_score;
											} else {
												$diet_scoring[ $score_el ] += $do_score;
											}
										}
									}
								}
							}
						}
					}
					// $components_group
					if ( ! empty( $answer['components'] ) ) {
						foreach ( $answer['components'] as $key => $answer_scoring ) {
							if ( $answer_scoring['mode'] === 'remove' ) {
								$do_score = 'remove';
							} else {
								$do_score = intval( $answer_scoring['score'] );
							}

							if ( ! empty( $answer_scoring['items'] ) ) {
								foreach ( $answer_scoring['items'] as $key => $score_item ) {
									if ( is_string( $score_item ) ) {
										if ( empty( $components_group[ $score_item ] ) ) {
											$scoring_elems = [ $score_item ];
										} else {
											$scoring_elems = $components_group[ $score_item ];
										}
									} else {
										$scoring_elems = [ $score_item ];
									}
									foreach ( $scoring_elems as $key => $score_el ) {
										if ( ! empty( $component_scoring[ $score_el ] ) && $component_scoring[ $score_el ] === 'remove' ) {
											continue;
										}
										if ( $do_score === 'remove' ) {
											$component_scoring[ $score_el ] = 'remove';
										} else {
											if ( empty( $component_scoring[ $score_el ] ) ) {
												$component_scoring[ $score_el ] = $do_score;
											} else {
												$component_scoring[ $score_el ] += $do_score;
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		$this->survey_score = [
			'component_scoring'  => $component_scoring,
			'diet_scoring'       => $diet_scoring,
			'diet_filter'        => $diet_filter,
			'allergen_scoring'   => $allergen_scoring,
			'allergen_filter'    => $allergen_filter,
			'ingredient_scoring' => $ingredient_scoring,
		];

		return $this->survey_score;
	}

	function ajax_survey_init() {

		$survey_first_state             = [];
		$survey_first_state['products'] = $this->filter_products_by_category();
		$survey_first_state['banner']   = [
			'title'      => carbon_get_theme_option( "sf_survey_welcome_title" ),
			'anonymous'  => explode( "\r\n", carbon_get_theme_option( "sf_survey_welcome_anonymous" ) ),
			'authorized' => explode( "\r\n", carbon_get_theme_option( "sf_survey_welcome_authorized" ) ),
		];
		$welcome_background             = carbon_get_theme_option( "sf_survey_welcome_background" );
		if ( ! empty( $welcome_background ) ) {
			$welcome_background_image                   = wp_get_attachment_image_url( $welcome_background, 'full' );
			$survey_first_state['banner']['background'] = $welcome_background_image;
		}

		$current_user     = wp_get_current_user();
		$personal_success = false;

		if ( is_user_logged_in() ) {
			if ( ! empty( $current_user->user_firstname ) ) {
				$personal_success                          = true;
				$survey_first_state['answers']['personal'] = [
					'name' => $current_user->user_firstname . ' ' . $current_user->user_lastname,
				];
			}

			foreach (
				[
					'age',
					'gender',
					'pregnant',
					'weight',
					'height',
				] as $user_field
			) {

				if ( $current_user->{"sf_" . $user_field} ) {
					$survey_first_state['answers']['personal'][ $user_field ] = $current_user->{"sf_" . $user_field};
				} else {
					$personal_success = false;
				}

			}

			$other_answers = $current_user->survey_answers;
			if ( ! empty( $other_answers ) ) {
				foreach ( $other_answers as $step_key => $questions ) {
					if ( empty( $questions ) ) {
						continue;
					}
					foreach ( $questions as $question_key => $question_value ) {
						$survey_first_state['answers'][ $step_key ][ $question_key ] = $question_value;
					}
				}
			}

		} else {

			// $_SESSION['user_data'];
			// $_SESSION['steps_data'];
			if ( ! empty( $_SESSION['user_data'] ) ) {
				foreach ( $_SESSION['user_data'] as $user_field => $user_value ) {
					$survey_first_state['answers']['personal'][ $user_field ] = $user_value;
				}

				$personal_success = true;
			}
			if ( ! empty( $_SESSION['steps_data'] ) ) {
				foreach ( $_SESSION['steps_data'] as $step_key => $questions ) {
					if ( empty( $questions ) ) {
						continue;
					}
					foreach ( $questions as $question_key => $question_value ) {
						$survey_first_state['answers'][ $step_key ][ $question_key ] = $question_value;
					}
				}
			}
		}

		$survey_first_state['steps'] = [
			[
				'id'    => "personal",
				'title' => 'Personal Details',
//				'success' => $personal_success,
//				'error'   => ! $personal_success,
			]
		];
		$active_steps                = carbon_get_theme_option( 'sf_survey_steps' );
		$step_list                   = get_posts( [
			'post_type' => 'sf_survey_step',
			'post__in'  => array_column( $active_steps, 'id' ),
			'orderby'   => 'post__in'
		] );

		foreach ( $step_list as $key => $step_post ) {
			// $step_post = get_post( $step_data['id'] );
			$step_state          = [];
			$step_state['id']    = $step_post->ID;
			$step_state['title'] = $step_post->post_title;

			$survey_first_state['steps'][] = $step_state;
		}

		if (! empty( $survey_first_state['answers'] )) {
			$steps_data = $survey_first_state['answers'];
			$change_status = array_map( function ( $item ) use ( $steps_data ) {


				foreach ( $steps_data as $key => $value ) {
						foreach ( $value as $answer ) {
							if ( ! empty( $answer ) ) {
								if ( $item['id'] === $key ) {
									if ( $answer === 'skipped' ) {
										$item['error']   = true;
										$item['success'] = false;
									} else {
										$item['success'] = true;
										$item['error']   = false;
									}
								}
							} else {
								if ( $item['id'] === $key ) {
									$item['error'] = true;
								}
							}
						}

					}

				return $item;

			}, $survey_first_state['steps'] );

			$survey_first_state['steps'] = $change_status;
		}


		//$survey_first_state['debug'] = $_SESSION;

		wp_send_json_success( $survey_first_state );

	}

	function ajax_survey_get_step() {
		$step_post = get_post( $_POST['step_id'] );

		if ( is_null( $step_post ) ) {
			wp_send_json_error( 'Cannot found this step' );
		}

		$survey_plan = carbon_get_post_meta( $step_post->ID, 'sf_survey' );

		$react_styled_survey_plan = $this->transform_survey_for_react( $survey_plan );

		wp_send_json_success( [
			'questions' => array_filter( $react_styled_survey_plan ),
			'answers'   => [],
		] );

	}

	function transform_survey_for_react( $survey_plan ) {

		$react_styled = array_map( function ( $question ) {

			$react_field = [
				'id'    => sanitize_title_with_dashes( $question['question_title'] ),
				'type'  => $question['_type'],
				'title' => $question['question_title'],
			];

			if ( ! empty( $question['question_placeholder'] ) ) {
				$react_field['label'] = $question['question_placeholder'];
			}


			foreach ( $question['components'] as $part ) {
				switch ( $part['_type'] ) {
					case 'condition':
						if ( $part['enabled'] ) {
							$react_field['condition'] = [];
							if ( ! empty( $part['target'] ) ) {
								$react_field['condition']['target'] = sanitize_title_with_dashes( $part['target'] );
							}
							if ( ! empty( $part['compare'] ) ) {
								$react_field['condition']['compare'] = $part['compare'];
							}
							if ( ! empty( $part['c_value'] ) ) {
								$react_field['condition']['value'] = sanitize_title_with_dashes( $part['c_value'] );
							}
						}
						break;
					case 'banner':
						if ( $part['enabled'] ) {
							$react_field['banner'] = [];
							if ( ! empty( $part['background'] ) ) {
								$react_field['banner']['background'] = wp_get_attachment_image_url( $part['background'],
									'full' );
							}
							if ( ! empty( $part['description'] ) ) {
								$react_field['banner']['description'] = explode( "\r\n", $part['description'] );
							}
							if ( ! empty( $part['title'] ) ) {
								$react_field['banner']['title'] = $part['title'];
							}
						}
						break;
					case 'question':
						if ( ! empty( $part['icon'] ) ) {
							$react_field['icon'] = wp_get_attachment_image_url( $part['icon'], 'full' );
						}
						if ( ! empty( $part['description'] ) ) {
							$react_field['description'] = explode( "\r\n", $part['description'] );
						}
						if ( ! empty( $part['notice'] ) ) {
							$react_field['notice'] = explode( "\r\n", $part['notice'] );
						}
						if ( isset( $part['confirm'] ) ) {
							$react_field['needConfirm']      = $part['confirm'];
							$react_field['buttonText']       = $part['button'];
							$react_field['requiredQuestion'] = $part['required_question'];

						} else {
							$react_field['needConfirm'] = false;
						}
						if ( isset( $part['skip'] ) ) {
							$react_field['canSkip'] = $part['skip'];
						} else {
							$react_field['canSkip'] = false;
						}
						if ( ! empty( $part['show_in_result'] ) ) {
							$react_field['showResult']['title'] = $part['result_title'];
						}


						break;
					case 'answer_list':
						if ( $question['_type'] === 'truefalse' ) {

							if ( isset( $question['answer_list'] ) ) {
								foreach ( $question['answer_list'] as $tf_answers ) {
									if ( ! empty( $tf_answers['title'] ) ) {
										$react_field[ 'title_' . $tf_answers['_type'] ] = $tf_answers['title'];
									}
								}
							}

						} else {

							if ( empty( $part['answer_list'] ) ) {
								return null;
							}
							if ( isset( $part['answers_in_row'] ) ) {
								$react_field['rowCount'] = $part['answers_in_row'];
							}
							$react_field['answers'] = [];
							foreach ( $part['answer_list'] as $answer ) {
								$react_field['answers'][] = [
									'icon'       => ( isset( $answer['icon'] ) ) ? wp_get_attachment_image_url( $answer['icon'],
										'full' ) : '',
									'iconShow'   => isset( $answer['icon_show'] ) ? $answer['icon_show'] : false,
									'title'      => $answer['title'],
									'value'      => sanitize_title_with_dashes( $answer['title'] ),
									'external'   => isset( $answer['external'] ) ? $answer['external'] : false,
									'reset'      => isset( $answer['reset'] ) ? $answer['reset'] : false,
									'showModal'  => isset( $answer['show_modal'] ) ? $answer['show_modal'] : false,
									'modalTitle' => isset( $answer['show_title'] ) ? $answer['show_title'] : false,
									'modalText'  => isset( $answer['modal_text'] ) ? $answer['modal_text'] : false,
								];
							}

						}
						break;
					case 'answer':
						if ( ! empty( $part['label'] ) ) {
							$react_field['label'] = $part['label'];
						}
						if ( ! empty( $part['sign'] ) ) {
							$react_field['sign'] = $part['sign'];
						}
						if ( ! empty( $part['min'] ) ) {
							$react_field['min'] = floatval( $part['min'] );
						} else {
							$react_field['min'] = 1;
						}
						if ( ! empty( $part['max'] ) ) {
							$react_field['max'] = floatval( $part['max'] );
						} else {
							$react_field['max'] = 100;
						}
						if ( ! empty( $part['step'] ) ) {
							$react_field['step'] = floatval( $part['step'] );
						} else {
							$react_field['step'] = 1;
						}
						break;
				}
			}

			return $react_field;

		}, $survey_plan );

		return $react_styled;

	}

	function get_components() {

		$attribute_taxonomies = wc_get_attribute_taxonomies();

		$attribute_taxonomies = array_map(
			'wc_attribute_taxonomy_name',
			array_column(
				wc_get_attribute_taxonomies(),
				'attribute_name'
			)
		);

		$term_query = new WP_Term_Query( [
			'taxonomy'   => $attribute_taxonomies,
			'hide_empty' => false
		] );


		$components_options = [];

		if ( empty( $this->components_in_groups ) ) {
			$this->components_in_groups = carbon_get_theme_option( 'sf_additional_component_groups' );
		}

		$components_in_groups = $this->components_in_groups;

		foreach ( $components_in_groups as $group ) {
			$components_options[ $group['slug'] ] = $group['title'] . ' - All Components';
			foreach ( $group['components'] as $component_item ) {
				$components_options[ $component_item['id'] ] = $group['title'];
			}
		}

		$components_options['non-grouped'] = 'Ungrouped';

		foreach ( $term_query->terms as $all_component_item ) {

			if ( isset( $components_options[ strval( $all_component_item->term_id ) ] ) ) {

				$components_options[ strval( $all_component_item->term_id ) ] .= ' - ' . $all_component_item->name;

			} else {

				$components_options[ strval( $all_component_item->term_id ) ] = 'Ungrouped  - ' . $all_component_item->name;

			}

		}

		return $components_options;

	}

	function add_fields_to_steps() {

		$question_labels = array(
			'plural_name'   => 'Questions',
			'singular_name' => 'Question',
		);

		$attribute_taxonomies = wc_get_attribute_taxonomies();

		if ( $attribute_taxonomies ) {

			$components_fields = Field::make( 'complex', 'components', __( 'Components effect' ) )
			                          ->set_layout( 'tabbed-horizontal' )
			                          ->add_fields( [
				                          Field::make( 'multiselect', 'items', __( 'Choose component or group' ) )
				                               ->add_options( $this->get_components() ),
				                          Field::make( 'radio', 'mode', __( 'Effect' ) )
				                               ->set_options( array(
					                               'remove' => 'Remove',
					                               'score'  => 'Modify score',
				                               ) ),
				                          Field::make( 'text', 'score', __( 'Score' ) )->set_attribute( 'type',
					                          'number' )->set_default_value( 0 )->set_conditional_logic( [
					                          [
						                          'field' => 'mode',
						                          'value' => 'score'
					                          ]
				                          ] )
			                          ] );
		} else {
			$components_fields = Field::make( 'html', 'components' )
			                          ->set_html( '<h3>You have to create attributes first</h3>' );
		}
		$ingredient_groups = carbon_get_theme_option( "op_variations_ingredients" );
		if ( ! empty( $ingredient_groups ) ) {

			$ingredients_options = [];
			foreach ( $ingredient_groups as $ingredient ) {
				$ingredients_options[ sanitize_title_with_dashes( $ingredient['slug'] ) ] = $ingredient['title'];
			}

			$ingredient_fields = Field::make( 'complex', 'ingredients', __( 'Ingredient effect' ) )
			                          ->set_layout( 'tabbed-horizontal' )
			                          ->add_fields( [
				                          Field::make( 'multiselect', 'items', __( 'Choose ingredients' ) )
				                               ->add_options( $ingredients_options ),
				                          Field::make( 'radio', 'mode', __( 'Effect' ) )
				                               ->set_options( array(
					                               'remove' => 'Remove',
					                               'score'  => 'Modify score',
				                               ) ),
				                          Field::make( 'text', 'score',
					                          __( 'Score' ) )->set_default_value( 0 )->set_attribute( 'type',
					                          'number' )->set_conditional_logic( [
					                          [
						                          'field' => 'mode',
						                          'value' => 'score'
					                          ]
				                          ] )
			                          ] );
		} else {
			$ingredient_fields = Field::make( 'html', 'no_ingredient_message' )
			                          ->set_html( '<h3>You have to create ingredients first</h3>' );
		}

		$allergen_groups = carbon_get_theme_option( "op_variations_allergens" );
		if ( ! empty( $allergen_groups ) ) {

			$allergens_options = [];
			foreach ( $allergen_groups as $allergen ) {
				$allergens_options[ sanitize_title_with_dashes( $allergen['slug'] ) ] = $allergen['title'];
			}

			$allergen_fields = Field::make( 'complex', 'allergens', __( 'Allergen effect' ) )
			                        ->set_layout( 'tabbed-horizontal' )
			                        ->add_fields( [
				                        Field::make( 'multiselect', 'items', __( 'Choose allergens' ) )
				                             ->add_options( $allergens_options ),
				                        Field::make( 'radio', 'mode', __( 'Effect' ) )
				                             ->set_options( array(
					                             'pick' => 'Pick (no score, just for filter)',
					                             //'remove' => 'Remove',
					                             //'score'  => 'Modify score',
				                             ) ),
				                        Field::make( 'text', 'score',
					                        __( 'Score' ) )->set_default_value( 0 )->set_attribute( 'type',
					                        'number' )->set_conditional_logic( [
					                        [
						                        'field' => 'mode',
						                        'value' => 'score'
					                        ]
				                        ] )
			                        ] );
		} else {
			$allergen_fields = Field::make( 'html', 'no_allergens_message' )
			                        ->set_html( '<h3>You have to create allergens first</h3>' );
		}

		$diet_groups = carbon_get_theme_option( "sf_diet_groups" );
		if ( ! empty( $diet_groups ) ) {

			$diets_options = [];
			foreach ( $diet_groups as $diet ) {
				$diets_options[ sanitize_title_with_dashes( $diet['slug'] ) ] = $diet['title'];
			}

			$diet_fields = Field::make( 'complex', 'diets', __( 'Diet effect' ) )
			                    ->set_layout( 'tabbed-horizontal' )
			                    ->add_fields( [
				                    Field::make( 'multiselect', 'items', __( 'Choose diets' ) )
				                         ->add_options( $diets_options ),
				                    Field::make( 'radio', 'mode', __( 'Effect' ) )
				                         ->set_options( array(
					                         'pick' => 'Pick (no score, just for filter)',
					                         //'remove' => 'Remove',
					                         //'score'  => 'Modify score',
				                         ) ),
				                    Field::make( 'text', 'score',
					                    __( 'Score' ) )->set_default_value( 0 )->set_attribute( 'type',
					                    'number' )->set_conditional_logic( [
					                    [
						                    'field' => 'mode',
						                    'value' => 'score'
					                    ]
				                    ] )
			                    ] );
		} else {
			$diet_fields = Field::make( 'html', 'no_diets_message' )
			                    ->set_html( '<h3>You have to create attributes first</h3>' );
		}

		$question_main_param = Field::make( 'complex', 'components', __( 'Question options' ) )
		                            ->set_layout( 'tabbed-horizontal' )->set_duplicate_groups_allowed( false )
		                            ->add_fields( 'condition', [
			                            Field::make( 'html',
				                            'crb_information_text' )->set_html( '<p>This is condition to skip question.</p>' ),
			                            Field::make( 'checkbox', 'enabled', __( 'Active?' ) ),
			                            Field::make( 'text', 'target', __( 'Question' ) ),
			                            Field::make( 'select', 'compare', __( 'Compare type' ) )->set_options( array(
				                            'equal'       => 'Equal',
				                            'not-equal'   => 'Not equal',
				                            'include'     => 'Include',
				                            'not-include' => 'Not Include',
			                            ) ),
			                            Field::make( 'text', 'c_value', __( 'Compare Value' ) ),
		                            ] )
		                            ->add_fields( 'banner', [
			                            Field::make( 'checkbox', 'enabled',
				                            __( 'Enable custon banner for this question?' ) ),
			                            Field::make( 'image', 'background', __( 'Banner background' ) ),
			                            Field::make( 'text', 'title', __( 'Banner title' ) ),
			                            Field::make( 'textarea', 'description',
				                            __( 'Banner description' ) )->set_help_text( "Each line is text block, rows started with [subtitle] is subtitles" ),
		                            ] )
		                            ->add_fields( 'question', [
			                            Field::make( 'image', 'icon', __( 'Question icon' ) ),
			                            Field::make( 'textarea', 'description', __( 'Question description' ) ),
			                            Field::make( 'textarea', 'notice', __( 'Question notice' ) ),
			                            Field::make( 'checkbox', 'confirm', __( 'Need confirm chosen answer?' ) ),
			                            Field::make( 'checkbox', 'required_question',
				                            __( 'Disable button, if no answer chosen (required question)' ) ),
			                            Field::make( 'text', 'button',
				                            __( 'Button text' ) )->set_default_value( "Continue" )->set_conditional_logic( array(
				                            array(
					                            'field' => 'confirm',
					                            'value' => true,
				                            )
			                            ) ),
			                            Field::make( 'checkbox', 'skip', __( 'Can skip this question?' ) ),
			                            Field::make( 'checkbox', 'show_in_result', __( 'Show in results?' ) ),
			                            Field::make( 'text', 'result_title', __( 'Title in result' ) ),
			                            Field::make( 'checkbox', 'strength', __( 'Active strength score' ) ),
			                            Field::make( 'text', 'strength_male_score',
				                            __( 'Male penalty' ) )->set_conditional_logic( array(
				                            array(
					                            'field' => 'strength',
					                            'value' => true,
				                            )
			                            ) ),
			                            Field::make( 'text', 'strength_female_score',
				                            __( 'Female and other penalty' ) )->set_conditional_logic( array(
				                            array(
					                            'field' => 'strength',
					                            'value' => true,
				                            )
			                            ) ),
		                            ] );

		Container::make( 'post_meta', 'Step Questions' )
		         ->where( 'post_type', '=', 'sf_survey_step' )
		         ->add_fields( array(

			         Field::make( 'complex', 'sf_survey', __( 'Questions' ) )
			              ->setup_labels( $question_labels )
			              ->set_layout( 'tabbed-vertical' )
			              ->add_fields( 'range', array(
				              Field::make( 'text', 'question_title', __( 'Question text' ) ),
				              ( clone $question_main_param )
					              ->add_fields( 'answer', [
						              Field::make( 'text', 'min', __( 'Answer min' ) )->set_attribute( 'type',
							              'number' ),
						              Field::make( 'text', 'max', __( 'Answer max' ) )->set_attribute( 'type',
							              'number' ),
						              Field::make( 'text', 'step', __( 'Answer step' ) )->set_attribute( 'type',
							              'number' ),
						              Field::make( 'text', 'label', __( 'Answer label' ) ),
						              Field::make( 'text', 'sign', __( 'Answer sign' ) ),
					              ] )
			              ,
			              ) )
			              ->set_header_template( '
            (Range)
            <% if (question_title) { %>
              <%- question_title %>
            <% } %>
          ' )
			              ->add_fields( 'checkbox', array(
				              Field::make( 'text', 'question_title', __( 'Question text' ) ),
				              Field::make( 'text', 'show_in_catalog_filters_title', __( 'Title for filters' ) ),
				              Field::make( 'checkbox', 'show_in_catalog_filters', __( 'Show filters' ) ),
				              Field::make( 'radio', 'filter_type', __( 'Filter type' ) )
				                   ->set_options( array(
					                   'allergies' => "Allergies",
					                   'diets'     => "Diets",
				                   ) )
				                   ->set_conditional_logic( array(
					                   array(
						                   'field' => 'show_in_catalog_filters',
						                   'value' => true,
					                   )
				                   ) ),
				              ( clone $question_main_param )
					              ->add_fields( 'answer_list', [
						              Field::make( 'radio', 'answers_in_row', __( 'Answers in each row' ) )
						                   ->set_options( array(
							                   '1' => "1 per row",
							                   '2' => "2 per row",
							                   '3' => "3 per row",
							                   '5' => "5 per row",
						                   ) ),
						              Field::make( 'complex', 'answer_list', __( 'Answers' ) )
						                   ->set_layout( 'tabbed-vertical' )
						                   ->add_fields( [
							                   Field::make( 'checkbox', 'reset',
								                   __( 'Reset answer?' ) )->set_help_text( 'If checked this answer will unchecked other answers' ),
							                   Field::make( 'checkbox', 'show_in_filter',
								                   __( 'Show in filter' ) )->set_help_text( 'If checked this answer will be shown in filter catalog' ),
							                   Field::make( 'image', 'icon', __( 'Answer icon' ) ),
							                   Field::make( 'checkbox', 'icon_show', __( 'Show icon' ) ),
							                   Field::make( 'text', 'title', __( 'Answer text' ) ),
							                   Field::make( 'checkbox', 'external', __( 'External text?' ) ),
							                   Field::make( 'textarea', 'note', __( 'Answer note' ) ),
							                   $components_fields,
							                   $diet_fields,
							                   $allergen_fields,
							                   $ingredient_fields,
						                   ] )
						                   ->set_header_template( '<%- title ? title : \'Not setted\' %>' )
					              ] )
			              ) )
			              ->set_header_template( '
            (Checkboxes)
            <% if (question_title) { %>
              <%- question_title %>
            <% } %>
          ' )
			              ->add_fields( 'radio', array(
				              Field::make( 'text', 'question_title', __( 'Question text' ) ),
				              Field::make( 'text', 'show_in_catalog_filters_title', __( 'Title for filters' ) ),
				              Field::make( 'checkbox', 'show_in_catalog_filters', __( 'Show filters' ) ),
				              Field::make( 'radio', 'filter_type', __( 'Filter type' ) )
				                   ->set_options( array(
					                   'allergies' => "Allergies",
					                   'diets'     => "Diets",
				                   ) )
				                   ->set_conditional_logic( array(
					                   array(
						                   'field' => 'show_in_catalog_filters',
						                   'value' => true,
					                   )
				                   ) ),
				              ( clone $question_main_param )
					              ->add_fields( 'answer_list', [
						              Field::make( 'radio', 'answers_in_row', __( 'Answers in each row' ) )
						                   ->set_options( array(
							                   '1' => "1 per row",
							                   '2' => "2 per row",
							                   '3' => "3 per row",
							                   '5' => "5 per row",
						                   ) ),
						              Field::make( 'complex', 'answer_list', __( 'Answers' ) )
						                   ->set_layout( 'tabbed-vertical' )
						                   ->add_fields( [
							                   Field::make( 'checkbox', 'show_in_filter',
								                   __( 'Show in filter' ) )->set_help_text( 'If checked this answer will be shown in filter catalog' ),
							                   Field::make( 'image', 'icon', __( 'Answer icon' ) ),
							                   Field::make( 'checkbox', 'icon_show', __( 'Show icon' ) ),
							                   Field::make( 'text', 'title', __( 'Answer text' ) ),
							                   Field::make( 'checkbox', 'external', __( 'External text?' ) ),
							                   Field::make( 'textarea', 'note', __( 'Answer note' ) ),
							                   Field::make( 'checkbox', 'show_modal', __( 'Show modal?' ) ),
							                   Field::make( 'text', 'modal_title', __( 'Modal title' ) ),
							                   Field::make( 'textarea', 'modal_note', __( 'Modal text' ) ),
							                   $components_fields,
							                   $diet_fields,
							                   $allergen_fields,
							                   $ingredient_fields,
						                   ] )
						                   ->set_header_template( '<%- title ? title : \'Not setted\' %>' )
					              ] )
			              ) )
			              ->set_header_template( '
            (Radio)
            <% if (question_title) { %>
              <%- question_title %>
            <% } %>
          ' )
			              ->add_fields( 'multiple', array(
				              Field::make( 'text', 'question_title', __( 'Question text' ) ),
				              Field::make( 'text', 'question_placeholder', __( 'Placeholder text' ) ),
				              ( clone $question_main_param )
					              ->add_fields( 'answer_list', [
						              Field::make( 'complex', 'answer_list', __( 'Answers' ) )
						                   ->set_layout( 'tabbed-vertical' )
						                   ->add_fields( [
							                   Field::make( 'text', 'title', __( 'Answer text' ) ),
							                   $components_fields,
							                   $diet_fields,
							                   $allergen_fields,
							                   $ingredient_fields,
						                   ] )
						                   ->set_header_template( '<%- title ? title : \'Not setted\' %>' )
					              ] )
			              ) )
			              ->set_header_template( '
            (Multiple)
            <% if (question_title) { %>
              <%- question_title %>
            <% } %>
          ' )
			              ->add_fields( 'truefalse', array(
				              Field::make( 'text', 'question_title', __( 'Question text' ) ),
				              ( clone $question_main_param )
					              ->add_fields( 'answer_list', [
						              Field::make( 'complex', 'answer_list', __( 'Answers' ) )
						                   ->set_layout( 'tabbed-vertical' )->set_min( 2 )->set_max( 2 )->set_duplicate_groups_allowed( false )
						                   ->add_fields( 'true', array(
							                   Field::make( 'text', 'title', __( 'Answer text' ) ),
							                   Field::make( 'checkbox', 'modal_alert', __( 'Show Alert Modal ?' ) ),
							                   Field::make( 'rich_text', 'modal_alert_text',
								                   __( 'Alert text' ) )->set_conditional_logic( [
								                   [
									                   'field' => 'modal_alert',
									                   'value' => true,
								                   ]
							                   ] ),
							                   $components_fields,
							                   $diet_fields,
							                   $allergen_fields,
							                   $ingredient_fields,
						                   ) )
						                   ->add_fields( 'false', array(
							                   Field::make( 'text', 'title', __( 'Answer text' ) ),
							                   $components_fields,
							                   $diet_fields,
							                   $allergen_fields,
							                   $ingredient_fields,
						                   ) )
					              ] )
			              ) )
			              ->set_header_template( '
            (TrueFalse)
            <% if (question_title) { %>
              <%- question_title %>
            <% } %>
          ' )

		         ) );

	}

	function register_survey_steps_posttype() {
		register_post_type( 'sf_survey_step', [
			'label'               => null,
			'labels'              => [
				'name'               => 'Survey',
				'singular_name'      => 'Survey Steps',
				'add_new'            => 'Add step',
				'add_new_item'       => 'Adding survey step',
				'edit_item'          => 'Edit step',
				'new_item'           => 'New step',
				'view_item'          => 'View step',
				'search_items'       => 'Find step',
				'not_found'          => 'Not found',
				'not_found_in_trash' => 'Not found in trash',
				'parent_item_colon'  => '',
				'menu_name'          => 'Survey',
			],
			'description'         => '',
			'public'              => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'rest_base'           => null,
			'menu_icon'           => 'dashicons-table-col-after',
			'hierarchical'        => false,
			'supports'            => [ 'title' ],
			'has_archive'         => false,
			'rewrite'             => true,
			'query_var'           => true,
		] );
	}

	function add_settings_subpage( $main_page ) {

		$attribute_taxonomies = array_map(
			'wc_attribute_taxonomy_name',
			array_column(
				wc_get_attribute_taxonomies(),
				'attribute_name'
			)
		);

		Container::make( 'theme_options', 'Survey Addon' )
		         ->set_page_parent( $main_page ) // reference to a top level container
		         ->add_tab( __( 'Main Settings' ), array(
				Field::make( 'image', 'sf_survey_welcome_background', __( 'Welcmoe Background Image' ) ),
				Field::make( 'text', 'sf_survey_welcome_title',
					__( 'Welcome title' ) )->set_default_value( 'Welcome to LifeChef™!' ),
				Field::make( 'textarea', 'sf_survey_welcome_anonymous',
					__( 'Welcome description for anonymous users' ) ),
				Field::make( 'textarea', 'sf_survey_welcome_authorized',
					__( 'Welcome description for authorized users' ) ),

				Field::make( 'complex', 'sf_survey_strenght', __( 'Strength limits' ) )
				     ->set_layout( 'tabbed-horizontal' )
				     ->add_fields( [
					     Field::make( 'text', 'title', __( 'Strength title' ) ),
					     Field::make( 'text', 'points', __( 'Strength Point Limit' ) )->set_attribute( 'type',
						     'number' )->set_default_value( 20 ),
				     ] )
				     ->set_header_template( '
            <% if (title) { %>
              <%- title %>
            <% } %>
            ( <%- points %> points )
          ' )
			,
			) )
		         ->add_tab( __( 'Steps Settings' ), array(
			         Field::make( 'association', 'sf_survey_steps', __( 'Survey Steps' ) )
			              ->set_types( array(
				              array(
					              'type'      => 'post',
					              'post_type' => 'sf_survey_step',
				              )
			              ) )
		         ) )
		         ->add_tab( __( 'Diet group' ), array(

			         Field::make( 'complex', 'sf_diet_groups', __( 'Diet group' ) )
			              ->set_layout( 'tabbed-vertical' )
			              ->add_fields( array(
				              Field::make( 'text', 'title', __( 'Title' ) ),
				              Field::make( 'text', 'slug', __( 'Unique slug' ) ),
			              ) )
			              ->set_header_template( '
                  <% if (title) { %>
                    <%- title %>
                  <% } %>
                ' )
		         ) )
		         ->add_tab( __( 'Additional Components group' ), array(
			         Field::make( 'complex', 'sf_additional_component_groups', __( 'Groups' ) )
			              ->set_layout( 'tabbed-vertical' )
			              ->add_fields( array(
				              Field::make( 'text', 'title', __( 'Title' ) ),
				              Field::make( 'text', 'slug', __( 'Slug' ) ),
				              Field::make( 'association', 'components', __( 'Components' ) )
				                   ->set_types( array_map( function ( $tax_name ) {
					                   return [
						                   'type'     => 'term',
						                   'taxonomy' => $tax_name,
					                   ];
				                   }, $attribute_taxonomies ) )
			              ) )
			              ->set_header_template( '
            <% if (title) { %>
              <%- title %>
            <% } %>
            ( <%- components.length %> chosen )
          ' )

		         ) )
		         ->add_tab( __( 'Survey results' ), array(
			         Field::make( 'complex', 'sf_survey_results', __( 'Survey results' ) )
			              ->set_layout( 'tabbed-vertical' )->set_duplicate_groups_allowed( false )
			              ->add_fields( 'banner', [
				              Field::make( 'image', 'background', __( 'Banner background' ) ),
				              Field::make( 'text', 'title',
					              __( 'Banner title' ) )->set_help_text( "Use [name] tag to show user\'s name" ),
				              Field::make( 'textarea', 'description',
					              __( 'Banner description' ) )->set_help_text( "Each line is text block, rows started with [subtitle] is subtitles" ),
			              ] )
			              ->add_fields( 'products', [
				              Field::make( 'text', 'offerings_title',
					              __( 'Offerings title' ) )->set_default_value( 'Your personal offerings' ),
				              Field::make( 'text', 'offerings_link', __( 'Offerings link' ) ),
				              Field::make( 'text', 'offerings_button',
					              __( 'Offerings Button Text' ) )->set_default_value( 'Go to my offerings' ),
				              Field::make( 'text', 'offerings_count',
					              __( 'Max count of products for slider' ) )->set_attribute( 'type',
					              'number' )->set_default_value( 8 ),
			              ] )
		         ) );
	}

}
