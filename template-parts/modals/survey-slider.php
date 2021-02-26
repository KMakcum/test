<!-- Modal slider -->
<div class="modal-slider modal-common" id="js-modal-slider" style="display: none">
    <!-- Swiper -->
    <div class="modal-slider__container swiper-container">
        <div class="modal-slider__wrapper swiper-wrapper">
            <div class="modal-slider__item swiper-slide">
                <div class="modal-slider__txt content">
                    <h2><?php _e('Welcome to Life Chef!'); ?></h2>
                    <p><?php _e('Our mission is to choose the best food and nutrition for you.
                        Before you proceed, please take a minute to complete a short survey'); ?>
                    </p>
                </div>
                <picture>
                    <source srcset="<?php echo get_template_directory_uri(); ?>/assets/img/base/modal-slide-1.webp" type="image/webp">
                    <img class="modal-slider__bg" src="<?php echo get_template_directory_uri(); ?>/assets/img/base/modal-slide-1.jpg" alt="">
                </picture>
            </div>
            <div class="modal-slider__item swiper-slide">
                <div class="modal-slider__txt modal-slider__txt--security content">
                    <h2><?php _e('LifeChef\'s™ unique health survey'); ?></h2>
                    <p><?php _e('Take our survey and get a personalized meal plan delivered to your door.'); ?></p>
                </div>
                <ul class="modal-slider__security-list security-list">
                    <li class="security-list__item">
                        <img class="security-list__icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/base/security-badge-white-1.svg" width="56" height="56" alt="">
                        <div class="security-list__txt content">
                            <p><?php _e('We keep your personal information safe.'); ?></p>
                        </div>
                    </li>
                    <li class="security-list__item">
                        <img class="security-list__icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/base/security-badge-white-2.svg" width="56" height="56" alt="">
                        <div class="security-list__txt content">
                            <p><?php _e('Your data is encripted, so LifeChef™ cannot read it or reuse it.'); ?></p>
                        </div>
                    </li>
                    <li class="security-list__item">
                        <img class="security-list__icon" src="<?php echo get_template_directory_uri(); ?>/assets/img/base/security-badge-white-3.svg" width="56" height="56" alt="">
                        <div class="security-list__txt content">
                            <p><?php _e('We don’t pass your data  to third parties.'); ?></p>
                        </div>
                    </li>
                </ul><!-- / .security-list -->
                <picture>
                    <source srcset="<?php echo get_template_directory_uri(); ?>/assets/img/base/modal-slide-5.webp" type="image/webp">
                    <img class="modal-slider__bg" src="<?php echo get_template_directory_uri(); ?>/assets/img/base/modal-slide-5.jpg" alt="">
                </picture>
            </div>
            <div class="modal-slider__item swiper-slide">
                <div class="modal-slider__txt content">
                    <h2><?php _e('Healthy food'); ?></h2>
                    <p><?php _e('We suggest food and groceries that are your tailored to your nutritional health needs'); ?></p>
                </div>
                <picture>
                    <source srcset="<?php echo get_template_directory_uri(); ?>/assets/img/base/modal-slide-2.webp" type="image/webp">
                    <img class="modal-slider__bg" src="<?php echo get_template_directory_uri(); ?>/assets/img/base/modal-slide-2.jpg" alt="">
                </picture>
            </div>
            <div class="modal-slider__item swiper-slide">
                <div class="modal-slider__txt content">
                    <h2><?php _e('For your lifestyle'); ?></h2>
                    <p><?php _e('Share your story and we will help you enhance your lifestyle with our Doctor recommended food and micronutrients'); ?></p>
                </div>
                <picture>
                    <source srcset="<?php echo get_template_directory_uri(); ?>/assets/img/base/modal-slide-3.webp" type="image/webp">
                    <img class="modal-slider__bg" src="<?php echo get_template_directory_uri(); ?>/assets/img/base/modal-slide-3.jpg" alt="">
                </picture>
            </div>
            <div class="modal-slider__item swiper-slide">
                <div class="modal-slider__txt content">
                    <h2><?php _e('In a convinient way'); ?></h2>
                    <p><?php _e('Get everything you need with Life Chef recurring deliveries when it is most convenient for you'); ?></p>
                </div>
                <picture>
                    <source srcset="<?php echo get_template_directory_uri(); ?>/assets/img/base/modal-slide-4.webp" type="image/webp">
                    <img class="modal-slider__bg" src="<?php echo get_template_directory_uri(); ?>/assets/img/base/modal-slide-4.jpg" alt="">
                </picture>
            </div>
        </div>
        <div class="modal-slider__static">
            <div class="modal-slider__pagination swiper-pagination"></div>
            <a class="modal-slider__button button" href="#"><?php _e('Take a survey'); ?></a>
            <a class="modal-slider__button" style="display: none;" href="#"><?php _e('Continue as a guest'); ?></a>
        </div>
    </div>
    <button class="modal-slider__skip" type="button" data-fancybox-close><?php _e('Skip for now'); ?></button>
</div><!-- / .modal-slider -->

