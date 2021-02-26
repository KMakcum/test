<?php
$page_title = carbon_get_theme_option( 'op_404_page_title' );
$page_description = carbon_get_theme_option( 'op_404_page_description' );
$page_try_title = carbon_get_theme_option( 'op_404_page_try_title' );
$page_try_first_link_text_before = carbon_get_theme_option( 'op_404_page_first_link_text_before' );
$page_try_first_link_title = carbon_get_theme_option( 'op_404_page_first_link_title' );
$page_try_first_link_url = carbon_get_theme_option( 'op_404_page_first_link_url' );
$page_try_second_link_text_before = carbon_get_theme_option( 'op_404_page_second_link_text_before' );
$page_try_second_link_title = carbon_get_theme_option( 'op_404_page_second_link_title' );
$page_try_second_link_url = carbon_get_theme_option( 'op_404_page_second_link_url' );
$page_try_third_link_title = carbon_get_theme_option( 'op_404_page_third_link_title' );
$page_try_third_link_url = carbon_get_theme_option( 'op_404_page_third_link_url' );

//if ( is_user_logged_in() ) {
//    $current_user = wp_get_current_user();
//    $zip_code     = trim( get_user_meta( $current_user->ID, 'sf_zipcode', true ) );
//} else {
//    $zip_code = op_help()->sf_user::op_get_zip_cookie();
//}

$zip_code = op_help()->zip_codes->get_current_user_zip();
$is_zip_national = op_help()->zip_codes->is_zip_zone_national( $zip_code );

get_header();
?>
<main class="site-main error-404-main">
    <section class="error-404">
        <div class="container">
            <div class="error-404__txt content">
                <h1><?php echo $page_title; ?></h1>
                <p><?php echo $page_description; ?></p>
                <p>
                    <strong><?php echo $page_try_title;?></strong>
                </p>
                <p><?php echo $page_try_first_link_text_before;
                    if( ! empty( $zip_code ) && ! $is_zip_national ) { ?>
                        <a href="<?php echo $page_try_first_link_url; ?>" ><?php echo $page_try_first_link_title; ?></a>
                    <?php } elseif ( ! empty( $zip_code ) && $is_zip_national ) { ?>
                        <a href="/offerings/" ><?php echo $page_try_first_link_title; ?></a>
                    <?php } else { ?>
                        <a class="<?php echo empty( $zip_code ) ? 'btn-modal' : ''; ?>" <?php echo empty( $zip_code ) ? 'href="#js-modal-zip-code" data-redirect="'. $page_try_first_link_url : 'href="' . $page_try_first_link_url . '"' ?>"><?php echo $page_try_first_link_title; ?></a>
                    <?php } ?>
                </p>
                <p><?php echo $page_try_second_link_text_before;
                    if( ! empty( $zip_code ) && ! $is_zip_national ) { ?>
                        <a href="<?php echo $page_try_second_link_url; ?>" ><?php echo $page_try_second_link_title; ?></a>
                    <?php } elseif ( ! empty( $zip_code ) && $is_zip_national ) { ?>
                        <a href="/offerings/" ><?php echo $page_try_second_link_title; ?></a>
                    <?php } else { ?>
                        <a class="<?php echo empty( $zip_code ) ? 'btn-modal' : ''; ?>" <?php echo empty( $zip_code ) ? 'href="#js-modal-zip-code" data-redirect="'. $page_try_second_link_url : 'href="' . $page_try_second_link_url . '"' ?>"><?php echo $page_try_second_link_title; ?></a>
                    <?php } ?>
                </p>
                <p>
                    <a href="<?php echo $page_try_third_link_url; ?>"><?php echo $page_try_third_link_title; ?></a>
                </p>
            </div>
        </div>
    </section><!-- / .error-404 -->
</main><!-- / .site-main .catalog-main -->

<?php get_footer();?>
