<?php
/**
 * Save zip-code for user
 */
function op_zipcode_user() {
	parse_str( $_POST['form'], $form_data );
	$modal_type = 'updated'; // make default type "updated"

	$subscription = op_help()->subscriptions->get_current_subscription();
	$zip_code     = sanitize_text_field( $form_data['zip_code'] );
	$redirect     = sanitize_text_field( $form_data['redirect'] );
	$is_ajax 	  = sanitize_text_field( $form_data['is_ajax'] );
	$need_signup  = filter_var( $form_data['sign_up_flow'], FILTER_VALIDATE_BOOLEAN );

	if ( empty( $zip_code ) ) {
		wp_send_json_error( [
			'message' => 'Field "Zip-Code" must not be empty',
		] );
	}

	$is_front_meal_slider = isset( $form_data['is-front-meal-slider'] ) ? (bool) $form_data['is-front-meal-slider'] : false;
	$current_page         = isset( $form_data['current_page'] ) ? $form_data['current_page'] : false;
	$reload_anyway        = in_array( $form_data['current_page'], [ 'offerings', 'product-category', 'product-tag' ] );
	$confirm_change_modal = isset( $_POST['confirm'] ) && $_POST['confirm'] == true;

	$user_zip        = op_help()->zip_codes->get_current_user_zip();
	$initial_set     = is_null( $user_zip );
	$user_zone       = ! empty( op_help()->zip_codes->get_zip_zone( $zip_code ) ) ? op_help()->zip_codes->get_zip_zone( $zip_code ) : 'national';
	$is_zip_national = op_help()->zip_codes->is_zip_zone_national( $zip_code, $user_zone );

	// Change flow if subscription exist
	if ($subscription === false) {
		// Check is confirmation
		if ( $confirm_change_modal ) {
			op_help()->zip_codes->set_user_data( $zip_code, $user_zone );

			// Remove all meals & groceries from cart
			$update_result          = op_help()->shop->remove_all_meals_from_cart();
			$grocery_remove_results = op_help()->shop->remove_all_groceries_from_cart();
			$modal_type             = 'updated';
		} else {
			// if national and not first set
			if ( $is_zip_national && ! $initial_set ) {
				wp_send_json_error( array(
					'confirm' => true,
					'zip'     => $zip_code,
					'show'    => $user_zone,
				) );
			}

			$modal_type = $user_zone;

			op_help()->zip_codes->set_user_data( $zip_code, $user_zone );
		}
	}else { // subscription exist
		if ($is_zip_national && !$confirm_change_modal) {
			wp_send_json_error( array(
				'confirm' => true,
				'zip' 	  => $zip_code,
				'show'    => $user_zone,
			) );
		}else {
			// Get address by zip
			$data = op_help()->sf_user::get_user_address_by_zip( $zip_code );
	        $term_data = $data['predictions'][0]['terms'];

			wp_send_json_error( array(
				'zip'     => $zip_code,
				'show'    => 'address',
				'city' => $term_data[0]['value'],
				'state' => $term_data[1]['value'],
				'country' => substr($term_data[3]['value'], 0, 2),
			) );
		}

		$modal_type = $user_zone;

		op_help()->zip_codes->set_user_data( $zip_code, $user_zone );
	}

	$success = [
		'cookie'               => op_help()->sf_user::op_get_zip_cookie(),
		'zip'                  => $zip_code,
		'redirect'             => $redirect,
		'is_ajax'			   => $is_ajax,
		'show'                 => $modal_type,
		'is_initial'           => $initial_set,
		'reload_anyway'        => $reload_anyway,
		'register_title'       => op_help()->zip_codes->get_city_by_zip( $zip_code ),
		'need_signup'          => $need_signup,
		'is_zip_not_national'  => ! $is_zip_national,
		'is_front_meal_slider' => $is_front_meal_slider,
		'current_page'         => $current_page
	];

	if ( $confirm_change_modal ) {
		$update_result            += $grocery_remove_results;
		$success['update_result'] = $update_result;
	}

	wp_send_json_success( $success );
}

if ( wp_doing_ajax() ) {
	add_action( 'wp_ajax_zipcode_user', 'op_zipcode_user' );
	add_action( 'wp_ajax_nopriv_zipcode_user', 'op_zipcode_user' );
}