<?php
if ( is_user_logged_in() ) {
    $current_user = wp_get_current_user();
    $zip_code     = trim( get_user_meta( $current_user->ID, 'sf_zipcode', true ) );
} else {
    $zip_code = op_help()->sf_user::op_get_zip_cookie();
}

$link_href = ( empty( $zip_code ) ) ? '#js-modal-zip-code' : '#js-modal-sign-up';
?>

<div class="modal-sign-in modal-common fancybox-content" id="js-modal-sign-in" style="display: none;">
    <div class="modal-common__data data">

        <div class="message message--full message--error" style="display: none;">
            <svg class="message__icon" width="24" height="24" fill="#690713">
                <use href="#icon-warning-3"></use>
            </svg>
            <p id="error_login" class="message__txt"></p>
        </div>

        <header class="data__header content">
            <h3>Welcome back!</h3>
            <p>Login to your LifeChef™ account</p>
        </header>

        <form id="login_form" class="data__form form" action="#" method="post">
            <ul class="form__list fields-list">
                <li class="fields-list__item field-box">
                    <input class="field-box__field" id="modal-sign-in-email" type="email" name="user_email" required autofocus>
                    <label class="field-box__label" for="modal-sign-in-email">Email</label>
                </li>
                <li class="fields-list__item field-box field-box--has-icon">
                    <input class="field-box__field field-box__field--password" id="modal-sign-in-name" type="password" name="user_password" required>
                    <label class="field-box__label" for="modal-sign-in-password">Password</label>
                    <button class="field-box__password-switch" type="button">
                        <svg class="field-box__icon" width="24" height="24" fill="#BEC1C4">
                            <use class="field-box__icon-eye" xlink:href="#icon-eye" stroke="#BEC1C4" stroke-width="1.5"></use>
                            <use class="field-box__icon-eye-slash" xlink:href="#icon-eye-slash"></use>
                        </svg>
                    </button>
                </li>
            </ul>
            <input type="hidden" name="user_redirect" value="<?php echo get_site_url().$_SERVER['REQUEST_URI']; ?>">
            <button class="form__button button button--medium" type="submit" name="login">Login</button>
        </form>

        <a class="data__reset-password link-2" href="<?php echo get_permalink(get_page_by_path('forgot-password')) ?>">Forgot password?</a>
        <ul class="data__button-list button-list">
            <li class="button-list__item">
                <a  class="button-list__button button button--medium button--block button--facebook"
                    href="<?php echo get_site_url(); ?>/wp-login.php?loginSocial=facebook"
                    data-plugin="nsl"
                    data-action="connect"
                    data-redirect="<?php echo get_permalink(); ?>"
                    data-provider="facebook"
                    data-popupwidth="475"
                    data-popupheight="175"
                >
                    <svg class="button__icon" width="24" height="24" fill="#fff">
                        <use href="#icon-facebook"></use>
                    </svg>
                    Continue With Facebook
                </a>
            </li>
            <li class="button-list__item">
                <a  class="button-list__button button button--medium button--block button--google"
                    href="<?php echo get_site_url(); ?>/wp-login.php?loginSocial=google"
                    data-plugin="nsl"
                    data-action="connect"
                    data-redirect="<?php echo get_permalink(); ?>"
                    data-provider="google"
                    data-popupwidth="600"
                    data-popupheight="600"
                >
                    <svg class="button__icon" width="24" height="24" fill="#fff">
                        <use href="#icon-google"></use>
                    </svg>
                    Continue With Google
                </a>
            </li>
        </ul>
        <p class="data__login-signup">Don’t have an account? <a class="link-2 btn-modal show-signup header-zip-update-link" href="<?php echo $link_href; ?>">Sign up</a></p>
    </div>
    <button class="modal-common__close fancybox-button fancybox-close-small" type="button" data-fancybox-close="" title="Close">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="#252728" xmlns="http://www.w3.org/2000/svg"><path d="M16.192 6.344l-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242-1.414-1.414z"></path></svg>
    </button>
</div>
