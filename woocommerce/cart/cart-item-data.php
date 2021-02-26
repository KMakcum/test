<?php
/**
 * Cart item data (when outputting non-flat)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-item-data.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce/Templates
 * @version     2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$subscribe_status = op_help()->subscriptions->get_subscribe_status();
?>

<table class="shop-list__product-table product-table product-table--meal">
    <tbody>
	<?php
    if ( !empty( $products ) ) {
        foreach ( $products as $cart_product ) {
            $_product = $cart_product['data'];
            $product_permalink = apply_filters( 'woocommerce_loop_product_link', $_product->is_visible() ? $_product->get_permalink( $cart_product ) : '', $_product );
            ?>

            <tr class="product-table__item" data-item-key="<?php echo esc_html( $cart_product['key'] ); ?>">
                <td class="product-table__thumbnail">
                    <a href="<?php echo $product_permalink; ?>">
                        <picture>
                            <?php echo $_product->get_image( 'woocommerce_thumbnail' ); ?>
                        </picture>
                    </a>
                </td>
                <td class="product-table__info">
                    <a class="product-table__name" href="<?php echo $product_permalink; ?>">
                        <?php echo get_the_title( $_product->get_id() ); ?>
                    </a>
                    <!-- <span class="product-table__add-info"><?php // echo $_product->get_weight(); ?> g</span> -->
                    <span class="product-table__price"><?php echo wc_price( $_product->get_price() ); ?></span>
                    <?php
                    if ( $has_frequency ) {
                        if ( count( $list_frequency ) > 1 ) {
                            $chosen_frequency = op_help()->shop->get_session_cart( 'frequency_item_' . $_product->get_id(), $list_frequency[0] );
                            ?>
                            <label class="product-table__delivery select select--rounded-corners">
                                <select
                                    class="select__field ajax_change_cart_frequency"
                                    autocomplete="off"
                                    <?php echo ( $subscribe_status['label'] == 'locked' ) ? 'disabled': ''; ?>
                                    data-key="<?php echo esc_attr( 'frequency_item_' . $_product->get_id() ); ?>" ><?php
                                    foreach ( $list_frequency as $frequency_value ) {
                                        $selected = $chosen_frequency == $frequency_value ? 'selected' : '';
                                        if ( $subscribe_status['label'] == 'locked' AND !$selected ) {
                                            continue;
                                        }
                                        echo '<option value="'.esc_attr( $frequency_value ).'" '.esc_attr($selected).'>'.esc_html( str_replace( '_', ' ', $frequency_value ) ).'</option>';
                                    }
                                    ?>
                                </select>
                            </label>
                            <?php
                        } else {
                            op_help()->shop->set_session_cart( 'frequency_item_' . $_product->get_id(), $list_frequency[0] );
                        }
                    }
                    ?>
                </td>

                <?php if ( $subscribe_status['label'] == 'locked' ) { ?>
                    <td class="product-table__price-cell"><?php echo $cart_product['quantity']; ?> x <?php echo wc_price( $_product->get_price() ); ?></td>
                <?php } else { ?>
                    <td class="product-table__quantity">
                        <?php do_action( 'woocommerce_before_quantity_input_field' ); ?>
                        <input
                            type="number"
                            min="0"
                            class="nice-number__field js-nice-number qty"
                            value="<?php echo esc_attr( $cart_product['quantity'] ); ?>"
                            name="cart[<?php echo esc_attr( $cart_product['key'] ); ?>][qty]"
                        >
                        <?php do_action( 'woocommerce_after_quantity_input_field' ); ?>
                    </td>
                <?php } ?>
            </tr>
            <?php
        }
    }
	?>
    </tbody>
</table>