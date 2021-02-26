<div class="modal-promo-code modal-common" id="js-promo-code" style="display: none">
    <div class="modal-common__data data">
        <div class="message message--full message--error" style="display: none;">
            <svg class="message__icon" width="24" height="24" fill="#690713">
                <use href="#icon-warning-3"></use>
            </svg>
            <div id="error_coupon" class="message__txt">
                <?php

                $all_notices  = WC()->session->get( 'wc_notices', array() );

                $notice_types = apply_filters( 'woocommerce_notice_types', array( 'error', 'success', 'notice' ) );


                ?>


            </div>
        </div>
        <header class="data__header content">
            <h3>Enter promo code</h3>
        </header>

        <?php wc_get_template('checkout/form-coupon.php'); ?>

    </div>
</div>