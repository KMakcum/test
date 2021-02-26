<?php
$custom_logo         = carbon_get_theme_option( 'op_theme_footer_logo' );
$under_logo_text     = carbon_get_theme_option( "op_theme_footer_slogan" );
$footer_menus        = carbon_get_theme_option( "op_theme_footer_menus" );
$social_links        = [
	[
		'title' => 'Facebook',
		'url'   => carbon_get_theme_option( "op_theme_footer_facebook" ),
		'icon'  => '#icon-facebook-circle',
	],
	[
		'title' => 'Twitter',
		'url'   => carbon_get_theme_option( "op_theme_footer_twitter" ),
		'icon'  => '#icon-twitter',
	],
	[
		'title' => 'Instagram',
		'url'   => carbon_get_theme_option( "op_theme_footer_instagram" ),
		'icon'  => '#icon-instagram',
	],
	[
		'title' => 'YouTube',
		'url'   => carbon_get_theme_option( "op_theme_footer_youtube" ),
		'icon'  => '#icon-youtube',
	],
];
$social_links_filled = array_filter( $social_links, function ( $social_item ) {
	return ! empty( $social_item['url'] );
} );
$copyright_text      = carbon_get_theme_option( "op_theme_footer_copyright" );
?>

<footer class="main-footer">
    <div class="main-footer__content">
        <div class="container">
            <div class="main-footer__logo-and-info">
                <a class="main-footer__logo" href="<?php echo esc_url( home_url() ); ?>">
					<?php
					if ( empty( $custom_logo ) ) {
						$header_logo_url = op_help()->assets_url( 'base/logo.svg' );
					} else {
						$header_logo_url = wp_get_attachment_image_url( $custom_logo, 'full' );
					}
					?>

                    <img src="<?php echo esc_url( $header_logo_url ); ?>"
                         width="138"
                         height="36"
                         alt="<?php bloginfo( 'name' ); ?>"
                         title="<?php bloginfo( 'name' ); ?>">

                </a>

				<?php if ( ! empty( $under_logo_text ) ) { ?>
                    <p class="main-footer__info"><?php echo esc_html( $under_logo_text ); ?></p>
				<?php } ?>

            </div>
            <ul class="main-footer__nav footer-nav">

				<?php
				if ( ! empty( $footer_menus ) ) {
					foreach ( $footer_menus as $menu_key => $menu ) {
						?>

                        <li class="footer-nav__item">
                            <p class="footer-nav__title"><?php echo esc_html( $menu['title'] ); ?></p>
							<?php
							wp_nav_menu( [
								'theme_location'  => 'footer_menu_' . $menu_key,
								'menu'            => 'footer_menu_' . $menu_key,
								'container'       => '',
								'container_class' => '',
								'container_id'    => '',
								'menu_class'      => 'footer-nav__sub-menu',
								'menu_id'         => '',
								'echo'            => true,
								'fallback_cb'     => '',
								'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
								'walker'          => new Crb_Main_Menu_Walker(),
							] );
							?>
                        </li>

						<?php
					}
				}
				?>

                <li class="footer-nav__item footer-nav__item--social">
                    <p class="footer-nav__title footer-nav__title--social">Follow Us</p>

					<?php if ( ! empty( $social_links_filled ) ) { ?>

                        <ul class="footer-nav__social social-links">

							<?php foreach ( $social_links_filled as $key => $social_link_item ) { ?>

                                <li class="social-links__item">
                                    <a class="social-links__link"
                                       href="<?php echo esc_url( $social_link_item['url'] ); ?>" target="_blank"
                                       rel="noopener nofollow">
                                        <svg class="social-links__icon" width="24" height="24" fill="#252728">
                                            <use xlink:href="<?php echo esc_attr( $social_link_item['icon'] ); ?>"></use>
                                        </svg>
                                        <span class="social-links__title"><?php echo esc_html( $social_link_item['title'] ); ?></span>
                                    </a>
                                </li>

							<?php } ?>

                        </ul>

					<?php } ?>

                </li>
            </ul>
        </div>
    </div>
    <div class="main-footer__legal">
        <div class="container">

			<?php if ( ! empty( $copyright_text ) ) { ?>
                <span class="main-footer__copyright"><?php echo esc_html( $copyright_text ); ?></span>
			<?php } ?>

			<?php
			wp_nav_menu( [
				'theme_location'  => 'right_menu',
				'menu'            => 'right_menu',
				'container'       => '',
				'container_class' => '',
				'container_id'    => '',
				'menu_class'      => 'main-footer__legal-list',
				'menu_id'         => '',
				'echo'            => true,
				'fallback_cb'     => '',
				'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'walker'          => new Crb_Main_Menu_Walker(),
			] );
			?>

        </div>
    </div>

	<?php // get_template_part( 'template-parts/meals-plan' ); ?>

