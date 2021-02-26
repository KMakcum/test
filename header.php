<!DOCTYPE html>
<html class="no-js" lang="en">
<head>
    <!-- Meta -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180"
          href="<?php echo get_template_directory_uri(); ?>/assets/img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32"
          href="<?php echo get_template_directory_uri(); ?>/assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16"
          href="<?php echo get_template_directory_uri(); ?>/assets/img/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicon/site.webmanifest">
    <link rel="mask-icon" href="<?php echo get_template_directory_uri(); ?>/assets/img/favicon/safari-pinned-tab.svg"
          color="#5bbad5">

    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/assets/fonts/sora-regular.woff2" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/assets/fonts/sora-medium.woff2" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/assets/fonts/sora-light.woff2" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/assets/fonts/sora-semi-bold.woff2" as="font" type="font/woff2" crossorigin="anonymous">

    <meta name="msapplication-TileColor" content="#00a300">
    <meta name="theme-color" content="#ffffff">

    <!-- Styles -->
	<?php wp_head(); ?>

    <!-- Google Tag Manager -->
    <script>
            (function (w, d, s, l, i) {
				w[l] = w[l] || [];
				w[l].push({
					'gtm.start':
						new Date().getTime(), event: 'gtm.js'
				});
				var f = d.getElementsByTagName(s)[0],
					j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
				j.async = true;
				j.src =
					'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
				f.parentNode.insertBefore(j, f);
			})(window, document, 'script', 'dataLayer', 'GTM-NXGDRJW');
    </script>
    <!-- End Google Tag Manager -->

    <script async defer src="//assets.pinterest.com/js/pinit.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
</head>
<?php
// $loader_class = ( op_help()->shop->is_page_have_loader() ) ? 'main-loader' : ''; // TODO: uncomment to show loader during page load

$tutorial_class = op_help()->tutorial->tutorial_body_class();   
//$grouped_classes = ''; // TODO: uncomment and update, if two variables will contain classes
?>
<body <?php body_class( $tutorial_class ); // TODO: add "$loader_class" variable ?> data-tutorial-page="<?php echo op_help()->tutorial->tutorial_page_name(); ?>">

<!-- Tutorial hidden blocks with texts -->
<?php echo op_help()->tutorial->tutorial_texts(); ?>

<?php if ( op_help()->shop->is_page_have_loader() ) { ?>
    <!-- Preload image -->
    <img src="<?php echo get_template_directory_uri() ?>/assets/img/loaders/main.gif" class="preload-image">
    <div class="loader-container">
        <div class="loader-image"></div>
    </div>
<?php } ?>

<?php  if ( is_front_page() || is_page('offerings') ) { ?>
    <script>
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push({
							'event': 'homepage_load'
			});
    </script>
<?php }?>

<!-- Google Tag Manager (noscript) -->
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NXGDRJW"
            height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<!-- End Google Tag Manager (noscript) -->

<?php
$has_transparent_header = false;
$transparent_header     = '';
if ( $has_transparent_header ) {
	$transparent_header = 'main-header--bg-opacity';
}
add_filter( 'nav_menu_submenu_css_class', 'change_wp_nav_menu', 10, 3 );
function change_wp_nav_menu( $classes, $args, $depth ) {
    if ( $args->theme_location == 'hamburger_sub_menu' ) {
        $classes   = [];
        $classes[] = 'mobile-nav__list-lv-2';

        return $classes;
    }
}

