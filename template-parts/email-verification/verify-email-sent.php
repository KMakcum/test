<?php
$display = $args['display'] ? $args['display'] : 'block';
$user_email = $args['user_email'];
?>
    <main class="site-main account-verify-main">
        <div class="container">
            <div class="account-verify-main__wr">
                <section class="account-verify-main__verify-email verify-email">
                    <div class="verify-email__head">
                        <p class="verify-email__status"><?php echo __('Verification email has been sent to') ?></p>
                        <p class="verify-email__email">
                            <span><?php echo __($user_email) ?></span>
                            <a class="control-button control-button--round btn-modal" type="button"
                               data-tippy-content="Edit" href="#js-modal-change-email">
                                <svg class="control-button__icon" width="16" height="16" fill="#252728">
                                    <use href="#icon-edit"></use>
                                </svg>
                            </a>
                        </p>
                    </div>
                    <div class="verify-email__code-confirmation code-confirmation">
                        <div class="code-confirmation__txt content">
                            <h1><?php echo __('Verify your account') ?></h1>
                            <p><?php echo __('Please, enter the code here or follow the link from the email. Link expires <strong>in 48
                                    hours.</strong>') ?></p>
                        </div>
                        <ul class="code-confirmation__code code">
                            <li class="code__item">
                                <input class="code__field"
                                       id="verification-code-1"
                                       type="text"
                                       name="verification-code-1"
                                       inputmode="numeric"
                                       autocomplete="one-time-code"
                                       pattern="\d"
                                       required
                                       aria-label="Enter the number №1 from the received code">
                            </li>
                            <li class="code__item">
                                <input class="code__field"
                                       id="verification-code-2"
                                       type="text"
                                       name="verification-code-2"
                                       inputmode="numeric"
                                       autocomplete="one-time-code"
                                       required
                                       pattern="\d"
                                       aria-label="Enter the number №2 from the received code">
                            </li>
                            <li class="code__item">
                                <input class="code__field"
                                       id="verification-code-3"
                                       type="text"
                                       name="verification-code-3"
                                       inputmode="numeric"
                                       autocomplete="one-time-code"
                                       required
                                       pattern="\d"
                                       aria-label="Enter the number №3 from the received code">
                            </li>
                            <li class="code__item">
                                <input class="code__field"
                                       id="verification-code-4"
                                       type="text"
                                       name="verification-code-4"
                                       inputmode="numeric"
                                       autocomplete="one-time-code"
                                       required
                                       pattern="\d"
                                       aria-label="Enter the number №4 from the received code">
                            </li>
                        </ul><!-- / .code -->
                    </div><!-- / .code-confirmation -->
                    <p class="verify-email__credits">
                        <?php echo __('Haven’t got an email? Check spam folder or') ?>
                        <button id="resendInvalidActivationLink" data-user-email="<?php echo $user_email ?>"
                                class="link-2 link-2--color--main"
                                type="button"><?php echo __('Resend') ?>
                        </button>
                    </p>
                </section><!-- / .verify-email -->

                <section class="account-verify-main__need-help need-help need-help--extra">
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
                            <a class="contact-list-2__link " href="<?php echo get_site_url().'/faq?ask-question=open'?>">
                                <svg class="contact-list-2__icon" width="40" height="40" fill="#252728">
                                    <use href="#icon-question"></use>
                                </svg>
                                <?php echo __('Ask a question') ?>
                            </a>
                        </li>
                    </ul>
                    <div class="need-help__credits content">
                        <p><?php echo __('Call Us:') ?> <a href="tel:18559324048"><?php echo __('1 855 932 4048') ?></a>
                        </p>
                        <p><?php echo __('Monday – Friday, 8:00 AM to 5:00 PM EST') ?> </p>
                    </div>
                </section><!-- / .need-help -->
            </div>
        </div>
    </main><!-- / .site-main .account-verify-main -->
<?php
