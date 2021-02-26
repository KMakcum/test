<?php

if( $order_paused == 'on' ){

  $status = 'Paused'; 

} else {

  $status = 'Planned'; 

}

$all_items_by_category = $order->get_items_n_categories();

$items_by_category = array_map( function( $category_data ) use($items){
  foreach ( $category_data['products'] as $key => $cat_item ) {
    if( !in_array( $cat_item->get_id(), $items ) ){
      unset( $category_data['products'][$key] );
    }
  }
  return $category_data;
}, $all_items_by_category );
$order_creating = clone $order_delivery;
$order_creating->modify("-2 days");
?>
<div class="delivery-slider__order order-box">
  <div class="order-box__head head-order-box">
      <ul class="head-order-box__info order-info">
          <li class="order-info__item">Future order</li>
          <li class="order-info__item status status--open"><?php echo $status; ?> (<?php echo $order_creating->format("d M Y"); ?>)</li><!-- / .status -->

          <label class="order-footer__control-button control-button" type="button" tabindex="0">
              <?php $is_paused = $order_paused == 'on' ? 'checked' : ''; ?>
              <input style="min-width: 16px;" type="checkbox" name="op_order_pause" <?php echo esc_attr( $is_paused ); ?>>
              <svg class="control-button__icon" width="20" height="20" fill="#87898C">
                  <use xlink:href="#icon-pause"></use>
              </svg>
              Pause
          </label>
      </ul><!-- / .order-info -->
  </div><!-- / .head-order-box -->
  <div class="order-box__body order order--has-footer">
      <ul class="order__accordion accordion">
        <?php 

        $order_total = 0;

        foreach ( $items_by_category as $category_key => $category_data ) { 
          if( !empty( $category_data['products'] ) ) { 
            
            $category_total = 0;

            foreach( $category_data['products'] as $cart_product ){

              $category_total += floatval( $cart_product->get_total() );
              
            }

            $order_total += $category_total;
            
            ?>
        <li class="accordion__item">
            <div class="accordion__header accordion-header">
                <div class="accordion-header__box">
                    <p class="accordion-header__title"><?php echo $category_data['category']->name; ?></p>
                    <p class="accordion-header__quantity"><?php echo count( $category_data['products'] ) . ' ' . $category_data['category']->name; ?></p>

                    
                    <p class="accordion-header__price"><?php echo $category_total; ?></p>
                </div>
            </div><!-- / .accordion-header -->
            <div class="accordion__content">
                <table class="product-table product-table--meal">
                    <tbody>
                      <?php

                      foreach( $category_data['products'] as $cart_product ){;

                        if( !empty( $items_mutations[ $cart_product->get_id() ]['op_pause'] ) ){

                          $cart_product->update_meta_data( "op_pause", $items_mutations[ $cart_product->get_id() ]['op_pause'] );

                        } elseif( !isset( $items_mutations[ $cart_product->get_id() ]['op_pause'] ) ) {

                          $cart_product->delete_meta_data( "op_pause" );

                        }

                        if( !empty( $items_mutations[ $cart_product->get_id() ]['quantity'] ) ){

                          $cart_product->set_quantity( $items_mutations[ $cart_product->get_id() ]['quantity'] );
                          
                        }
                        

                        wc_get_template( 'myaccount/order-box-item.php', [
                          'order' => $order,
                          'can_pause' => true,
                          'order_canbe_paused' => true,
                          'item' => $cart_product,
                          'category' => $category_data['category'],
                          'has_frequency' => false,
                          'frequency' => []
                        ] );

                      } 
                      
                      ?>
                    </tbody>
                </table><!-- / .product-table -->
            </div>
        </li>
        <?php } 
        } ?>
      </ul><!-- / .accordion -->
      <div class="order__footer order-footer">
          <div class="order-footer__col">
              <p class="order-footer__info-order">Your order will be shipped at <strong><?php echo esc_html( $order_delivery->format("d M Y") ); ?></strong><br></p>
              <p class="order-footer__info-order">Expected delivery: <strong>same day</strong></p>
          </div>
          <div class="order-footer__col">
              <p class="order-footer__shipping"><span>Shipping</span> <span>Free</span></p>
              <p class="order-footer__total"><span>TOTAL</span> <span><?php echo $order_total; ?></span></p>
          </div>
      </div><!-- / .order-footer -->
  </div><!-- / .order -->
  <small class="order-box__credits">Taxes are not included</small>
  <p class='test-result'></p>
  <button class="order-content__button button button--small" type="submit">Update</button>
</div><!-- / .order-box -->