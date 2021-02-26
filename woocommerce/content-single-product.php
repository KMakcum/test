<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $product, $variation, $include_groceries, $cached_product;


$variation = op_help()->variations->rules->getCurrentVariation();
$is_variation = !empty($variation);
$this_object = function () use ($is_variation, $product, $variation) {

    if ($is_variation) {
        return $variation;
    }

    return $product;
};

$cached_product = op_help()->global_cache->get_cached_product( $this_object()->get_id() );

$prod_terms = wp_get_post_terms($this_object()->get_id(), 'product_cat');
$include_component_template = false;
$include_vitamins = false;
$include_groceries = false;
$product_tags = [
    'Everyday Essentials'    => 'everyday-essentials',
    'Meats And Cheese'       => 'meats-and-cheese',
    'Dairy And Alternatives' => 'dairy-and-alternatives',
    'Snacks And Cookies'     => 'snacks-and-cookies',
    'Tea And Coffee'         => 'tea-and-coffee',
    'Water And Beverages'    => 'water-and-beverages',
];

add_filter( 'kama_breadcrumbs_filter_elements', function( $elms, $class, $ptype ) use ($product, $product_tags, $prod_terms) {
    $tags    = $product->get_tag_ids();
    $tag_id  = $tags[ array_key_last( $tags ) ];
    $tag_obj = get_term_by( 'id', $tag_id, 'product_tag' );
    if ($prod_terms[0]->slug === 'vitamins') {
        $elms['home'] = [
            $class->makelink('/offerings', 'Offerings'),
            $class->makelink('/product-category/'.$prod_terms[0]->slug, $prod_terms[0]->name),
        ];
        unset($elms['single']);

    } elseif ($prod_terms[0]->slug === 'staples') {
        if (in_array($tag_obj->slug, $product_tags)) {
            $elms['home'] = [
                $class->makelink('/offerings', 'Offerings'),
                $class->makelink('/groceries', 'Groceries'),
                $class->makelink('/product-tag/'.$tag_obj->slug, $tag_obj->name),
            ];
            unset($elms['single']);
        }
    } elseif (empty($prod_terms[0]->slug)) {
        $elms['home'] = [
            $class->makelink('/offerings', 'Offerings'),
            $class->makelink('/product-category/meals', 'Meals'),
        ];
        unset($elms['single']);
    }
    return $elms;
}, 10, 3 );

