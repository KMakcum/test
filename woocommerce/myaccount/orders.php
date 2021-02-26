<?php
/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

<main class="site-main site-main--padding-medium">
		<section class="orders">
				<div class="container">
						<h1 class="orders__title h2">My Orders</h1>
						<div class="tabs js-tabs">
								<ul class="tabs__nav">
										<li><a href="#tabs-subscription">Open <span><?php echo $customer_orders->total; ?></span></a></li>
										<?php
											$order_tab_statuses = [
												'wc-pending' => _x( 'Need payment', 'Order status', 'woocommerce' ),
												'wc-processing' => _x( 'Processing', 'Order status', 'woocommerce' ),
												'wc-completed'  => _x( 'Delivered', 'Order status', 'woocommerce' ),
												'wc-op-paused' => _x( 'Paused', 'Order status', 'woocommerce' ),
											];

											$orders_query = new WP_Query;
											foreach ( $order_tab_statuses as $order_status => $order_title ) {
												$other_customer_orders[ $order_status ] = $orders_query->query(
													[
														'numberposts' => 5,
														'meta_key'    => '_customer_user',
														'meta_value'  => get_current_user_id(),
														'post_type'   => wc_get_order_types( 'view-orders' ),
														'post_status' => $order_status,
														'fields' => 'ids' // get only ids, no need post object here
													]
												);
												
												if( $orders_query->found_posts !== 0 ){
													echo '<li><a href="#tabs-'.esc_attr( $order_status ).'">'.esc_html( $order_title ) .' <span>'. esc_html( $orders_query->found_posts ) . '</span></a></li>';
												} else {
													unset( $other_customer_orders[ $order_status ] );
												}
											}
										?>
								</ul>
								<div class="tabs__content order-content" id="tabs-subscription">
								<?php foreach ( $customer_orders->orders as $customer_order ) { ?>
									<form class="order-content__row op_ajax_save_subscription">
										<div class="order-content__col">
											<input type="hidden" name="order_id" value="<?php echo esc_attr( $customer_order->get_id() ); ?>">
											
											<?php	wc_get_template( 'myaccount/order-box.php', ['order' => $customer_order] ); ?>
											
										</div>
										<div class="order-content__col">
											
											<div class="fields-list__item field-box">
												<?php $next_delivery = new DateTime( $customer_order->get_meta( 'op_next_delivery' ) ); ?>
												<input class="field-box__field" type="text" data-datepicker name="op_next_delivery" value="<?php echo $next_delivery->format("M d, Y"); ?>">
												<label class="field-box__label" for="modal-sign-up-email">Next delivery date</label>
											</div>
											<label class="select select--rounded-corners">
												<select class="select__field" name="op_next_delivery_time">
													<option value="10">Delivery time</option>
													<?php 
													$delivery_time = $next_delivery->format("H");
													for ($i=10; $i < 20; $i++) { 
														$selected = $delivery_time == $i ? 'selected' : '';
														?>
													<option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i . ':00'; ?></option>
													<?php } ?>
												</select>
											</label>
											
											<?php if( $customer_order->has_future_modifications() ){ ?>
											<p>You have modified future orders for this subscription.<br>After update this changes will be deleted!</p>
											<?php } ?>
											<button class="order-content__button button button--small" type="submit">Update</button>
											<p class='test-result'></p>
											<br><br>

											<p>Pick up needed time</p>
											<?php 


											
												$order_variations = $customer_order->get_order_variations();
											?>
											<label class="select select--rounded-corners">
												<select class="select__field" name="create_order">
													<option value="">Choose date</option>
													<?php foreach ( $order_variations as $week_num => $items) {
														$now_date = new DateTime();
														if( $week_num === 0 ){
															echo '<option value="0">Now ('.$now_date->format('Y-m-d').')</option>';
														} else {
															$now_date->add( new DateInterval('P'.(7*$week_num).'D') );
															echo '<option value="'.$week_num.'">Week '.$week_num.' ('.$now_date->format('Y-m-d').')</option>';
														}
													} ?>
												</select>
											</label>
											<label class="select select--rounded-corners">
												<select class="select__field" name="create_order_status">
													<option value="wc-processing">Order will have status</option>
													<option value="wc-pending">Need payment</option>
													<option value="wc-processing">Processing</option>
													<option value="wc-completed">Delivered'</option>
												</select>
											</label>
											<br>
											<button class="order-content__button button button--small" type="submit">Create order</button>

										</div>
									</form>
								<?php } ?>
								</div><!-- / .order-content -->
								<?php 
								foreach ( $other_customer_orders as $order_status => $order_list_for_status ) { 
									$can_edit = in_array( $order_status, ['wc-op-subscription','wc-op-paused'] ) ? true : false;
									?>
								<div class="tabs__content order-content" id="tabs-<?php echo esc_attr( $order_status ); ?>">

									<?php 
									if( in_array( $order_status, ['wc-op-subscription','wc-op-paused'] ) ) {
										foreach ( $order_list_for_status as $customer_order_id ) {
											?>
									<form class="order-content__row op_ajax_save_subscription">
										<input type="hidden" name="order_id" value="<?php echo esc_attr( $customer_order_id ); ?>">
											<div class="order-content__col">
												<?php
													$customer_order = wc_get_order( $customer_order_id );
													wc_get_template( 'myaccount/order-box.php', ['order' => $customer_order] );
												?>
											</div>
											<div class="order-content__col">
													<button class="order-content__button button button--small" type="submit">Update</button>
											</div>
									</form>
										<?php } ?>
									<?php } else { ?>
									<div class="order-content__row op_ajax_save_subscription">
											<div class="order-content__col">
												<?php foreach ( $order_list_for_status as $customer_order_id ) {
													$customer_order = wc_get_order( $customer_order_id );
													wc_get_template( 'myaccount/order-box.php', ['order' => $customer_order] );
												} ?>
											</div>
											<div class="order-content__col">
													<button class="order-content__button button button--small" type="button">CTA</button>
											</div>
									</div>
									<?php } ?>
								</div><!-- / .order-content -->
								<?php } ?>
						</div><!-- / .tabs -->
				</div>
		</section><!-- / .orders -->
</main><!-- / .site-main -->

<?php if( false ) : //if ( $has_orders ) : ?>

	<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>
				<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
					<th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
			<?php
			foreach ( $customer_orders->orders as $customer_order ) {
				$order      = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();
				?>
				<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $order->get_status() ); ?> order">
					<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
							<?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) : ?>
								<?php do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order ); ?>

							<?php elseif ( 'order-number' === $column_id ) : ?>
								<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
									<?php echo esc_html( _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number() ); ?>
								</a>

							<?php elseif ( 'order-date' === $column_id ) : ?>
								<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>

							<?php elseif ( 'order-status' === $column_id ) : ?>
								<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>

							<?php elseif ( 'order-total' === $column_id ) : ?>
								<?php
								/* translators: 1: formatted order total 2: total order items */
								echo wp_kses_post( sprintf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ) );
								?>

							<?php elseif ( 'order-actions' === $column_id ) : ?>
								<?php
								$actions = wc_get_account_orders_actions( $order );

								if ( ! empty( $actions ) ) {
									foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
										echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
									}
								}
								?>
							<?php endif; ?>
						</td>
					<?php endforeach; ?>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>

	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if ( 1 !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
			<?php endif; ?>

			<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php //else : ?>
	<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
		<a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php esc_html_e( 'Browse products', 'woocommerce' ); ?>
		</a>
		<?php esc_html_e( 'No order has been made yet.', 'woocommerce' ); ?>
	</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
