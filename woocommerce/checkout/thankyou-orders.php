<?php
$order = $args['order'];
$statuses = op_help()->shop->order_statuses_to_show_on_front($order);
?>

<section class="checkout-thanks__section orders-section">
    <div class="orders-section__head head-orders-section">
        <div class="head-orders-section__txt content">
            <h2><?php echo _e('Your order has been placed!', 'order-thank-you'); ?></h2>
            <p><?php echo _e('We have sent you a confirmation email with your order details.', 'order-thank-you'); ?></p>
        </div>
        <a class="head-orders-section__download-receipt link-2" href="<?php echo wp_nonce_url( "/?action=download_receipt&order-id=" . $order->get_order_number(), 'order-receipt', '_nonce' ); ?>"><?php echo _e('Download Receipt', 'order-thank-you'); ?></a>
    </div>
    <ul class="orders-section__list order-list">

        <?php
        if ( $order ) {

            $order_status = $order->get_status();
            // $object_title = in_array( $order_status, ['op-subscription','op-paused'] ) ? 'Subscription' : 'Order';
            $order_canbe_paused = in_array( $order_status, ['op-subscription','op-paused'] ) ? true : false;

            $object_status = ($order->get_status() == 'delivered') ? 'Closed' : 'Opened';
            $order_paused  = '';

            if ( $order_status == 'op-paused' ) {
                $order_paused  =  'checked';
                $object_status = 'Paused';
            }
            $next_delivery = new DateTime( $order->get_meta("op_next_delivery") );
        ?>

            <li class="order-list__item order-item">
                <header class="order-item__head head-order-item">
                    <div class="head-order-item__col left-head-order-item">
                        <div class="left-head-order-item__head">
                            <p class="left-head-order-item__number"><?php //echo esc_html( $object_title ); ?>№ <?php echo $order->get_order_number(); ?></p>
                            <span class="left-head-order-item__pill pill pill--no-icon pill--small pill--bg--warning--light"><?php echo $object_status; ?></span>
                        </div>

                        <div class="order-item-products">
                            <?php
                                $products_count = count($order->get_items());
                                foreach ($order->get_items() as $key => $item) {
                                    $product = $item->get_product(); ?>

                                    <li class="meals-list-2__item">
                                        <picture>
                                            <?php if ($product->is_type('simple')) {
                                                echo $product->get_image(array(32, 32));
                                            }else { ?>
                                                <img width="32" height="32" src="<?php echo wp_get_attachment_image_src( get_post_thumbnail_id( $item->get_variation_id() ), 'woocommerce_gallery_thumbnail' )[0]; ?>">
                                            <?php } ?>
                                        </picture>
                                    </li>
                                <?php }
                            ?>

                            <?php if ( $products_count > 6 ) { ?>
                                <span class="order-item-products__other">+<?php echo $products_count - 6; ?></span>
                            <?php } ?>
                        </div>
                    </div><!-- / .left-head-order-item -->
                    <div class="head-order-item__col">
                        <p class="head-order-item__term"><?php echo _e('Ships on:', 'order-thank-you'); ?></p>
                        <p class="head-order-item__value"><?php echo esc_html( $next_delivery->format("l, F j, Y") ); ?></p>
                    </div>
                    <div class="head-order-item__col">
                        <p class="head-order-item__term"><?php echo _e('Total:', 'order-thank-you'); ?></p>
                        <p class="head-order-item__value"><?php echo wc_price( $order->get_total() ); ?></p>
                    </div>
                    <!--<div class="head-order-item__col">
                        <button class="head-order-item__button button button--small">Manage Order</button>
                    </div>-->
                </header>

                <div class="body-order-item">
                    <ul class="body-order-item__status order-status">
                        <?php $counter = 1;
                        foreach ($statuses as $key => $status) {
                            if ($counter == 1) { ?>
                                <li class="order-status__item order-status__item--open <?php echo ($status['active']) ? 'order-status__item--completed' : '' ?>">
                                    <svg class="order-status__icon" width="24" height="24" fill="#E1E3E6">
                                        <use href="#icon-box-2"></use>
                                    </svg>
                                    <?php echo $status['name']; ?>
                                </li>
                            <?php }else {
                                if (count($statuses) > $counter) { ?>
                                    <li class="order-status__item <?php echo ($status['active']) ? 'order-status__item--completed' : '' ?>">
                                        <?php echo $status['name']; ?>
                                    </li>
                                <?php }else { ?>
                                    <li class="order-status__item <?php echo ($status['active']) ? 'order-status__item--completed' : '' ?> order-status__item--delivered">
                                        <?php echo $status['name']; ?>
                                        <svg class="order-status__icon" width="24" height="24" fill="#E1E3E6">
                                            <use href="#icon-check-circle-stroke"></use>
                                        </svg>
                                    </li>
                                <?php }
                            }

                            $counter++;
                        } ?>
                    </ul>
                    <p class="body-order-item__info"><?php echo _e('We will send you notifications prior order status change.', 'order-thank-you'); ?></p>
                </div>
        <?php } ?>