foreach ( $prod_terms as $prod_term ) {
    if ( $prod_term->slug === 'components' ) {
        $include_component_template = true;
        break;
    }
    if ( $prod_term->slug === 'vitamins' ) {
        $vitamins_data = get_post_meta($this_object()->get_id());
        $all_badges = carbon_get_theme_option( 'op_variations_badges' );
        $badges = unserialize($vitamins_data['op_simple_badges'][0]);
        $img_badge = $does_not_contain_badge = [];
        foreach($all_badges as $key => $badge) {
            if ( in_array($badge['slug'], $badges) ) {
                $img_badge[$badge['slug']] = $badge['icon_contains'];
            }
        }
        $key_ingredients = explode('|', $vitamins_data['op_key_ingredients'][0]);
        $list_frequency = carbon_get_term_meta( $this_object()->get_id(), 'op_categories_subscription_frequency' );
        $list_frequency_all = carbon_get_term_meta($prod_terms[0]->term_id,'op_categories_subscription_frequency');
        $other_ingridients = $vitamins_data['op_other_ingredients'][0];
        $does_not_contain = unserialize($vitamins_data['op_simple_badges_not_contain'][0]);
        $taking_pills = explode('|', $vitamins_data['op_taking_pills'][0]);
        foreach($all_badges as $key => $badge) {
            if ( in_array($badge['slug'], $does_not_contain) ) {
                $does_not_contain_badge[$badge['slug']] = $badge['icon_contains'];
            }
        }
        $warnings = $vitamins_data['op_warnings'][0];
        $instructions_content = explode('|', $vitamins_data['op_instructions'][0]);
        $include_vitamins = true;
        break;
    }
    if ( $prod_term->slug === 'staples' || $prod_term->slug === 'groceries' ) {
        $offerins_data = get_post_meta( $this_object()->get_id() );
        $all_badges = carbon_get_theme_option( 'op_variations_badges' );
        $badges = unserialize( $offerins_data['op_simple_badges'][0] );
        if (! empty( $badges )) {
	        $img_badge = $does_not_contain_badge = [];
	        foreach( $all_badges as $key => $badge ) {
		        if ( in_array( $badge['slug'], $badges ) ) {
			        $img_badge[ $badge['slug'] ] = $badge['icon_contains'];
		        }
	        }
        }
	    $list_frequency = carbon_get_term_meta( $this_object()->get_id(), 'op_categories_subscription_frequency' );
	    $list_frequency_all = carbon_get_term_meta($prod_terms[0]->term_id,'op_categories_subscription_frequency');

        $include_groceries = true;
        break;
    }
}
if ($include_component_template) {
    require_once get_stylesheet_directory() . '/template-single-component.php';
} else {
    $nutrition_image = $cached_product['op_variation_nutrition_image_url'];
    $product_cat_url = '';
    $terms = get_the_terms($product->get_id(), 'product_cat');
    foreach ($terms as $term) {
        $product_cat_url = get_term_link($term);
        break;
    }
    $faq_content = op_help()->faq->get_faq_fields(get_page_by_path('faq')->ID);
    /**
     * Hook: woocommerce_before_single_product.
     *
     * @hooked woocommerce_output_all_notices - 10
     */
    do_action('woocommerce_before_single_product');
    if (post_password_required()) {
        echo get_the_password_form(); // WPCS: XSS ok.
        return;
    }

    ?>
    <main id="product-<?php the_ID(); ?>" <?php wc_product_class('site-main product-main catalog-loader', $product); ?>>
        <div class="loader-container">
            <div class="loader-image"></div>
            <div class="loader-text"><?php echo carbon_get_theme_option( 'op_loaders_catalog_text' ); ?></div>
        </div>
        <div class="breadcrumbs-box">
            <div class="container">
                <?php do_action('echo_kama_breadcrumbs'); ?>
            </div>
        </div>
        <section class="product-card">
            <div class="container">
                <header class="product-card__header">
                    <div class="header-product-card__top">
                        <a class="header-product-card__back" href="<?php echo $product_cat_url; ?>">
                            <svg width="24" height="24" fill="#252728">
                                <use href="#icon-arrow-left"></use>
                            </svg>
                        </a>
                        <h1 class="header-product-card__name"><?php echo ( $cached_product['op_post_title'] ) ? $cached_product['op_post_title'] : $cached_product['post_title']; ?></h1>
                        <a class="header-product-card__share" href="#">
                            <svg width="24" height="24" fill="#252728">
                                <use href="#icon-share"></use>
                            </svg>
                        </a>
                    </div>
                    <div class="header-product-card__bottom">
                        <?php
                        /**
                         * Hook: woocommerce_after_shop_loop_item_title.
                         *
                         * @hooked woocommerce_template_loop_rating - 5
                         * @hooked woocommerce_template_loop_price - 10
                         */
                        remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
                        do_action('woocommerce_after_shop_loop_item_title');
                        ?>
                    </div>
                </header>
                <div class="product-card__body">
                    <?php wc_get_template_part('single-product/gallery'); ?>
                    <div class="product-card__right">
                        <div class="product-card__details product-details">
                            <div class="product-details__body">
                                <?php
                                if ( $include_vitamins ) { ?>
                                    <div class="product-details__labels product-labels">
                                        <ul class="product-labels__badges badges">
                                            <?php if ( ! empty( $img_badge ) ) {
                                                foreach($img_badge as $key => $badge) { ?>
                                                    <li class="badges__item" data-tippy-content="<?php echo $key ?>">
                                                        <?php
                                                        echo file_get_contents( get_attached_file( $badge, 'full' ) );
                                                        ?>
                                                    </li>
                                                <?php }
                                            } ?>
                                        </ul><!-- / .badges -->
                                    </div>
                                    <section class="product-details__section info-box info-box--small content content--small">
                                        <?php echo do_shortcode( $cached_product['post_content'] ); ?>
                                    </section><!-- / .info-box -->
                                    <section class="product-details__section terms-section">
                                        <h2 class="terms-section__title"><?php _e('Key Ingredients'); ?></h2>
                                        <ul class="terms-section__list terms-list">
                                            <?php if (!empty($key_ingredients)) {
                                                foreach($key_ingredients as $ingredient) {
                                                    $exploded = explode(',', $ingredient)?>
                                                    <li class="terms-list__item">
                                                        <p class="terms-list__term"><?php echo $exploded[0]; ?></p>
                                                        <p class="terms-list__value"><?php echo $exploded[1]; ?></p>
                                                    </li>
                                                <?php }
                                            } ?>
                                        </ul>
                                    </section>
                                    <section class="product-details__section options-section">
                                        <h2 class="options-section__title"><?php _e('Deliver every'); ?></h2>
                                        <div class="options-section__row">
                                            <label class="options-section__select select select--big select--rounded-corners">

                                                <select class="select__field">
                                                    <?php
                                                    if (! empty($list_frequency_all)) {
                                                        foreach ( $list_frequency_all as $frequency ) {
                                                            $selected = '';
                                                            if ( $frequency == $list_frequency[0] ) {
                                                                $selected = 'selected';
                                                            }
                                                            echo '<option value="'.esc_attr( $frequency ).'" '.esc_attr($selected).'>'.ucfirst(esc_html( str_replace( '_', ' ', $frequency ) ) ).'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </label><!-- / .select -->
                                            <div class="message message--inline message--color--main-light">
                                                <svg class="message__icon" width="20" height="20" fill="#34A34F">
                                                    <use href="#icon-info"></use>
                                                </svg>
                                                <p class="message__txt"><?php _e('You can edit, pause or cancel anytime.'); ?></p>
                                            </div><!-- / .message -->
                                        </div>
                                    </section><!-- / .options-section -->
                                <?php } elseif ( $include_groceries ) { ?>
                                    <div class="product-details__labels product-labels">
                                        <ul class="product-labels__badges badges">
                                            <?php if ( ! empty( $img_badge ) ) {
                                                foreach($img_badge as $key => $badge) { ?>
                                                    <li class="badges__item" data-tippy-content="<?php echo $key ?>">
                                                        <?php
                                                        echo file_get_contents( get_attached_file( $badge, 'full' ) );
                                                        ?>
                                                    </li>
                                                <?php }
                                            } ?>
                                        </ul><!-- / .badges -->
                                        <?php if ($survey_default && $success_survey && $count_hats < $max_hats) { ?>
                                            <div class="product-item__rating-extra rating-extra js-rating--readonly--true"
                                                 data-rate-value="<?php echo $count_hats; ?>">
                                            </div>
                                        <?php } elseif ($survey_default && $success_survey) { ?>
                                            <p class="product-item__label label label--best">
                                                <svg class="label__icon" width="16" height="16"
                                                     fill="#34A34F">
                                                    <use href="#icon-cap"></use>
                                                </svg>
                                                <?php echo __('Best for you'); ?>
                                            </p>
                                        <?php } ?>
                                    </div><!-- / .product-labels -->
                                    <section class="product-details__section info-box info-box--small content content--small">
                                        <?php the_content(); ?>
                                    </section><!-- / .info-box -->
                                    <section class="product-details__section nutrition">
                                        <div class="nutrition__wr">
                                            <?php wc_get_template_part('single-product/nutrition'); ?>
                                        </div>
                                        <a class="nutrition__full-info link" href="<?php echo get_template_directory_uri(); ?>/assets/img/base/nutrition-facts-1.png" data-fancybox="full-nutrition-info"><?php _e('View full nutrition info'); ?></a>
                                        <a class="d-none" href="<?php echo get_template_directory_uri(); ?>/assets/img/base/nutrition-facts-2.png" data-fancybox="full-nutrition-info">&nbsp;</a>
                                        <a class="d-none" href="<?php echo get_template_directory_uri(); ?>/assets/img/base/nutrition-facts-3.jpg" data-fancybox="full-nutrition-info">&nbsp;</a>
                                    </section><!-- / .nutrition -->
                                    <section class="product-details__section options-section">
                                        <h2 class="options-section__title"><?php _e('Deliver every'); ?></h2>
                                        <div class="options-section__row">
                                            <label class="options-section__select select select--big select--rounded-corners">
                                                <select class="select__field">
                                                    <?php
                                                    if (! empty($list_frequency_all)) {
                                                        foreach ( $list_frequency_all as $frequency ) {
                                                            $selected = '';
                                                            if ( $frequency == $list_frequency[0] ) {
                                                                $selected = 'selected';
                                                            }
                                                            echo '<option value="'.esc_attr( $frequency ).'" '.esc_attr($selected).'>'.ucfirst(esc_html( str_replace( '_', ' ', $frequency ) ) ).'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </label><!-- / .select -->
                                            <div class="message message--inline message--color--main-light">
                                                <svg class="message__icon" width="20" height="20" fill="#34A34F">
                                                    <use href="#icon-info"></use>
                                                </svg>
                                                <p class="message__txt"><?php _e('You can edit, pause or cancel anytime.'); ?></p>
                                            </div><!-- / .message -->
                                        </div>
                                    </section><!-- / .options-section -->
                                <?php } else { ?>
                                    <?php wc_get_template_part('single-product/customize'); ?>
                                    <section class="product-details__section nutrition">
                                        <div class="nutrition__wr">
                                            <?php wc_get_template_part('single-product/description'); ?>
                                            <?php wc_get_template_part('single-product/nutrition'); ?>
                                            <?php wc_get_template_part('single-product/badges'); ?>
                                        </div>
                                        <?php if (!empty($nutrition_image)) { ?>
                                            <a class="nutrition__full-info link" href="<?php echo $nutrition_image; ?>" data-fancybox="full-nutrition-info">View full nutrition info</a>
                                        <?php } ?>
                                    </section>
                                <?php } ?>
                            </div>
                            <div class="product-details__footer footer-product-details">
                                <?php wc_get_template_part('single-product/price'); ?>
                                <?php wc_get_template_part('single-product/add-to-cart'); ?>
                            </div>
                        </div>
                        <?php wc_get_template_part('single-product/social-links'); ?>
                    </div>
                    <div class="product-card__other">
                    <?php if ( $include_vitamins ) { ?>
                        <section class="product-card__section info-box content content--small">
                            <h2><?php echo ( $cached_product['op_post_title'] ) ? $cached_product['op_post_title'] : $cached_product['post_title']; ?></h2>
                            <?php the_excerpt(); ?>
                            <p><strong><?php _e( 'Other Ingredients: ' ); ?><?php echo $other_ingridients;?></strong></p>
                        </section>
                        <section class="product-card__section substances-section">
                            <h2 class="substances-section__title"><?php echo ( $cached_product['op_post_title'] ) ? $cached_product['op_post_title'] : $cached_product['post_title']; _e(' Does Not Contain'); ?></h2>
                            <ul class="substances-section__list substances-list substances-list--columns--3">
                                <?php
                                foreach ($does_not_contain_badge as $key => $item) { ?>
                                    <li class="substances-list__item substance">
                                        <img class="substance__icon" src="<?php echo wp_get_attachment_image_url( $item, 'full' ); ?> " alt="icon">
                                        <p class="substance__txt"><?php echo $key; ?></p>
                                    </li><!-- / .substance -->
                                <?php } ?>
                            </ul><!-- / .nutrition-list -->
                        </section><!-- / .substances-section -->
                        <?php if ( ! empty( $warnings ) ) { ?>
                            <section class="product-card__section info-box content content--small">
                                <h2><?php _e('Warnings'); ?></h2>
                                <p><?php echo $warnings; ?></p>
                            </section><!-- / .info-box -->
                        <?php } ?>
<!--                        <?php /*if ( ! empty( $taking_pills ) ) { */?>
                            <section class="product-card__section instruction">
                                <div class="instruction__head">
                                    <h2 class="instruction__title"><?php /*_e( 'Instructions' ); */?></h2>
                                    <ul class="instruction__meal-overview meal-overview">
                                        <?php /*foreach ( $taking_pills as $pill ) { */?>
                                            <li class="meal-overview__item" data-tippy-content="<?php /*echo $pill; */?>">
                                                <svg class="meal-overview__icon" width="16" height="16" fill="#252728">
                                                    <use href="#icon-vitamins"></use>
                                                </svg>
                                                <?php /*echo $pill; */?>
                                            </li>
                                        <?php /*} */?>
                                    </ul>
                                </div>
                                <div class="instruction__body content">
                                    <p>
                                        <?php /*echo $instructions_content[0]; */?>
                                    </p>
                                    <ol class="instruction__numbered-list numbered-list">
                                        <?php /*for ($i = 1; $i < count($instructions_content); $i++) { */?>
                                            <li class="numbered-list__item">
                                                <span class="numbered-list__number"><?php /*echo $i; */?></span>
                                                <div class="numbered-list__txt content">
                                                    <p>
                                                        <?php /*echo $instructions_content[$i]; */?>
                                                    </p>
                                                </div>
                                            </li>
                                        <?php /*} */?>
                                    </ol>
                                </div>
                            </section>
                        --><?php /*} */?>
        <?php //wc_get_template_part('single-product/q-and-a'); ?>
        <?php //wc_get_template_part('single-product/zendesk-q-and-a') ?>
        <?php } elseif ( $include_groceries ) { ?>
            <section class="product-card__section info-box content content--small">
                <h2><?php the_title(); ?></h2>
                <?php the_excerpt(); ?>
            </section>
            <?php wc_get_template_part('single-product/q-and-a'); ?>
            <?php wc_get_template_part('single-product/zendesk-q-and-a') ?>
        <?php } else { ?>
            <?php wc_get_template_part('single-product/content'); ?>
            <?php wc_get_template_part('single-product/instruction'); ?>
            <!--                        --><?php //wc_get_template_part('single-product/feedback-1'); ?>
            <?php wc_get_template_part('single-product/q-and-a'); ?>
            <?php wc_get_template_part('single-product/zendesk-q-and-a') ?>
            <?php wc_get_template_part('single-product/feedback-2'); ?>
        <?php } ?>
        </div>

        </div>
        </div>
        </section>
        <?php wc_get_template_part('single-product/related-products'); ?>
        <?php if ( $include_groceries ) {
            wc_get_template_part('single-product/recommended-meals');
        } ?>
        <?php //wc_get_template_part('single-product/essential-staples'); ?>
        <?php get_template_part('template-parts/modals/meals-filled', '', []); ?>
        <?php get_template_part('template-parts/modals/submit-question', '', ['faq-content' => $faq_content]) ?>
    </main>
<?php } ?>
