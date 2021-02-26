<?php
$status = $order->get_status(); 
$items_by_category = op_help()->subscriptions->get_items_n_categories_for_order( $order );
?>

<div class="delivery-slider__order order-box">
  <div class="order-box__head head-order-box">
      <ul class="head-order-box__info order-info">
          <li class="order-info__item">Order: #<?php echo esc_html( $order->get_id() ); ?></li>
          <li class="order-info__item status status--open"><?php echo $status; ?> (<?php echo $order->get_date_modified()->format('d M Y') ?>)</li><!-- / .status -->
      </ul><!-- / .order-info -->
  </div><!-- / .head-order-box -->
  <div class="order-box__body order order--has-footer">
      <ul class="order__accordion accordion">
        <?php foreach ( $items_by_category as $category_key => $category_data ) { 
          if( !empty( $category_data['products'] ) ) { ?>
        <li class="accordion__item">
            <div class="accordion__header accordion-header">
                <div class="accordion-header__box">
                    <p class="accordion-header__title"><?php echo $category_data['category']->name; ?></p>
                    <p class="accordion-header__quantity"><?php echo count( $category_data['products'] ) . ' ' . $category_data['category']->name; ?></p>
                </div>
            </div><!-- / .accordion-header -->
            <div class="accordion__content">
                <table class="product-table product-table--meal">
                    <tbody>
                      <?php
                        foreach( $category_data['products'] as $cart_product ){

                          wc_get_template( 'myaccount/order-box-item.php', [
                            'order' => $order,
                            'can_pause' => false,
                            'order_canbe_paused' => false,
                            'item' => $cart_product,
                            'category' => $category_data['category'],
                            'has_frequency' => false,
                            'frequency' => []
                          ] );

                        } ?>
                    </tbody>
                </table><!-- / .product-table -->
            </div>
        </li>
        <?php } 
        } ?>
      </ul><!-- / .accordion -->
      <div class="order__footer order-footer">
          <div class="order-footer__col">
              <p class="order-footer__info-order">Your order will be shipped at <strong>Wed, Aug 02</strong><br></p>
              <p class="order-footer__info-order">Expected delivery: <strong>same day</strong></p>
          </div>
          <div class="order-footer__col">
              <p class="order-footer__shipping"><span>Shipping</span> <span>Free</span></p>
              <p class="order-footer__total"><span>TOTAL</span> <span>$199.05</span></p>
          </div>
      </div><!-- / .order-footer -->
  </div><!-- / .order -->
  <small class="order-box__credits">Taxes are not included</small>
</div><!-- / .order-box -->