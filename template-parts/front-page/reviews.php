<?php 
$reviews = op_help()->mainpage->get_reviews( get_the_ID() ); 

if ( ! empty( $reviews['reviews'] ) ) : ?>

    <section class="reviews">
        <div class="container">

            <?php if ( ! empty( $reviews['title'] ) ) : ?>
                <h2 class="reviews__title"><?php echo $reviews['title']; ?></h2>
            <?php endif; ?>

            <!-- Swiper -->
            <div class="reviews__slider reviews-slider swiper-container">
                <div class="reviews-slider__wrapper swiper-wrapper">

                    <?php foreach ( $reviews['reviews'] as $review ) : ?>



                        <div class="swiper-slide">
                            <div class="review">
                                <div class="review__txt content">

                                    <?php if ( ! empty( $review['review'] ) ) : ?>
                                        <?php echo apply_filters( "the_content", $review['review'] ); ?>
                                    <?php endif; ?>

                                </div>
                                <div class="review__rating rating js-rating--readonly--true" data-rate-value="5"></div>
                                <div class="review__author">
                                    
                                    <?php if ( $review['name'] ) : ?>
                                        <p class="review__name"><?php echo esc_html( $review['name'] ); ?></p>
                                    <?php endif; ?>

                                    <?php if ( $review['position'] ) : ?>
                                        <p class="review__position"><?php echo esc_html( $review['position'] ); ?></p>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>

                </div>
                <!-- Add Arrows -->
                <div class="reviews-slider__nav slider-nav">
                    <button class="slider-nav__button slider-nav__button--prev slider-arrow" type="button">
                        <span class="visually-hidden">Back</span>
                        <svg width="24" height="24" fill="#252728">
                            <use href="#icon-angle-left-light"></use>
                        </svg>
                    </button>
                    <span class="slider-nav__pagination"></span>
                    <button class="slider-nav__button slider-nav__button--next slider-arrow" type="button">
                        <span class="visually-hidden">Next</span>
                        <svg width="24" height="24" fill="#252728">
                            <use href="#icon-angle-rigth-light"></use>
                        </svg>
                    </button>
                </div><!-- / .slider-nav -->
            </div><!-- / .reviews-slider -->
        </div>
    </section>

<?php 
endif;