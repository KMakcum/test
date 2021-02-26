<div class="btn-modal" href="#js-modal-remove-item" style="display: none"></div>
<div class="modal-remove-item modal-common" id="js-modal-remove-item" style="display: none">
    <div class="modal-common__notice modal-notice">
        <svg class="modal-notice__icon" width="48" height="48" fill="#F2AE04">
            <use href="#icon-warning-3"></use>
        </svg>
        <div class="modal-notice__content">
            <p class="modal-notice__title-big"><?php echo __('You’ve reached your Meal Plan limit') ?></p>
            <p class="modal-notice__txt"><?php echo __('In order to add a desired meal you need to remove one item at least out of your cart') ?></p>
        </div>
        <button class="modal-notice__button button" type="button" data-fancybox-close><?php echo __('OK, Got It') ?></button>
    </div><!-- / .modal-notice -->
</div><!-- / .modal-remove-item -->
