<?php

if (is_user_logged_in()) {
    header('Location: ' . get_site_url() . '/offerings');
}
$statuses =
    [
        'sended' => $_GET['sended'] ? (boolean)$_GET['sended'] : false,
        'sent' => $_GET['sent'] ? (boolean)$_GET['sent'] : false,
        'success' => $_GET['success'] ? (boolean)$_GET['success'] : false,
        'user_email' => $_GET['user-email'] ? $_GET['user-email'] : false,
        'invalid-link' => $_GET['invalid-link'] ? $_GET['invalid-link'] : false
    ];

$is_activation = $_GET['user-activation'] ? $_GET['user-activation'] : false;

if ($statuses['sent'] && !$statuses['sended'] && !$statuses['invalid-link'] && !$statuses['success'] && $statuses['user_email']) {
    get_header();
    get_template_part('template-parts/email-verification/verify-email-sent', '', ['user_email' => str_replace(' ', '+', urldecode($statuses['user_email']))]);
    get_template_part('template-parts/email-verification/verify-email-change-modal', '', ['user_email' => str_replace(' ', '+', urldecode($statuses['user_email']))]);
    get_footer();
}
if (!$statuses['sent'] && !$statuses['invalid-link'] && $statuses['sended'] && $statuses['user_email'] && !$statuses['success'] ) {
    get_header();
    get_template_part('template-parts/email-verification/verify-email-sended', '', ['user_email' => str_replace(' ', '+', urldecode($statuses['user_email']))]);
    get_template_part('template-parts/email-verification/verify-email-change-modal', '', ['user_email' => str_replace(' ', '+', urldecode($statuses['user_email']))]);
    get_footer();
}
if (!$statuses['sent'] && !$statuses['invalid-link'] && !$statuses['sended'] && $statuses['success']) {
    get_template_part('template-parts/email-verification/verify-email-success', '');
}
if ($statuses['invalid-link'] && !$statuses['sended'] && !$statuses['success'] && !$statuses['sent']) {
    get_header();
    get_template_part('template-parts/email-verification/verify-email-check-link-invalid', '', ['user_email' => str_replace(' ', '+', urldecode($statuses['user_email']))]);
    get_footer();
}
if (($_REQUEST['user_verification_action'] && trim($_REQUEST['user_verification_action']) == 'email_verification' && $_REQUEST['activation_key']) || $is_activation) {
    $is_activation = true;
    get_template_part('template-parts/email-verification/verify-email-check-link', '');
}
if (!$statuses['sent'] && !$statuses['sended'] && !$statuses['success'] && !$statuses['invalid-link'] && !$is_activation) {
    wp_safe_redirect(get_site_url() . '/offerings');
}
