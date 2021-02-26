<?php  
$fields = op_help()->our_story->get_our_story_fields( get_the_ID() );
$bg_url = wp_get_attachment_url( $fields['bg_main'] );
$icon_url = wp_get_attachment_url( $fields['icon_main'] );
?>

<section class="our-story-offer" style="background-image: url(<?php echo $bg_url; ?>);">
    <div class="container">
        <div class="our-story-offer__txt content">

            <?php if ( ! empty( $icon_url ) ) : ?>
                <img src="<?php echo $icon_url; ?>" width="40" height="40" alt="">
            <?php endif; ?>

            <h1><?php echo $fields['title_main']; ?></h1>
        </div>
        <a class="our-story-offer__scroll-down scroll-down js-scrollto" href="#js-about-us">
            <svg width="24" height="24" fill="#252728">
                <use href="#icon-angle-down-light"></use>
            </svg>
        </a>
    </div>
</section> 