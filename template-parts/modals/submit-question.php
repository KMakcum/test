<?php $faq_content = $args['faq-content']; ?>
<div class="modal-submit-question modal-common" id="js-modal-submit-question" style="display: none">
    <div class="modal-common__data data">
        <header class="data__header content">
            <h3><?php echo __('Submit a question') ?></h3>
        </header>
        <form class="data__form form" action="#" method="post">
            <ul class="form__list fields-list">
                <li class="fields-list__item field-box">
                    <input class="field-box__field" id="modal-submit-question-name" type="text" name="user-name"
                           required>
                    <label class="field-box__label"
                           for="modal-submit-question-name"><?php echo __('Your name') ?></label>
                </li><!-- / .field-box -->
                <li class="fields-list__item field-box">
                    <input class="field-box__field" id="modal-submit-question-email" type="email" name="user-email"
                           required>
                    <label class="field-box__label"
                           for="modal-submit-question-email"><?php echo __('Your email') ?></label>
                </li><!-- / .field-box -->
                <li class="fields-list__item field-box">
                    <input class="field-box__field" id="modal-submit-question-phone" type="tel" name="user-phone"
                           required>
                    <label class="field-box__label"
                           for="modal-submit-question-phone"><?php echo __('Phone number') ?></label>
                </li><!-- / .field-box -->
                <li class="fields-list__item field-box">
                    <input class="field-box__field" id="modal-submit-question-order-number" type="tel"
                           name="user-order-number">
                    <label class="field-box__label" for="modal-submit-question-order-number"><?php echo __('Order number
                        (optional)') ?></label>
                </li><!-- / .field-box -->
                <li class="fields-list__item field-box">
                    <select class="field-box__field field-box__field--select field-box__field--entered"
                            id="modal-submit-question-category" name="question-category">
                        <?php
                        foreach ($faq_content['faq_single_categories'] as $faq_category) { ?>
                            <option><?php echo $faq_category['faq_category_name'] ?></option>
                        <?php } ?>
                    </select>
                    <label class="field-box__label"
                           for="modal-submit-question-category"><?php echo __('Category') ?></label>
                    <svg class="field-box__select-icon" width="24" height="24" fill="#252728">
                        <use href="#icon-angle-down-light"></use>
                    </svg>
                </li><!-- / .field-box -->
                <li class="fields-list__item field-box">
                        <textarea class="field-box__field field-box__field--textarea js-auto-size"
                                  id="modal-submit-question-message" name="user-message" required></textarea>
                    <label class="field-box__label"
                           for="modal-submit-question-message"><?php echo __('Message') ?></label>
                </li><!-- / .field-box -->
                <li class="fields-list__item attachments-box">
                    <div class="attachments">
                        <p class="attachments-box__label"><?php echo __('Attachments <span>(optional)</span>') ?></p>
                        <div class="attachments">
                            <div method="post" action="#" enctype="multipart/form-data"
                                 id="user-files-dropzone"
                                 class="attachments__dropzone dropzone"
                                 style="min-height:0">
                                <input id="user-files" type="file" name="file" multiple style="display: none;">
                                <label class="attachments__title dz-message"><span><?php echo __('Add file</span> or drop files
                                    here') ?></label>
                            </div>
                            <div class="attachments__files files"></div><!-- / .files -->
                        </div><!-- / .attachments -->
                </li><!-- / .attachments-box -->
            </ul><!-- / .fields-list -->
            <button class="form__button button" type="submit"><?php echo __('Submit a question') ?></button>
        </form><!-- / .form -->
    </div><!-- / .data -->
</div><!-- / .modal-submit-question -->
<!-- Modal modal-faq-thank-you -->
<div class="modal-faq-thank-you modal-common" id="js-modal-faq-thank-you" style="display: none">
    <div class="modal-common__status-page status-page">
        <svg class="status-page__icon" width="48" height="48" fill="#34A34F">
            <use href="#icon-check-circle-stroke"></use>
        </svg>
        <div class="status-page__txt content">
            <h1><?php echo __('Thank You!'); ?></h1>
            <p><?php echo __('We’ve received your question. Our manager will contact you soon regarding this matter.') ?></p>
        </div>
        <button class="status-page__button button" type="button"
                data-fancybox-close><?php echo __('Back'); ?></button>
    </div><!-- / .status-page -->
</div><!-- / .modal-faq-thank-you -->
