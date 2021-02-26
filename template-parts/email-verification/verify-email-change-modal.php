<?php $user_email = $args['user_email']; ?>
<div class="modal-change-email modal-common" id="js-modal-change-email" style="display: none">
    <div class="modal-common__data data">
        <header class="data__header content">
            <h3><?php echo __('Change Email') ?></h3>
        </header>
        <form id="changeEmailFrom" class="data__form form" action="#" method="post" novalidate="novalidate">
            <ul class="form__list fields-list">
                <li class="fields-list__item">
                    <div class="field-wr">
                        <p class="field-wr__label"><?php echo __('Enter new e-mail') ?></p>
                        <p class="field-wr__field field-box">
                            <input class="field-box__field" id="modal-change-email-value" type="email"
                                   name="user-email" required="">
                            <label class="field-box__label"
                                   for="modal-change-email-value"><?php echo __('New e-mail') ?></label>
                        </p><!-- / .field-box -->
                    </div>
                </li>
            </ul><!-- / .fields-list -->
            <button id="changeEmailButton" data-user-email="<?php echo $user_email ?>"
                    class="form__button button" type="submit"><?php echo __('Save') ?></button>
        </form><!-- / .form -->
    </div><!-- / .data -->
</div>
