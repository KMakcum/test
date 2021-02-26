<?php 

$order_status = $order->get_status();

$object_title = in_array( $order_status, ['op-subscription','op-paused'] ) ? 'Subscription' : 'Order';
$order_canbe_paused = in_array( $order_status, ['op-subscription','op-paused'] ) ? true : false;
$object_status = 'Open';
$order_paused = '';
if( $order_status == 'op-paused' ){
  $order_paused =  'checked';
  $object_status = 'Paused';
}


?>

<div class="delivery-slider__order order-box">
  <div class="order-box__head head-order-box">
      <ul class="head-order-box__info order-info">
          <li class="order-info__item">
            <a href="<?php echo esc_url( $order->get_view_order_url() );  ?>">
              <?php echo esc_html( $object_title ); ?>: #<?php echo $order->get_order_number(); ?>
            </a>
          </li>
          <li class="order-info__item status status--open"><?php echo esc_html( $object_status ); ?> (3 days left)</li><!-- / .status -->
          <?php if( $order_canbe_paused ){ ?>
          <label class="order-footer__control-button control-button" type="button" tabindex="0">
             <input style="min-width: 16px;" type="checkbox" name="order_paused" <?php echo esc_attr( $order_paused ); ?>>
              <svg class="control-button__icon" width="20" height="20" fill="#87898C">
                  <use xlink:href="#icon-pause"></use>
              </svg>
              Pause
          </label>
          <?php } ?>
      </ul><!-- / .order-info -->
  </div><!-- / .head-order-box -->
  <div class="order-box__body order order--has-footer">
      <ul class="order__accordion accordion">
          <?php

          if( method_exists( $order, 'get_items_n_categories' ) ){ 

            $order_items_n_categories = $order->get_items_n_categories();

          } else {
            
            $order_items_n_categories = op_help()->subscriptions->get_items_n_categories_for_order( $order );

          }
          
          foreach ( $order_items_n_categories as $category_id => $order_category_data) {
            
            $items_frequency = carbon_get_term_meta( $order_category_data['category']->term_id, 'op_categories_subscription_frequency_by_item' );
            $list_frequency = carbon_get_term_meta( $order_category_data['category']->term_id, 'op_categories_subscription_frequency' );
            $pause_options = carbon_get_term_meta( $order_category_data['category']->term_id, 'op_categories_subscription_pause' );

            $pause_items = false;
            $pause_category = false;
            if( $pause_options == 'pause-items' ){
              $pause_items = true;
              $pause_category = true;
            } elseif( $pause_options == 'pause-category' ){
              $pause_category = true;
            }
            

            if( !empty( $order_category_data['products'] ) ){
            ?>
            <li class="accordion__item">
              <div class="accordion__header accordion-header">
                  <div class="accordion-header__box">
                      <p class="accordion-header__title"><?php echo $order_category_data['category']->name; ?></p>
                      <p class="accordion-header__quantity"><?php echo count( $order_category_data['products'] ) . ' ' . $order_category_data['category']->name; ?></p>
                      <div class="accordion-header__message message message--inline message--green">
                          <svg class="message__icon" width="20" height="20" fill="#30be30">
                              <use xlink:href="#icon-check-circle"></use>
                          </svg>
                      </div><!-- / .message -->
                      <?php if( $order_canbe_paused && $pause_category ){ ?>
                      <label class="order-footer__control-button control-button" type="button" tabindex="0">
                          <?php $is_paused = $order->get_meta('op_paused')[ $order_category_data['category']->term_id ] == 'on' ? 'checked' : ''; ?>
                          <input style="min-width: 16px;" type="checkbox" name="paused[<?php echo esc_attr( $order_category_data['category']->term_id ); ?>][all]" <?php echo esc_attr( $is_paused ); ?>>
                          <svg class="control-button__icon" width="20" height="20" fill="#87898C">
                              <use xlink:href="#icon-pause"></use>
                          </svg>
                          Pause
                      </label>
                      <?php } ?>
                      <p class="accordion-header__price"><?php echo $order_category_data['total']; ?></p>
                  </div>
              </div><!-- / .accordion-header -->
              <div class="accordion__content">
                  <table class="product-table product-table--meal">
                      <tbody>
                          <?php 

                          
    

                          foreach( $order_category_data['products'] as $cart_product ){

                            wc_get_template( 'myaccount/order-box-item.php', [
                              'order' => $order,
                              'can_pause' => $pause_items,
                              'order_canbe_paused' => $order_canbe_paused,
                              'item' => $cart_product,
                              'category' => $order_category_data['category'],
                              'has_frequency' => $items_frequency,
                              'frequency' => $list_frequency
                            ] );

                          } ?>
                      </tbody>
                  </table><!-- / .product-table -->
                  
      <?php 
      if( !$items_frequency ){ 
        if( count( $list_frequency ) > 1 ){
        ?>
      <div class="order__footer order-footer order-footer--bg-white">
          <label class="order-footer__delivery select select--rounded-corners">
              <span>Delivery:</span>
              <select class="select__field" name="frequency[<?php echo esc_attr( $order_category_data['category']->term_id ); ?>][all]"><?php
              foreach ( $list_frequency as $frequency_value) {
                $selected = $order->get_meta('op_frequency')[ $order_category_data['category']->term_id ] == $frequency_value ? 'selected' : '';
                echo '<option value="'.esc_attr( $frequency_value ).'" '.$selected.'>'.esc_html( $frequency_value ).'</option>';
              } ?>
              </select>
          </label><!-- / .select -->
      </div>
      <?php
        } else {
          ?>
          <input type="hidden" name="frequency[<?php echo esc_attr( $order_category_data['category']->term_id ); ?>][all]" value="<?php echo esc_attr( $list_frequency[0] ); ?>">
          <?php
        }
      }
      ?>
              </div>
          </li>
          <?php }
          } ?>
      </ul><!-- / .accordion -->
      <div class="order__footer order-footer">
          <div class="order-footer__col">
              <?php $next_delivery = new DateTime( $order->get_meta( 'op_next_delivery' ) ); ?>
              <p class="order-footer__info-order">Next delivery will be at <strong><?php echo $next_delivery->format("D, M d"); ?></strong><br></p>
              <p class="order-footer__info-order">Expected delivery: <strong><?php echo $next_delivery->format("D, M d"); ?></strong></p>
          </div>
          <div class="order-footer__col">
              <p class="order-footer__shipping"><span>Shipping</span> <span>Free</span></p>
              <p class="order-footer__total"><span>TOTAL</span> <span><?php echo $order->get_total(); ?></span></p>
          </div>
      </div><!-- / .order-footer -->
  </div><!-- / .order -->
  <small class="order-box__credits">Taxes are not included</small>

  <?php if( $order->has_future_modifications() ){ ?>
  <p>You have modified future orders for this subscription.<br>After update this changes will be deleted!</p>
  <?php } ?>
  
  <div class="fields-list__item field-box">
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
  <p class='test-result'></p>
  <button class="order-content__button button button--small" type="submit">Update</button>
</div><!-- / .order-box -->