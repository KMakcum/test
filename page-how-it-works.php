<?php
/**
* Template Name: How it works
*/

$how_it_works_fields = op_help()->how_it_works->get_how_it_works_fields(get_the_ID());

$zip_code = op_help()->sf_user::op_get_zip_cookie();
$link_href = ( empty( $zip_code ) ) ? '#js-modal-zip-code' : '#js-modal-sign-up';

get_header(); ?>

<main class="site-main site-main--padding-bottom--no">
    <section class="how-it-works-2">
        <div class="container">
            <div class="how-it-works-2__head content">
                <h1 class="how-it-works-2__title"><?php echo $how_it_works_fields['how_it_works_title'] ?></h1>
                <p class="how-it-works-2__text"><?php echo $how_it_works_fields['how_it_works_subheader'] ?></p>
            </div>
            <ul class="how-it-works-2__list work-stages-2">
                <?php
                foreach ($how_it_works_fields['how_it_works_content'] as $how_it_works_field):
                    ?>
                    <li class="work-stages-2__item">
                        <div class="work-stages-2__img-box">
                            <figure class="work-stages-2__figure">
                                <picture>
                                    <source srcset="<?php echo wp_get_attachment_url($how_it_works_field['how_it_works_right_image']) ?>"
                                            type="image/webp">
                                    <img class="work-stages-2__img"
                                         src="<?php echo wp_get_attachment_url($how_it_works_field['how_it_works_right_image']) ?>"
                                         alt="">
                                </picture>
                            </figure>
                        </div>
                        <div class="work-stages-2__content">
                            <img src="<?php echo wp_get_attachment_url($how_it_works_field['how_it_works_left_image']); ?>"
                                 width="64"
                                 height="64" alt="">
                            <div class="work-stages-2__txt">
                                <h3><?php echo $how_it_works_field['how_it_works_left_title'] ?></h3>
                                <p><?php echo $how_it_works_field['how_it_works_left_text'] ?></p>
                            </div>
                        </div>
                    </li>
                <?php
                endforeach;
                ?>
            </ul><!-- / .work-stages-2 -->
        </div>
    </section><!-- / .how-it-works -->
    <section class="ready-to-start-2">
        <div class="container">
            <div class="ready-to-start-2__wr">
                <div class="ready-to-start-2__box">
                    <h2 class="ready-to-start-2__title"><?php echo $how_it_works_fields['how_it_works_rdy_to_start_title'] ?></h2>
                    <p class="ready-to-start-2__text">
                        <?php echo $how_it_works_fields['how_it_works_rdy_to_start_text'] ?>
                    </p>
                    <?php
                    if ( ! is_user_logged_in() || empty( $zip_code ) ) {

                        ?>
                        <a  class="ready-to-start-2__button button btn-modal show-signup"
                            data-redirect="<?php echo esc_attr( $how_it_works_fields['how_it_works_rdy_to_start_button_url'] ); ?>"
                            href="<?php echo $link_href; ?>">
                            <?php echo $how_it_works_fields['how_it_works_rdy_to_start_button_text']; ?>
                        </a>
                    <?php } elseif ( is_user_logged_in() && ! empty( $zip_code ) ) {
                        ?>
                        <a  class="ready-to-start-2__button button"
                            href="<?php echo esc_attr( $how_it_works_fields['how_it_works_rdy_to_start_button_url'] ); ?>">
                            <?php echo $how_it_works_fields['how_it_works_rdy_to_start_button_text']; ?>
                        </a>
                    <?php } else {
                        ?>
                        <a  class="ready-to-start-2__button button"
                            href="<?php echo esc_attr( $how_it_works_fields['how_it_works_rdy_to_start_button_url'] ); ?>">
                            <?php echo $how_it_works_fields['how_it_works_rdy_to_start_button_text']; ?>
                        </a>
                    <?php }  ?>
                </div>
                <figure class="ready-to-start-2__figure">
                    <picture>
                        <source srcset="<?php echo wp_get_attachment_url($how_it_works_fields['how_it_works_rdy_to_start_bg']); ?>"
                                type="image/webp">
                        <img class="ready-to-start-2__img"
                             src="<?php echo wp_get_attachment_url($how_it_works_fields['how_it_works_rdy_to_start_bg']); ?>"
                             alt="">
                    </picture>
                </figure>
            </div>
        </div>
    </section><!-- / .ready-to-start-2 -->
</main><!-- / .site-main -->

<?php get_footer(); ?>
