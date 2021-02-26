<?php
/**
 * Template Name: Sitemap
 */

$product_tags = get_terms( 'product_tag' );
$faq = get_page_by_path( 'faq' );
$primary_menu_name = 'hamburger_sub_menu';
$learn_menu_name = 'hamburger_nav_menu';
$privacy_menu_name = 'hamburger_terms_menu';
$locations = get_nav_menu_locations();
if( $locations ) {
    if ( isset( $locations[ $primary_menu_name ] ) ) {
        $primary_menu_items = wp_get_nav_menu_items( $locations[ $primary_menu_name ] );
    }
    if ( isset( $locations[ $learn_menu_name ] ) ) {
        $learn_menu_items = wp_get_nav_menu_items( $locations[ $learn_menu_name ] );
    }
    if ( isset( $locations[ $privacy_menu_name ] ) ) {
        $privacy_menu_items = wp_get_nav_menu_items( $locations[ $privacy_menu_name ] );
    }
}
$products_meals = wc_get_products(array(
    'category' => array('meals'),
));
$allVariations = op_help()->global_cache->getAll();
$products_meals = array_filter( $allVariations, function ( $val ) {
    return $val['type'] === 'variation';
});
$products_components = wc_get_products(array(
    'category' => array('components'),
));
$products_staples = wc_get_products(array(
    'category' => array('staples'),
));
$products_vitamins = wc_get_products(array(
    'category' => array('vitamins'),
));
get_header(); ?>
<main class="site-main sitemap-main">
    <section class="sitemap">
        <div class="container">
            <div class="sitemap__box">
                <picture>
                    <source media="(max-width: 576px)"
                            srcset="<?php echo get_template_directory_uri(); ?>/assets/img/base/sitemap-mobile.webp"
                            type="image/webp">
                    <source srcset="<?php echo get_template_directory_uri(); ?>/assets/img/base/sitemap.webp"
                            type="image/webp">
                    <source media="(max-width: 576px)"
                            srcset="<?php echo get_template_directory_uri(); ?>/assets/img/base/sitemap-mobile.jpg">
                    <img class="sitemap__img"
                         src="<?php echo get_template_directory_uri(); ?>/assets/img/base/sitemap.jpg"
                         width="2304"
                         height="600"
                         alt="Life Chef – Sitemap">
                </picture>
                <p class="sitemap__subtitle"><?php _e( 'Home' ); ?></p>
                <h1 class="sitemap__title"><?php the_title(); ?></h1>
                <section class="sitemap__menu sitemap-menu">
                    <div class="sitemap-menu__header">
                        <h2 class="sitemap-menu__title"><?php _e( 'LifeChef™ General' ); ?></h2>
                    </div>
                    <ul class="sitemap-menu__sections">
                        <li>
                            <section class="sitemap-menu__section">
                                <h3 class="sitemap-menu__subtitle"><?php _e( 'On the Menu' ); ?></h3>
                                <ul class="sitemap-menu__sublist">
                                <?php
                                foreach ( $primary_menu_items as $item ) {
                                    if ( $item->menu_item_parent === '0' ) {
                                        echo '<li><a href="'.$item->url.'">'.$item->title.'</a></li>';
                                    }
                                }
                                ?>
                                </ul>
                            </section>
                        </li>
                        <li>
                            <section class="sitemap-menu__section">
                                <h3 class="sitemap-menu__subtitle"><?php _e( 'Learn' ); ?></h3>
                                <ul class="sitemap-menu__sublist">
                                    <?php
                                    foreach ( $learn_menu_items as $item ) {
                                        if ( $item->menu_item_parent === '0' ) {
                                            echo '<li><a href="'.$item->url.'">'.$item->title.'</a></li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            </section>
                        </li>
                        <li>
                            <section class="sitemap-menu__section">
                                <h3 class="sitemap-menu__subtitle"><?php _e( 'Support' ); ?></h3>
                                <ul class="sitemap-menu__sublist">
                                    <?php // TODO fix page getting logic ?>
                                    <li><a href="<?php echo $faq->guid; ?>"><?php _e( $faq->post_title ); ?></a></li>
                                    <li><a href="#"><?php _e( 'Help' ); ?></a></li>
                                    <li><a href="/contact-us"><?php _e( 'Contact Us' ); ?></a></li>
                                    <?php
                                    foreach ( $privacy_menu_items as $item ) {
                                        if ( $item->menu_item_parent === '0' ) {
                                            echo '<li><a href="'.$item->url.'">'.$item->title.'</a></li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            </section>
                        </li>
                        <li>
                            <section class="sitemap-menu__section">
                                <h3 class="sitemap-menu__subtitle"><?php _e( 'User' ); ?></h3>
                                <ul class="sitemap-menu__sublist">
                                    <li><a href="/my-account"><?php _e( 'Sign Up' ); ?></a></li>
                                    <li><a href="/my-account"><?php _e( 'Login' ); ?></a></li>
                                </ul>
                            </section>
                        </li>
                    </ul>
                </section><!-- / .sitemap-menu -->
                <section class="sitemap__menu sitemap-menu">
                    <div class="sitemap-menu__header">
                        <h2 class="sitemap-menu__title"><?php _e( 'Category' ); ?></h2>
                    </div>
                    <ul class="sitemap-menu__list">
                        <?php
                        foreach ( $product_tags as $tag ) {
                            echo '<li><a href="'.get_term_link( $tag ).'">'.__( $tag->name ).'</a></li>';
                        }
                        ?>
                    </ul>
                </section><!-- / .sitemap-menu -->
                <section class="sitemap__menu sitemap-menu sitemap-menu--accordion">
                    <div class="sitemap-menu__header">
                        <h2 class="sitemap-menu__title"><?php _e('Meals'); ?></h2>
                        <button class="sitemap-menu__more sitemap-menu__more control-button control-button--color--main-light control-button--invert" type="button">
                            <?php _e('See all'); ?>
                            <svg class="control-button__icon" width="24" height="24" fill="#34A34F">
                                <use href="#icon-angle-down-light"></use>
                            </svg>
                        </button>
                    </div>
                    <ul class="sitemap-menu__list">
                        <?php foreach ( $products_meals as $meal ) {
                            $pageUrl = op_help()->variations->rules->variationLink( $meal['var_id'] );
                            if ( $pageUrl && ! strpos( $pageUrl, '__trashed' ) ) {
                                $title = get_post_meta( $meal['var_id'], 'op_post_title', 1 );
                                echo '<li><a href="'.$pageUrl.'">'.$title.'</a></li>';
                            }
                        } ?>
                    </ul>
                </section><!-- / .sitemap-menu -->
                <section class="sitemap__menu sitemap-menu sitemap-menu--accordion">
                    <div class="sitemap-menu__header">
                        <h2 class="sitemap-menu__title"><?php _e('Components'); ?></h2>
                        <button class="sitemap-menu__more sitemap-menu__more control-button control-button--color--main-light control-button--invert" type="button">
                            <?php _e('See all'); ?>
                            <svg class="control-button__icon" width="24" height="24" fill="#34A34F">
                                <use href="#icon-angle-down-light"></use>
                            </svg>
                        </button>
                    </div>
                    <ul class="sitemap-menu__list">
                        <?php foreach ($products_components as $component) {
                            echo '<li><a href="'.$component->get_permalink().'">'.__($component->get_name()).'</a></li>';
                        }?>
                    </ul>
                </section><!-- / .sitemap-menu -->
                <section class="sitemap__menu sitemap-menu sitemap-menu--accordion">
                    <div class="sitemap-menu__header">
                        <h2 class="sitemap-menu__title"><?php _e('Staples'); ?></h2>
                        <button class="sitemap-menu__more sitemap-menu__more control-button control-button--color--main-light control-button--invert" type="button">
                            <?php _e('See all'); ?>
                            <svg class="control-button__icon" width="24" height="24" fill="#34A34F">
                                <use href="#icon-angle-down-light"></use>
                            </svg>
                        </button>
                    </div>
                    <ul class="sitemap-menu__list">
                        <?php foreach ($products_staples as $staple) {
                            echo '<li><a href="'.$staple->get_permalink().'">'.__($staple->get_name()).'</a></li>';
                        }?>
                    </ul>
                </section><!-- / .sitemap-menu -->
                <section class="sitemap__menu sitemap-menu sitemap-menu--accordion">
                    <div class="sitemap-menu__header">
                        <h2 class="sitemap-menu__title"><?php _e('Vitamins & Supplements'); ?></h2>
                        <button class="sitemap-menu__more sitemap-menu__more control-button control-button--color--main-light control-button--invert" type="button">
                            <?php _e('See all'); ?>
                            <svg class="control-button__icon" width="24" height="24" fill="#34A34F">
                                <use href="#icon-angle-down-light"></use>
                            </svg>
                        </button>
                    </div>
                    <ul class="sitemap-menu__list">
                        <?php foreach ($products_vitamins as $vitamin) {
                            echo '<li><a href="'.$vitamin->get_permalink().'">'.__($vitamin->get_name()).'</a></li>';
                        }?>
                    </ul>
                </section><!-- / .sitemap-menu -->
            </div>
        </div>
    </section><!-- / .sitemap -->
</main><!-- / .site-main .sitemap -->
<?php get_footer(); ?>
