<?php  
$fields = op_help()->our_story->get_our_story_fields( get_the_ID() );
$bg_url = wp_get_attachment_url( $fields['bg_c'] );
?>

<section class="info-section-2 info-section-2--centred convenience" style="background-image: url(<?php echo $bg_url; ?>);">
    <div class="container">
        <div class="info-section-2__txt content">
            <h2><?php echo $fields['title_c']; ?></h2>
            <p><?php echo nl2br( $fields['desc_c'] ); ?></p>
        </div>
    </div>
</section>