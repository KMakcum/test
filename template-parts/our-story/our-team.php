<?php  
$fields = op_help()->our_story->get_our_story_fields( get_the_ID() );
?>

<section class="our-team">

    <?php if ( ! empty( $fields['title_op'] ) ) : ?>

        <div class="our-team__head">
            <div class="container">
                <h2 class="our-team__title"><?php echo $fields['title_op']; ?></h2>
            </div>
        </div>

    <?php endif; ?>

    <?php if ( ! empty( $fields['rep_op'] ) ) : ?>

        <div class="team-slider swiper-container">
            <div class="swiper-wrapper">

                <?php 
                foreach ( $fields['rep_op'] as $item ) : 

                    $img_url = wp_get_attachment_url( $item['img_rep_op_os'] );
                ?>

                    <div class="team-slider__item swiper-slide">
                        <div class="member">
                            <div class="member__inner">

                                <?php if ( ! empty( $img_url ) ) : ?>
                                    <figure class="member__img-box">
                                        <picture>
                                            <img class="member__img" src="<?php echo $img_url; ?>" alt="<?php echo $item['name_rep_op_os']; ?>">
                                        </picture>
                                    </figure>
                                <?php endif; ?>

                                <div class="member__info">

                                    <?php if ( ! empty( $item['name_rep_op_os'] ) ) : ?>
                                        <p class="member__name"><?php echo $item['name_rep_op_os']; ?></p>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $item['position_rep_op_os'] ) ) : ?>
                                        <p class="member__position"><?php echo $item['position_rep_op_os']; ?></p>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $item['txt_rep_op_os'] ) ) : ?>
                                        <div class="member__txt content">
                                            <p><?php echo nl2br( $item['txt_rep_op_os'] ); ?></p>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>

            </div>
        </div>

    <?php endif; ?>

</section>