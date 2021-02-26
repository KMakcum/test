<?php
$faq_post = get_page_by_path('faq');
$faq_email_cf = carbon_get_post_meta($faq_post->ID, 'contact_section_content') ?
    carbon_get_post_meta($faq_post->ID, 'contact_section_content') :
    [
        [
            'type' => '_',
            'contact_us_item_title' => 'Email',
            'contact_us_item_text' => 'hello@lifechef.com'
        ]
    ]

?>
<section class="product-card__need-help need-help">
    <h2 class="need-help__title"> <?php echo __('Need help?'); ?></h2>
    <ul class="need-help__contacts contact-list-2">
        <li class="contact-list-2__item" id="chat-toggle">
            <a class="contact-list-2__link">
                <svg class="contact-list-2__icon" width="40" height="40" fill="#34A34F">
                    <use href="#icon-chat"></use>
                </svg>
                <?php echo __('Chat'); ?>
            </a>
        </li>
        <?php foreach ($faq_email_cf as $email_cf) {
            if (strtolower($email_cf['contact_us_item_title']) === 'email') {
                ?>
                <li class="contact-list-2__item">
                    <a class="contact-list-2__link" href="mailto:<?php echo $email_cf['contact_us_item_text']; ?>">
                        <svg class="contact-list-2__icon" width="40" height="40" fill="#34A34F">
                            <use href="#icon-envelope-open"></use>
                        </svg>
                        <?php echo __('Email Us'); ?>
                    </a>
                </li>
            <?php }
        } ?>
        <li class="contact-list-2__item">
            <a class="contact-list-2__link btn-modal" href="#js-modal-submit-question">
                <svg class="contact-list-2__icon" width="40" height="40" fill="#34A34F">
                    <use href="#icon-question"></use>
                </svg>
                <?php echo __('Ask a question') ?>
            </a>
        </li>
    </ul>
    <div class="need-help__credits content">
        <p> <?php echo __('Call Us:') ?> <a href="tel:18559324048"> <?php echo __('1 855 932 4048') ?></a></p>
        <p> <?php echo __('Monday – Friday, 8:00 AM to 5:00 PM EST') ?> </p>
    </div>
</section><!-- / .need-help -->
