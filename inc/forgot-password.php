<?php
require_once 'user-table.php';

class ResetPasswordPageClass
{
    private static $_instance = null;

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    private function getURLSegments()
    {
        return explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    }

    private function getURLSegment($n)
    {
        $segs = $this->getURLSegments();
        return count($segs) > 0 && count($segs) >= ($n - 1) ? $segs[$n] : '';
    }


    public function init()
    {

        add_action('wp_ajax_nopriv_check_user_email', [$this, 'checkUserEmail']);
        add_action('wp_ajax_nopriv_set_user_password', [$this, 'setUserPassword']);
        add_action('wp_enqueue_scripts', function () {
            if (($this->getURLSegment(1) === 'forgot-password')) {
                if (!post_exists('Forgot password')) {
                    $forgot_password_page = [
                        'post_title' => 'Forgot password',
                        'post_name' => 'forgot-password',
                        'post_status' => 'publish',
                        'post_author' => 1,
                        'post_type' => 'page'
                    ];
                    wp_insert_post($forgot_password_page);
                }
                wp_enqueue_script('ajax_script', get_stylesheet_directory_uri() . '/assets/js/forgot-password-ajax.js', ['jquery']);
                wp_localize_script('ajax_script', 'ajaxSettings', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'ajax_nonce' => wp_create_nonce('life-chef-action')
                ]);
            } elseif (($this->getURLSegment(1) === 'setup-password')) {
                if (!post_exists('New password')) {
                    $new_password_page = [
                        'post_title' => 'New password',
                        'post_name' => 'setup-password',
                        'post_status' => 'publish',
                        'post_author' => 1,
                        'post_type' => 'page'
                    ];
                    wp_insert_post($new_password_page);
                }
                wp_enqueue_script('ajax_script', get_stylesheet_directory_uri() . '/assets/js/setup-password-ajax.js', ['jquery']);
                wp_localize_script('ajax_script', 'ajaxSettings', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'ajax_nonce' => wp_create_nonce('life-chef-action'),
                    'forgot_password_url' => get_site_url() . '/forgot-password?invalid-token'
                ]);
            }
        });
    }

    public function checkUserEmail()
    {
        $request = (object)$_POST;
        $request->data = json_decode(stripslashes($request->data), true);
        if (!$this->checkNonce($request->nonce, 'life-chef-action')) {
            self::returnError('wrong nonce');
        }

        if (array_key_exists('user_email', $request->data)
            && !$this->isSetNotEmpty($request->data['user_email'])) {
            self::returnError('empty email');
        }
        $userTable = new UserTable();
        $user = $userTable->getUserEmail($request->data['user_email']);
        if ($user) {
            $reset_key = get_password_reset_key($user);
            $apiUrl = 'https://api.sendgrid.com/v3/mail/send';
            $apiKey = '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';
            $templateId = 'd-4d33dde36e8643e08ca0491b971fb44f';
            $params = [
                'from' =>
                    [
                        'name' => 'LifeChef™',
                        'email' => 'hello@lifechef.com',
                    ],
                'personalizations' =>
                    [
                        0 =>
                            [
                                'to' =>
                                    [
                                        0 =>
                                            [
                                                'email' => $user->user_email,
                                            ],
                                    ],
                                'dynamic_template_data' =>
                                    [
                                        'name' => $user->user_nicename,
                                        'link' => add_query_arg([
                                            'token' => $reset_key,
                                            'login' => $user->user_login
                                        ],
                                            get_site_url() . '/forgot-password')
                                    ],
                            ],
                    ],
                'template_id' => $templateId,
            ];
            wp_remote_post($apiUrl, [
                'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => 'Bearer ' . $apiKey],
                'body' => json_encode($params),
                'method' => 'POST',
                'data_format' => 'body'
            ]);
            self::returnData(['email_found' => true]);
        } else {
            self::returnData(['email_found' => false]);
        }
    }

    public function checkToken($token, $login)
    {
        return check_password_reset_key($token, $login);
    }

    public function setUserPassword()
    {
        $request = (object)$_POST;
        $request->password = json_decode(stripslashes($request->password), true);
        $request->user_data = json_decode(stripslashes($request->user_data), true);
        if (!$this->checkNonce($request->nonce, 'life-chef-action')) {
            self::returnError('wrong nonce');
        }
        if (!$request->password || !$request->user_data) {
            self::returnError('required parameters are empty');
        }
        $user = $this->checkToken($request->user_data['token'], $request->user_data['login']);
        if ($user instanceof WP_Error) {
            self::returnError('wrong parameters');
        }

        // Return error if pwd is too weak
        if ($request->checkedC < $request->totalC) {
            self::returnData(['password_changed' => false, 'message' => 'Password is too weak']);
        }

        $userTable = new UserTable();
        $response = $userTable->setUserPassword($user->user_email, $request->password['user_new_password']);
        if ($response) {
            self::returnData(['password_changed' => true]);
        } else {
            self::returnData(['password_changed' => false]);
        }
    }

    private function checkNonce($nonce, $action)
    {
        return wp_verify_nonce($nonce, $action);
    }

    private static function returnData($data = [])
    {
        header('Content-Type:application/json');
        echo json_encode(array('status' => true, 'data' => $data));
        wp_die();
    }

    private static function returnError($message)
    {
        header('Content-Type:application/json');
        wp_send_json_error($message, 400);
        wp_die();
    }

    private function isSetNotEmpty($var)
    {
        return (isset($var) && !empty($var)) ? $var : null;
    }

}
