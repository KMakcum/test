<?php

class SFZipCodes
{

    private static $_instance = null;

    public $zips;

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function init()
    {

    }

    /**
     * @return mixed|string|null
     */
    public function get_current_user_zip()
    {
        if (is_user_logged_in()) {
            $user_zip = get_user_meta(get_current_user_id(), 'sf_zipcode', 1);
        } else {
            $user_zip = op_help()->sf_user::op_get_zip_cookie();
        }

        return $user_zip;
    }

    /**

     * @return string
     */
    public function get_current_user_zone($user_id = false)
    {
        if (is_user_logged_in() && !$user_id) {
            $user_zone = get_user_meta(get_current_user_id(), 'sf_zone', 1);
        } elseif ($user_id) {
            $user_zone = get_user_meta($user_id, 'sf_zone', 1);
        } else {
            //$user_zone = WC()->session->get( 'sf_zone' );
            $user_zone = op_help()->sf_user::sf_get_cookie('sf_zone');
        }

        return $user_zone;
    }

    /**
     * @param $zip_code
     *
     * @return false|string
     */
    public function get_city_by_zip($zip_code)
    {
        if (!empty($zip_code)) {
            $data = op_help()->sf_user::get_user_address_by_zip($zip_code);
            $address_data = $data['predictions'][0]['terms'];

            return "We deliver to {$address_data[0]['value']}!";
        }

        return false;
    }

    /**
     * @param $zip_code
     *
     * @return string
     */
    public function get_zip_zone($zip_code)
    {

        if (empty($this->zips)) {
            $zones = carbon_get_theme_option('op_zones');
            $this->zips = array_column($zones, 'zip_op_zones', 'slug_op_zones');
        }

        foreach ((array)$this->zips as $user_zone => $zip) {
            $zip_codes_in_db = array_column($zip, 'code_zip_op_zones');

            if (in_array($zip_code, $zip_codes_in_db)) {
                return $user_zone;
            }
        }
    }

    /**
     * @param $zip_code
     */
    public function set_user_info_by_zip_code($zip_code)
    {
        $data = op_help()->sf_user::get_user_address_by_zip($zip_code);
        $term_data = $data['predictions'][0]['terms'];
        $user_id = get_current_user_id();

        update_user_meta($user_id, 'sf_zipcode', $zip_code);

        update_user_meta($user_id, 'shipping_city', $term_data[0]['value']);
        update_user_meta($user_id, 'shipping_state', $term_data[1]['value']);
        update_user_meta($user_id, 'shipping_postcode', $zip_code);
        update_user_meta($user_id, 'shipping_country', 'US');

        update_user_meta($user_id, 'billing_city', $term_data[0]['value']);
        update_user_meta($user_id, 'billing_state', $term_data[1]['value']);
        update_user_meta($user_id, 'billing_postcode', $zip_code);
        update_user_meta($user_id, 'billing_country', 'US');
    }

    /**
     * @param $user_zone
     */
    public function set_user_zone($user_zone)
    {
        if (is_user_logged_in()) {
            $current_user_id = get_current_user_id();
            update_user_meta($current_user_id, 'sf_zone', $user_zone);
        } else {
            op_help()->sf_user::sf_set_cookie('sf_zone', $user_zone);
            //WC()->session->set( 'sf_zone', $user_zone );
        }
    }

    /**
     * @param $zip_code
     * @param false $user_zone
     */
    public function set_user_data($zip_code, $user_zone = false)
    {
        if (!$user_zone) {
            $user_zone = $this->get_zip_zone($zip_code);
        }

        if (!empty($user_zone)) {
            $this->set_user_zone($user_zone);
            op_help()->sf_user::op_set_zip_cookie($zip_code);

            if (is_user_logged_in()) {
                $this->set_user_info_by_zip_code($zip_code);
            }
        } else {
            $this->set_user_zone('not_in');
        }
    }

    public function is_zip_zone_national($zip_code, $zip_zone = false)
    {
        if (!$zip_zone) {
            $zip_zone = $this->get_zip_zone($zip_code);
        }

        return $zip_zone === 'national';
    }

}