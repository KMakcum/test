<?php

if (isset($_REQUEST['user_verification_action']) && trim($_REQUEST['user_verification_action']) == 'email_verification') {
    $activation_key = isset($_REQUEST['activation_key']) ? sanitize_text_field($_REQUEST['activation_key']) : '';
    $user_verification_settings = get_option('user_verification_settings');
    $login_after_verification = isset($user_verification_settings['email_verification']['login_after_verification']) ? $user_verification_settings['email_verification']['login_after_verification'] : '';


    //$redirect_after_verification = isset( $user_verification_settings['email_verification']['redirect_after_verification'] ) ? $user_verification_settings['email_verification']['redirect_after_verification'] : '';
    //$verification_page_id        = isset( $user_verification_settings['email_verification']['verification_page_id'] ) ? $user_verification_settings['email_verification']['verification_page_id'] : '';
    //$redirect_page_url = get_permalink( $redirect_after_verification );

    $jsData = [];

    global $wpdb;

    if (is_multisite()) {
        $table = $wpdb->base_prefix . "usermeta";
    } else {
        $table = $wpdb->prefix . "usermeta";
    }

    $meta_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE meta_value = %s", $activation_key));

    $user_id = $meta_data->user_id;

    $user_activation_status = get_user_meta($meta_data->user_id, 'user_activation_status', true);

    $user_email = get_user_by('id', $meta_data->user_id)->user_email;

    $user = get_user_by('id', $meta_data->user_id);
    if (!empty($meta_data)) {
        $jsData['is_valid_key'] = 'yes';

        if ($user_activation_status != 0) {
            wp_safe_redirect(get_site_url() . '/user-verification?invalid-link=true&user-email=' . $user_email);

//            $jsData['activation_status'] = 0;
//            $jsData['status_icon'] = '<svg class="modal-notice__icon" width="48" height="48" fill="#DB4827">
//                            <use href="#icon-error"></use>
//                        </svg>';
//            $jsData['status_text'] = __('Sorry! Verification failed.', 'user-verification');
//            $jsData['is_redirect'] = 'no';
//            $jsData['redirect_url'] = esc_url_raw(OP_User::sf_get_cookie('sf_register_page_user'));

        } else {
            $jsData['activation_status'] = 1;
            $jsData['status_icon'] = '<svg class="status-page__icon" width="48" height="48" fill="#34A34F">
                            <use href="#icon-check-circle-stroke"></use>
                        </svg>';
            $jsData['status_text'] = __('Thank you for confirming your email', 'user-verification');

            $jsData['is_redirect'] = 'yes';
            $jsData['redirect_url'] = esc_url_raw(OP_User::sf_get_cookie('sf_register_page_user'));


            $class_user_verification_emails = new class_user_verification_emails();
            $email_templates_data = $class_user_verification_emails->email_templates_data();

            $logo_id = isset($user_verification_settings['logo_id']) ? $user_verification_settings['logo_id'] : '';


            $exclude_user_roles = isset($user_verification_settings['email_verification']['exclude_user_roles']) ? $user_verification_settings['email_verification']['exclude_user_roles'] : array();
            $email_templates_data = isset($user_verification_settings['email_templates_data']['email_confirmed']) ? $user_verification_settings['email_templates_data']['email_confirmed'] : $email_templates_data['email_confirmed'];
            $email_templates_data = isset($user_verification_settings['email_templates_data']['email_confirmed']) ? $user_verification_settings['email_templates_data']['email_confirmed'] : $email_templates_data['email_confirmed'];


            $enable = isset($email_templates_data['enable']) ? $email_templates_data['enable'] : 'yes';

            $email_bcc = isset($email_templates_data['email_bcc']) ? $email_templates_data['email_bcc'] : '';
            $email_from = isset($email_templates_data['email_from']) ? $email_templates_data['email_from'] : '';
            $email_from_name = isset($email_templates_data['email_from_name']) ? $email_templates_data['email_from_name'] : '';
            $reply_to = isset($email_templates_data['reply_to']) ? $email_templates_data['reply_to'] : '';
            $reply_to_name = isset($email_templates_data['reply_to_name']) ? $email_templates_data['reply_to_name'] : '';
            $email_subject = isset($email_templates_data['subject']) ? $email_templates_data['subject'] : '';
            $email_body = isset($email_templates_data['html']) ? $email_templates_data['html'] : '';

            $email_body = do_shortcode($email_body);
            $email_body = wpautop($email_body);


            //update_user_meta( $user_id, 'user_activation_key', $user_activation_key );

            $user_data = get_userdata($user_id);
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

            $vars = [];

            $email_data['email_to'] = $user_data->user_email;
            $email_data['email_bcc'] = $email_bcc;
            $email_data['email_from'] = $email_from;
            $email_data['email_from_name'] = $email_from_name;
            $email_data['reply_to'] = $reply_to;
            $email_data['reply_to_name'] = $reply_to_name;

            $email_data['subject'] = strtr($email_subject, $vars);
            $email_data['html'] = strtr($email_body, $vars);
            $email_data['attachments'] = array();


            if ($enable == 'yes') {
                $mail_status = $class_user_verification_emails->send_email($email_data);
            }

            if ($login_after_verification == "yes") {

                $jsData['login_after_verify'] = 'yes';

                $user = get_user_by('id', $meta_data->user_id);

                if (!is_wp_error($user)) {

                }
            }
        }

    } else {
        wp_safe_redirect(get_site_url() . '/offerings');
    }
    get_header();
    ?>
    <main class="site-main checking-verification-main"
          style="justify-content: center;align-items: center;display: flex;">
        <section class="status-page">
            <div class="container">
                <svg class="status-page__icon" width="48" height="48" fill="#F2AE04">
                    <use href="#icon-warning-2"></use>
                </svg>
                <div class="status-page__txt content">
                    <h3><?php echo __('Checking Verification', 'user-verification'); ?></h3>
                    <p class="status-text"><?php echo __('Please wait...', 'user-verification'); ?></p>
                </div>
            </div>
        </section>
    </main>
    <script>
        jQuery(document).ready(function ($) {
            jsData = <?php echo json_encode($jsData); ?>

                activation_status = jsData['activation_status'];
            status_icon = jsData['status_icon'];
            status_text = jsData['status_text'];
            redirect_url = jsData['redirect_url'];
            is_redirect = jsData['is_redirect'];
            is_valid_key = jsData['is_valid_key'];

            setTimeout(function () {

                if (is_valid_key === 'yes') {

                    $( '.status-page > .container > svg' ).remove();
                    $( '.status-page > .container' ).prepend( status_icon );

                    $('.status-text').html(status_text);
                } else {
                    is_valid_icon = jsData['is_valid_icon'];
                    is_valid_text = jsData['is_valid_text'];

                    $('.status-icon').html(is_valid_icon);
                    $('.status-text').html(is_valid_text);

                    $('.redirect').fadeOut();
                }

            }, 1300);

            if (is_valid_key === 'yes') {
                setTimeout(function () {
                    if (is_redirect === 'yes') {
                        let formData = new FormData()
                        formData.append('action', 'verify_user_by_code_handler')
                        formData.append('nonce', ajaxSettings.ajax_nonce)
                        formData.append('verification_code', '<?php echo get_user_meta($user->ID,'four_digit_activation_code',true)?>')
                        formData.append('user_email', '<?php echo $user->user_email ?>')
                        $.ajax({
                            url: ajaxSettings.ajax_url,
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            success: function (response) {
                                if (response.data.status === 202) {
                                    window.location.href = response.data.redirect_url
                                }
                            },
                            error: function (response) {
                                console.log(response)
                            }
                        })
                    }
                    //$('.check-email-verification').fadeOut('slow');

                }, 1300);
            }

            $(document).on('click', '.check-email-verification', function () {
                $('.check-email-verification').fadeOut();
            })
        })
    </script>
    <?php
    get_footer();

}
