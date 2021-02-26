<?php  
list( $about_title, $about_descr, $about_img, $desc_left, $txt_link, $link, $img_right  ) = op_help()->mainpage->get_block_about( get_the_ID() ); 
?>

<section class="about-us">
    <div class="container">
        <div class="about-us__wr">
            <div class="about-us__box content">
                
                <?php if ( ! empty( $about_title ) ) : ?>
                    <h2><?php echo esc_html( $about_title ); ?></h2>
                <?php endif; ?>

                <?php if ( ! empty( $about_descr ) ) : ?>
                    <p><?php echo  $about_descr; ?></p>
                <?php endif; ?>

            </div>
            <div class="about-us__box content">
                
                <?php if ( ! empty( $desc_left ) ) : ?>
                    <p><?php echo  $desc_left; ?></p>
                <?php endif; ?>

                <?php if ( ! empty( $link ) && ! empty( $txt_link ) ) : ?>
                    <a class="about-us__more" href="<?php echo $link; ?>">
                        <?php echo $txt_link; ?>
                    </a>
                <?php endif; ?>

            </div>

            <?php if ( ! empty( $about_img ) ) : ?>
                <figure class="about-us__figure">
                    <picture>
                        <img class="about-us__img" src="<?php echo esc_url( $about_img ); ?>" alt="">
                    </picture>
                </figure>
            <?php endif; ?>

            <?php if ( ! empty( $img_right ) ) : ?>
                <figure class="about-us__figure">
                    <picture>
                        <img class="about-us__img" src="<?php echo esc_url( $img_right ); ?>" alt="">
                    </picture>
                </figure>
            <?php endif; ?>

        </div>
    </div>
</section>