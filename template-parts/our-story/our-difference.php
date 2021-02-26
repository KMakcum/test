<?php  
$fields = op_help()->our_story->get_our_story_fields( get_the_ID() );
if ( is_user_logged_in() ) {
    $current_user = wp_get_current_user();
    $zip_code     = trim( get_user_meta( $current_user->ID, 'sf_zipcode', true ) );
} else {
    $zip_code = op_help()->sf_user::op_get_zip_cookie();
}
if ( isset( $zip_code ) ) {
    $is_zip_national = op_help()->zip_codes->is_zip_zone_national( $zip_code );
    $current_zip_zone = op_help()->zip_codes->get_zip_zone( $zip_code );
} else {
    $current_zip_zone = '';
}

?>

<?php if ( ! empty( $fields['rep_od'] ) ) : ?>
<?php
    if ( $current_zip_zone == 'overnight' ) {
        array_push( $fields['rep_od'], $fields['rep_od'][ 0 ]);
        unset( $fields['rep_od'][ 0 ] );
    } else if ( $current_zip_zone == 'national' ) {
        array_unshift( $fields['rep_od'], $fields['rep_od'][ count( $fields['rep_od'] ) - 1 ]);
        unset( $fields['rep_od'][ count( $fields['rep_od'] ) - 1 ] );
    } ?>
    <section class="our-difference">
        <div class="container">
            <ul class="our-difference__list card-list-2">
                <?php 
                foreach ( $fields['rep_od'] as $item ) {
                    $bg_url = wp_get_attachment_url( $item['bg_rep_od_os'] );
                    $icon_url = wp_get_attachment_url( $item['img_rep_od_os'] );
                    $title =  $item['txt_rep_od_os'];
                    $link = 'href="' . $item['link_rep_od_os'] . '"';
                    $button_text = $item['txt_link_rep_od_os'];
                    $span_class = 'card-2__button button button--light';
                    $modal_class = '';
                    switch ( $current_zip_zone ) {
                        case 'local':
                            break;
                        case 'overnight':
                            if ( $item['link_rep_od_os'] == '/product-category/meals/' ) {
                                $link = 'href="javascript: void(0);"';
                                $button_text = __( 'Coming Soon');
                                $span_class = 'card-2__coming-soon';
                            }
                            break;
                        case 'national':
                            if ( $item['link_rep_od_os'] == '/product-category/meals/' ||
                                $item['link_rep_od_os'] == '/groceries/' ) {
                                $link = 'href="javascript: void(0);"';
                                $button_text = __( 'Coming Soon');
                                $span_class = 'card-2__coming-soon';
                            }
                            break;
                        default:
                            $link = 'href="#js-modal-zip-code" data-redirect="'. $item['link_rep_od_os'] . '"';
                            $modal_class = 'btn-modal';
                            break;
                    } ?>
                    <li class="card-list-2__item">
                        <a class="card-2 <?php echo $modal_class; ?>" <?php echo $link; ?>>
                            <div class="card-2__body">
                                <?php if ( ! empty( $icon_url ) ) : ?>
                                    <img class="card-2__icon" src="<?php echo $icon_url; ?>" width="32" height="32" alt="">
                                <?php endif; ?>
                                <p class="card-2__title"><?php echo $title; ?></p>
                                <span class="<?php echo $span_class; ?>"><?php echo $button_text; ?></span>
                            </div>
                            <?php if ( ! empty( $bg_url ) ) : ?>
                                <picture>
                                    <img class="card-2__bg" src="<?php echo $bg_url; ?>" alt="">
                                </picture>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </section>

<?php endif; ?>
