<?php
$slides = op_help()->mainpage->get_slider( get_the_ID() );
$button_text = carbon_get_theme_option( 'op_homepage_slider_button_title' );
if ( is_user_logged_in() ) {
    $current_user = wp_get_current_user();
    $zip_code     = trim( get_user_meta( $current_user->ID, 'sf_zipcode', true ) );
} else {
    $zip_code = op_help()->sf_user::op_get_zip_cookie();
}
$link_href = ( empty( $zip_code ) ) ? '#js-modal-zip-code' : '#js-modal-sign-up';

if ( ! empty ( $slides ) ) :
?>

    <section class="offer-slider swiper-container">
        <div class="offer-slider__wrapper swiper-wrapper">

            <?php foreach ( $slides as $slide ) : ?>

                <div class="offer-slider__item swiper-slide">
                    <div class="container">
                        <div class="offer-slider__lead lead">
                            <div class="lead__txt content">

                                <?php if ( ! empty( $slide['title'] ) ) : ?>
                                    <h2><?php echo esc_html( $slide['title'] ); ?></h2>
                                <?php endif; ?>

                                <?php if ( ! empty( $slide['descr'] ) ) : ?>
                                    <p><?php echo esc_html( $slide['descr'] ); ?></p>
                                <?php endif; ?>

                            </div>
                            <div class="lead__actions">
                                <ul class="lead__buttons">
                                    <?php if ( ! is_user_logged_in() || empty( $zip_code ) ) : ?>
                                        <?php $data_redirect = ( empty( $zip_code ) ) ? '#js-modal-sign-up' : esc_url( $slide['link'] ); ?>
                                        <li class="lead__buttons-item">
                                            <a  class="lead__button button button--medium show-signup"
                                                data-redirect="<?php echo esc_url( $slide['link'] ); ?>"
                                                href="<?php echo $link_href; ?>">Get Started</a>
                                        </li>
                                        <li class="lead__buttons-item">
                                            <a  class="lead__button"
                                                data-redirect="<?php echo esc_url( $slide['link'] ); ?>"
                                                href="<?php echo $link_href; ?>">
                                                <?php echo !empty( $button_text ) ? $button_text : __( 'Discover meals' ); ?>
                                            </a>
                                        </li>
                                    <?php else : ?>
                                        <li class="lead__buttons-item">
                                            <a  class="lead__button button button--medium"
                                                href="<?php echo esc_url( $slide['link'] ); ?>">Get Started</a>
                                        </li>
                                        <li class="lead__buttons-item">
                                            <a  class="lead__button"
                                                href="<?php echo esc_url( $slide['link'] ); ?>">
                                                <?php echo !empty( $button_text ) ? $button_text : __( 'Discover meals' ); ?>
                                            </a>
                                        </li>

                                    <?php endif; ?>

                                </ul>
                                <!-- Add Arrows -->
                                <div class="lead__slider-nav slider-nav">
                                    <button class="slider-nav__button slider-nav__button--prev slider-arrow slider-arrow--gray" type="button">
                                        <span class="visually-hidden">Back</span>
                                        <svg width="24" height="24" fill="#BEC1C4">
                                            <use href="#icon-angle-left-light"></use>
                                        </svg>
                                    </button>
                                    <span class="slider-nav__pagination"></span>
                                    <button class="slider-nav__button slider-nav__button--next slider-arrow slider-arrow--gray" type="button">
                                        <span class="visually-hidden">Next</span>
                                        <svg width="24" height="24" fill="#BEC1C4">
                                            <use href="#icon-angle-rigth-light"></use>
                                        </svg>
                                    </button>
                                </div><!-- / .slider-nav -->
                            </div>
                        </div><!-- / .lead -->
                    </div>

                    <?php if ( ! empty( $slide['image'] ) ) : ?>
                        <picture>
                            <img class="offer-slider__bg"
                                 src="<?php echo esc_url( $slide['image'] ); ?>"
                                 alt="<?php echo strip_tags( $slide['title'] ); ?>">
                        </picture>
                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        </div>
        <div class="offer-slider__mobile-nav slider-nav slider-nav--white">
            <button class="slider-nav__button slider-nav__button--prev slider-arrow slider-arrow--white" type="button">
                <span class="visually-hidden">Back</span>
                <svg width="24" height="24" fill="#fff">
                    <use href="#icon-angle-left-light"></use>
                </svg>
            </button>
            <span class="slider-nav__pagination"></span>
            <button class="slider-nav__button slider-nav__button--next slider-arrow slider-arrow--white" type="button">
                <span class="visually-hidden">Next</span>
                <svg width="24" height="24" fill="#fff">
                    <use href="#icon-angle-rigth-light"></use>
                </svg>
            </button>
        </div><!-- / .slider-nav -->
    </section>

<?php
endif;
