<?php 
$txt = carbon_get_theme_option( "op_theme_header_txt_promo" );
$btn = carbon_get_theme_option( "op_theme_header_btn_promo" );

$zip_cookie = op_help()->sf_user->op_get_zip_cookie();
$modal_link = '#js-modal-zip-code';
if ( ! is_null( $zip_cookie ) ) {
	$modal_link = '#js-modal-promo-code-to-use';
}

$classes = '';

if ( is_front_page() ) {
    $classes = 'show-signup';
}

if ( ! is_user_logged_in() && ! isset( $_COOKIE['hide_offer_banner'] ) ) : ?>
	<div class="main-header__banner promo-bar">
		<?php if ( ! empty( $txt ) ) : ?>
	    	<span class="promo-bar__txt"><?php echo nl2br( $txt ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $btn ) ) : ?>
	    	<a class="promo-bar__button button button--small button--light btn-modal <?php echo $classes; ?>" href="<?php echo $modal_link ?>">
	    		<?php echo $btn; ?>
	    	</a>
    	<?php endif; ?>
        <button class="promo-bar__close control-button control-button--no-txt" type="button" title="Close">
            <svg width="24" height="24" fill="#fff">
                <use href="#icon-times"></use>
            </svg>
        </button>
	</div>
<?php endif; ?>
