<?php


      // op_categories_subscription_frequency_by_item
      // op_categories_subscription_frequency
      
    $_product = $item->get_product();
    
    $product_permalink = apply_filters( 'woocommerce_loop_product_link', get_the_permalink( $_product->get_id() ), $_product );

?>
<tr class="product-table__item">
    <td class="product-table__thumbnail">
        <a href="<?php echo $product_permalink; ?>">
            <picture>
              <!-- <source srcset="img/base/my-meals-1.webp" type="image/webp"> -->
              <?php echo $_product->get_image( 'woocommerce_thumbnail' ); ?>
            </picture>
        </a>
    </td>
    <td class="product-table__info">
        <a class="product-table__name" href="<?php echo $product_permalink; ?>"><?php echo get_the_title( $_product->get_id() ); ?></a>
        <span class="product-table__add-info"><?php echo esc_html($_product->get_weight()); ?> g</span>
        <span class="product-table__price"><?php echo get_woocommerce_currency_symbol(); ?> <?php echo $item->get_total(); ?></span>
    </td>
    <?php if( !empty( $has_frequency ) ){ 
        if( count( $frequency ) > 1 ){ ?>
    <td class="product-table__delivery">
        <label class="select select--rounded-corners">
            <select class="select__field" name="frequency[<?php echo esc_attr( $category->term_id ); ?>][<?php echo esc_attr( $item->get_id() ); ?>]"><?php
            foreach ( $frequency as $frequency_value) {
                $chosen = $item->get_meta('op_frequency') === $frequency_value ? 'selected' : '';
                echo '<option value="'.esc_attr( $frequency_value ).'"  '.$chosen.'>'.esc_html( $frequency_value ).'</option>';
            } ?>
            </select>
        </label><!-- / .select -->
    </td>

    <?php } else { ?>
        <input type="hidden" name="frequency[<?php echo esc_attr( $category->term_id ); ?>][<?php echo esc_attr( $item->get_id() ); ?>]" value="<?php echo esc_attr( $frequency[0] ); ?>">
    <?php } 

    } 
    
    if( $_product->is_sold_individually() ){
        if( $order_canbe_paused && $can_pause ){
    ?>
    <td class="product-table__remove-2">
        <label class="order-footer__control-button control-button" type="button" tabindex="0">
            <?php $is_paused = $item->get_meta('op_paused') == 'on' ? 'checked' : ''; ?>
            <input type="checkbox" name="paused[<?php echo esc_attr( $category->term_id ); ?>][<?php echo esc_attr( $item->get_id() ); ?>]" <?php echo esc_attr( $is_paused ); ?>>
            <svg class="control-button__icon" width="20" height="20" fill="#87898C">
                <use xlink:href="#icon-pause"></use>
            </svg>
            Pause
        </label>
    </td>
    <?php 
        }
    } else { 
    ?>
    <td class="product-table__quantity-and-remove">
        <div class="nice-number"><input class="nice-number__field js-nice-number" type="number" value="1" min="1" name="quantity[<?php echo esc_attr( $item->get_id() ); ?>]"></div>
        <?php if( $order_canbe_paused && $can_pause ){ ?>
        <label class="order-footer__control-button control-button" type="button" tabindex="0">
            <?php $is_paused = $item->get_meta('op_paused') == 'on' ? 'checked' : ''; ?>
            <input type="checkbox" name="paused[<?php echo esc_attr( $category->term_id ); ?>][<?php echo esc_attr( $item->get_id() ); ?>]" <?php echo esc_attr( $is_paused ); ?>>
            <svg class="control-button__icon" width="20" height="20" fill="#87898C">
                <use xlink:href="#icon-pause"></use>
            </svg>
            Pause
        </label>
        <?php } ?>
    </td>
    <?php } ?>
</tr>