<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class PaymentPayeezy {

	private static $_instance = null;

	/** payeezy gateway API production URL, v19 */
	const PRODUCTION_URL = 'https://api.globalgatewaye4.firstdata.com/transaction/v19';

	/** payeezy gateway API demo URL, v19 */
	const DEMO_URL = 'https://api.demo.globalgatewaye4.firstdata.com/transaction/v19';

	const AUTH_TYPE_ONLY = '05';

	const CAPTURE_TYPE = '00';

	const GATEWAY_ID = 'payeezy';

	protected $request_headers = array(
		'content-type' => 'application/json',
		'accept'       => 'application/json'
	);

	protected function set_request_header( $name, $value ) {

		$this->request_headers[ $name ] = $value;
	}

	/**
	 * @var mixed
	 */
	private $environment;

	private $options = [];

	static public function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function get_request_headers() {
		return $this->request_headers;
	}

	public function get_request_uri() {
		if ( $this->environment == 'production' ) {
			return self::PRODUCTION_URL;
		}

		return self::DEMO_URL;
	}

	public function init() {
		add_action( 'sf_add_theme_suboption', [ $this, 'add_settings_subpage' ], 11 );
		add_action( 'init', [ $this, 'set_options' ] );
	}

	public function set_options() {
		$this->options = $this->getOptions();
	}

	public function getOptions() {
		//sf_payment_environment
		//sf_demo_gateway_id
		//sf_demo_password
		//sf_demo_key_id
		//sf_demo_hmac_key

		$options        = [];
		$options['env'] = carbon_get_theme_option( 'sf_payment_environment' );

		$this->environment = $options['env'];

		if ( isset( $options['env'] ) && $options['env'] == 'demo' ) {
			$options['gateway_id'] = carbon_get_theme_option( 'sf_demo_gateway_id' );
			$options['password']   = carbon_get_theme_option( 'sf_demo_password' );
			$options['key_id']     = carbon_get_theme_option( 'sf_demo_key_id' );
			$options['hmac_key']   = carbon_get_theme_option( 'sf_demo_hmac_key' );
		} else {
			$options['gateway_id'] = carbon_get_theme_option( 'sf_gateway_id' );
			$options['password']   = carbon_get_theme_option( 'sf_password' );
			$options['key_id']     = carbon_get_theme_option( 'sf_key_id' );
			$options['hmac_key']   = carbon_get_theme_option( 'sf_hmac_key' );
		}

		return $options;
	}

	public function createHeaders( $req_data ) {
		return [
			'method'     => 'POST',
			'uri'        => $this->get_request_uri(),
			'user-agent' => '(WooCommerce; WordPress)',
			'headers'    => $this->get_request_headers(),
			'body'       => json_encode( $req_data ),
		];
	}

	public function createToken( $data ) {

		// Return true, if card exist and wasn't changed during checkout
		if ( isset( $data['checkout_saved_card'] ) && $data['checkout_saved_card'] 
			&& $data['checkout']['card_number'] == '' && $data['checkout']['cvv'] == '' ) {
			return true;
		}

		if ( $existingToken = $this->checkIfCardExist( substr( $data['checkout']['card_number'], - 4 ) ) ) {
			// Make existing token as default (all other tokens will be secondary)
			$this->setCurrentAsDefault( $existingToken->get_id() );

			return true;
//			return [
//				'error'   => true,
//				'message' => 'Card already exist'
//			];
		}

		// get options creds
		// $this->options = $this->getOptions();

		$card_data = array_filter( $data, function ( $item ) {
			return ! empty( $item['card_number'] ) || ! empty( $item['name_on_card'] ) || ! empty( $item['exp_date'] ) || ! empty( $item['cvv'] );
		} );

		if ( empty( array_filter( $card_data ) ) ) {
			return new WP_Error( 'empty_data', 'Fill card data' );
		}

		$req_data = $this->requestData( $card_data );

		$this->authorization( $req_data );

		$headers = $this->createHeaders( $req_data );

		return $this->createAuth( $headers );
	}


	public function authorization( $request ) {
		$content_type   = 'application/json';
		$method         = 'POST';
		$content_digest = sha1( json_encode( $request ) );
		$gge4_date      = gmdate( 'Y-m-d\TH:i:s\Z' );
		$request_url    = parse_url( $this->get_request_uri(), PHP_URL_PATH );
		$hmac_hash      = base64_encode( hash_hmac( 'sha1',
			$method . "\n" . $content_type . "\n" . $content_digest . "\n" . $gge4_date . "\n" . $request_url,
			$this->options['hmac_key'], true ) );

		$this->set_request_header( 'Authorization',
			sprintf( 'GGE4_API %1$s:%2$s', $this->options['key_id'], $hmac_hash ) );
		$this->set_request_header( 'x-gge4-date', $gge4_date );
		$this->set_request_header( 'x-gge4-content-sha1', $content_digest );
	}

	public function requestData( $data ) {
		$card_data = [
			'cc_number'       => sanitize_text_field( str_replace( ' ', '', $data['checkout']['card_number'] ) ),
			'cardholder_name' => sanitize_text_field( $data['checkout']['name_on_card'] ),
			'cc_expiry'       => sanitize_text_field( str_replace( '/', '', trim( $data['checkout']['exp_date'] ) ) ),
			'cvd_code'        => sanitize_text_field( str_replace( ' ', '', $data['checkout']['cvv'] ) )
		];

		$base_data = [
			'transaction_type'   => self::AUTH_TYPE_ONLY, // auth-only
			'reference_no'       => '1', //id order
			'customer_ref'       => get_current_user_id(), // user_id
			'client_email'       => sanitize_email( $data['billing_email'] ),
			'currency_code'      => 'USD',
			'ecommerce_flag'     => '7',
			'address'            => [
				'phone_type' => 'D'
			],
			'partial_redemption' => false,
			'cvd_presence_ind'   => '1',
			'gateway_id'         => $this->options['gateway_id'],
			'password'           => $this->options['password']
		];

		return array_merge( $base_data, $card_data );
	}

	public function createAuth( $headers ) {
		$response = wp_safe_remote_request( $this->get_request_uri(), $headers );
		$response = $this->handle_response( $response );

		if ( isset( $response->transaction_approved ) && $response->transaction_approved ) {

			if ( is_user_logged_in() ) {
				$this->saveUserToken( $response );
			}

			return [
				'error'   => false,
				'message' => 'You must be logged in'
			];
		} else {
			return new WP_Error( 'not_auth', print_r( $response, true ) );
		}

	}

	public function handle_response( $response ) {
		if ( is_wp_error( $response ) ) {
			// Error
		}

		$code             = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );
		$response_body    = wp_remote_retrieve_body( $response );
		$response_json    = json_decode( $response_body );
		$order_id = $response_json->reference_no;

		$response_headers = wp_remote_retrieve_headers( $response );

		if ( is_object( $response_headers ) ) {
			$response_headers = $response_headers->getAll();
		}

		// Add meta information about payment into order
		add_post_meta( $order_id, '_payment_method', $response_json->credit_card_type );
		add_post_meta( $order_id, '_cc_expiry', $response_json->cc_expiry );
		add_post_meta( $order_id, '_authorization_num', $response_json->authorization_num );
		if ( preg_match('/CARD NUMBER\s+:\s+(.+)[\\\\n|DATE]/mU', $response_body, $matches) ) {
			add_post_meta( $order_id, '_card_number', $matches[1] );
		}

		if ( $code !== 201 ) {
			return $response_body;
		}

		return json_decode( $response_body );

	}

	public function checkIfCardExist( $last4 ) {
		$user_id = get_current_user_id();

		$data_store = new WC_Payment_Token_Data_Store();

		$saved_tokens = $data_store->get_tokens( [
			'user_id'    => $user_id,
			'gateway_id' => self::GATEWAY_ID,
		] );

		foreach ( $saved_tokens as $saved_token ) {
			$token_meta_data = $data_store->get_metadata( $saved_token->token_id );

			if ( $token_meta_data['last4'][0] == $last4 ) {
				$token = new WC_Payment_Token_CC( $saved_token->token_id );
				break;
			}
		}

		if ( isset( $token ) ) {
			return $token;
		}

		return false;
	}

	public function saveUserToken( $response, $user_id = false ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$date  = str_split( $response->cc_expiry, 2 );
		$last4 = substr( $response->transarmor_token, - 4 );

		$token = $this->checkIfCardExist( $last4 );

		if ( ! $token ) {
			$token = new WC_Payment_Token_CC();
		}

		//$token = new WC_Payment_Token_CC();
		$token->set_token( $response->transarmor_token ); // Token comes from payment processor
		$token->set_gateway_id( self::GATEWAY_ID );
		$token->set_last4( $last4 );
		$token->set_expiry_year( '20' . $date[1] );
		$token->set_expiry_month( $date[0] );
		$token->set_card_type( $response->credit_card_type );
		$token->set_user_id( $user_id );
		$token->update_meta_data( 'cardholder', $response->cardholder_name );
		$token->save();

		// Make new token default (all existing tokens will be secondary)
		$this->setCurrentAsDefault( $token->get_id() );

		// Set this token as the users new default token
		// $WC_Payment_Token_Data_Store = new WC_Payment_Token_Data_Store();
		// $WC_Payment_Token_Data_Store->set_default_status( $token->get_id() );
	}

	public function createOrderPayment( $user_id, $price, $order_id ) {
		$user       = get_user_by( 'id', $user_id );
		$user_token = WC_Payment_Tokens::get_customer_default_token( $user_id );

        if ( !method_exists( $user_token, 'get_data' ) ) {
            //TODO добавить нормальную реакцию на отсутствие у пользователя токена
            return new stdClass();
        }
		$token_data = $user_token->get_data();

		$body_request = [
			'transaction_type'   => self::CAPTURE_TYPE,
			'reference_no'       => $order_id, //id order
			'customer_ref'       => $user_id, // user_id
			'client_email'       => $user->user_email(),
			'currency_code'      => 'USD',
			'ecommerce_flag'     => '7',
			'address'            => [
				'phone_type' => 'D'
			],
			'cardholder_name'    => $user_token->get_meta( 'cardholder' ),
			'cc_expiry'          => $token_data['expiry_month'] . substr( $token_data['expiry_year'], - 2 ),
			'partial_redemption' => false,
			'tax1_amount'        => '0.00',
			'amount'             => $price,
			'credit_card_type'   => $token_data['card_type'],
			'transarmor_token'   => $token_data['token'], // token
			'gateway_id'         => $this->options['gateway_id'],
			'password'           => $this->options['password']
		];

		$this->authorization( $body_request );

		$headers = $this->createHeaders( $body_request );

		$response = wp_safe_remote_request( $this->get_request_uri(), $headers );
		$response = $this->handle_response( $response );

		return $response;
	}

	public function getCards( $user_id ) {
		if ( $user_id < 1 ) {
			return false;
		}

		$data_store = new WC_Payment_Token_Data_Store();

		return $data_store->get_tokens( [
			'user_id'    => $user_id,
			'gateway_id' => self::GATEWAY_ID,
		] );
	}

	public function getCardMeta( $token_id ) {
		$data_store      = new WC_Payment_Token_Data_Store();
		$token_meta_data = $data_store->get_metadata( $token_id );

		return [
			'cardholder' => $token_meta_data['cardholder'][0],
			'month'      => $token_meta_data['expiry_month'][0],
			'year'       => substr( $token_meta_data['expiry_year'][0], - 2 ),
			'type'       => $token_meta_data['card_type'][0],
			'last4'		 => $token_meta_data['last4'][0]
		];
	}

	public function getLatestDefaultCard( $cards ) {
		$result = [];
		foreach ($cards as $card) {
			if ($card->is_default == '1') {
				$result = $card;
			}
		}

		// Return latest default card
		return $result;
	}

	public function setCurrentAsDefault( $tokenID ) {
		$userID = get_current_user_id();

		$data_store = new WC_Payment_Token_Data_Store();
		$tokens = $this->getCards( $userID );

		foreach ($tokens as $token) {
			if ($token->token_id == $tokenID) {
				$data_store->set_default_status( $token->token_id, true );
			}else {
				$data_store->set_default_status( $token->token_id, false );
			}
		}
	}

	//	fields in admin
	public function add_settings_subpage( $main_page ) {
		Container::make( 'theme_options', 'Payment Settings' )
		         ->set_page_parent( $main_page ) // reference to a top level container
		         ->add_fields( [
				Field::make( 'select', 'sf_payment_environment', __( 'Environment' ) )
				     ->set_options( array(
					     'production' => 'Production',
					     'demo'       => 'Demo',
				     ) ),
				Field::make( 'text', 'sf_demo_gateway_id', __( 'Demo Gateway Id' ) )
				     ->set_attribute( 'placeholder', 'e.g. FE1490-70' )
				     ->set_conditional_logic( array(
					     'relation' => 'AND',
					     array(
						     'field'   => 'sf_payment_environment',
						     'value'   => 'demo',
						     'compare' => '=',
					     )
				     ) ),
				Field::make( 'text', 'sf_demo_password', __( 'Demo Gateway Password' ) )
				     ->set_attribute( 'type', 'password' )
				     ->set_conditional_logic( array(
					     'relation' => 'AND',
					     array(
						     'field'   => 'sf_payment_environment',
						     'value'   => 'demo',
						     'compare' => '=',
					     )
				     ) ),
				Field::make( 'text', 'sf_demo_key_id', __( 'Demo Key id' ) )
				     ->set_attribute( 'placeholder', 'e.g. 712406' )
				     ->set_conditional_logic( array(
					     'relation' => 'AND',
					     array(
						     'field'   => 'sf_payment_environment',
						     'value'   => 'demo',
						     'compare' => '=',
					     )
				     ) ),
				Field::make( 'text', 'sf_demo_hmac_key', __( 'Demo HMAC Key' ) )
				     ->set_attribute( 'type', 'password' )
				     ->set_conditional_logic( array(
					     'relation' => 'AND',
					     array(
						     'field'   => 'sf_payment_environment',
						     'value'   => 'demo',
						     'compare' => '=',
					     )
				     ) ),
				// production fields
				Field::make( 'text', 'sf_gateway_id', __( 'Gateway Id' ) )
				     ->set_attribute( 'placeholder', 'e.g. FE1490-70' )
				     ->set_conditional_logic( array(
					     'relation' => 'AND',
					     array(
						     'field'   => 'sf_payment_environment',
						     'value'   => 'production',
						     'compare' => '=',
					     )
				     ) ),
				Field::make( 'text', 'sf_password', __( 'Gateway Password' ) )
				     ->set_attribute( 'type', 'password' )
				     ->set_conditional_logic( array(
					     'relation' => 'AND',
					     array(
						     'field'   => 'sf_payment_environment',
						     'value'   => 'production',
						     'compare' => '=',
					     )
				     ) ),
				Field::make( 'text', 'sf_key_id', __( 'Key id' ) )
				     ->set_attribute( 'placeholder', 'e.g. 712406' )
				     ->set_conditional_logic( array(
					     'relation' => 'AND',
					     array(
						     'field'   => 'sf_payment_environment',
						     'value'   => 'production',
						     'compare' => '=',
					     )
				     ) ),
				Field::make( 'text', 'sf_hmac_key', __( 'HMAC Key' ) )
				     ->set_attribute( 'type', 'password' )
				     ->set_conditional_logic( array(
					     'relation' => 'AND',
					     array(
						     'field'   => 'sf_payment_environment',
						     'value'   => 'production',
						     'compare' => '=',
					     )
				     ) )
			] );
	}

}