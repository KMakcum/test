<?php  
$fields = op_help()->our_story->get_our_story_fields( get_the_ID() );
?>

<section class="info-section-1" id="js-about-us">
    <div class="container">
        <div class="info-section-1__txt content content--2-columns">
            <?php echo apply_filters( 'the_content', $fields['txt_about'] ); ?>
        </div>
    </div>
</section>