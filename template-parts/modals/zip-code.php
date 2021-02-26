<?php
$zip_from_cookie = op_help()->sf_user::op_get_zip_cookie();

if ( is_user_logged_in() ) {
    $current_user = wp_get_current_user();
    $zip_code = get_user_meta( $current_user->ID, 'sf_zipcode', true );
} else {
    $zip_code = isset( $zip_from_cookie) ? $zip_from_cookie : 0;
}
$zip_code = empty( $zip_code ) ? '' : $zip_code;

// Get page slug
$current_slug = trim( parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/' );
$slug_keyword = explode( '/', $current_slug )[ 0 ];

// Setup initial zip status (true if page in zip AJAX list)
$is_ajax = false;
if ( in_array( $current_slug, carbon_get_theme_option('op_zip_ajax_pages') ) && $zip_code == '' ) {
    $is_ajax = true;
}

// Redirect url
$current_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

?>
    <script>
        var is_user_logged_in = '<?php echo ( is_user_logged_in() ) ? 1 : 0 ; ?>';
        var user_zip_code = '<?php echo ( ! is_null( $zip_from_cookie ) ) ? $zip_from_cookie : 0 ; ?>';
    </script>

<div class="modal-zip-code modal-common" id="js-modal-zip-code" style="display: none" <?php echo empty( $zip_code ) ? 'data-autoshow=1' : ''; ?>>
    <div class="modal-common__data data">

        <div class="message message--full message--error" style="display: none;">
            <svg class="message__icon" width="24" height="24" fill="#690713">
                <use href="#icon-warning-3"></use>
            </svg>
            <p id="error_zipcode" class="message__txt"></p>
        </div>

        <header class="data__header content">

            <img class="data__logo" src="<?php echo get_template_directory_uri(); ?>/assets/img/base/logo.svg" width="205" alt="Life Chef">

            <h3>Before you begin</h3>
            <p>Let us know where to ship your order</p>
        </header>
        <form id="zipcode_form" class="data__form form" action="#" method="post">
            <ul class="form__list fields-list">
                <li class="fields-list__item field-box">
                    <input id="signup-flow" type="hidden" name="sign_up_flow" value="false">
                    <input id="redirect" type="hidden" name="redirect" value="<?php echo $current_url; ?>">
                    <input id="is_ajax" type="hidden" name="is_ajax" value="<?php echo $is_ajax; ?>">
                    <input id="current_page" type="hidden" name="current_page" value="<?php echo $slug_keyword; ?>">
                    <input class="field-box__field postal_code"
                           id="modal-zip-code-value"
                           type="number"
                           inputmode="numeric"
                           name="zip_code" 
                           value="<?php echo esc_attr( $zip_code ); ?>"
                           maxlength="5"
                           placeholder=""
                           autocomplete="off"
                           required>
                    <label class="field-box__label" for="modal-zip-code-value">ZIP Code</label>
                </li>
            </ul>
            <button type="submit" class="form__button button button--medium" disabled>Continue</button>
        </form>
        <?php if ( ! is_user_logged_in() ) : ?>

            <p class="data__login-signup">Have an account? <a class="link-2 btn-modal" href="#js-modal-sign-in">Log in</a></p>

        <?php endif; ?>

        <p class="data__credits">Meals and groceries are only delivered to limited areas, whereas vitamins and supplements are delivered Nationwide</p>
    </div>
</div>
