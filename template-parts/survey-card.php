<?php
if ( ! op_help()->sf_user->check_survey_exist() ) { ?>
    <div class="swiper-slide">
        <a class="offer-card <?php echo is_user_logged_in() ? ' sf_open_survey' : 'btn-modal'; ?>" href="<?php echo is_user_logged_in() ? '#' : '#js-modal-sign-up'; ?>">

            <div class="offer-card__body">
                <div class="offer-card__txt content">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/base/icon-planning.svg"
                         width="64" height="64" alt="">
                    <h3>Personalize your experience</h3>
                    <p>Take a few minutes to complete our nutritional survey to build your
                        personalized
                        menu.</p>
                </div>
                <?php  if ( is_user_logged_in() ) { ?>
                    <span class="offer-card__button button">
	                    <?php _e('Take a Survey') ?>
                    </span>
                <?php } else { ?>
                    <span class="offer-card__button button">
                        <?php _e('Take a Survey') ?>
                    </span>
                <?php } ?>

            </div>
            <picture>
                <source srcset="<?php echo get_template_directory_uri(); ?>/assets/img/base/personalize-your-experience.webp"
                        type="image/webp">
                <img class="offer-card__bg"
                     src="<?php echo get_template_directory_uri(); ?>/assets/img/base/personalize-your-experience.jpg"
                     alt="">
            </picture>
        </a>
    </div>
<?php } ?>
