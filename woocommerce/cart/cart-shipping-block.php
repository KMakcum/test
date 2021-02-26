<?php
$order = op_help()->subscriptions->get_current_subscription();
if ( $order === false ) {
    return;
}
$name = '';
if ( $order->shipping_first_name OR $order->shipping_last_name ) {
	$name = "{$order->shipping_first_name} {$order->shipping_last_name}";
}
?>
<div class="shipping-block">
    <?php do_action( 'woocommerce_before_shipping_block' ); ?>
    <section class="cart__right-section info-box info-box--extra-small content content--extra-small">
        <div class="info-box__head">
            <h2>Shipping address</h2>
            <?php if ( ( op_help()->subscriptions->get_subscribe_status() )['label'] != 'locked' ) { ?>
                <a class="control-button control-button--round" type="button" href="/checkout/#Delivery-Address" data-tippy-content="Edit">
                    <svg class="control-button__icon" width="16" height="16" fill="#252728">
                        <use href="#icon-edit"></use>
                    </svg>
                </a>
            <?php } ?>
        </div>
        <p data-id="<?php echo $order->ID; ?>">
            <?php echo ( $name ) ? "{$name}<br>" : ''; ?>
            <?php echo ( $order->shipping_address_1 ) ? "{$order->shipping_address_1}<br>" : ''; ?>
            <?php echo ( $order->shipping_address_2 ) ? "{$order->shipping_address_2}<br>" : ''; ?>
            <?php echo ( $order->shipping_city && $order->shipping_state ) ? "{$order->shipping_city}, {$order->shipping_state}<br>" : ''; ?>
            <?php echo ( $order->shipping_postcode ) ? "{$order->shipping_postcode}" : ''; ?>
        </p>
	    <?php echo ( $order->customer_note ) ? "<p class=\"info-box__notice\">{$order->customer_note}</p>" : ''; ?>
    </section><!-- / .info-box -->
</div>