$show_cart = true;
if ( ! WC()->cart->get_cart() ) {
    $cart_exclude_templates = [
        'page-how-it-works.php',
        'template-our-story.php',
        'page-faq.php',
        'page-contact-us.php',
        'front-page.php'
    ];
    $cart_exclude_pages = [
        'privacy-policy',
        'terms-conditions',
    ];
    $current_template = pathinfo( get_page_template() )['basename'];
    if ( in_array( $current_template, $cart_exclude_templates ) || is_page( $cart_exclude_pages ) ) {
        $show_cart = false;
    }
}
?>
<header class="main-header <?php echo esc_attr( $transparent_header ); ?>">
    <?php get_template_part( 'template-parts/promo-bar' ); ?>

    <nav class="main-nav">

        <div class="main-nav__top">
            <div class="main-nav__top-wr <?php echo is_front_page() ? 'main-nav__top-wr--centered-on-mobile' : ''; ?>">
                <div class="container">

                    <div class="main-nav__hamburger" title="Main menu">
                        <b></b>
                        <b></b>
                        <b></b>
                    </div>

					<?php
					$custom_logo = carbon_get_theme_option( 'op_theme_header_logo' );

					if ( empty( $custom_logo ) ) {
						$header_logo_url = op_help()->assets_url( 'base/logo.svg' );
					} else {
						$header_logo_url = wp_get_attachment_image_url( $custom_logo, 'full' );
					}
					?>
                    <a class="main-nav__logo" href="<?php echo esc_url( home_url() ); ?>">
                        <img src="<?php echo esc_url( $header_logo_url ); ?>" width="138" height="36"
                             alt="<?php bloginfo( 'name' ); ?>" title="<?php bloginfo( 'name' ); ?>">
                    </a>


					<?php
					if ( is_user_logged_in() ) {
						$current_user = wp_get_current_user();
						$zip_code     = trim( get_user_meta( $current_user->ID, 'sf_zipcode', true ) );
						$user_name    = get_user_meta( $current_user->ID, 'first_name', true );
					} else {
						$zip_code = op_help()->sf_user::op_get_zip_cookie();
					}

					$zip_show = empty( $zip_code ) ? 'Not set' : $zip_code;
					?>

					<?php if ( ! is_front_page() ) { ?>
                        <a class="main-nav__location main-nav__location--desktop btn-modal sf_show_zip_picker"
                           href="#js-modal-zip-code">
                            <svg width="24" height="24" fill="#252728">
                                <use href="#icon-map-marker"></use>
                            </svg>
							<span class="header-zip-value">
                                <?php echo esc_html( $zip_show ); ?>
                            </span>
                        </a>


					<?php } ?>

					<?php
					if ( has_nav_menu( 'header_menu' ) ) {
						wp_nav_menu( [
							'theme_location'  => 'header_menu',
							'menu'            => 'header_menu',
							'container'       => '',
							'container_class' => '',
							'container_id'    => '',
							'menu_class'      => 'main-nav__list site-nav',
							'menu_id'         => '',
							'echo'            => true,
							'fallback_cb'     => '',
							'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
							'walker'          => new Crb_Main_Menu_Walker(),
						] );
					}
					?>

					<?php
					if ( ! is_user_logged_in() ) {
						$link_href = ( empty( $zip_code ) ) ? '#js-modal-zip-code' : '#js-modal-sign-up';
						?>

                        <ul class="main-nav__user-list user-list">
                            <li class="user-list__item user-list__item--sign-in">
                                <a class="user-list__link user-list__link--full-height btn-modal"
                                   href="#js-modal-sign-in"><?php _e( 'Login' ); ?></a>
                            </li>
                            <li class="user-list__item user-list__item--full-height user-list__item--sign-up">
                                <a class="user-list__link button button--small btn-modal header-zip-update-link
                                <?php //echo is_front_page() ? 'show-signup' : ''; ?> show-signup"
                                   href="<?php echo $link_href; ?>"><?php _e( 'Sign Up' ); ?></a>
                            </li>
                            <li class="user-list__item user-list__item--mobile-profile">
                                <a class="user-list__link user-list__link--full-height btn-modal"
                                   href="<?php echo $link_href; ?>">
                                    <svg class="user-list__icon" width="24" height="24" fill="#252728">
                                        <use href="#icon-profile"></use>
                                    </svg>
                                </a>
                            </li>
                            <li class="user-list__item user-list__item--mobile-search-trigger">
                                <button class="user-list__link user-list__link--full-height" type="button">
                                    <svg class="user-list__icon" width="24" height="24" fill="#252728">
                                        <use href="#icon-search"></use>
                                    </svg>
                                </button>
                            </li>
                            <?php if ( op_help()->tutorial->tutorial_page_name() !== '' ) { ?>
                                <li class="user-list__item user-list__item--start-tutorial">
                                    <button class="control-button control-button--no-txt js-start-<?php echo op_help()->tutorial->tutorial_page_name(); ?>-tutorial remove-tutorial-status">
                                        <svg class="control-button__icon" width="24" height="24" fill="#252728">
                                            <use href="#icon-question"></use>
                                        </svg>
                                    </button>
                                </li>
                            <?php } ?>
                        </ul>

					<?php } else { ?>

                        <ul class="main-nav__user-list user-list">
                            <li class="user-list__item user-list__item--sign-in">
                                <a class="user-list__link user-list__link--full-height"
                                   href="<?php echo wp_logout_url( home_url() ); ?>">Sign Out</a>
                            </li>
                            <li class="user-list__item user-list__item--sign-in user-list__item--mobile-profile">
                                <a class="user-list__link user-list__link--full-height"
                                   href="<?php echo wp_logout_url( home_url() ); ?>">Sign Out</a>
                            </li>
                            <li class="user-list__item user-list__item--mobile-search-trigger">
                                <button class="user-list__link user-list__link--full-height" type="button">
                                    <svg class="user-list__icon" width="24" height="24" fill="#252728">
                                        <use href="#icon-search"></use>
                                    </svg>
                                </button>
                            </li>
                            <?php if ( op_help()->tutorial->tutorial_page_name() !== '' ) { ?>
                                <li class="user-list__item user-list__item--start-tutorial">
                                    <button class="control-button control-button--no-txt js-start-<?php echo op_help()->tutorial->tutorial_page_name(); ?>-tutorial remove-tutorial-status">
                                        <svg class="control-button__icon" width="24" height="24" fill="#252728">
                                            <use href="#icon-question"></use>
                                        </svg>
                                    </button>
                                </li>
                            <?php } ?>
                        </ul>

					<?php } ?>

                    <?php
                    if ( $show_cart ) {
                        op_help()->shop->header_cart_html();
                    }
                    ?>
                </div>
            </div>
        </div>

		<?php if ( ! is_front_page() ) { ?>
            <div class="mobile-actions-header">
                <!-- <a class="main-nav__location main-nav__location--mobile btn-modal" href="#js-modal-zip-code"> -->
                <a class="mobile-actions-header__location btn-modal" href="#js-modal-zip-code">
                    <svg width="24" height="24" fill="#252728">
                        <use href="#icon-map-marker"></use>
                    </svg>
                    Deliver
    				<?php if ( ! empty( $user_name ) ) { ?>
                        to <?php echo $user_name;
    				} ?> — <span class="header-zip-value"><?php echo esc_html( $zip_show ); ?></span>
                </a>
                <?php if ( op_help()->tutorial->tutorial_page_name() !== '' ) { ?>
                    <button class="mobile-actions-header__start-tutorial control-button control-button--no-txt js-start-<?php echo op_help()->tutorial->tutorial_page_name(); ?>-tutorial remove-tutorial-status">
                        <svg class="control-button__icon" width="24" height="24" fill="#252728">
                            <use href="#icon-question"></use>
                        </svg>
                    </button>
                <?php } ?>
            </div><!-- / .mobile-actions-header -->
		<?php } ?>
		<?php
        if ( is_tax( 'product_cat' )
            || is_singular( 'product' )
            || is_page_template( 'page-offerings.php' )
            || is_404()
            || is_tax( 'product_tag' )
            || get_post()->post_name === 'search-results') : ?>

            <div class="main-nav__bottom">
                <div class="container">

					<?php
					if ( has_nav_menu( 'header_menu_category' ) ) {
						wp_nav_menu( [
							'theme_location' => 'header_menu_category',
							'menu_class'     => 'main-nav__list-extra site-nav site-nav--extra',
							'container'      => false,
							'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
							'walker'         => new Walker_Menu_Submenu(),
						] );
					}
					?>
                    <?php get_template_part( 'inc/elastic-search/Views/search' ) ?>
                </div>
            </div>

		<?php endif; ?>

    </nav>
    <?php $zip_show = empty( $zip_code ) ? 'Not set' : 'Delivery to: <span class="header-zip-value">' . $zip_code . '</span>'; ?>
    <div class="side-nav">
        <div class="side-nav__head head-side-nav">
            <button class="head-side-nav__close" type="button">
                <svg width="24" height="24" fill="#252728">
                    <use href="#icon-times"></use>
                </svg>
            </button>
            <img class="head-side-nav__logo" src="<?php echo esc_url( $header_logo_url ); ?>" width="107"
                 alt="<?php bloginfo( 'name' ); ?>">
        </div>
        <div class="side-nav__body">
            <a class="location btn-modal" href="#js-modal-zip-code">
                <svg width="24" height="24" fill="#252728">
                    <use href="#icon-map-marker"></use>
                </svg>
                <?php echo html_entity_decode($zip_show); ?>
            </a>
            <section class="side-nav__section nav-section" <?php echo ! is_user_logged_in() ? 'style="padding-top: 0;"' : '' ?>>
                <?php if ( is_user_logged_in() ) { ?>
                    <h3 class="nav-section__title"><?php _e( 'On the menu' ); ?></h3>
                    <?php
                    if ( has_nav_menu( 'hamburger_sub_menu' ) ) {
                        wp_nav_menu( [
                            'theme_location'  => 'hamburger_sub_menu',
                            'menu'            => 'hamburger_sub_menu',
                            'container'       => '',
                            'container_class' => '',
                            'container_id'    => '',
                            'before'          => '<p class="mobile-nav__actions">',
                            'after'           => '</p>',
                            'menu_class'      => 'nav-section__list mobile-nav',
                            'menu_id'         => '',
                            'echo'            => true,
                            'fallback_cb'     => '',
                            'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                            'walker'          => new Crb_Main_Menu_Walker_Mobile(),
                        ] );
                    }
                } else {
                    if ( has_nav_menu( 'header_menu' ) ) {
                        wp_nav_menu( [
                            'theme_location'  => 'header_menu',
                            'menu'            => 'header_menu',
                            'container'       => '',
                            'container_class' => '',
                            'container_id'    => '',
                            'menu_class'      => 'nav-section__list mobile-nav',
                            'menu_id'         => '',
                            'echo'            => true,
                            'fallback_cb'     => '',
                            'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                            'walker'          => new Crb_Main_Menu_Walker_Mobile(),
                        ] );
                    }
                } ?>
            </section><!-- / .nav-section -->
            <?php
            if ( is_user_logged_in() ) { ?>
                <section class="side-nav__section nav-section">
                <h3 class="nav-section__title">Learn</h3>
                <?php
                if ( has_nav_menu( 'hamburger_nav_menu' ) ) {
                    wp_nav_menu( [
                        'theme_location'  => 'hamburger_nav_menu',
                        'menu'            => 'hamburger_nav_menu',
                        'container'       => '',
                        'container_class' => '',
                        'container_id'    => '',
                        'before'          => '<p class="mobile-nav__actions">',
                        'after'           => '</p>',
                        'menu_class'      => 'nav-section__list mobile-nav',
                        'menu_id'         => '',
                        'echo'            => true,
                        'fallback_cb'     => '',
                        'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                        'walker'          => new Crb_Main_Menu_Walker_Mobile(),
                    ] );
                }
                ?>
            </section><!-- / .nav-section -->
            <?php } ?>
            <?php
            $faq = get_page_by_path( 'faq' );
            ?>
            <ul class="side-nav__mobile-nav-2 mobile-nav-2">
                <li class="mobile-nav-2__item">
                    <a class="mobile-nav-2__link" href="<?php echo $faq->guid; ?>">
                        <svg class="mobile-nav-2__icon" width="24" height="24" fill="#252728">
                            <use href="#icon-question"></use>
                        </svg>
                        FAQ
                    </a>
                </li>
            </ul><!-- / .mobile-nav-2 -->
            <?php
            if ( has_nav_menu( 'hamburger_terms_menu' ) ) {
                wp_nav_menu( [
                    'theme_location'  => 'hamburger_terms_menu',
                    'menu'            => 'hamburger_terms_menu',
                    'container'       => '',
                    'container_class' => '',
                    'container_id'    => '',
                    'menu_class'      => 'side-nav__mobile-nav-3 mobile-nav-3',
                    'menu_id'         => '',
                    'echo'            => true,
                    'fallback_cb'     => '',
                    'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    'walker'          => new Crb_Main_Menu_Walker(),
                ] );
            }
            ?><!-- / .mobile-nav-3 -->
        </div>

    </div>

    <div class="overlay"></div>

</header><!-- / .main-header -->
