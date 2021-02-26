<?php
$user_email = $args['user_email'];
?>
<main class="site-main verify-failed-main">
    <div class="container">
        <section class="verify-failed-main__data data">
            <svg class="data__icon" width="104" height="104" fill="#DB4827">
                <use href="#icon-warning-2"></use>
            </svg>
            <header class="data__header content">
                <h1><?php echo __('Invalid link<') ?>/h1>
                    <p><?php echo __('Your link has expired or this email has already been verified') ?></p>
            </header>
            <button id="resendInvalidActivationLink" class="data__button button"
                    type="button"
                    data-user-email="<?php echo $user_email ?>"><?php echo __('Send a New Verification Code') ?></button>
        </section><!-- / .data -->

        <section class="verify-failed-main__need-help need-help need-help--center">
            <h2 class="need-help__title"><?php echo __('Need help?') ?></h2>
            <ul class="need-help__contacts contact-list-2">
                <li class="contact-list-2__item">
                    <a class="contact-list-2__link" href="#" id="intercom">
                        <svg class="contact-list-2__icon" width="40" height="40" fill="#252728">
                            <use href="#icon-chat"></use>
                        </svg>
                        <?php echo __('Chat') ?>
                    </a>
                </li>
                <li class="contact-list-2__item">
                    <a class="contact-list-2__link" href="mailto:contact@lifechef.com">
                        <svg class="contact-list-2__icon" width="40" height="40" fill="#252728">
                            <use href="#icon-envelope-open"></use>
                        </svg>
                        <?php echo __('Email Us') ?>
                    </a>
                </li>
                <li class="contact-list-2__item">
                    <a class="contact-list-2__link" href="<?php echo get_site_url().'/faq?ask-question=open'?>">
                        <svg class="contact-list-2__icon" width="40" height="40" fill="#252728">
                            <use href="#icon-question"></use>
                        </svg>
                        <?php echo __('Ask a question') ?>
                    </a>
                </li>
            </ul>
            <div class="need-help__credits content">
                <p><?php echo __('Call Us: ') ?><a href="tel:18559324048"><?php echo __('1 855 932 4048') ?></a></p>
                <p><?php echo __('Monday – Friday, 8:00 AM to 5:00 PM EST') ?> </p>
            </div>
        </section><!-- / .need-help -->
    </div>
</main><!-- / .site-main .verify-failed-main -->
