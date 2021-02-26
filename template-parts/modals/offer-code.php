<?php
$coupon = op_help()->coupons->get_public_coupon();
?>

<div class="modal-promo-code-to-use modal-common" id="js-modal-promo-code-to-use" style="display: none">
    <div class="modal-common__data data">
        <header class="data__header content">
			<?php if ( ! empty( $coupon['title'] ) ) { ?>
                <h3><?php echo $coupon['title']; ?></h3>
			<?php } ?>
			<?php if (! empty( $coupon['description'] )) { ?>
                <p><?php echo $coupon['description']; ?></p>
			<?php } ?>
        </header>
        <div class="data__discount-code discount-code">
            <p class="discount-code__title">Use The Following Code:</p>
            <div class="discount-code__copy-txt-2 copy-txt-2">
                <input class="copy-txt-2__field" type="text" value="<?php echo $coupon['discount']; ?>" aria-label="Code" readonly>
                <button class="copy-txt-2__button button button--color--dark" type="button">Copy Code</button>
            </div>
            <?php if ( ! empty( $coupon['expires'] ) ) { ?>
                <p class="discount-code__credits">Code Expires in <?php echo $coupon['expires']; ?></p>
            <?php } ?>
        </div>
        <button class="data__button button js-discount-button" type="button" data-fancybox-close>Continue</button>
    </div>
</div>