<?php  
$fields = op_help()->our_story->get_our_story_fields( get_the_ID() );
?>

<section class="info-section-3">
    <div class="container">
        <div class="info-section-3__head content">
            <h2><?php echo $fields['title_oa']; ?></h2>
            <p><?php echo nl2br( $fields['desc_oa'] ); ?></p>
        </div>

        <?php if ( ! empty( $fields['rep_oa'] ) ) : ?>

            <ul class="info-section-3__collage collage">

                <?php 
                foreach ( $fields['rep_oa'] as $item ) : 

                    $img_url = wp_get_attachment_url( $item['img_rep_oa_os'] );
                ?>

                    <li class="collage__item">

                        <?php if ( ! empty( $img_url ) ) : ?>
                            <figure class="collage__figure">
                                <picture>
                                    <img class="collage__img" src="<?php echo $img_url; ?>" alt="">
                                </picture>
                            </figure>
                        <?php endif; ?>

                        <?php if ( ! empty( $item['rep_rep_oa_os'] ) ) : ?>

                            <ul class="collage__txt collage-txt">

                                <?php foreach ( $item['rep_rep_oa_os'] as $child_item ) : ?>

                                    <li class="collage-txt__item content">
                                        <?php echo apply_filters( 'the_content', $child_item['txt_rep_rep_oa_os'] ); ?>
                                    </li>
                                
                                <?php endforeach; ?>

                            </ul>

                        <?php endif; ?>

                    </li>

                <?php endforeach; ?>

            </ul>

        <?php endif; ?>

    </div>
</section>