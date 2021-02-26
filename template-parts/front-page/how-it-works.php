<?php 
list( $hiw_title, $hiw_items, $hiw_redirect ) = op_help()->mainpage->get_block_hiw( get_the_ID() );

$user_logged = is_user_logged_in();

if ( $user_logged ) {
    $current_user = wp_get_current_user();
    $zip_code     = trim( get_user_meta( $current_user->ID, 'sf_zipcode', true ) );
} else {
    $zip_code = op_help()->sf_user::op_get_zip_cookie();
}
$link_href = empty( $zip_code ) ? '#js-modal-zip-code' : '#js-modal-sign-up';
?>

<section class="how-it-works">
    <div class="container">
        <?php if ( ! empty( $hiw_title ) ) { ?>
            <h2 class="how-it-works__title"><?php echo esc_html( $hiw_title ); ?></h2>
        <?php } ?>

        <ul class="how-it-works__list work-stages">

            <?php foreach ( $hiw_items as $item ) : ?>

                <li class="work-stages__item content">

                    <?php if ( $item['image'] ) :
                        $icon = wp_get_attachment_image_url( $item['image'], 'thumbnail' );
                        ?>
                        <img class="work-stages__img" 
                             src="<?php echo esc_url( $icon ); ?>" 
                             width="96" 
                             height="96" 
                             alt="<?php echo esc_attr( $item['title'] ); ?>">
                    <?php endif; ?>

                    <div class="work-stages__txt content">
                        <h3><?php echo esc_html( $item['title'] ); ?></h3>
                        <p><?php echo esc_html( $item['descr'] ); ?></p>
                    </div>
                </li>

            <?php endforeach; ?>
        </ul>
        <?php if ( ! is_user_logged_in() || empty( $zip_code ) ) { ?>
            <a class="how-it-works__button button button--medium btn-modal show-signup" data-redirect="<?php echo $hiw_redirect; ?>" href="<?php echo $link_href; ?>">Let's Go!</a>
        <?php  } else { ?>
            <a class="how-it-works__button button button--medium" href="<?php echo $hiw_redirect; ?>">Let's Go!</a>
        <?php } ?>
    </div>
</section>