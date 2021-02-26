<?php  
$fields = op_help()->our_story->get_our_story_fields( get_the_ID() );
$bg_url = wp_get_attachment_url( $fields['bg_oph'] );
?>

<section class="info-section-2 our-philosophy" style="background-image: url(<?php echo $bg_url; ?>);">
    <div class="container">
        <div class="info-section-2__txt info-section-2__txt--align--right content">
            <h2><?php echo $fields['title_oph']; ?></h2>
            <p><?php echo nl2br( $fields['desc_oph'] ); ?></p>
        </div>
    </div>
</section>