<section class="product-details__subs-notify subs-notify">
    <div class="subs-notify__txt content">
        <h2> <?php echo __('Comming Soon') ?></h2>
        <p>
            <?php echo __('We are working hard to launch healthy meals delivery to your area.
            We will inform you when meal delivery is available') ?>
        </p>
    </div>
    <form class="subs-notify__form form-row" action="#" method="post">
        <p class="form-row__box">
            <label class="visually-hidden" for="subs-notify"> <?php echo __('Subscribe to notification') ?></label>
            <input class="form-row__field" id="subs-notify" type="email" name="email" placeholder="Your Email" required>
            <button class="form-row__button button button--light"> <?php echo __('Notify me!') ?></button>
        </p>
    </form><!-- / .form-row -->
</section><!-- / .subs-notify -->
