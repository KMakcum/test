<?php

class SFCoupons {
	private static $_instance = null;

	public $public_coupon;

	private function __construct() {
	}

	protected function __clone() {
	}

	static public function getInstance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function init() {
		add_action( 'edit_form_after_title', [ $this, 'edit_form_after_title' ], 5 );
		add_action( 'woocommerce_coupon_options_save', [ $this, 'add_coupon_title' ], 5, 1 );
		add_action( 'template_redirect', [ $this, 'apply_coupon' ] );

		add_action( 'woocommerce_removed_coupon', [ $this, 'change_remove_coupon_state' ], 11, 1 );
		add_action( 'wp_login', [$this, 'add_coupon_state'], 100 );
	}

	public function add_coupon_title( $post_id ) {
		update_post_meta( $post_id, 'op_coupon_title', $_POST['op_coupon_title'] );
	}

	public function edit_form_after_title( $post ) {

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( 'shop_coupon' === $post->post_type ) {
			$coupon_title = get_post_meta( $post->ID, 'op_coupon_title', 1 );
			?>
            <textarea id="woocommerce-coupon-description" name="op_coupon_title" cols="2" rows="1"
                      placeholder="<?php esc_attr_e( 'Title',
				          'woocommerce' ); ?>"><?php echo $coupon_title; ?></textarea>
			<?php
		}
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function get_public_coupon() {
		if ( empty( $this->public_coupon ) ) {
			$post         = carbon_get_theme_option( 'op_theme_header_coupon' );
			$coupon_id    = $post[0]['id'];
			$coupon_title = get_post_meta( $coupon_id, 'op_coupon_title', 1 );
			$coupon       = new WC_Coupon( ( $coupon_id ) );
			$data         = $coupon->get_data();
			$date_expires = false;

			if ( ! empty( $data['date_expires'] ) ) {
				$date         = $data['date_expires'];
				$date_expires = $this->covert_expire_date( $date->getTimestamp() );
			}

			$this->public_coupon = [
				'coupon_id'   => $coupon_id,
				'title'       => $coupon_title,
				'description' => $data['description'],
				'discount'    => $data['code'],
				'expires'     => $date_expires
			];
		}

		return $this->public_coupon;
	}

	public function covert_expire_date( $time ) {
		$time   = $time - time(); // to get the time since that moment
		$time   = ( $time < 1 ) ? 1 : $time;
		$tokens = array(
			31536000 => 'year',
			2592000  => 'month',
			604800   => 'week',
			86400    => 'day',
			3600     => 'hour',
			60       => 'minute',
			1        => 'second'
		);

		foreach ( $tokens as $unit => $text ) {
			if ( $time < $unit ) {
				continue;
			}
			$number_of_units = floor( $time / $unit );

			return $number_of_units . ' ' . $text . ( ( $number_of_units > 1 ) ? 's' : '' );
		}
	}

	public function apply_coupon() {

		if ( ! op_help()->sf_user->sf_get_cookie( 'discount_promo_bar' ) ) {
			return;
		}

		$session_coupon = WC()->session->get( 'removed_auto_coupon' );

		if ( ! isset( $session_coupon ) ) {
			WC()->session->set( 'removed_auto_coupon', false );
			$session_coupon = WC()->session->get( 'removed_auto_coupon' );
		}

		if ( ! $session_coupon ) {
			$coupon = $this->get_public_coupon();

			// do not add if subscription
			if ( op_help()->subscriptions->get_current_subscription() ) {
				return;
			}

			// do not add if have another coupons
			if ( WC()->cart->has_discount() ) {
				return;
			}

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				WC()->cart->apply_coupon( $coupon['discount'] );
			}

		}

	}

	public function change_remove_coupon_state() {
		WC()->session->set( 'removed_auto_coupon', true );
		op_help()->sf_user->sf_set_cookie( 'discount_promo_bar', 'false', time() - 3600 );
	}

	public function add_coupon_state() {
		WC()->session->set( 'removed_auto_coupon', false );
    }

}