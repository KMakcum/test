<?php
/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited

if ( ! $order ) {
	return;
}

$order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();
$downloads             = $order->get_downloadable_items();
$show_downloads        = $order->has_downloadable_item() && $order->is_download_permitted();

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}

if( in_array( $order->get_status(), ['op-subscription', 'op-paused'] ) ){
	$subscription = $order;
} else {
	$subscription = wc_get_order( $order->get_parent_id() );
}
// new Automattic\WooCommerce\Admin\Overrides\Order;
// new WC_DateTime;
$done_orders = $subscription->get_created_orders_by_weeks();
$future_orders = $subscription->get_order_variations();



?>


<main class="site-main site-main--padding-medium">
	<section class="delivery">
			<div class="container">
					<div class="delivery__top">
							<h1 class="delivery__title h2">Subscription #<?php echo esc_html( $subscription->get_id() ); ?></h1>
							<div class="delivery__range range-slider js-delivery-range-slider">
									<?php foreach( $done_orders as $week_num => $week_data){ ?>
									<div class="range-slider__item">Week <?php echo esc_html( $week_data['title'] ) ?></div>
									<?php } ?>
									<div class="range-slider__item">This Subscription</div>
									<?php
									$next_delivery = new DateTime( $subscription->get_meta( 'op_next_delivery' ) );
									$next_delivery_date = clone $next_delivery;
									foreach ( $future_orders as $week_key => $week_items ) {
										if( $week_key > 0 ){
											$next_delivery_date->modify( "+1 week" );
										}
										$next_delivery_week = clone $next_delivery_date;
										$week_title = $next_delivery_week->modify('Monday this week')->format('M, d') . ' - ' . $next_delivery_week->modify('Sunday this week')->format('M, d');
										
										// $week_title = $order_date->modify('Monday this week')->format('M, d') . ' - ' . $order_date->modify('Sunday this week')->format('M, d');
										?>
									<div class="range-slider__item">Week <?php echo esc_html( $week_title ) ?></div>
									<?php } ?>
							</div><!-- / .range-slider -->
					</div>
					<div class="delivery__slider delivery-slider">

						<?php foreach( $done_orders as $week_num => $week_data){ ?>	
						<div class="delivery-slider__item">
							<?php foreach ( $week_data['orders'] as $order_key => $week_order ) { 

								wc_get_template( 'order/order-details-order.php', [
									'order' => $week_order
								]);
								
							} ?>
						</div>
						<?php } ?>

						<form class="delivery-slider__item op_ajax_save_subscription">
							<input type="hidden" name="order_id" value="<?php echo esc_attr( $subscription->get_id() ); ?>">
							<?php	wc_get_template( 'order/order-details-subscription.php', ['order' => $subscription] ); ?>
						</form>

						<?php
						$next_delivery = new DateTime( $subscription->get_meta( 'op_next_delivery' ) );
						$next_delivery_date = clone $next_delivery;
						foreach( $future_orders as $week_num => $week_items){ ?>	
						<form class="delivery-slider__item op_ajax_save_future_order">
							
							<input type="hidden" name="order_id" value="<?php echo esc_attr( $subscription->get_id() ); ?>">
							<input type="hidden" name="order_week" value="<?php echo esc_attr( $week_num ); ?>">
							<?php
								if( $week_num > 0 ){
									$next_delivery_date->modify( "+1weeks" );
								}

								wc_get_template( 'order/order-details-future.php', [
									'order' => $subscription,
									'items' => $week_items,
									'order_paused' => $subscription->get_meta( "op_future_pause_" . $week_num ),
									'items_mutations' => $subscription->get_meta( "op_future_items_" . $week_num ),
									'order_delivery' => $next_delivery_date
								]);
								
							?>
						</form>
						<?php } ?>

					</div><!-- / .delivery-slider -->
			</div>
	</section><!-- / .delivery -->
</main><!-- / .site-main -->



<!-- <section class="woocommerce-order-details">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>

	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">

		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php
			do_action( 'woocommerce_order_details_before_order_table_items', $order );

			foreach ( $order_items as $item_id => $item ) {
				$product = $item->get_product();

				wc_get_template(
					'order/order-details-item.php',
					array(
						'order'              => $order,
						'item_id'            => $item_id,
						'item'               => $item,
						'show_purchase_note' => $show_purchase_note,
						'purchase_note'      => $product ? $product->get_purchase_note() : '',
						'product'            => $product,
					)
				);
			}

			do_action( 'woocommerce_order_details_after_order_table_items', $order );
			?>
		</tbody>

		<tfoot>
			<?php
			foreach ( $order->get_order_item_totals() as $key => $total ) {
				?>
					<tr>
						<th scope="row"><?php echo esc_html( $total['label'] ); ?></th>
						<td><?php echo ( 'payment_method' === $key ) ? esc_html( $total['value'] ) : wp_kses_post( $total['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
					</tr>
					<?php
			}
			?>
			<?php if ( $order->get_customer_note() ) : ?>
				<tr>
					<th><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
					<td><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
				</tr>
			<?php endif; ?>
		</tfoot>
	</table>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section> -->

<?php
if ( $show_customer_details ) {
	wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
}
