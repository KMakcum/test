<?php
$order = op_help()->subscriptions->get_current_subscription();
if ( $order === false ) {
	return;
}
$name = '';
if ( $order->billing_first_name OR $order->billing_last_name ) {
	$name = "{$order->billing_first_name} {$order->billing_last_name}";
}
$email = wp_get_current_user()->user_email;
?>
<section class="cart__right-section card-data card-data--extra-small">
	<div class="card-data__head">
		<h2 class="card-data__title">Payment Method</h2>
        <?php if ( ( op_help()->subscriptions->get_subscribe_status() )['label'] != 'locked' ) { ?>
            <a class="control-button control-button--round" type="button" href="/checkout/#Payment-Method" data-tippy-content="Edit">
                <svg class="control-button__icon" width="16" height="16" fill="#252728">
                    <use href="#icon-edit"></use>
                </svg>
            </a>
        <?php } ?>
	</div>
    <?php
    $user_token = WC_Payment_Tokens::get_customer_default_token( get_current_user_id() );
    if ( is_object( $user_token ) ) {
        $token_data = $user_token->get_data();
        if ( !empty( $token_data ) AND isset( $token_data['last4'] ) ) {
            ?>
            <span
                class="card-data__number card-data__number--<?php echo strtolower( str_replace( ' ', '-', $token_data['card_type'] ) ); ?>"><?php echo $token_data['card_type']; ?> •••• <?php echo $token_data['last4']; ?></span>
            <?php
        }
    }
    ?>
</section><!-- / .card-data -->
<section class="cart__right-section info-box info-box--extra-small content content--extra-small">
	<h2>Billing info</h2>
	<p>
		<?php echo ( $name ) ? "{$name}<br>" : ''; ?>
		<?php echo ( $order->billing_phone ) ? "{$order->billing_phone}<br>" : ''; ?>
		<?php echo ( $order->billing_address_1 ) ? "{$order->billing_address_1}<br>" : ''; ?>
		<?php echo ( $order->billing_address_2 ) ? "{$order->billing_address_2}<br>" : ''; ?>
        <?php echo ( $order->billing_city && $order->billing_state ) ? "{$order->billing_city}, {$order->billing_state}<br>" : ''; ?>
		<?php echo ( $order->billing_postcode ) ? "{$order->billing_postcode}<br>" : ''; ?>
		<?php //echo ( $email ) ? "<a href=\"mailto:{$email}\">{$email}</a>" : ''; ?>
	</p>
</section><!-- / .info-box -->
