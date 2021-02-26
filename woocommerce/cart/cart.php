<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.8.0
 */

defined('ABSPATH') || exit;

$cur_user_id = get_current_user_id();
$first_name = get_user_meta($cur_user_id, 'billing_first_name', true);
$subscribe_status = op_help()->subscriptions->get_subscribe_status();

if (!empty($first_name)) {
    $first_name .= '\'s';
}

// do_action( 'woocommerce_before_cart' );
?>

<main class="site-main cart-main">
    <section class="cart">
        <div class="container">
	        <?php if ( $subscribe_status['label'] == 'locked' ) {
		        $delivery_date = op_help()->subscriptions->get_delivery_date();
		        $now = new DateTime();
		        $dif = $now->diff( $delivery_date );
		        $dif_d = $dif->format( '%a' );
		        $dif_h = $dif->format( '%h' );
	            ?>
                <div class="cart__head cart__head--centered content">
                    <h1>Feel excited! Your order is comming out!</h1>
                    <p>
                        We are preparing things for you. You will get a notice when your order is shipped<br>
                        and you will be able to plan your next order. Come back in <?php if ( $dif_d ) { echo ( $dif_d > 1 ) ? "$dif_d days " : "$dif_d day "; } echo ( $dif_h > 1 OR $dif_h == 0 ) ? "$dif_h hours" : "$dif_h hour"; ?> to start
                        working on your next week order.
                    </p>
                    <p>Below are items that are going to be shipped to you:</p>
                </div>
            <?php } else { ?>
                <div class="cart__head <?php echo ( op_help()->tutorial->tutorial_page_name() !== '' ) ? 'cart__head--flex' : ''; ?>">
                    <h1 class="cart__title h2"><?php echo (!empty($first_name)) ? $first_name : 'Your'; ?> Order</h1>
                    <?php if ( op_help()->tutorial->tutorial_page_name() !== '' ) { ?>
                        <button class="cart__start-tutorial control-button control-button--no-txt js-start-<?php echo op_help()->tutorial->tutorial_page_name(); ?>-tutorial" data-tippy-content="Start Tutorial" aria-expanded="false">
                            <svg class="control-button__icon" width="24" height="24" fill="#252728">
                                <use href="#icon-question"></use>
                            </svg>
                        </button>
                    <?php } ?>
                </div>
            <?php } ?>
            <div class="cart__row">
                <div class="cart__col">
                    <form class="cart__form woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>"
                          method="post">

                        <?php do_action('woocommerce_before_cart_table'); ?>

                        <?php do_action('woocommerce_before_cart_contents'); ?>

                        <?php
                        $exclude_cats = op_help()->settings->excluded_cats();

                        $prod_cat_args = [
                            'taxonomy' => 'product_cat',
                            'orderby' => 'id',
                            'hide_empty' => false,
                            'parent' => 0,
                        ];

                        if (!empty($exclude_cats)) {
                            $prod_cat_args['exclude'] = $exclude_cats;
                        }

                        $variation_ids = [];
                        $woo_categories = get_categories($prod_cat_args);

                        $shop_variationcat = carbon_get_theme_option("op_shop_variationcat");
                        foreach ((array)$shop_variationcat as $item) {
                            $variation_ids[] = $item['id'];
                        }
                        ?>

                        <ul class="cart__shop-list shop-list">

                            <?php
                            foreach ($woo_categories as $woo_cat) {
                            //TODO after refactoring Staples->Groceries remove hardcoded cat_name
                            if ($woo_cat->name === 'Staples') {
                                $woo_cat->name = 'Groceries';
                            }
                            if (carbon_get_term_meta($woo_cat->term_id, "sf_separeted")) {
                            ?>
                            </ul>
                            <ul class="cart__shop-list shop-list">
                            <?php
                            }

                            $new_products = op_help()->subscriptions->sort_items_by_category(WC()->cart->get_cart(), $woo_cat);
                            $subscription_category_has_errors = op_help()->subscriptions->check_category_for_allowed_item_numbers($new_products, $woo_cat, false, 'short');

                            if (empty($new_products) || is_array($subscription_category_has_errors)) {
                                $show_link_to_category = true;
                            } else {
                                $show_link_to_category = false;
                            }
                            if (!empty($new_products)) {
                                $product_link = $woo_cat->name;
                            } else{
                                //TODO after refactoring Staples->Groceries remove hardcoded cat_name
                                if ($woo_cat->name === 'Groceries') {
                                    $product_link = '<a href="'.get_site_url().'/groceries">'.$woo_cat->name.'</a>';
                                } else {
                                    $product_link = '<a href="'.get_term_link($woo_cat).'">'.$woo_cat->name.'</a>';
                                }
                            }
                            ?>

                            <li class="shop-list__item <?php echo ( is_array($subscription_category_has_errors) ) ? 'shop-list__item--warning' : ''; ?>">
                                <div class="shop-list__head head-shop-list">
                                    <div class="head-shop-list__top">
                                        <p class="head-shop-list__title"><?php echo $product_link; ?></p>

                                        <?php if (empty ($new_products)) { ?>

                                            <div class="accordion-header__message message message--inline message--gray">
                                                <svg class="message__icon" width="20" height="20" fill="#e1e3e6">
                                                    <use xlink:href="#icon-error"></use>
                                                </svg>
                                                <p class="message__txt"><?php echo $product_link; ?></p>
                                            </div>

                                        <?php } else { ?>
                                            <?php
                                            if (!is_array($subscription_category_has_errors)) {
                                                if (!empty($subscription_category_has_errors)) {
                                                    ?>
                                                    <!-- TODO: green -->
                                                    <span class="head-shop-list__counter js-head-shop-list__counter--exist pill pill--bg--main-light">
                                                        <svg class="pill__icon" width="24" height="24" fill="#fff">
                                                            <use href="#icon-check-circle-stroke"></use>
                                                        </svg>
                                                        <?php echo $subscription_category_has_errors . ' of ' . $subscription_category_has_errors . ' selected'; ?>
                                                    </span>
                                                    <span style="display: none"
                                                          id="<?php echo strtolower($woo_cat->name); ?>-max-count"><?php echo $subscription_category_has_errors ?></span>
                                                    <?php
                                                }
                                            } else {
                                                ?>
                                                <!-- TODO: red -->
                                                <span class="head-shop-list__counter pill pill--bg--warning">
                                                        <svg class="pill__icon" width="24" height="24" fill="#fff">
                                                            <use href="#icon-check-circle-stroke"></use>
                                                        </svg>
                                                        <?php echo $subscription_category_has_errors['count']['current'] . ' of ' . $subscription_category_has_errors['count']['max'] . ' selected'; ?>
                                                     <span style="display: none"
                                                           id="<?php echo strtolower($woo_cat->name); ?>-max-count"><?php echo $subscription_category_has_errors['count']['max'] ?></span>
                                                    </span>
                                                <?php
                                            }
                                        }

                                        $single_items_frequency = carbon_get_term_meta($woo_cat->term_id, 'op_categories_subscription_frequency_by_item');
                                        $list_frequency = carbon_get_term_meta($woo_cat->term_id, 'op_categories_subscription_frequency');

                                        if (!$single_items_frequency) {
                                            if (count($list_frequency) > 1) {
                                                $chosen_frequency = op_help()->shop->get_session_cart('frequency_' . $woo_cat->term_id, $list_frequency[0]);
                                                ?>
                                                <label class="order-footer__delivery select select--rounded-corners">
                                                    <span>Delivery:</span>
                                                    <select class="select__field ajax_change_cart_frequency"
                                                            autocomplete="off"
                                                            data-key="<?php echo esc_attr('frequency_' . $woo_cat->term_id); ?>">
                                                        <?php
                                                        foreach ($list_frequency as $frequency_val) {
                                                            $selected = $chosen_frequency == $frequency_value ? 'selected' : '';
                                                            ?>
                                                            <option value="<?php echo esc_attr($frequency_val); ?>" <?php echo esc_attr($selected); ?>><?php echo esc_html(str_replace('_', ' ', $frequency_val)); ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </label>
                                                <?php
                                            } else {
                                                op_help()->shop->set_session_cart('frequency_' . $woo_cat->term_id, $list_frequency[0]);
                                            }
                                        } ?>
                                    </div>
                                    <?php
                                    if (is_array($subscription_category_has_errors)) {
                                        foreach ($subscription_category_has_errors['errors'] as $key => $error_item) {
                                            ?>
                                            <p class="head-shop-list__notice"><a href="/product-category/meals/"><?php echo $error_item; ?></a></p>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <div id="<?php echo strtolower($woo_cat->name) . '-category-list' ?>"
                                     class="shop-list__content" <?php echo !empty ($new_products) ? 'style="display: block;"' : ''; ?>>
                                    <?php

                                    if (!empty ($new_products)) {

                                        wc_get_template(
                                            'cart/cart-item-data.php',
                                            [
                                                'products' => $new_products,
                                                'has_frequency' => $single_items_frequency,
                                                'list_frequency' => $list_frequency,
                                            ]
                                        );

                                    }
                                    ?>

                                </div>

                            </li>

                            <?php } ?>

                        </ul>

                        <?php do_action('woocommerce_after_cart_table'); ?>

                        <button style="display: none" class="sf_update_cart" type="submit" class="button"
                                name="update_cart"
                                value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>"><?php esc_html_e('Update cart', 'woocommerce'); ?></button>
                        <?php do_action('woocommerce_cart_actions'); ?>
                        <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>

                    </form>

                </div>

                <?php wc_get_template('cart/cart-totals.php'); ?>

            </div>
        </div>
        <?php get_template_part('template-parts/modals/meals-filled', '', []); ?>
    </section>

</main>

