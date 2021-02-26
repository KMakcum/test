<?php
$display = $args['display'] ? $args['display'] : 'block';
$user_email = $args['user_email'];
?>

    <section class="verify-email-2" style="display:<?php echo $display ?>">
        <div class="container">
            <div class="verify-email-2__box">
                <div class="verify-email-2__txt content">
                    <p>
                        <?php echo __('Sorry! Verification email has been already sent to you. Please, check
                        your inbox to verify. Link expires <strong>in 48 hours.</strong>'); ?>
                    </p>
                </div>
                <p class="verify-email-2__email-title"><?php echo __('Your email') ?></p>
                <p class="verify-email-2__email"><?php echo __($user_email, ''); ?></p>
                <a class="verify-email-2__button button btn-modal"
                   href="#js-modal-change-email"> <?php echo __('Change email') ?></a>
                <p class="verify-email-2__credits">
                    <?php echo __('Haven’t got an email? Check spam folder or') ?>
                    <a class="link-2 link-2--color--main" style="cursor: pointer" data-user-email="<?php echo $user_email ?>" id="verification-resend"><?php echo __('Resend') ?></a>
                </p>
            </div>
        </div>
    </section><!-- / .verify-email -->

<?php
