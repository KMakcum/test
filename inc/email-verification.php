<?php


class EmailVerification
{

    private static $_instance = null;

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function init()
    {
        if (stripos($_SERVER['REQUEST_URI'], 'user-verification')) {
            if (!post_exists('User Verification')) {
                $faq_page = [
                    'post_title' => 'User Verification',
                    'post_name' => 'user-verification',
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_type' => 'page'
                ];
                wp_insert_post($faq_page);
            }
            add_option('active_campaign_register_passed', 139);
            add_option('active_campaign_one_day_retention', 140);
            add_option('active_campaign_survey_passed', 141);
            add_option('active_campaign_refer_url', 20);
            add_filter('wp_mail', 'disabling_emails', 10, 1);
            add_action('phpmailer_init', 'react2wp_clear_recipients');
            function disabling_emails($args)
            {
                unset ($args['to']);
                return $args;
            }

            function react2wp_clear_recipients($phpmailer)
            {
                $phpmailer->ClearAllRecipients();
            }

            add_action('wp_enqueue_scripts', function () {
                wp_enqueue_script('email-verification', get_stylesheet_directory_uri() . '/assets/js/email-verification.js', ['jquery']);
                wp_localize_script('email-verification', 'ajaxSettings', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'ajax_nonce' => wp_create_nonce('life-chef-action'),
                    'site_url' => get_site_url(),
                    'ajax_form_page_slug' => 'user-verification'
                ]);
            });
        }
        add_action('wp_ajax_nopriv_resend_verification_code_handler', [$this, 'resend_verification_code']);
        add_action('wp_ajax_nopriv_user_change_email_handler', [$this, 'user_change_email']);
        add_action('wp_ajax_nopriv_verify_user_by_code_handler', [$this, 'verify_user_by_code']);
        add_action('wp_ajax_verify_user_by_code_handler', [$this, 'verify_user_by_code']);
        add_action('user_verification_email_verified', [$this, 'activecampaign_add_new_wp_user'], 10, 1);
        add_action('resend_verification_link', [$this, 'sendgrid_confirmation_new_wp_user'], 50);
        add_action('user_register', [$this, 'sendgrid_confirmation_new_wp_user'], 50);
    }

    public function user_change_email()
    {
        $request = (object)$_POST;
        $request->data = json_decode(stripslashes($request->data), true);
        if (!$this->checkNonce($request->nonce, 'life-chef-action')) {
            self::returnError('wrong nonce');
        }
        if (!$request->old_user_email) {
            self::returnError('empty old user email');
        }
        if (!$request->data['user-email']) {
            self::returnError('empty new user email');
        }
        $user = get_user_by('email', $request->old_user_email);
        if (!get_user_by('email', $request->data['user-email'])) {
            wp_update_user([
                'ID' => $user->ID,
                'user_email' => $request->data['user-email']
            ]);
            $send_grid = $this->sendgrid_confirmation_new_wp_user($user->ID);
            self::returnData($send_grid['response']['code']);
        } else {
            self::returnError('user with this email exist');
        }
    }

    public function resend_verification_code()
    {
        $request = (object)$_POST;
        if (!$this->checkNonce($request->nonce, 'life-chef-action')) {
            self::returnError('wrong nonce');
        }
        if (!$request->user_email) {
            self::returnError('empty user email');
        }
        $user = get_user_by('email', $request->user_email);
        $send_grid = $this->sendgrid_confirmation_new_wp_user($user->ID);
        self::returnData($send_grid['response']['code']);
    }

    public function verify_user_by_code()
    {
        $request = (object)$_POST;

        if (!$this->checkNonce($request->nonce, 'life-chef-action')) {
            self::returnError('wrong nonce');
        }
        if (!$request->user_email) {
            self::returnError('empty user email');
        }
        if (!$request->verification_code) {
            self::returnError('empty verification code');
        }
        $user = get_user_by('email', $request->user_email);
        $code = get_user_meta($user->ID, 'four_digit_activation_code');
        $verified = get_user_meta($user->ID, 'user_activation_status', true);
        if ($code[0] === $request->verification_code) {
            if (!$verified) {
                update_user_meta($user->ID, 'user_activation_status', 1);
                do_action('user_verification_email_verified', ['user_id' => $user->ID]);
                $redirect_url = esc_url_raw(OP_User::sf_get_cookie('sf_register_page_user'));
                wp_clear_auth_cookie();
                wp_set_current_user($user->ID, $user->user_login);
                wp_set_auth_cookie($user->ID);
                OP_User::sf_add_user_fields_after_login($user->user_login, $user);
                do_action('wp_login', $user->user_login, $user);
                self::returnData(['status' => 202, 'redirect_url' => $redirect_url]);
            } else {
                $redirect_url = get_site_url() . '/user-verification?invalid-link=true&user-email=' . $user->user_email . '';
                self::returnData(['status' => 202, 'redirect_url' => $redirect_url]);
            }
        } else {
            self::returnError('codes are not same');
        }
    }

    function sendgrid_confirmation_new_wp_user($user_id)
    {
        $user_data = get_userdata($user_id);

        $user_verification_settings = get_option('user_verification_settings');
        $email_verification_enable = isset($user_verification_settings['email_verification']['enable']) ? $user_verification_settings['email_verification']['enable'] : 'yes';

        if ($email_verification_enable != 'yes') return;
        $exclude_user_roles = isset($user_verification_settings['email_verification']['exclude_user_roles']) ? $user_verification_settings['email_verification']['exclude_user_roles'] : array();

        $email_bcc = isset($email_templates_data['email_bcc']) ? $email_templates_data['email_bcc'] : '';
        $email_from = isset($email_templates_data['email_from']) ? $email_templates_data['email_from'] : '';
        $email_from_name = isset($email_templates_data['email_from_name']) ? $email_templates_data['email_from_name'] : '';
        $reply_to = isset($email_templates_data['reply_to']) ? $email_templates_data['reply_to'] : '';
        $reply_to_name = isset($email_templates_data['reply_to_name']) ? $email_templates_data['reply_to_name'] : '';
        $email_subject = isset($email_templates_data['subject']) ? $email_templates_data['subject'] : '';

        $user_page_register = get_user_meta($user_id, 'sf_register_page_user', 1);
        $verification_page_url = get_site_url() . '/user-verification/';

        if (!empty($user_page_register)) {
            $verification_page_url = $user_page_register;
        }

        $permalink_structure = get_option('permalink_structure');

        $user_activation_key = md5(uniqid('', true));
        update_user_meta($user_id, 'user_activation_key', $user_activation_key);
        update_user_meta($user_id, 'user_activation_status', 0);
        update_user_meta($user_id, 'four_digit_activation_code', rand(1000, 9999));

        $zip = op_help()->sf_user::op_get_zip_cookie();
        update_user_meta($user_id, 'activation_zip', $zip);

        $user_roles = !empty($user_data->roles) ? $user_data->roles : array();
        if (!empty($exclude_user_roles)) {
            foreach ($exclude_user_roles as $role):
                if (in_array($role, $user_roles)) {
                    //update_option('uv_custom_option', $role);
                    update_user_meta($user_id, 'user_activation_status', 1);
                    return;
                }
            endforeach;
        }

        $verification_url = add_query_arg(
            array(
                'activation_key' => $user_activation_key,
                'user_verification_action' => 'email_verification',
            ),
            $verification_page_url
        );

        $verification_url = wp_nonce_url($verification_url, 'email_verification');

        // footer links
        $open_chat_url = get_bloginfo('url') . '/offerings?chat-open=open';
        $ask_question_url = get_bloginfo('url') . '/faq?ask-question=open';
        $terms_url = get_bloginfo('url') . '/terms-conditions/';
        $privacy_url = get_bloginfo('url') . '/privacy-policy/';
        $contact_us_url = get_bloginfo('url') . '/contact-us';

        // send mail with sendgrid
        $social_fb_url = 'https://google.com';
        $social_insta_url = 'https://google.com';
        $social_twitter_url = 'https://google.com';
        $unsubscribe_url = 'https://google.com';

        $apiUrl = 'https://api.sendgrid.com/v3/mail/send';
        $apiKey = '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';
        $templateId = 'd-dc232606354f4fe0a881991e861b0023';

        $four_digit_code = get_user_meta($user_id, 'four_digit_activation_code');

        $params = array(
            'from' =>
                array(
                    'email' => 'hello@lifechef.com',
                    'name' => 'LifeChef™ Team',
                ),
            'personalizations' =>
                array(
                    0 =>
                        array(
                            'to' =>
                                array(
                                    0 =>
                                        array(
                                            'email' => $user_data->user_email,
                                        ),
                                ),
                            'dynamic_template_data' =>
                                array(
                                    'activation_key' => $user_activation_key,
                                    'user_verification_action' => 'email_verification',
                                    'url' => $verification_url,
                                    'open_chat_url' => $open_chat_url,
                                    'ask_question_url' => $ask_question_url,
                                    'terms_url' => $terms_url,
                                    'privacy_url' => $privacy_url,
                                    'social_fb_url' => $social_fb_url,
                                    'social_insta_url' => $social_insta_url,
                                    'social_twitter_url' => $social_twitter_url,
                                    'unsubscribe_url' => $unsubscribe_url,
                                    'contact_us_url' => $contact_us_url,
                                    'four_digit_code' => $four_digit_code
                                ),
                        ),
                ),
            'template_id' => $templateId,
        );

        return wp_remote_post($apiUrl, array(
            'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => 'Bearer ' . $apiKey],
            'body' => json_encode($params),
            'method' => 'POST',
            'data_format' => 'body',
        ));
    }

    public function user_registration_passed($user)
    {
        if ($user !== 0) {
            update_user_meta($user->ID, 'registration_passed', 1);
            setcookie("customer_first_login", time(), time() + 86400);
            $acUserId = get_user_meta($user->ID, 'activecampaign_user_id', true);
            // add activecampaign customer meta
            $apiUrl = 'https://frontrowlabs.api-us1.com/api/3/contactTags';
            $apiKey = 'd7692ca0146eddda45220525bc08e7855e79c96dbd30930b0dff426cd114755b490e0838';
            $params = [
                'contactTag' => [
                    'contact' => $acUserId,
                    'tag' => get_option('active_campaign_register_passed')
                ]
            ];

            $data = wp_remote_post($apiUrl, array(
                'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Api-Token' => $apiKey],
                'body' => json_encode($params),
                'method' => 'POST',
                'data_format' => 'body',
            ));

        }
    }

    public function activecampaign_add_new_wp_user($user_id)
    {
        $user = get_userdata($user_id['user_id']);

        // activecampaign add new user
        $apiUrl = 'https://frontrowlabs.api-us1.com/api/3/contacts';
        $apiKey = 'd7692ca0146eddda45220525bc08e7855e79c96dbd30930b0dff426cd114755b490e0838';
        $acUserId = get_user_meta($user->ID, 'activecampaign_user_id', true);
        $params = [
            'contact' => [
                'email' => $user->user_email,
                'firstName' => $user->first_name,
                'lastName' => $user->last_name
            ]
        ];

        $data = wp_remote_post($apiUrl, array(
            'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Api-Token' => $apiKey],
            'body' => json_encode($params),
            'method' => 'POST',
            'data_format' => 'body',
        ));
        $response = json_decode($data['body']);

        $contact = $response->contact;
        if ($contact->id) {
            $user_activation_status = new DateTime('now', new DateTimeZone('EST'));
            update_user_meta($user->ID, 'activecampaign_user_id', $contact->id);
            update_user_meta($user->ID, 'user_activation_date', $user_activation_status->getTimestamp());
            $this->user_registration_passed($user);
        }
    }

    private function checkNonce($nonce, $action)
    {
        return wp_verify_nonce($nonce, $action);
    }

    private static function returnData($data = [])
    {
        header('Content-Type:application/json');
        echo json_encode(['status' => true, 'data' => $data]);
        wp_die();
    }

    private static function returnError($message)
    {
        header('Content-Type:application/json');
        wp_send_json_error($message, 400);
        wp_die();
    }
}
