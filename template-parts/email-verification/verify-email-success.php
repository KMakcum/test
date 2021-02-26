<?php
$display = $args['display'] ? $args['display'] : 'block';
?>
    <section class="verify-email-2" style="display:<?php echo $display ?>">
        <div class="container">
            <div class="verify-email-2__box">
                <div class="verify-email-2__txt content">
                    <h1><?php echo __('Your email has been successfully verified!') ?></h1>
                    <p>
                        <?php __('Thank you for verifying your account with the email mike.korsky@gmail.com.
                        Now you can login using your credentials'); ?>
                    </p>
                </div>
                <a class="verify-email-2__button button btn-modal"
                   href="#js-modal-sign-in"><?php echo __('Login') ?></a>
            </div>
        </div>
    </section><!-- / .verify-email -->
<?php