<!--    commented out 25-10-2020    -->
<!--        --><?php //
//        $future_orders = $order->get_order_variations();
//        $next_delivery_date = clone $next_delivery;
//        foreach ( $future_orders as $week => $future_items ) {
//
//            if ( $week > 0 ) {
//                $next_delivery_date->modify( "+1weeks" );
//            }
//            ?>
<!---->
<!--            <li class="order-list__item order-item">    -->
<!--                <div class="order-item__top order-item-top">-->
<!--                    <div class="order-item-top__col order-item-top-left">-->
<!--                        <div class="order-item-top-left__head">-->
<!--                            <p class="order-item-top-left__number">-->
<!--                                --><?php //echo esc_html( $object_title ); ?><!--: №--><?php //echo $order->get_order_number(); ?>
<!--                            </p>-->
<!--                            <span class="order-item-top-left__pill pill pill--no-icon pill--small pill--bg--warning--light">Opened</span>-->
<!--                        </div>-->
<!--                        <div class="order-item-products">-->
<!--                            -->
<!--                            --><?php
//                            $variation_cats_options = carbon_get_theme_option('op_shop_variationcat');
//                            $variation_cats_ids = array_column( $variation_cats_options, 'id' );
//                            foreach ( $order_items_n_categories as $category_id => $order_category_data ) {
//
//                                $future_order_products = array_filter( $order_category_data['products'], function($item) use($future_items){
//                                    return in_array( $item->get_id() , $future_items );
//                                } );
//                                ?>
<!---->
<!--                                <ul class="order-item-products__list meals-list--><?php //echo !in_array( $category_id, $variation_cats_ids ) ? '-2' : ''; ?><!--">-->
<!---->
<!--                                --><?php //
//                                foreach ( $future_order_products as $count => $cart_product ) {
//
//                                    for ( $i=0; $i < 1;/*$cart_product->get_quantity()*/ $i++ ) {
//                                    ?>
<!---->
<!--                                        --><?php //if ( in_array( $category_id, $variation_cats_ids ) || $cart_product->get_quantity() > 1 ) { ?>
<!--                                            <li class="meals-list__item">-->
<!--                                        --><?php //} else { ?>
<!--                                            <li class="meals-list-2__item">-->
<!--                                        --><?php //} ?>
<!--                                                <picture>-->
<!--                                                    --><?php //echo $cart_product->get_product()->get_image( 'woocommerce_thumbnail', ['class'=>'meals-list-2__img'] ); ?>
<!--                                                </picture>-->
<!--                                            </li>-->
<!---->
<!--                                    --><?php //
//                                    }
//
//                                    if ( $count > 0 ) break;
//                                }
//                                ?>
<!---->
<!--                                </ul>-->
<!---->
<!--                            --><?php //} ?>
<!---->
<!--                            --><?php //if ( $cart_product->get_quantity() > 6 ) { ?>
<!--                                <span class="order-item-products__other">+--><?php //echo $cart_product->get_quantity() - 6; ?><!--</span>-->
<!--                            --><?php //} ?>
<!---->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="order-item-top__col">-->
<!--                        <p class="order-item-top__term">Shipps on:</p>-->
<!--                        <p class="order-item-top__value">--><?php //echo esc_html( $next_delivery_date->format("l, F j, Y") ) ?><!--</p>-->
<!--                    </div>-->
<!--                    <div class="order-item-top__col">-->
<!--                        <p class="order-item-top__term">Total:</p>-->
<!--                        <p class="order-item-top__value">--><?php //echo wc_price( $order->get_total() ); ?><!--</p>-->
<!--                    </div>-->
<!--                    <div class="order-item-top__col">-->
<!--                        <a href="--><?php //echo esc_url( $order->get_view_order_url() ); ?><!--" class="order-item-top__button button button--small">Manage Order</a>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <ul class="order-item__status order-status">-->
<!--                    <li class="order-status__item order-status__item--open order-status__item--completed">-->
<!--                        <svg class="order-status__icon" width="24" height="24" fill="#E1E3E6">-->
<!--                            <use href="#icon-box-2"></use>-->
<!--                        </svg>-->
<!--                        Planned-->
<!--                    </li>-->
<!--                    <li class="order-status__item">Open</li>-->
<!--                    <li class="order-status__item">Processing</li>-->
<!--                    <li class="order-status__item">Processing</li>-->
<!--                    <li class="order-status__item">Shipped</li>-->
<!--                    <li class="order-status__item order-status__item--delivered">-->
<!--                        Delivered-->
<!--                        <svg class="order-status__icon" width="24" height="24" fill="#E1E3E6">-->
<!--                            <use href="#icon-check-circle-stroke"></use>-->
<!--                        </svg>-->
<!--                    </li>-->
<!--                </ul>-->
<!--                <p class="order-item__info">We will send you notifications prior order status change.</p>-->
<!--            </li>-->
<!---->
<!--        --><?php //} ?>

    </ul>
</section>