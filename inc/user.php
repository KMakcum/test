<?php

require 'vendor/autoload.php';

use SKAgarwal\GoogleApi\PlacesApi;

class OP_User
{
    private static $_instance = null;

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static $redirect_page = '/offerings/';

    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public static function init()
    {
        // register
        add_action('wp_ajax_user_registration', __CLASS__ . '::op_user_registration');
        add_action('wp_ajax_nopriv_user_registration', __CLASS__ . '::op_user_registration');
        // auth
        add_action('wp_ajax_user_login', __CLASS__ . '::op_user_login');
        add_action('wp_ajax_nopriv_user_login', __CLASS__ . '::op_user_login');
        // after signon - redirect
        add_filter('login_redirect', __CLASS__ . '::op_redirect_after_login');
        add_filter('registration_redirect', __CLASS__ . '::op_redirect_after_login');

        if (is_user_logged_in()) {
            add_action('template_redirect', __CLASS__ . '::op_redirect_logged_users');
        } else {
            add_action('template_redirect', __CLASS__ . '::op_redirect_users_with_zipcode');
        }

        remove_class_action('wp_footer', 'class_user_verification_manage_verification', 'check_email_verification',
            10);

//        add_action('wp_footer', [__CLASS__, 'sf_check_email_verification']);

        add_action('wp_login', [__CLASS__, 'sf_add_user_fields_after_login'], 11, 2);
    }

    /**
     * Set user zipcode cookie
     */

    public static function op_set_zip_cookie($zip_code)
    {
        setcookie('user_zip_code', $zip_code, strtotime('+30 days'), '/');
    }

    public static function op_get_zip_cookie()
    {
        return (isset($_COOKIE['user_zip_code'])) ? trim($_COOKIE['user_zip_code']) : null;
    }

    public static function sf_set_cookie($name, $value, $expire = false)
    {
        if (!$expire) {
            $expire = strtotime('+30 days');
        }

        if (!empty($name) && !empty($value)) {
            setcookie($name, $value, $expire, '/');
        }
    }

    public static function sf_get_cookie($name)
    {
        if (!empty($name)) {
            return (isset($_COOKIE[$name])) ? trim($_COOKIE[$name]) : null;
        }

        return null;
    }

    public static function get_user_address_by_zip($zip_code)
    {
        $googlePlaces = new PlacesApi(op_help()->settings->google_api_key);

        $response = $googlePlaces->placeAutocomplete($zip_code,
            [
                'types' => '(regions)',
                'components' => 'country:us'
            ]
        );

        return $response->toArray();
    }

    /**
     * Set/get/delete user tutorial status cookie
     */

    public static function op_set_tutorial_cookie( $status, $tutorial_page, $tutorial_type )
    {
        if ( $tutorial_type == 'main' ) {
            setcookie( $tutorial_page . 'user_tutorial_status', $status, strtotime('+30 days'), '/' );
        } else {
            setcookie( 'user_tutorial_status_' . $tutorial_type, $status, strtotime( '+30 days' ), '/' );
        }
    }

    public static function op_get_tutorial_cookie( $tutorial_page, $tutorial_type = '' )
    {
        $cookie_name = ( $tutorial_type == '' ) ? $tutorial_page . 'user_tutorial_status' : 'user_tutorial_status_' . $tutorial_type;

        return ( isset( $_COOKIE[ $cookie_name ] )) ? trim( $_COOKIE[ $cookie_name ] ) : false;
    }

    public static function op_delete_tutorial_cookie( $tutorial_page, $tutorial_type = '' )
    {
        // $cookie_name = ( $tutorial_type == '' ) ? $tutorial_page . 'user_tutorial_status' : 'user_tutorial_status_' . $tutorial_type;
        $cookie_name = $tutorial_page . 'user_tutorial_status';

        unset( $_COOKIE[ $cookie_name ] );
        setcookie( $cookie_name, null, -1, '/' );
    }

    /**
     * Set/get/delete user tutorial status meta data
     */

    public static function op_set_tutorial_meta( $status, $tutorial_page, $tutorial_type ) {
        if ( $tutorial_type == 'main' ) {
            update_user_meta( get_current_user_id(), $tutorial_page . 'tutorial_status', $status );
        } else {
            update_user_meta( get_current_user_id(), 'tutorial_status_' . $tutorial_type, $status );
        }
    }

    public static function op_get_tutorial_meta( $tutorial_page, $tutorial_type = '' ) {
        $meta_name = ( $tutorial_type == '' ) ? $tutorial_page . 'tutorial_status' : 'tutorial_status_' . $tutorial_type;
        $status    = get_user_meta( get_current_user_id(), $meta_name, true );

        return ( $status == '' ) ? false : $status;
    }

