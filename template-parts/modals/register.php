<?php
    $zip_cookie = op_help()->sf_user::op_get_zip_cookie();

    if ( ! is_null( $zip_cookie ) ) {
        $data      = op_help()->sf_user::get_user_address_by_zip( $zip_cookie );
        $address_data = $data['predictions'][0]['terms'];

        $deliver = "We deliver to {$address_data[0]['value']}!";
    }
?>

<div class="modal-sign-up modal-common fancybox-content" id="js-modal-sign-up" style="display: none;">
    <button class="modal-common__back control-button control-button--no-txt" type="button" title="Back">
        <svg class="control-button__icon" width="24" height="24" fill="#252728">
            <use href="#icon-arrow-left"></use>
        </svg>
    </button>
    <div class="modal-common__data data">

        <div class="message message--full message--error" style="display: none;">
            <svg class="message__icon" width="24" height="24" fill="#690713">
                <use href="#icon-warning-3"></use>
            </svg>
            <p id="error_register" class="message__txt"></p>
        </div>

        <div class="message message--full message--success" style="display: none;">
            <svg class="message__icon" width="24" height="24" fill="#690713">
                <use href="#icon-warning-3"></use>
            </svg>
            <p id="success_register" class="message__txt"></p>
        </div>

        <header class="data__header content">
            <?php if ( isset( $address_data ) ) { ?>
                <h3><?php echo $deliver; ?></h3>
            <?php } ?>
            <p>Create your free LifeChef™ account</p>
        </header>

        <form id="register_form" class="data__form form" action="" method="post">
            <ul class="form__list fields-list">
                <li class="fields-list__item field-box">
                    <input class="field-box__field" id="modal-sign-up-email" type="email" name="user_email" required autofocus>
                    <label class="field-box__label" for="modal-sign-up-email">Email</label>
                </li>
                <li class="fields-list__item field-box field-box--has-icon">
                    <input class="field-box__field field-box__field--password pr-password" id="modal-sign-up-name" type="password" name="user_password" autocomplete="new-password" required>
                    <label class="field-box__label" for="modal-sign-up-name">Password</label>
                    <button class="field-box__password-switch" type="button">
                        <svg class="field-box__icon" width="24" height="24" fill="#BEC1C4">
                            <use class="field-box__icon-eye" xlink:href="#icon-eye" stroke="#BEC1C4" stroke-width="1.5"></use>
                            <use class="field-box__icon-eye-slash" xlink:href="#icon-eye-slash"></use>
                        </svg>
                    </button>
                </li>
            </ul>
            <p class="form__agreement checkbox">
                <input class="checkbox__field visually-hidden" id="modal-sign-up-agreement" type="checkbox" name="modal_sign_up[agreement]">
                <label class="checkbox__label" for="modal-sign-up-agreement">
                    I confirm that I have read and accepted <a href="<?php echo get_site_url().'/terms-conditions/' ?>" target="_blank">Terms and Conditions</a>
                    and <a href="<?php echo get_site_url().'/privacy-policy/' ?>" target="_blank">Privacy Policy</a>
                </label>
            </p>
            <input type="hidden" name="user_redirect" value="<?php echo get_site_url().$_SERVER['REQUEST_URI']; ?>">
            <button class="form__button button button--medium" name="register" disabled>Continue</button>
        </form>

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
                    Sign Up With Facebook
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
                    Sign Up With Google
                </a>
            </li>
        </ul>
        <p class="data__login-signup">Have an account? <a class="link-2 btn-modal" href="#js-modal-sign-in">Log in</a></p>
    </div>
    <button class="modal-common__close fancybox-button fancybox-close-small" type="button" data-fancybox-close="" title="Close">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="#252728" xmlns="http://www.w3.org/2000/svg">
            <path d="M16.192 6.344l-4.243 4.242-4.242-4.242-1.414 1.414L10.535 12l-4.242 4.242 1.414 1.414 4.242-4.242 4.243 4.242 1.414-1.414L13.364 12l4.242-4.242-1.414-1.414z"></path>
        </svg>
    </button>
</div>
