<!-- Cookies -->
<div class="cookies" style="display: none;">
    <button class="cookies__close button-close" type="button">
        <span class="visually-hidden"><?php _e('Close'); ?></span>
    </button><!-- / .button-close -->
    <div class="cookies__txt content content--extra-small">
        <p class="cookies__title">
            <img class="cookies__logo-mobile" src="<?php echo get_template_directory_uri(); ?>/assets/img/base/logo.svg" width="80" height="21" alt="Life Chef">
            <?php _e('Our website uses cookies'); ?>
        </p>
        <p>
            <?php _e('We use cookies to help personalise content, measure ads, and provide a safer experience.
            By navigating the site, you agree to the use of cookies to collect information. Read our
            Cookies Policy to learn more.'); ?>
        </p>
    </div>
    <div class="cookies__actions">
        <button id="cookie-submit" class="cookies__button button button--small button--color--dark" type="button"><?php _e('Accept All Cookies'); ?></button>
        <a class="cookies__link link-2" href="<?php echo get_privacy_policy_url().'#privacy-anchor'; ?>" target="_blank"><?php _e('Cookies Policy'); ?></a>
    </div>
</div><!-- / .cookies -->