    public static function op_delete_tutorial_meta( $tutorial_page, $tutorial_type = '' ) {
        // $meta_name = ( $tutorial_type == '' ) ? $tutorial_page . 'tutorial_status' : 'tutorial_status_' . $tutorial_type;
        $meta_name = $tutorial_page . 'tutorial_status';
        
        delete_user_meta( get_current_user_id(), $meta_name );

        // Remove additional tutorials TODO: rewrite this part, when all additional tutorials will be enabled
        if ( $tutorial_page == 'catalog' ) {
            delete_user_meta( get_current_user_id(), 'tutorial_status_' . $tutorial_type );
        }
    }

    /**
     * User redirect to offerings page after login
     */

    public static function op_redirect_after_login()
    {
        return get_site_url() . self::$redirect_page;
    }

    /**
     * Create new user
     */

    public static function op_user_registration()
    {

//        check_ajax_referer('op_check', 'ajax_nonce');
        parse_str($_POST['form'], $_POST['form']);

        if ($_POST['pwd']['checkedC'] < $_POST['pwd']['totalC']) {
            wp_send_json_error([
                'message' => 'Password is too weak',
            ]);
        }
        if (empty($_POST['form']['user_email']) || empty($_POST['form']['user_password'])) {
            wp_send_json_error([
                'message' => 'Fill in all the fields please',
            ]);
        }
        if (!is_email($_POST['form']['user_email'])) {
            wp_send_json_error([
                'message' => 'Email address is not correct',
            ]);
        }

        $user_email = sanitize_email($_POST['form']['user_email']);
        $parts = explode("@", $user_email);
        $user_login = $parts[0];
        $user_password = $_POST['form']['user_password'];

        if (isset($_POST['form']['user_redirect'])
            && $_POST['form']['user_redirect'] == esc_url(wc_get_cart_url())
            || $_POST['form']['user_redirect'] == 'https://life-chef.dev-test.pro/cart/?from-email-template=true') {
            $redirect = $_POST['form']['user_redirect'];
        } else {
            $redirect = self::op_redirect_after_login();
        }

        $user_probably = get_user_by('email', $user_email);
        if ($user_probably) {
            $valid_password = wp_check_password($user_password, $user_probably->data->user_pass);
            $activated_user = get_user_meta($user_probably->ID, 'user_activation_status');
            $exist_activation_key = get_user_meta($user_probably->ID, 'user_activation_key');

            if ($exist_activation_key && $activated_user[0] == 0 || ($exist_activation_key && !$valid_password)) {
                wp_send_json_success([
                    'status' => 'sended',
                    'message' => __('Verification link already sended', ''),
                    'redirectUrl' => get_site_url() . '/user-verification?sended=true&user-email=' . urlencode($user_email) . ''
                ]);
            }
            if ($activated_user[0] == 1 && $valid_password) {
                wp_clear_auth_cookie();
                wp_set_current_user($user_probably->ID, $user_probably->user_login);
                wp_set_auth_cookie($user_probably->ID);
                do_action('wp_login',$user_probably->user_login, $user_probably);
                wp_send_json_success(
                    [
                        'status' => 'login',
                        'message' => __('Hello, Friend.', ''),
                        'redirectUrl' => $redirect
                    ]);
            } elseif ($activated_user[0] == 1 && !$valid_password) {
                wp_send_json_success(
                    [
                        'status' => 'verified',
                        'message' => __('User exist.', '')
                    ]);
            }
        }

        $user = wp_insert_user([
            'user_login' => $user_login,
            'user_pass' => $user_password,
            'user_email' => $user_email,
            'role' => 'subscriber',
        ]);
        update_user_meta($user,'_woocommerce_load_saved_cart_after_login',true);
        self::sf_set_cookie('sf_register_page_user', sanitize_text_field($_POST['form']['user_redirect']));


        wp_send_json_success([
            'status' => 'sent',
            'message' => 'We sent verification to your email',
            'redirectUrl' => get_site_url() . '/user-verification?sent=true&user-email=' . urlencode($user_email) . ''
        ]);


    }


    /**
     * User authorization
     */

    public static function op_user_login()
    {

        //check_ajax_referer('op_check', 'ajax_nonce');
        parse_str($_POST['form'], $_POST['form']);

        if (empty($_POST['form']['user_email']) || empty($_POST['form']['user_password'])) {
            wp_send_json_error([
                'message' => 'Fill in all the fields please',
            ]);
        }

        if (!is_email($_POST['form']['user_email'])) {
            wp_send_json_error([
                'message' => 'Email address is not correct',
            ]);
        }

        // if ( isset( $_POST['form']['user_redirect'] ) && ! empty( $_POST['form']['user_redirect'] ) ) {
        // $redirect = $_POST['form']['user_redirect'];
        // } else {
        // $redirect = home_url();
        // }
        if (isset($_POST['form']['user_redirect']) && $_POST['form']['user_redirect'] == esc_url(wc_get_cart_url())) {
            $redirect = $_POST['form']['user_redirect'];
        } else {
            $redirect = self::op_redirect_after_login();
        }

        $creds = array();
        $creds['user_login'] = $_POST['form']['user_email'];
        $creds['user_password'] = $_POST['form']['user_password'];
        // $creds['remember'] 		= true;
        $user = wp_signon($creds, false);

        if (!is_wp_error($user)) {
            self::sf_add_user_fields_after_login($user->user_login, $user);
        }

        if (is_wp_error($user)) {
            wp_send_json_error([
                'message' => __('The provided password does not match our records'),
            ]);
        }

        wp_send_json_success([
            'redirect_url' => $redirect,
        ]);
    }

