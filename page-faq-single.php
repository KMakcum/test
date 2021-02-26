<?php
/* Template Name: Faq Single Page */
$faq_cat_qa = carbon_get_post_meta(get_the_ID(), 'faq_category_qa');
$parent_meta = op_help()->faq->get_parent_faq_fields(wp_get_post_parent_id(get_the_ID()));
get_header();
?>
    <main class="site-main faq-main">
        <section class="faq-single-offer">
            <div class="container">
                <a class="faq-single-offer__back control-button control-button--color--white"
                   href=" <?php echo get_site_url() . '/faq' ?>">
                    <svg class="control-button__icon" width="24" height="24" fill="#fff">
                        <use href="#icon-arrow-left"></use>
                    </svg>
                    Back
                </a>
                <h1 class="faq-single-offer__title"><?php echo get_the_title(get_the_ID()); ?></h1>
            </div>
        </section><!-- / .faq-single-offer -->

        <section class="faq">
            <div class="container">
                <div class="faq__row">
                    <div class="faq__col">
                        <ul class="faq__accordion accordion-extra accordion-extra--contrast">
                            <?php foreach ($faq_cat_qa as $qa) { ?>
                                <li class="accordion-extra__item">
                                    <p class="accordion-extra__header"><?php echo $qa['single_category_question'] ?></p>
                                    <div class="accordion-extra__content content content--small">
                                        <p><?php echo $qa['single_category_answer'] ?></p>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul><!-- / .accordion-extra -->
                    </div>
                    <div class="faq__col">
                        <div class="contacts-widget">
                            <h2 class="contacts-widget__title"><?php echo $parent_meta['contact_section_header'] ?></h2>
                            <ul class="contacts-widget__list contact-list contact-list--extra">
                                <?php foreach ($parent_meta['contact_section_content'] as $contact_content) { ?>
                                    <?php
                                    $link = '';
                                    $class = 'contact-list__item--chat';
                                    $field_name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $contact_content['contact_us_item_title']);
                                    switch ( strtolower( $field_name ) ) {
                                        case 'have a question':
                                            $link = '#js-modal-submit-question';
                                            $class = 'contact-list__item--question';
                                            $link_class = 'btn-modal';
                                            break;
                                        case 'email':
                                            $link = 'mailto:' . $contact_content['contact_us_item_text'];
                                            $class = 'contact-list__item--email';
                                            break;
                                        case 'chat now':
                                            $link = '##';
                                            $data_attr = 'chat';
                                    }
                                    ?>
                                    <li class="contact-list__item <?php echo $class; ?>">
                                        <p class="contact-list__title"><?php echo $contact_content['contact_us_item_title'] ?></p>
                                        <a class="contact-list__link link <?php echo ! empty( $link_class ) ? $link_class : ''; ?>"
                                           href="<?php echo $link ?>"
                                            <?php
                                            if (strtolower($contact_content['contact_us_item_title']) == 'chat now') {
                                                ?>
                                                data-<?php echo $data_attr ?>
                                                <?php
                                            }
                                            ?>
                                        >
                                            <?php echo $contact_content['contact_us_item_text'] ?></a>
                                    </li>
                                <?php } ?>
                            </ul><!-- / .contact-list -->
                        </div><!-- / .contacts-widget -->
                    </div>
                </div>
            </div>
        </section><!-- / .faq -->
    </main><!-- / .site-main .faq-main -->
<?php get_template_part('template-parts/modals/submit-question', '', ['faq-content' => $contact_content]) ?>
<?php
get_footer();