</footer>
<?php

if ( ! is_user_logged_in() ) {
	get_template_part( 'template-parts/modals/login' );
	get_template_part( 'template-parts/modals/register' );
	if ( is_page_template( 'page-offerings.php' ) ) {
		get_template_part( 'template-parts/modals/survey-slider' );
	}
}


if ( is_checkout() ) {
	get_template_part( 'template-parts/modals/promo-code' );
}

get_template_part( 'template-parts/modals/zip-code' );

// Zip change modals
get_template_part( 'template-parts/modals/zip-update/zip-to-local' );
get_template_part( 'template-parts/modals/zip-update/zip-to-overnight' );
get_template_part( 'template-parts/modals/zip-update/zip-to-national' );
get_template_part( 'template-parts/modals/zip-update/zip-updated' );

get_template_part( 'template-parts/modals/zip-update/zip-address' );
get_template_part( 'template-parts/modals/zip-update/zip-billing-address' );

get_template_part( 'template-parts/modals/offer-code' );
get_template_part( 'template-parts/modals/survey' );
get_template_part( 'template-parts/modals/cookie-banner' );

if ( is_page_template( 'page-contact-us.php' ) ) {
    get_template_part( 'template-parts/modals/submit-question' );
}

if ( is_singular( 'product' ) ) { ?>
    <?php op_help()->customizer->getCustomizeComponentsJson(); ?>
<?php }

wp_footer();
?>

<script type="text/javascript">
	// Clickup
	window.onUsersnapCXLoad = function (api) {
		api.init();
	}
	var script = document.createElement('script');
	script.defer = 1;
	script.src = 'https://widget.usersnap.com/global/load/358e1901-7915-449e-9957-e63211eea8b7?onload=onUsersnapCXLoad';
	document.getElementsByTagName('head')[0].appendChild(script);
</script>
<script>
	window.intercomSettings = {
		app_id: "pvtl5q2o"
	};
</script>
<script>
	// We pre-filled your app ID in the widget URL: 'https://widget.intercom.io/widget/pvtl5q2o'
	(function () {
		var w = window;
		var ic = w.Intercom;
		if (typeof ic === "function") {
			ic('reattach_activator');
			ic('update', w.intercomSettings);
		} else {
			var d = document;
			var i = function () {
				i.c(arguments);
			};
			i.q = [];
			i.c = function (args) {
				i.q.push(args);
			};
			w.Intercom = i;
			var l = function () {
				var s = d.createElement('script');
				s.type = 'text/javascript';
				s.async = true;
				s.src = 'https://widget.intercom.io/widget/pvtl5q2o';
				var x = d.getElementsByTagName('script')[0];
				x.parentNode.insertBefore(s, x);
			};
			if (w.attachEvent) {
				w.attachEvent('onload', l);
			} else {
				w.addEventListener('load', l, false);
			}
		}
	})();
</script>

<?php if ( ! op_help()->sf_user->check_survey_exist() && isset( $_GET['take-survey'] ) ) { ?>
    <script>
			const surveyClass = '.sf_open_survey';

            document.addEventListener('DOMContentLoaded', function () {
            	let el = document.querySelector(surveyClass);

                setTimeout(() => {
                    el.click();
                }, 1000);
            });


    </script>
<?php } ?>

</body>
</html>
