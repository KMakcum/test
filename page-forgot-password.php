<?php
if (!defined('ABSPATH')) {
    exit;
}
if ($_GET['token'] && $_GET['login']) {
    $user = op_help()->forgot_password->checkToken($_GET['token'], $_GET['login']);
    if ($user instanceof WP_User) {
        header('Location:' .
            add_query_arg([
                'login' => $_GET['login'],
                'token' => $_GET['token']
            ], get_site_url() . '/setup-password'));
    }
    if ($user instanceof WP_Error || $_GET['invalid-token']):
        get_header(); ?>
        <section class="reset-password-token-invalid">
            <main class="site-main site-main--has-border-top">
                <section class="reset-password">
                    <div class="container">
                        <div class="reset-password__data data">
                            <svg class="data__icon" width="104" height="104" fill="#DB4827">
                                <use href="#icon-warning-2"></use>
                            </svg>
                            <header class="data__header content">
                                <h1><?php echo __('Invalid link') ?></h1>
                                <p><?php echo __('Your reset link is expired or broken. To reset your password please enter your email
                                    below.') ?></p>
                            </header>
                            <div class="message message--full message--error" style="display: none">
                                <svg class="message__icon" width="24" height="24" fill="#690713">
                                    <use href="#icon-warning-3"></use>
                                </svg>
                                <p class="message__txt">
                                    <?php echo __('Please check spelling, we can\'t seem to find your email in our database.') ?>
                                </p>
                            </div>
                            <form class="data__form form" action="#" method="post">
                                <ul class="form__list fields-list">
                                    <li class="fields-list__item field-box">
                                        <input class="field-box__field" id="form-reset-password-email" type="email"
                                               name="user_email" required autofocus>
                                        <label class="field-box__label"
                                               for="form-reset-password-email"><?php echo __('Email') ?></label>
                                    </li><!-- / .field-box -->
                                </ul><!-- / .fields-list -->
                                <button class="form__button button"><?php echo __('Reset my password') ?></button>
                            </form><!-- / .form -->
                        </div><!-- / .data -->
                    </div>
                </section><!-- / .reset-password -->
            </main><!-- / .site-main -->
        </section>
        <?php
        get_footer();
    endif;
} else {
    get_header(); ?>
    <section class="reset-password-start">
        <main class="site-main site-main--has-border-top">
            <section class="reset-password">
                <div class="container">
                    <div class="reset-password__data data">
                        <div class="message message--full message--error" style="display: none">
                            <svg class="message__icon" width="24" height="24" fill="#690713">
                                <use href="#icon-warning-3"></use>
                            </svg>
                            <p class="message__txt">
                                <?php echo __('Please check spelling, we can\'t seem to find your email in our database.') ?>
                            </p>
                        </div>
                        <header class="data__header content">
                            <h1><?php echo __('Forgot password?') ?></h1>
                            <p><?php echo __('We are here to help. Just enter your email below.') ?></p>
                        </header>
                        <form class="data__form form">
                            <ul class="form__list fields-list">
                                <li class="fields-list__item field-box">
                                    <input class="field-box__field" id="form-reset-password-email" type="email"
                                           name="user_email" required autofocus>
                                    <label class="field-box__label" for="form-reset-password-email">Email</label>
                                </li><!-- / .field-box -->
                            </ul><!-- / .fields-list -->
                            <button class="form__button button"><?php echo __('Reset my password') ?></button>
                        </form><!-- / .form -->
                    </div><!-- / .data -->
                </div>
            </section><!-- / .reset-password -->
        </main><!-- / .site-main -->
    </section>
    <section class="reset-password-sent" style="display: none;">
        <main class="site-main site-main--has-border-top">
            <section class="status-page">
                <div class="container">
                    <svg class="status-page__icon" width="48" height="48" fill="#34A34F">
                        <use href="#icon-check-circle-stroke"></use>
                    </svg>
                    <div class="status-page__txt content">
                        <h1><?php echo __('Success!') ?></h1>
                        <p>
                            <?php echo __('We’ve just sent you an email with reset instructions. Please check your inbox and
                            follow reset instructions in the email.'); ?>
                        </p>
                    </div>
                </div>
            </section><!-- / .status-page -->
        </main><!-- / .site-main -->
    </section>
    <?php
    get_footer();
}

