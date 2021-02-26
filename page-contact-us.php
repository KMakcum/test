<?php
/**
 * Template Name: Page Contact us
 */
get_header();
$page_description         = carbon_get_theme_option( 'op_contact_page_description' );
$page_form_heading        = carbon_get_theme_option( 'op_contact_page_form_heading' );
$page_form_description    = carbon_get_theme_option( 'op_contact_page_form_description' );
$page_sidebar_title       = carbon_get_theme_option( 'op_contact_page_sidebar_title' );
$page_sidebar_mail        = carbon_get_theme_option( 'op_contact_page_sidebar_mail' );
$page_sidebar_phone_title = carbon_get_theme_option( 'op_contact_page_sidebar_phone_title' );
$page_sidebar_phone       = carbon_get_theme_option( 'op_contact_page_sidebar_phone' );
$page_sidebar_work_time   = carbon_get_theme_option( 'op_contact_page_work_time' );
$page_sidebar_address   = carbon_get_theme_option( 'op_contact_page_address' );
?>
<main class="site-main contacts-main">
    <div class="contacts-offer">
        <div class="container">
            <h1><?php the_title(); ?></h1>
            <p><?php echo $page_description; ?></p>
        </div>
    </div>
    <section class="contacts">
        <div class="container">
            <div class="contacts__row">
                <div class="contacts__col">
                    <div class="contacts__main">
                        <h2><?php echo $page_form_heading; ?></h2>
                        <p><?php echo $page_form_description; ?></p>
                    </div>
                    <form class="contacts__form form" action="#" method="post">
                        <ul class="form__list fields-list">
                            <li class="fields-list__item field-box">
                                <input class="field-box__field" id="modal-submit-question-name" type="text" name="user-name" required>
                                <label class="field-box__label" for="modal-submit-question-name"><?php _e( 'Your name' ); ?></label>
                            </li><!-- / .field-box -->
                            <li class="fields-list__item field-box">
                                <input class="field-box__field" id="modal-submit-question-email" type="email" name="user-email" required>
                                <label class="field-box__label" for="modal-submit-question-email"><?php _e( 'Your email' ); ?></label>
                            </li><!-- / .field-box -->
                            <li class="fields-list__item field-box">
                                <input class="field-box__field" id="modal-submit-question-phone" type="tel" name="user-phone" required>
                                <label class="field-box__label" for="modal-submit-question-phone"><?php _e( 'Phone number' ); ?></label>
                            </li><!-- / .field-box -->
                            <li class="fields-list__item field-box">
                                <input class="field-box__field" id="modal-submit-question-order-number" type="tel" name="user-order-number">
                                <label class="field-box__label" for="modal-submit-question-order-number"><?php _e( 'Order number (optional)' ); ?></label>
                            </li><!-- / .field-box -->
                            <li class="fields-list__item field-box">
                                <select class="field-box__field field-box__field--select field-box__field--entered" id="modal-submit-question-category" name="question-category">
                                    <option><?php _e( 'About LifeChef™' ); ?></option>
                                    <option><?php _e( 'Meals & Pricing' ); ?></option>
                                    <option><?php _e( 'Dietary and Nutrition' ); ?></option>
                                    <option><?php _e( 'Packaging & Recycling' ); ?></option>
                                    <option><?php _e( 'Managing my Subscription' ); ?></option>
                                    <option><?php _e( 'Payment/Promotions' ); ?></option>
                                    <option><?php _e( 'Delivery & Shipping' ); ?></option>
                                    <option><?php _e( 'Get In Touch' ); ?></option>
                                </select>
                                <label class="field-box__label" for="modal-submit-question-category"><?php _e( 'Category' ); ?></label>
                                <svg class="field-box__select-icon" width="24" height="24" fill="#252728">
                                    <use href="#icon-angle-down-light"></use>
                                </svg>
                            </li><!-- / .field-box -->
                            <li class="fields-list__item field-box">
                                <textarea class="field-box__field field-box__field--textarea js-auto-size" id="modal-submit-question-message" name="user-message" required></textarea>
                                <label class="field-box__label" for="modal-submit-question-message"><?php _e( 'Message' ); ?></label>
                            </li><!-- / .field-box -->
                            <li class="fields-list__item attachments-box">
                                <p class="attachments-box__label"><?php printf('%s <span>(%s)</span>', __('Attachments'), __('optional')); ?></p>
                                <div class="attachments">
                                    <div method="post" action="#" enctype="multipart/form-data"
                                         id="user-files-dropzone"
                                         class="attachments__dropzone dropzone"
                                         style="min-height:0">
                                        <input id="user-files" type="file" name="file" multiple style="display: none;">
                                        <label class="attachments__title dz-message"><?php printf('<span>%s</span> %s', __('Add file'), __('or drop files here')); ?></label>
                                    </div>
                                    <div class="attachments__files files"></div><!-- / .files -->
                                </div><!-- / .attachments -->
                            </li><!-- / .attachments-box -->
                        </ul><!-- / .fields-list -->
                        <button class="form__button button"><?php _e( 'Submit' ); ?></button>
                    </form><!-- / .form -->
                    <?php get_template_part( 'template-parts/modals/thank-you' ); ?>
                </div>
                <div class="contacts__col">
                    <div class="contacts-widget">
                        <h2 class="contacts-widget__title"><?php echo $page_sidebar_title; ?></h2>
                        <ul class="contacts-widget__list contact-list contact-list--extra">
                            <li class="contact-list__item contact-list__item--chat">
                                <p class="contact-list__title"><?php _e( 'Chat' ); ?></p>
                                <a id="chat-now" class="contact-list__link link" href="#"><?php _e( 'Chat now' ); ?></a>
                            </li>
                            <li class="contact-list__item contact-list__item--email">
                                <p class="contact-list__title"><?php _e( 'Email' ); ?></p>
                                <a class="contact-list__link link" href="mailto:<?php echo $page_sidebar_mail; ?>"><?php echo $page_sidebar_mail; ?></a>
                            </li>
                            <li class="contact-list__item contact-list__item--question">
                                <p class="contact-list__title"><?php _e( 'Have a question?' ); ?></p>
                                <a class="contact-list__link link btn-modal" href="#js-modal-submit-question"><?php _e( 'Submit a question' ); ?></a>
                            </li>
                        </ul><!-- / .contact-list -->
                        <div class="contacts-widget__additional-info content">
                            <p><?php echo $page_sidebar_phone_title; ?> <a href="tel:<?php echo $page_sidebar_phone; ?>"><?php echo $page_sidebar_phone; ?></a></p>
                            <p><?php echo $page_sidebar_work_time; ?> </p>
                            <div class="contacts-widget__address">
                                <p><?php echo $page_sidebar_address; ?></p>
                            </div>
                        </div>
                    </div><!-- / .contacts-widget -->
                </div>
            </div>
        </div>
    </section><!-- / .contacts -->
</main><!-- / .site-main -->
<?php get_footer(); ?>
