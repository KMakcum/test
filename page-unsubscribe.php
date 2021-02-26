<?php get_header();
$reasons_array = explode( '|', carbon_get_theme_option( 'op_unsubscribe_reasons' ) );
$title = carbon_get_theme_option( 'op_unsubscribe_reasons_title' );
$description = carbon_get_theme_option( 'op_unsubscribe_reasons_description' );
$button_text = carbon_get_theme_option( 'op_unsubscribe_reasons_button' );
?>
<main class="site-main unsubscribe-main">
    <div class="container">
        <section class="unsubscribe">
            <div class="unsubscribe__head content">
                <h1><?php _e( $title ); ?></h1>
                <p>
                    <?php _e( $description ); ?>
                </p>
            </div>
            <form class="unsubscribe__form form" action="#" method="post">
                <ul class="form__checkbox-list checkbox-list checkbox-list--columns--1">
                    <?php foreach ( $reasons_array as $reason ) { ?>
                    <li class="checkbox-list__item">
                        <label class="checkbox-item checkbox-item--type--4">
                            <input class="checkbox-item__field visually-hidden" type="radio" name="unsubscribe-reason">
                            <span class="checkbox-item__box"><?php _e( $reason ); ?></span>
                        </label><!-- / .checkbox-item -->
                    </li>
                    <?php } ?>
                </ul>
                <button class="form__button button"><?php _e( $button_text ); ?></button>
            </form><!-- / .form -->
        </section><!-- / .unsubscribe -->
    </div>
</main><!-- / .site-main -->
<?php get_footer(); ?>
