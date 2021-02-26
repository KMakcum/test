<?php
/**
 * Template Name: Page FAQ
 */
$faq_content = op_help()->faq->get_faq_fields(get_the_ID());
get_header(); ?>
    <main class="site-main faq-main">
        <section class="faq-offer">
            <div class="container">
                <div class="faq-offer__head content">
                    <h1><?php echo $faq_content['search_title']; ?></h1>
                    <p><?php echo $faq_content['search_subtitle_text']; ?></p>
                </div>
                <form class="faq-offer__search-form search-form search-form--no-border"
                      role="search" method="get"
                      action="<?php echo esc_url(home_url('/')); ?>">
                    <p class="search-form__field-box">
                        <label class="visually-hidden" for="s">Search</label>
                        <input class="search-form__field search-field" type="search" name="s"
                               value="<?php echo get_search_query() ?>"
                               placeholder="<?php echo $faq_content['search_placeholder'] ?>">
                        <button class="search-form__button control-button control-button--no-txt search-submit"
                                type="submit"
                                id="searchsubmit">
                            <svg class="control-button__icon" width="24" height="24" fill="#252728">
                                <use href="#icon-search"></use>
                            </svg>
                        </button><!-- / .control-button -->
                    </p>
                </form><!-- / .search-form -->
            </div>
        </section><!-- / .faq-offer -->

        <section class="faq">
            <div class="container">
                <ul class="faq__categories faq-categories">
                    <?php foreach ($faq_content['faq_categories'] as $category) { ?>
                        <li class="faq-categories__item">
                            <a class="faq-categories__link"
                               href="<?php echo get_permalink(get_the_ID()) . strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', trim($category['category_name']))) ?>">
                                <img class="faq-categories__icon" src="<?php echo $category['category_image'] ?>"
                                     width="96" height="96"
                                     alt="">
                                <div class="faq-categories__txt content">
                                    <h2><?php echo $category['category_name'] ?></h2>
                                    <p><?php echo $category['category_info'] ?></p>
                                </div>
                            </a>
                        </li>
                    <?php } ?>
                </ul><!-- / .faq-categories -->
                <div class="faq__row">
                    <div class="faq__col">
                        <ul class="faq__list faq-list">
                            <?php
                            foreach (array_reverse($faq_content['faq_single_categories']) as $faq_category) { ?>
                                <li class="faq-list__item faq-item">
                                    <div class="faq-item__head">
                                        <h2 class="faq-item__title"><?php echo $faq_category['faq_category_name']; ?></h2>
                                        <a class="faq-item__more link-2"
                                           href="<?php echo get_permalink(get_the_ID()) . strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', trim($faq_category['faq_category_name']))) ?>">See
                                            all</a>
                                    </div>
                                    <ul class="faq-item__accordion accordion-extra accordion-extra--contrast">
                                        <?php foreach ($faq_category['faq_category_qa'] as $qa) { ?>
                                            <li class="accordion-extra__item">
                                                <p class="accordion-extra__header"><?php echo $qa['single_category_question'] ?></p>
                                                <div class="accordion-extra__content content content--small">
                                                    <p><?php echo $qa['single_category_answer'] ?></p>
                                                </div>
                                            </li>
                                        <?php } ?>
                                    </ul><!-- / .accordion-extra -->
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="faq__col">
                        <div class="contacts-widget">
                            <h2 class="contacts-widget__title"><?php echo $faq_content['contact_section_header'] ?></h2>
                            <ul class="contacts-widget__list contact-list contact-list--extra">
                                <?php foreach ( $faq_content['contact_section_content'] as $contact_content ) { ?>
                                    <?php
                                    $link = '';
                                    $class = 'contact-list__item--chat';
                                    $link_class = '';
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
                            <div class="contacts-widget__additional-info content">
                                <p><?php echo $faq_content['faq_sidebar_data']['faq_page_sidebar_phone_title']; ?>
                                    <a href="tel:<?php echo $faq_content['faq_sidebar_data']['faq_contact_page_sidebar_phone']; ?>"><?php echo $faq_content['faq_sidebar_data']['faq_contact_page_sidebar_phone']; ?></a>
                                </p>
                                <p><?php echo $faq_content['faq_sidebar_data']['faq_contact_page_work_time']; ?> </p>
                                <div class="contacts-widget__address">
                                    <p><?php echo $faq_content['faq_sidebar_data']['faq_contact_page_address']; ?></p>
                                </div>
                            </div>
                        </div><!-- / .contacts-widget -->
                    </div>
                </div>
            </div>
        </section><!-- / .faq -->
        <section class="submit-question">
            <div class="container">
                <div class="submit-question__box">
                    <h2 class="submit-question__title"><?php echo $faq_content['question_form_title'] ?></h2>
                    <a class="submit-question__button button btn-modal"
                       href="#js-modal-submit-question"><?php echo $faq_content['question_button_text'] ?></a>
                </div>
            </div>
        </section><!-- / .submit-question -->
    </main><!-- / .site-main .faq-main -->

<?php get_template_part('template-parts/modals/submit-question', '', ['faq-content' => $faq_content]) ?>

<?php
get_footer();