    public static function sf_add_user_fields_after_login($user_login, $user)
    {
        $sf_zipcode = get_user_meta($user->ID, 'sf_zipcode', true);
        $billing_postcode = get_user_meta($user->ID, 'billing_postcode', true);
        if (empty($sf_zipcode) && empty($billing_postcode)) {
            $zip_cookie = self::op_get_zip_cookie();

            if (empty($zip_cookie)) {
                $zip_cookie = get_user_meta($user->ID, 'activation_zip', 1);
            }

            $data = self::get_user_address_by_zip($zip_cookie);
            $user_zone =  op_help()->zip_codes->get_zip_zone( $zip_cookie );
            $term_data = $data['predictions'][0]['terms'];

            update_user_meta($user->ID, 'sf_zipcode', $zip_cookie);
	        update_user_meta($user->ID, 'sf_zone', $user_zone);

            update_user_meta($user->ID, 'shipping_city', $term_data[0]['value']);
            update_user_meta($user->ID, 'shipping_state', $term_data[1]['value']);
            update_user_meta($user->ID, 'shipping_postcode', $zip_cookie);
            update_user_meta($user->ID, 'shipping_country', 'US');

            update_user_meta($user->ID, 'billing_city', $term_data[0]['value']);
            update_user_meta($user->ID, 'billing_state', $term_data[1]['value']);
            update_user_meta($user->ID, 'billing_postcode', $zip_cookie);
            update_user_meta($user->ID, 'billing_country', 'US');
            update_user_meta($user->ID, '_woocommerce_load_saved_cart_after_login', true);
        }

        // Update tutorial statuses
        foreach (op_help()->tutorial->tutorials_numbers as $page_name => $number) {
            $tutorial_cookie = self::op_get_tutorial_cookie( $page_name );
            
            // Update Tutorial status if cookie exist
            if ( $tutorial_cookie ) {
                update_user_meta( $user->ID, $page_name . 'tutorial_status', $tutorial_cookie );
            }
        }
    }

    /**
     * Redirect to offerings page
     */

    public static function op_redirect_logged_users()
    {

        if (self::op_check_user_role(['subscriber']) && is_page_template('front-page.php') || is_shop()) {
            wp_redirect(self::op_redirect_after_login());
            exit;
        }
    }

    /**
     * check if zip code in cookie and redirect if not
     */

    public static function op_redirect_users_with_zipcode()
    {

        if (!is_null(op_help()->sf_user::op_get_zip_cookie()) && is_page_template('front-page.php') || is_shop()) {

            wp_redirect(self::op_redirect_after_login());
            exit;
        }

    }


    /**
     * Check user role
     *
     * @param $roles
     * @param null $user_id
     *
     * @return bool
     */

    public static function op_check_user_role($roles, $user_id = null)
    {

        if ($user_id) {
            $user = get_userdata($user_id);
        } else {
            $user = wp_get_current_user();
        }

        if (empty($user)) {
            return false;
        }

        foreach ($user->roles as $role) {

            if (in_array($role, $roles)) {
                return true;
            }
        }

        return false;
    }

    public function check_survey_exist($user_id = false)
    {

        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (is_user_logged_in()) {
            return get_user_meta($user_id, 'customer_success_survey', true) ? true : false;
        } else {
            return false;
        }
    }

    public function check_survey_default($user_id = false)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (is_user_logged_in()) {
            return get_user_meta($user_id, 'survey_default', true) ? true : false;
        } else {
            return false;
        }
    }


    /**
     * return true if not national
     * @param string $zip
     * @return bool
     */
    public static function check_zip_group($zip = '')
    {
        $zones = carbon_get_theme_option('op_zones');
        $current_user_zip = empty($zip) ? self::op_get_zip_cookie() : $zip;

        $area_zip_code = array_filter($zones, function ($item) use ($current_user_zip) {
            $zones_zip_codes = array_column($item['zip_op_zones'], 'code_zip_op_zones');

            return in_array($current_user_zip, $zones_zip_codes);
        });

        $zone = array_filter($area_zip_code, function ($item) {
            if ($item['slug_op_zones'] !== 'national') {
                return true;
            }

            return false;
        });

        return (!empty($zone));
    }

}
