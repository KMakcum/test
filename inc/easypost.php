<?php
require_once("vendor/easypost/easypost-php/lib/easypost.php");

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class SFEasyPost
{
    private static $_instance = null;

    private $demoKey = 'EZTKe09c868c02884e1d856bcb7c24335aa3244V9rBW4KiFLLuVIj6ang';
    private $liveKey = 'EZTKe09c868c02884e1d856bcb7c24335aa3244V9rBW4KiFLLuVIj6ang'; // TODO: Set live key

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function init()
    {
        add_action( 'wp_ajax_sf_ep_check_address', [ $this, 'checkAddress' ] );
        add_action( 'wp_ajax_nopriv_sf_ep_check_address', [ $this, 'checkAddress' ] );
    }

    private function setAPIKey($envType) {
        $key = $this->demoKey;
        if ($envType == 'prod') {
            $key = $this->$liveKey;
        }

        \EasyPost\EasyPost::setApiKey($key);
    }

    public function checkAddress() {
        $this->setAPIKey(carbon_get_theme_option('op_easypost_env'));

        $address_params = array(
            'verify'  => array('delivery'),
            'street1' => $_POST[$_POST['address_type'] . '_address_1'],
            'street2' => $_POST[$_POST['address_type'] . '_address_2'],
            'city'    => $_POST[$_POST['address_type'] . '_city'],
            'state'   => $_POST[$_POST['address_type'] . '_state'],
            'zip'     => $_POST[$_POST['address_type'] . '_postcode'],
            'country' => $_POST[$_POST['address_type'] . '_country'],
            //'phone'   => $_POST[$_POST['address_type'] . '_phone'] // TODO: do we need phone here?
        );

        $response = \EasyPost\Address::create($address_params);

        if (!$response->verifications->delivery->success) {

            wp_send_json_error([
                'error' => $response->verifications->delivery->errors[0]->message,
                'field' => $response->verifications->delivery->errors[0]->field
            ]);
        }else {
            wp_send_json_success([
                'address_1' => $this->updateAddressView($response->street1),
                'address_2' => $this->updateAddressView($response->street2),
                'city' => $this->updateAddressView($response->city),
                'state' => $response->state,
                'country' => $response->country,
            ]);
        }
    }

    // Filter addresses from API
    public function updateAddressView($text) {
        return ucwords(strtolower($text));
    }

}
