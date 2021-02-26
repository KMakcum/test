<?php
$cta_block = op_help()->mainpage->get_cta_section( get_the_ID() );

if ( $cta_block['show'] ) :

    $bg_url = ( ! empty( $cta_block['bg'] ) ) ? 'style="background-image: url(' . $cta_block['bg'] . ');"' : '';

    if ( is_user_logged_in() ) {
        $current_user = wp_get_current_user();
        $zip_code     = trim( get_user_meta( $current_user->ID, 'sf_zipcode', true ) );
    } else {
        $zip_code = op_help()->sf_user::op_get_zip_cookie();
    }
    $zip_code    = empty( $zip_code ) ? null : $zip_code;
    $link_class  = '';
    $link_data_r = '';

    if ( ! $zip_code && $cta_block['link_modal'] ) {
        $link_class  = 'btn-modal';
        $link_href   = '#js-modal-zip-code';
        $link_data_r = esc_html( $cta_block['link'] );
    } elseif (! is_user_logged_in() && $zip_code) {
        $link_data_r = '#js-modal-sign-up';
        $link_href   = esc_html( $cta_block['link'] );
    }
    else {
        $link_href = esc_html( $cta_block['link'] );
    }
    ?>

    <section class="ready-to-start" <?php echo $bg_url; ?>>
        <div class="container">
            <div class="ready-to-start__box">
                <h2 class="ready-to-start__title"><?php echo esc_html( $cta_block['title'] ); ?></h2>

                <?php if ( ! is_user_logged_in() && ! empty( $zip_code )) { ?>
                    <a  class="ready-to-start__button button button--medium btn-modal show-signup"
                        data-redirect="<?php echo $cta_block['redirect']; ?>"
                        href="<?php echo $link_data_r; ?>">
                        Let’s Go!
                    </a>

                    <a  class="ready-to-start__button <?php echo $link_class; ?>"
                        data-redirect="<?php echo $link_data_r; ?>"
                        href="<?php echo $link_href; ?>">
                        Check your options
                    </a>
                <?php } elseif ( ! is_user_logged_in() && empty( $zip_code ) ) {
                    ?>
                    <a  class="ready-to-start__button button button--medium btn-modal show-signup"
                        data-redirect="<?php echo $cta_block['redirect']; ?>"
                        href="<?php echo $link_href; ?>">
                        Let’s Go!
                    </a>

                    <a  class="ready-to-start__button <?php echo $link_class; ?>"
                        data-redirect="<?php echo $link_data_r; ?>"
                        href="<?php echo $link_href; ?>">
                        Check your options
                    </a>
                <?php }

                else { ?>
                    <a  class="ready-to-start__button button button--medium"
                        href="<?php echo $cta_block['redirect']; ?>">
                        Let’s Go!
                    </a>

                    <a  class="ready-to-start__button <?php echo $link_class; ?>"
                        href="<?php echo $link_href; ?>">
                        Check your options
                    </a>

                <?php } ?>
            </div>
        </div>
    </section>

<?php
endif;