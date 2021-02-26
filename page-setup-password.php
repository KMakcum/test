<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!$_GET['token'] || !$_GET['login']) {
    header('Location:' . get_site_url() . '/forgot-password');
}

get_header(); ?>
    <section class="reset-password-token-valid">
        <main class="site-main site-main--has-border-top">
            <section class="reset-password">
                <div class="container">
                    <div class="reset-password__data data">
                        <header class="data__header content">
                            <h1><?php echo __('Set up your new password'); ?></h1>
                        </header>
                        <div class="message message--full message--error" style="display: none">
                            <svg class="message__icon" width="24" height="24" fill="#690713">
                                <use href="#icon-warning-3"></use>
                            </svg>
                            <p class="message__txt">
                                <?php echo __('Your passwords are not identical, please check it.') ?>
                            </p>
                        </div>
                        <div class="message message--full message--error callback--error" style="display: none">
                            <svg class="message__icon" width="24" height="24" fill="#690713">
                                <use href="#icon-warning-3"></use>
                            </svg>
                            <p class="message__txt">
                            </p>
                        </div>
                        <form class="data__form form" action="#" method="post">
                            <ul class="form__list fields-list">
                                <li class="fields-list__item field-box field-box--has-icon">
                                    <input class="field-box__field pr-password-new" id="form-reset-password-new-password"
                                           type="password" name="user_new_password" required autofocus>
                                    <label class="field-box__label"
                                           for="form-reset-password-new-password"><?php echo __('Create
                                        password') ?></label>
                                    <button class="field-box__password-switch" type="button">
                                        <svg class="field-box__icon" width="24" height="24" fill="#BEC1C4">
                                            <use class="field-box__icon-eye" xlink:href="#icon-eye" stroke="#BEC1C4" stroke-width="1.5"></use>
                                            <use class="field-box__icon-eye-slash" xlink:href="#icon-eye-slash"></use>
                                        </svg>
                                    </button>
                                </li><!-- / .field-box -->
                                <li class="fields-list__item field-box field-box--has-icon">
                                    <input class="field-box__field" id="form-reset-password-confirm-password"
                                           type="password" name="user_new_password" required>
                                    <label class="field-box__label"
                                           for="form-reset-password-confirm-password"><?php echo __('Confirm
                                        password') ?></label>
                                    <button class="field-box__password-switch" type="button">
                                        <svg class="field-box__icon" width="24" height="24" fill="#BEC1C4">
                                            <use class="field-box__icon-eye" xlink:href="#icon-eye" stroke="#BEC1C4" stroke-width="1.5"></use>
                                            <use class="field-box__icon-eye-slash" xlink:href="#icon-eye-slash"></use>
                                        </svg>
                                    </button>
                                </li><!-- / .field-box -->
                            </ul><!-- / .fields-list -->
                            <button class="form__button button"><?php echo __('Login') ?></button>
                        </form><!-- / .form -->
                    </div><!-- / .data -->
                </div>
            </section><!-- / .reset-password -->
        </main><!-- / .site-main -->
    </section>

    <section class="reset-password-has-been-changed" style="display: none">
        <main class="site-main site-main--has-border-top">
            <section class="status-page">
                <div class="container">
                    <svg class="status-page__icon" width="48" height="48" fill="#34A34F">
                        <use href="#icon-check-circle-stroke"></use>
                    </svg>
                    <div class="status-page__txt content">
                        <h1><?php echo __('Your password has been changed!') ?></h1>
                        <p><?php echo __('Please') ?> <a class="btn-modal"
                                                         href="#js-modal-sign-in"><?php echo __('Login') ?></a> <?php echo __('with your new credentials.') ?>
                        </p>
                    </div>
                </div>
            </section><!-- / .status-page -->
        </main><!-- / .site-main -->
    </section>

<?php
get_footer();
