<?php
/**
 * Template Name: Single component template
 */
$component_content = op_help()->single_component->get_single_component_fields( $this_object()->get_slug() );

$gallery_ids                = $component_content['op_variations_component__gallery'];
$ingredients                = $component_content['op_variations_component_ingredients'];
$allergens                  = $component_content['op_variations_component_allergens'];
$badges                     = $component_content['op_variations_component_badges'];
$description                = $component_content['op_variations_component_description'];
$nutrition_data             =
    [
        'calories'      => $component_content['op_variations_component_calories'],
        'fats'          => $component_content['op_variations_component_fats'],
        'proteins'      => $component_content['op_variations_component_proteins'],
        'carbohydrates' => $component_content['op_variations_component_carbohydrates']
    ];
$nutrition_image            = $component_content['op_variations_component_nutrition_img'];
$cooking_steps              = $component_content['op_variations_component_cooking_steps'];
$cooking_steps_header       = $component_content['op_variations_component_steps_header'];
$cooking_steps_instructions = explode( '|', $component_content['op_variations_component_instructions'] );

$faq_content = op_help()->faq->get_faq_fields( get_page_by_path( 'faq' )->ID );


?>
<main id="product-<?php the_ID(); ?>" <?php wc_product_class( 'site-main product-main', $product ); ?>>
    <div class="breadcrumbs-box">
        <!--<div class="container">
            <?php //do_action('echo_kama_breadcrumbs') ?>
        </div> -->
    </div>
    <section class="product-card">
        <div class="container">
            <header class="product-card__header">
                <div class="product-card__header-top">
                    <h1 class="product-card__name"><?php echo get_the_title( $this_object()->get_id() ); ?></h1>
                </div>
                <?php
                /**
                 * Hook: woocommerce_after_shop_loop_item_title.
                 *
                 * @hooked woocommerce_template_loop_rating - 5
                 * @hooked woocommerce_template_loop_price - 10
                 */
                remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
                do_action( 'woocommerce_after_shop_loop_item_title' );
                ?>
            </header>
            <div class="product-card__body">
                <?php get_template_part( 'template-parts/single-component/gallery', '', [ 'gallery_ids' => $gallery_ids ] ); ?>
                <div class="product-card__right">
                    <div class="product-card__details product-details">
                        <?php get_template_part( 'template-parts/single-component/subs-notify', '', [] ) ?>
                        <div class="product-details__body">
                            <?php get_template_part( 'template-parts/single-component/badges', '', [ 'badges' => $badges ] ); ?>
                            <?php get_template_part( 'template-parts/single-component/description', '', [ 'description' => $description ] ); ?>
                            <section class="product-details__section nutrition">
                                <div class="nutrition__wr">
                                    <?php get_template_part( 'template-parts/single-component/nutrition', '', [ 'nutrition_data' => $nutrition_data ] ); ?>
                                </div>
                                <?php if ( ! empty( $nutrition_image ) ) { ?>
                                    <a class="nutrition__full-info link"
                                       href="<?php echo wp_get_attachment_image_url( $nutrition_image, 'large' ); ?>"
                                       data-fancybox="full-nutrition-info"><?php _e( 'View full nutrition info' ); ?></a>
                                <?php } ?>
                            </section>
                        </div>
                        <div class="product-details__footer footer-product-details">
                            <a class="footer-product-details__button button"
                               href="<?php echo get_site_url() . '/offerings' ?>"><?php echo __( 'Explore Offerings' ) ?></a>
                        </div><!-- / .footer-product-details -->
                    </div>
                    <?php wc_get_template_part( 'single-product/social-links' ); ?>
                    <?php get_template_part( 'template-parts/single-component/help-section', '', [] ) ?>
                </div>

                <div class="product-card__other">
                    <?php get_template_part( 'template-parts/single-component/ingredients', '',
                        [
                            'ingredients' => $ingredients,
                            'allergens'   => $allergens
                        ] ); ?>
                    <?php
                    if ( ! empty( $cooking_steps ) && ! empty( $cooking_steps_header ) && ! empty( $cooking_steps_instructions ) ) {
                        get_template_part( 'template-parts/single-component/instructions', '',
                            [
                                'cooking_steps'              => $cooking_steps,
                                'cooking_steps_header'       => $cooking_steps_header,
                                'cooking_steps_instructions' => $cooking_steps_instructions
                            ] );
                    }
                    ?>
                    <?php wc_get_template_part( 'single-product/zendesk-q-and-a' ) ?>
                </div>
            </div>
        </div>
    </section>
    <!--    --><?php //get_template_part('template-parts/single-component/products-with-component', '',
    //        [
    //            'products' => op_help()->single_component->similar_meals_content($this_object()->get_slug()),
    //            'product_name' => $this_object()->get_name()
    //        ]) ?>
    <div class="modal-submit-question modal-common" id="js-modal-submit-question" style="display: none">
        <div class="modal-common__data data">
            <header class="data__header content">
                <h3>Submit a question</h3>
            </header>
            <form class="data__form form" action="#" method="post">
                <ul class="form__list fields-list">
                    <li class="fields-list__item field-box">
                        <input class="field-box__field" id="modal-submit-question-name" type="text" name="user-name"
                               required>
                        <label class="field-box__label"
                               for="modal-submit-question-name"><?php echo __( 'Your name' ) ?></label>
                    </li><!-- / .field-box -->
                    <li class="fields-list__item field-box">
                        <input class="field-box__field" id="modal-submit-question-email" type="email" name="user-email"
                               required>
                        <label class="field-box__label"
                               for="modal-submit-question-email"><?php echo __( 'Your email' ) ?></label>
                    </li><!-- / .field-box -->
                    <li class="fields-list__item field-box">
                        <input class="field-box__field" id="modal-submit-question-phone" type="tel" name="user-phone"
                               required>
                        <label class="field-box__label"
                               for="modal-submit-question-phone"><?php echo __( 'Phone number' ) ?></label>
                    </li><!-- / .field-box -->
                    <li class="fields-list__item field-box">
                        <input class="field-box__field" id="modal-submit-question-order-number" type="tel"
                               name="user-order-number">
                        <label class="field-box__label" for="modal-submit-question-order-number"><?php echo __( 'Order number
                            (optional)' ) ?></label>
                    </li><!-- / .field-box -->
                    <li class="fields-list__item field-box">
                        <select class="field-box__field field-box__field--select field-box__field--entered"
                                id="modal-submit-question-category" name="question-category">
                            <?php
                            foreach ( $faq_content['faq_single_categories'] as $faq_category ) { ?>
                                <option><?php echo $faq_category['faq_category_name'] ?></option>
                            <?php } ?>
                        </select>
                        <label class="field-box__label"
                               for="modal-submit-question-category"><?php echo __( 'Category' ) ?></label>
                        <svg class="field-box__select-icon" width="24" height="24" fill="#252728">
                            <use href="#icon-angle-down-light"></use>
                        </svg>
                    </li><!-- / .field-box -->
                    <li class="fields-list__item field-box">
                        <textarea class="field-box__field field-box__field--textarea js-auto-size"
                                  id="modal-submit-question-message" name="user-message" required></textarea>
                        <label class="field-box__label"
                               for="modal-submit-question-message"><?php echo __( 'Message' ) ?></label>
                    </li><!-- / .field-box -->
                    <li class="fields-list__item attachments-box">
                        <div class="attachments">
                            <p class="attachments-box__label"><?php echo __( 'Attachments <span>(optional)</span>' ) ?></p>
                            <div class="attachments">
                                <div method="post" action="#" enctype="multipart/form-data"
                                     id="user-files-dropzone"
                                     class="attachments__dropzone dropzone"
                                     style="min-height:0">
                                    <input id="user-files" type="file" name="file" multiple style="display: none;">
                                    <label class="attachments__title dz-message"><?php echo __( '<span>Add file</span> or drop files
                                        here' ) ?></label>
                                </div>
                                <div class="attachments__files files"></div><!-- / .files -->
                            </div><!-- / .attachments -->
                    </li><!-- / .attachments-box -->
                </ul><!-- / .fields-list -->
                <button class="form__button button" type="submit"><?php echo __( 'Submit a question' ) ?></button>
            </form><!-- / .form -->
        </div><!-- / .data -->
    </div><!-- / .modal-submit-question -->
    <!-- Modal modal-faq-thank-you -->
    <div class="modal-faq-thank-you modal-common" id="js-modal-faq-thank-you" style="display: none">
        <div class="modal-common__status-page status-page">
            <svg class="status-page__icon" width="48" height="48" fill="#34A34F">
                <use href="#icon-check-circle-stroke"></use>
            </svg>
            <div class="status-page__txt content">
                <h1><?php echo __( 'Thank You!' ); ?></h1>
                <p><?php echo __( 'We’ve received your question. Our manager will contact you soon regarding this matter.' ) ?></p>
            </div>
            <button class="status-page__button button" type="button"
                    data-fancybox-close><?php echo __( 'Back to FAQ' ); ?></button>
        </div><!-- / .status-page -->
    </div><!-- / .modal-faq-thank-you -->
</main>
