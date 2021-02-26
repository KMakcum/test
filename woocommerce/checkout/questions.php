<?php  
$checkout_questions = carbon_get_theme_option( "sf_checkout_questions" );

if ( ! empty( $checkout_questions ) ) {
?>

    <section class="questions" style="margin-bottom: 16px;">
        <h2 class="questions__title">Common Questions</h2>
        <ul class="questions__accordion accordion-extra accordion-extra--contrast">

            <?php
            foreach ( $checkout_questions as $question_id ) {
                $question = get_post( $question_id['id'] );
                ?>

                    <li class="accordion-extra__item">
                        <p class="accordion-extra__header">
                            <?php echo esc_html( $question->post_title ); ?>
                        </p>
                        <div class="accordion-extra__content content content--small">
                            <?php echo apply_filters("the_content", $question->post_content ); ?>
                        </div>
                    </li>

            <?php } ?>

        </ul>
    </section>

<?php } ?>