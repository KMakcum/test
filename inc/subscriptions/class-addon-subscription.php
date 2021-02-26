<?php

/**
 * Subscriptions.
 *
 * @class   SFAddonSubscription
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Class SFAddonSubscription
 */
class SFAddonSubscription {
    private static $_instance = null;
    private $woo_categories = [];

    /**
     * @var WC_Order
     */
    public static $current_subscription;

    /**
     * Cache of $subscribe_status after hook for frontend.
     *
     * @var array
     */
    private $subscribe_status = '';

    /**
     * @var bool
     */
    private $fill_cart_process = false;

    private $statuses = [
        'wc-op-subscription' => [
            'name'        => 'Subscription',
            'label_count' => 'Subscriptions (%s)',
            'class'       => 'open',
        ],
        'wc-op-incomplete'   => [
            'name'        => 'Incomplete',
            'label_count' => 'Incomplete subscriptions (%s)',
            'class'       => 'incomplete',
        ],
        'wc-op-paused'       => [
            'name'        => 'Subscription (Paused)',
            'label_count' => 'Paused (%s)',
            'class'       => 'paused',
        ],
        'wc-picked'          => [
            'name'        => 'Picked',
            'label_count' => 'Picked (%s)',
        ],
        'wc-packed'          => [
            'name'        => 'Packed',
            'label_count' => 'Packed (%s)',
        ],
        'wc-shipping'        => [
            'name'        => 'Shipping',
            'label_count' => 'Shipping (%s)',
        ],
        'wc-shipped'         => [
            'name'        => 'Shipped',
            'label_count' => 'Shipped (%s)',
        ],
        'wc-delivered'       => [
            'name'        => 'Delivered',
            'label_count' => 'Delivered (%s)',
        ],
    ];

    private function __construct() {
    }

    protected function __clone() {
    }

    /**
     * @return SFAddonSubscription
     */
    static public function getInstance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    function init() {
        include_once 'class-subscription.php';
        include_once 'class-checkout.php';
        include_once 'class-cron-pause-incomplete.php';

        if ( class_exists( 'SF_Subscription' ) ) {
            // Init hooks and cron for Incomplete and Pause statuses of subscribe-orders.
            $pause_incomplete = new \SF_Pause_Incomplete();
            $pause_incomplete->init();

            add_action( 'sf_add_theme_suboption', [ $this, 'add_settings_subpage' ] );
            add_action( 'carbon_fields_register_fields', [ $this, 'add_fields_to_categories' ], 20 );

            add_filter( 'woocommerce_order_class', [ $this, 'set_order_subscription_class' ], 20, 3 );
            add_filter( 'init', [ $this, 'register_subscription_status' ] );

            add_filter( 'wc_order_statuses', [ $this, 'add_subscription_status' ], 10, 1 );

            add_filter( 'woocommerce_default_order_status', [ $this, 'change_default_order_status' ], 10, 1 );

            add_filter( 'wc_order_is_editable', [ $this, 'do_subscription_editable' ], 10, 2 );

            add_filter( 'woocommerce_my_account_my_orders_query', [ $this, 'change_order_query' ], 10, 1 );


            add_filter( 'wp_ajax_op_update_subscription', [ $this, 'ajax_update_subscription' ] );
            add_filter( 'wp_ajax_nopriv_op_update_subscription', [ $this, 'ajax_update_subscription' ] );

            add_filter( 'wp_ajax_op_update_future_order', [ $this, 'ajax_update_future_order' ] );
            add_filter( 'wp_ajax_nopriv_op_update_future_order', [ $this, 'ajax_update_future_order' ] );


            add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart_before_checkout' ), 1 );

            add_action( 'woocommerce_checkout_create_order', array( $this, 'modify_cart_order_to_subscription' ), 10,
                2 );
            // mb should use more elegant solution???
            add_filter( 'woocommerce_cart_needs_payment', '__return_false' );

            // Create Order Processing by cron
            add_action( 'carbon_fields_theme_options_container_saved', [ $this, 'change_cron_settings' ], 99, 2 );
            add_action( 'op_subscription_order_creator', [ $this, 'cron_order_generator' ] );
            add_action( 'init', [ $this, 'check_cron_order_generator' ] );

            // Payment by hook change Order status
            add_action( "woocommerce_order_status_shipped", [ $this, 'payment_by_hook' ], 10, 2 );

            add_filter( 'wp_ajax_sf_checkout_test_step', [ $this, 'ajax_checkout_test_step' ] );
            add_filter( 'wp_ajax_nopriv_sf_checkout_test_step', [ $this, 'ajax_checkout_test_step' ] );

            add_filter( 'wp_ajax_sf_checkout_update_step', [ $this, 'update_checkout_subscription_data' ] );
            add_filter( 'wp_ajax_nopriv_sf_checkout_update_step', [ $this, 'update_checkout_subscription_data' ] );

            add_action( 'woocommerce_after_checkout_validation', [ $this, 'add_more_check_to_checkout' ], 10, 2 );

//          add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'change_max_product_count' ], 10, 3 );

            add_action( 'sf_woo_thankyou', [ $this, 'update_order_addresses' ], 10, 1 );

            if ( ! is_admin() ) {
                // body class with status of subscribe.
                add_action( 'body_class', [ $this, 'body_class_subscribe_status' ], 10, 1 );

                // Init cart after first purchase.
                add_action( 'wp_head', [ $this, 'init_cart_after_first_purchase' ], 10, 1 );

                // Init cart after first purchase.
                add_action( 'wp_head', [ $this, 'set_cache_subscribe_status' ], 100 );

                // On update Cart - update and check subscribe
                add_action( 'woocommerce_cart_updated', [ $this, 'update_subscribe' ], 10 );

                // On remove coupon
                add_action( 'woocommerce_removed_coupon', array( $this, 'remove_coupon' ), 10, 1 );

                // Applied coupon from Checkout
                add_action( 'woocommerce_applied_coupon', [ $this, 'on_update_coupon_from_checkout' ], 0, 1 );
            }

            // Remove clear cart on the Thank you page
            remove_action( 'get_header', 'wc_clear_cart_after_payment' );

            // Update Cart if needed (if flag _is_cart_updated)
            add_action( 'woocommerce_before_save_order_items', [ $this, 'on_update_order' ], 10, 1 );
            add_action( 'woocommerce_saved_order_items', [ $this, 'on_update_order' ], 10, 2 );

            // On change coupon in the admin panel
            add_action( 'wp_ajax_woocommerce_add_coupon_discount', [ $this, 'on_update_order_on_coupon' ], 0 );
            add_action( 'wp_ajax_woocommerce_remove_order_coupon', [ $this, 'on_update_order_on_coupon' ], 0 );

        } else {

            add_filter( 'sf_check_plugins', function () {
                return false;
            } );
            add_filter( 'sf_check_plugins_notices', function ( $notices ) {
                return array_merge( $notices, [ 'You must include OP_Subscription class' ] );
            } );
        }

        return $this;
    }

    /**
     * Update Cart - update and check subscribe.
     *
     * @param WC_Order|bool $order
     *
     * @throws Exception
     */
    function update_subscribe( $order = false ) {
        if ( ! $order ) {
            $order = $this->get_current_subscription();
            if ( $order === false ) {
                return;
            }
        }

        // if Cart empty after first purchase
        if ( ! get_post_meta( $order->get_ID(), '_is_cart_not_new', true ) ) {
            return;
        }

        // if Checkout and Thank you page
        if (
            ( is_page( 'checkout' ) OR strpos( $_SERVER['REQUEST_URI'], 'order-received' ) !== false OR isset( $_GET['wc-ajax'] ) )
            AND ! isset( $_GET['wc-ajax'] ) AND $_GET['wc-ajax'] !== 'apply_coupon'
        ) {
            $this->update_cart_from_order();

            return;
        }

        // if Cart changed in the admin Panel
        if ( ! get_post_meta( $order->get_ID(), '_is_cart_updated', true ) ) {
            $this->fill_cart_from_previous_order( $order, true );
        }

        $current_status = $this->get_subscribe_status();
        if ( $current_status['label'] == 'locked' ) {
            $this->update_cart_from_order();

            return;
        }

        // Sync subscribe items from cart
        $this->sync_subscribe_from_cart();

        if ( $current_status['label'] == 'wc-op-paused' ) {
            return;
        }

        $is_valid_cart = $this->is_valid_cart();
        if ( ! $is_valid_cart ) {
            if ( $current_status['label'] == 'wc-op-incomplete' ) {
                return;
            }
            $order->set_status( 'wc-op-incomplete', 'Order is set as incomplete.' );
            $order->save();
        } else {
            if ( $current_status['label'] == 'wc-op-subscription' ) {
                return;
            }
            $order->set_status( 'wc-op-subscription', 'Order is set as normal subscription.' );
            $order->save();
        }
    }

    /**
     * Возвращает текущий subscription order. Если не существует - false.
     *
     * @return WC_Order|bool
     */
    function get_current_subscription() {
        global $wpdb;

        $user_id = get_current_user_id();

        if ( ! $user_id ) {
            return false;
        }

        // Return cache
        if ( method_exists( self::$current_subscription, 'get_id' ) ) {
            return self::$current_subscription;
        }

        $subscription = $wpdb->get_row( $wpdb->prepare(
            "SELECT ID, post_status FROM {$wpdb->posts} as p LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id WHERE (post_status='wc-op-subscription' OR post_status='wc-op-incomplete' OR post_status='wc-op-paused') AND pm.meta_key = '_customer_user' AND pm.meta_value= %s ORDER BY ID desc",
            $user_id
        ) );

        if ( ! $subscription ) {
            return false;
        }

        self::$current_subscription = wc_get_order( $subscription->ID );

        return self::$current_subscription;
    }

    /**
     * Return subscribe status in array[class, name, label].
     *
     * @param WC_Order|bool $order
     *
     * @return array
     */
    function get_subscribe_status( $order = false ) {
        if ( $this->subscribe_status ) {
            return $this->subscribe_status;
        }

        if ( ! $order ) {
            $order = $this->get_current_subscription();
        }

        $classes = [];
        if ( $order === false ) {
            $classes['class'] = 'none-subscribe';
            $classes['name']  = 'none-subscribe';
            $classes['label'] = 'none-subscribe';
        } else {
            $order_status = $this->get_post_status( $order->get_id() );
            if ( $this->is_lock( $order ) and $order_status != 'wc-op-incomplete' ) {
                $classes['label'] = 'locked';
                $classes['class'] = 'locked';
                $classes['name']  = 'locked';
            } else {
                $classes['label'] = $order_status;
                $classes['class'] = $this->statuses[ $order_status ]['class'];
                $classes['name']  = $this->statuses[ $order_status ]['name'];
            }
        }

        return $classes;
    }

    /**
     * Set session frequency for products in cart from order.
     *
     * @param WC_Order|bool $order
     */
    function set_session_frequency_from_order( $order = false ) {
        global $_SESSION;

        if ( ! $order ) {
            $order = $this->get_current_subscription();
            if ( $order === false ) {
                return;
            }
        }

        // clear $_SESSION
        foreach ( $_SESSION as $key => $item ) {
            if ( strpos( $key, 'sf_cart_' ) !== false ) {
                unset( $_SESSION[ $key ] );
            }
        }

        // set $_SESSION
        foreach ( $order->get_items() as $order_item ) {
            $product = $order_item->get_product();
            /** @var WC_Product $product */
            op_help()->shop->set_session_cart( 'frequency_item_' . $product->get_id(), $order_item->get_meta( 'op_frequency' ) );
        }
    }

    /**
     * Set cache of subscribe status.
     */
    function set_cache_subscribe_status() {
        $this->subscribe_status = $this->get_subscribe_status();
    }

    /**
     * Return post_status by id.
     *
     * @param int $post_id
     *
     * @return string
     */
    function get_post_status( $post_id ) {
        global $wpdb;

        return $wpdb->get_var( $wpdb->prepare(
            "SELECT post_status FROM {$wpdb->posts} WHERE ID = %s",
            $post_id
        ) );
    }

    /**
     * Update Cart items from Order.
     *
     * @param WC_Order|bool $order
     * @param bool $force_update
     *
     * @return void
     * @throws Exception
     */
    public function fill_cart_from_previous_order( $order = false, $force_update = false ) {
        if ( $this->fill_cart_process ) {
            return;
        }

        if ( ! $order ) {
            $order = $this->get_current_subscription();
            if ( $order === false ) {
                return;
            }
        }

        $subscription    = $order;
        $is_cart_updated = false;
        if ( ! $force_update ) {
            $is_cart_updated = get_post_meta( $subscription->get_ID(), '_is_cart_updated', true );
        }

        if ( ! $is_cart_updated OR $force_update ) {
            if ( WC()->cart ) {
                $this->fill_cart_process = true;
                update_post_meta( $subscription->get_ID(), '_is_cart_updated', '1' );

                $items = $subscription->get_items();
                if ( ! empty( $items ) ) {
                    WC()->cart->empty_cart( true );
                    foreach ( $items as $order_item_id => $order_item ) {
                        $product = $order_item->get_product();
                        /** @var WC_Product $product */
                        if ( $product ) {
                            WC()->cart->add_to_cart( $product->get_id(), $order_item->get_quantity() );
                        }
                    }
                }

                $coupons = $subscription->get_coupons();
                if ( ! empty( $coupons ) ) {
                    foreach ( $coupons as $coupon_id => $coupon_item ) {
                        $code = $coupon_item->get_code();
                        if ( $code ) {
                            WC()->cart->apply_coupon( $code );
                        }
                    }
                }

                $this->fill_cart_process = false;
            }
        }
    }

    /**
     * Init cart after first purchase.
     *
     * @param WC_Order|bool $order
     *
     * @throws Exception
     */
    function init_cart_after_first_purchase( $order = false ) {
        if ( ! $order ) {
            $order = $this->get_current_subscription();
            if ( $order === false ) {
                return;
            }
        }

        if ( ! get_post_meta( $order->get_ID(), '_is_cart_not_new', true ) ) {

            $this->set_session_frequency_from_order( $order );

            $order->calculate_totals( true );

            $this->fill_cart_from_previous_order();
            $order->update_meta_data( '_is_cart_not_new', '1' );
            $order->save();
        }
    }

    /**
     * On change coupon in the admin panel.
     */
    function on_update_order_on_coupon() {
        check_ajax_referer( 'order-item', 'security' );

        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        if ( $order_id ) {
            update_post_meta( $order_id, '_is_cart_updated', '0', true );
        }
    }

    /**
     * On apply coupon from the Checkout.
     *
     * @param string $coupon_code
     *
     * @throws WC_Data_Exception
     * @throws Exception
     */
    function on_update_coupon_from_checkout( $coupon_code ) {
        $order = $this->get_current_subscription();
        if ( $order === false ) {
            return;
        }

        if ( $order->ID AND isset( $_POST['coupon_code'] ) ) {
            $order->add_coupon( $coupon_code );
            $order->recalculate_coupons();

            // Save current shipping method to custom meta field
            $shipping_method = @array_shift( $order->get_shipping_methods() );
            $order->update_meta_data( "initial_shipping_method_id", $shipping_method['method_id'] );
            $order->update_meta_data( "initial_shipping_method_instance_id", $shipping_method['instance_id'] );

            // Update order shipping method, if coupon is free shipping
            $this->update_order_shipping_method( $coupon_code, $order, 'add' );

            $order->save();

            do_action( 'sf_update_subscription', $order->ID );
        }
    }

    /**
     * On update Cart - update and check subscribe.
     *
     * @param WC_Order|bool $order_id
     *
     * @throws Exception
     */
    function on_update_order( $order_id ) {
        update_post_meta( $order_id, '_is_cart_updated', '0', true );
    }

    /**
     * Body class with status of subscribe.
     *
     * @param array $classes
     *
     * @return array
     * @throws Exception
     */
    function body_class_subscribe_status( $classes ) {
        if (
            ( is_page( 'checkout' ) OR strpos( $_SERVER['REQUEST_URI'], 'order-received' ) !== false OR isset( $_GET['wc-ajax'] ) )
            AND ! isset( $_GET['wc-ajax'] ) AND $_GET['wc-ajax'] !== 'apply_coupon'
        ) {
            if ( $this->is_has_subscribe() ) {

                $order = $this->get_current_subscription();
                update_post_meta( $order->get_ID(), '_is_cart_not_new', '0', true ); // fix for infinity loop
                $this->fill_cart_from_previous_order( $order, true );
                $order->update_meta_data( '_is_cart_not_new', '1' );
                $order->save();

//                update_post_meta( $this->get_current_subscription()->get_id(), '_is_cart_updated', '0' );
            }
        }

        $classes[] = ( $this->get_subscribe_status() )['class'];

        return $classes;
    }

    /**
     * Remove coupon on event.
     *
     * @param string $coupon
     *
     * @return void
     */
    function remove_coupon( $coupon = '' ) {
        if ( ! $coupon ) {
            $coupon = $_GET['remove_coupon'];
            if ( ! $coupon AND isset( $_POST['wc-ajax'] ) AND $_POST['wc-ajax'] == 'remove_coupon' AND $_POST['coupon'] ) {
                $coupon = $_POST['coupon'];
            }

            if ( ! $coupon ) {
                return;
            }
        }

        $order = $this->get_current_subscription();
        if ( $order === false ) {
            return;
        }

        $order->remove_coupon( $coupon );
        $order->recalculate_coupons();

        // Update order shipping method
        $this->update_order_shipping_method( $coupon, $order, 'remove' );

        $order->save();

        $this->update_cart_from_order();

        do_action( 'sf_update_subscription', $order->ID );
    }

    /**
     * Update order shipping method on coupon add/remove, if coupon have "free shipping"
     *
     * @param string $coupon
     * @param object $order
     * @param string $status - "add" / "remove"
     *
     * @return void
     */
    function update_order_shipping_method( $coupon, $order, $status ) {
        $couponData = new WC_Coupon( $coupon );

        if ( $couponData->get_free_shipping() ) { //&& $order->get_shipping_method() == 'Free shipping'

            $remove_coupon_method = ( $order->meta_exists( "initial_shipping_method_id" ) ) ? explode( ':', $order->get_meta( "initial_shipping_method_id" ) )[0] : 'local_pickup';
            $new_method_id        = ( $status == 'add' ) ? 'free_shipping' : $remove_coupon_method;

            // Array for tax calculations
            $calculate_tax_for = array(
                'country' => $order->get_shipping_country()
            );

            $changed = false; // Initializing

            // Loop through order shipping items
            foreach ( $order->get_items( 'shipping' ) as $item_id => $item ) {

                // Retrieve the customer shipping zone
                $shipping_zone = WC_Shipping_Zones::get_zone_by( 'instance_id', $item->get_instance_id() );

                // Get an array of available shipping methods for the current shipping zone
                $shipping_methods = $shipping_zone->get_shipping_methods();

                // Loop through available shipping methods
                foreach ( $shipping_methods as $instance_id => $shipping_method ) {

                    // Targeting specific shipping method
                    if ( $shipping_method->is_enabled() && $shipping_method->id === $new_method_id ) {

                        // Set an existing shipping method for customer zone
                        $item->set_method_title( $shipping_method->get_title() );
                        $item->set_method_id( $shipping_method->get_rate_id() ); // set an existing Shipping method rate ID
                        $item->set_total( $shipping_method->cost );

                        $item->calculate_taxes( $calculate_tax_for );
                        $item->save();

                        $changed = true;
                        break; // stop the loop
                    }
                }
            }

            if ( $changed ) {
                // Calculate totals and save
                $order->calculate_totals(); // the save() method is included
            }
        }
    }

    /**
     * Update cart from order.
     *
     * @param WC_Order|bool $order
     * @param WC_Cart|bool $cart
     *
     * @return void
     * @throws Exception
     */
    function update_cart_from_order( $order = false, $cart = false ) {
        if ( ! $order ) {
            $order = $this->get_current_subscription();
            if ( $order === false ) {
                return;
            }
        }
        if ( ! $cart ) {
            $cart = WC()->cart->get_cart();
        }

        update_post_meta( $order->get_ID(), '_is_cart_not_new', '0', true ); // fix for infinity loop

        if ( ! $this->is_equal_cart_subscribe( $order, $cart ) ) {
            $this->fill_cart_from_previous_order( $order, true );
        }

        $order->update_meta_data( '_is_cart_not_new', '1' );
        $order->save();
    }

    /**
     * Set subscribe status Pause or Play.
     *
     * @param string $type
     *
     * @return void
     */
    function set_subscribe_status( $type ) {
        $order = $this->get_current_subscription();
        if ( $order === false ) {
            return;
        }

        if ( $type == 'pause' ) {
            $order->set_status( 'wc-op-paused', 'Order is paused.' );
            $order->save();

            // Update status for mailing
            update_post_meta( $order->get_id(), 'op_subscription_notify_pause', 'true' );
            op_help()->notifications->op_subscription_order_pause_notifier( $order->get_id() );
        }
        if ( $type == 'play' ) {
            $order->set_status( 'wc-op-subscription', 'Order returned from pause.' );
            $order->save();

            // Update status for mailing
            update_post_meta( $order->get_id(), 'op_subscription_notify_pause', 'false' );
        }
    }

    /**
     * Sync subscribe items from cart.
     *
     * @param WC_Order|bool $order
     * @param bool $is_force
     *
     * @return void
     * @throws Exception
     */
    function sync_subscribe_from_cart( $order = false, $is_force = false ) {
        if ( ! $order ) {
            $order = $this->get_current_subscription();
            if ( $order === false ) {
                return;
            }
        }
        $cart = WC()->cart->get_cart();

        $this->add_coupon_from_order_if_not_exist( $order, WC()->cart );

        // Is equal cart and subscribe
        if ( $this->is_equal_cart_subscribe( $order, $cart ) AND ! $is_force ) {
            $current_status = $this->get_subscribe_status();
            if ( $current_status['label'] != 'wc-op-paused' ) {
                $this->modify_cart_order_to_subscription( $order, '', false ); // Setup frequency

                $order->save();

                do_action( 'sf_update_subscription', $order->ID );
            }

            return;
        }

        // Remove products
        $order->remove_order_items( 'line_item' );

        // Add new products from cart
        foreach ( $cart as $key => $item ) {
            if ( isset( $item['product_id'] ) and $item['product_id'] ) {
                $order_item_product = new WC_Order_Item_Product();
                $order_item_product->set_id( 0 );
                $order_item_product->set_variation_id( $item['variation_id'] );
                $order_item_product->set_quantity( $item['quantity'] );
                $order_item_product->set_subtotal( $item['line_subtotal'] );
                $order_item_product->set_subtotal_tax( $item['line_subtotal_tax'] );
                $order_item_product->set_total( $item['line_total'] );
                $order_item_product->set_total_tax( $item['line_tax'] );
                $order_item_product->set_product( $item['data'] );
                $order_item_product->save();

                $order->add_item( $order_item_product );
            }
        }

        // Setup frequency
        $this->modify_cart_order_to_subscription( $order, '', false );

        // Recalculate and save
//        $order->generate_order_variations();
        $order->calculate_totals();
        $order->save();

        do_action( 'sf_update_subscription', $order->ID );
    }

    /**
     * Is equal cart and subscribe items?
     *
     * @param WC_Order|bool $order
     * @param WC_Cart|bool $cart
     *
     * @return bool
     */
    function is_equal_cart_subscribe( $order = false, $cart = false ) {
        if ( ! $order ) {
            $order = $this->get_current_subscription();
            if ( $order === false ) {
                return false;
            }
        }
        if ( ! $cart ) {
            $cart = WC()->cart->get_cart();
        }

        if ( $order->get_total() != WC()->cart->get_totals()['total'] ) {
            return false;
        }

        foreach ( $cart as $cart_item ) {
            if ( isset( $cart_item['product_id'] ) and $cart_item['product_id'] ) {
                $flag = false;
                foreach ( $order->get_items() as $order_item ) {
                    $product = $order_item->get_product();
                    /** @var WC_Product $product */
                    if ( $product->get_id() == $cart_item['data']->get_id() ) {
                        if ( $order_item->get_quantity() != $cart_item['quantity'] ) {
                            return false;
                        }
                        $flag = true;
                        break;
                    }
                }
                if ( ! $flag ) {
                    return false;
                }
            }
        }

        foreach ( $order->get_items() as $order_item ) {
            $product = $order_item->get_product();
            /** @var WC_Product $product */
            $flag = false;
            foreach ( $cart as $cart_item ) {
                if ( $product->get_id() == $cart_item['data']->get_id() ) {
                    if ( $order_item->get_quantity() != $cart_item['quantity'] ) {
                        return false;
                    }
                    $flag = true;
                    break;
                }
            }
            if ( ! $flag ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return delivery date.
     *
     * @param WC_Order|bool $order
     * @param WC_Cart|bool $cart
     *
     * @return void
     * @throws Exception
     */
    function add_coupon_from_order_if_not_exist( $order = false, $cart = false ) {
        if ( ! $order ) {
            $order = $this->get_current_subscription();
            if ( $order === false ) {
                return;
            }
        }
        if ( ! $cart ) {
            $cart = WC()->cart;
        }

        $cart_coupons  = $cart->get_coupons();
        $order_coupons = $order->get_coupons();

        foreach ( $order_coupons as $order_coupon ) {
            $order_coupon_name = $order_coupon->get_name();
            $flag              = false;

            foreach ( $cart_coupons as $cart_coupon ) {
                if ( $cart_coupon->get_code() == $order_coupon_name ) {
                    $flag = true;
                    break;
                }
            }

            if ( ! $flag ) {
                $order->recalculate_coupons();
                $order->save();

                update_post_meta( $order->get_ID(), '_is_cart_not_new', '0', true ); // fix for infinity loop
                $this->fill_cart_from_previous_order( $order, true );
                $order->update_meta_data( '_is_cart_not_new', '1' );
                $order->save();
                break;
            } else {
                continue;
            }
        }
    }

    /**
     * Return delivery date.
     *
     * @param WC_Order|bool $order
     *
     * @return DateTime|bool
     */
    function get_delivery_date( $order = false ) {
        if ( ! $order ) {
            $order = $this->get_current_subscription();
            if ( $order === false ) {
                return false;
            }
        }

        return DateTime::createFromFormat( "Y-m-d H:i:s", get_post_meta( $order->get_ID(), "op_next_delivery", true ) );
    }

    /**
     * Check to valid subscribe.
     *
     * @param WC_Order|bool $order
     *
     * @return bool
     */
    function is_valid_cart( $order = false ) {
        if ( ! $order ) {
            $order = $this->get_current_subscription();
            if ( $order === false ) {

                return false;
            }
        }

        $woo_cat = get_term( 15 ); // 15 - Meals category

        $new_products                     = op_help()->subscriptions->sort_items_by_category( WC()->cart->get_cart(), $woo_cat );
        $subscription_category_has_errors = op_help()->subscriptions->check_category_for_allowed_item_numbers( $new_products, $woo_cat, false );
        if ( empty( $new_products ) || is_array( $subscription_category_has_errors ) ) {

            return false;
        } else {
            return true;
        }
    }


    /**
     * Is lock subscription?
     *
     * @param WC_Order|bool $order
     *
     * @return bool
     */
    function is_lock( $order ) {
        if ( ! $order ) {
            $order = $this->get_current_subscription();
        }

        $delivery_date        = $this->get_delivery_date( $order );
        $delivery_date_offset = carbon_get_theme_option( 'op_subscription_order_offset' ) ? (integer) carbon_get_theme_option( 'op_subscription_order_offset' ) + 1 : 49;

        if ( ! $delivery_date ) {
            return false;
        }

        try {
            $lock_date = $delivery_date->sub( new DateInterval( "PT{$delivery_date_offset}H" ) );
            $now       = new DateTime();
        } catch ( Exception $e ) {
            return false;
        }

        if ( $now > $lock_date ) {
            return true;
        }

        return false;
    }

    /**
     * User has subscribe?
     *
     * @return bool
     */
    function is_has_subscribe() {
        return (bool) $this->get_current_subscription();
    }

    /**
     * Payment by hook change Order status to Shipped.
     *
     * @param integer $order_id
     * @param WC_Order $order
     *
     * @return void
     * @throws WC_Data_Exception
     */
    function payment_by_hook( $order_id, $order ) {
        if ( $order->get_transaction_id() ) {
            return;
        }

        $user_id   = $order->get_user_id();
        $order_pay = op_help()->payment->createOrderPayment( $user_id, $order->get_total(), $order->get_id() );

        if ( $order_pay->transaction_approved ) {
            $order->set_transaction_id( $order_pay->transaction_tag );
            $order->add_order_note( 'SUCCESS PAYMENT' );
        } else {
            $order->set_status( 'failed', 'Status changed after failed payment!' );
            $order->add_order_note( 'ERROR PAYMENT' );
            error_log( print_r( $order_pay, true ) );
        }
        $order->save();

        do_action( 'in8sync_async_sendOrderToNetsuite', $order->get_id() );
    }

    /**
     * добавляет проверку даты доставки при оформлении заказа в Checkout
     */
    function add_more_check_to_checkout( $data, $errors ) {
        // $my_field_name = get_post_meta( $order->get_id(), 'my_field_name', true );
        $delivery_date        = op_help()->shop->get_session_cart( 'delivery_date', ( new DateTime() )->format( "Y-m-d" ) );
        $delivery_date_offset = carbon_get_theme_option( 'op_subscription_order_offset' ) ? (integer) carbon_get_theme_option( 'op_subscription_order_offset' ) : 36;
        $date_delivery        = ( new DateTime( $delivery_date ) )->modify( '- ' . $delivery_date_offset . ' hour' )->setTime( 12, 1, 2 );
        $date_now             = ( new DateTime() )->setTime( 12, 1, 1 );
        if ( empty( $delivery_date ) ) {
            $errors->add( 'delivery_date', __( 'Your delivery date incorrect', 'woocommerce' ),
                array( 'id' => 'delivery_date' ) );
        } else {
            if ( $date_now >= $date_delivery ) {
                $errors->add( 'delivery_date',
                    __( 'We can send the order in 2 days' . $date_now->format( "Y-m-d h:m:s" ) . $date_delivery->format( "Y-m-d h:m:s" ),
                        'woocommerce' ), array( 'id' => 'delivery_date' ) );
            }
        }
    }

    /**
     * Принимает POST Данные чекаута и проверяет их к готовности на создание заказа.
     * Получает также текущий шаг чекаута и фильтрует ошибки, показывает ошибки только выбранного шага.
     */
    function ajax_checkout_test_step() {
        $sf_checkout = new SF_Checkout;
        $errors      = $sf_checkout->sf_get_errors();

        $result_errors = [];

        foreach ( $errors->errors as $code => $messages ) {
            $data = $errors->get_error_data( $code );
            foreach ( $messages as $message ) {
                // wc_add_notice( $message, 'error', $data );
                $result_errors[] = [
                    'message'    => $message,
                    'field_name' => $data['id'],
                ];

            }
        }
        $this_step_errors    = [];
        $this_step_data_html = [];
        switch ( $_POST['step'] ) {
            case 'address':
                $this_step_errors = array_filter( $result_errors, function ( $error ) {
                    return in_array( $error['field_name'], [
                        'billing_first_name',
                        'billing_last_name',
                        'billing_address_1',
                        'billing_postcode',
                        'billing_city',
                        'billing_phone',
                        'billing_email',
                    ] );
                } );

                $this_step_data_html['address'] = '
        <div class="checkout-item__data-block data-block">
            <div class="data-block__head">
                <p class="data-block__title">Delivery Details</p>
            </div>
            <p class="data-block__txt">
                ' . esc_html( $_POST['billing_address_1'] ) . ', ' . esc_html( $_POST['billing_city'] ) . ' ' . esc_html( $_POST['billing_postcode'] ) . '<br>
                ' . esc_html( $_POST['billing_first_name'] ) . ' ' . esc_html( $_POST['billing_last_name'] ) . ', ' . esc_html( $_POST['billing_email'] ) . '<br>
                ' . esc_html( $_POST['billing_phone'] ) . '
            </p>
        </div>
        ';
                // вернуть красивый html с информацией по адресу
                break;
            case 'delivery':
                $this_step_errors = array_filter( $result_errors, function ( $error ) {
                    return $error['field_name'] === "delivery_date";
                } );
                // вернуть красивый html c информацией по доставке
                $delivery_time                   = op_help()->shop->get_session_cart( 'delivery_date',
                    ( new DateTime() )->format( "Y-m-d" ) );
                $delivery_date_time              = new DateTime( $delivery_time );
                $this_step_data_html['delivery'] = '
        <div class="checkout-item__data-block data-block">
            <div class="data-block__head">
                <p class="data-block__title">Every ' . esc_html( $delivery_date_time->format( "l" ) ) . '</p>
            </div>
            <p class="data-block__txt">First delivery date: ' . esc_html( $delivery_date_time->format( "l, F j, Y" ) ) . '</p>
        </div>
        ';
                break;
            case 'payment':
                $payment_method = op_help()->payment->createToken( $_POST );

                if ( is_wp_error( $payment_method ) ) {
                    $result_errors[] = [
                        'message'    => $payment_method->get_error_message(),
                        'field_name' => 'checkout',
                    ];
                }

                $this_step_errors = array_filter( $result_errors, function ( $error ) {
                    return $error['field_name'] === "checkout";
                } );

                $last_digits = $_POST['checkout']['card_number'];
                $expire      = $_POST['checkout']['exp_date'];

                // проверить что привязали способ оплаты
                // создать пустой заказ со всеми параметрами для создания токена
                //$abstract_order = $sf_checkout->sf_get_order();

                // попробовать создать токен
                //$tokenizer = new SV_WC_Payment_Gateway_Payment_Tokens_Handler();
                //$some_response = $tokenizer->create_token( $abstract_order );
                // Созранить токен у юзера или в сессии(???) вернуть данные по токену (html)
                // Вернуть ошибку про оплату
                // -> Text block data

                if ( $_POST['checkout']['use_as_billing_address'] == 'on' ) {
                    $billing_text_block = '<p class="data-block__txt">
                            ' . esc_html( $_POST['billing_address_1'] ) . ', ' . esc_html( $_POST['billing_city'] ) . ' ' . esc_html( $_POST['billing_postcode'] ) . '<br>
                            ' . esc_html( $_POST['billing_first_name'] ) . ' ' . esc_html( $_POST['billing_last_name'] ) . '<br>
                            ' . esc_html( $_POST['billing_phone'] ) . '
                        </p>';
                } else {
                    $billing_text_block = '<p class="data-block__txt">
                            ' . esc_html( $_POST['shipping_address_1'] ) . ', ' . esc_html( $_POST['shipping_city'] ) . ' ' . esc_html( $_POST['shipping_postcode'] ) . '<br>
                            ' . esc_html( $_POST['shipping_first_name'] ) . ' ' . esc_html( $_POST['shipping_last_name'] ) . '<br>
                            ' . esc_html( $_POST['shipping_phone'] ) . '
                        </p>';
                }


                $this_step_data_html['payment'] = '
                    <div class="checkout-item__data-block data-block">
                        <div class="data-block__card-data card-data">
                            Sample info
                            <span class="card-data__number">•••• ' . substr( $last_digits, - 4 ) . '</span>
                            <span class="card-data__exp">Exp. date: ' . $expire . '</span>
                        </div>
                        <div class="data-block__item">
                            <div class="data-block__head">
                                <p class="data-block__title">Billing Details</p>
                            </div>
                            ' . $billing_text_block . '
                        </div>
                    </div>
                    ';
                break;
        }

        if ( ! empty( $this_step_errors ) ) {
            wp_send_json_error( [
                'step_errors' => $this_step_errors,
                'step'        => $_POST['step'],
                'debug'       => $result_errors,
            ] );
        } else {
            wp_send_json_success( [
                'step'  => $_POST['step'],
                'html'  => $this_step_data_html,
                // 'card' => '', // html
                'debug' => $result_errors,
            ] );
        }

    }

    /**
     * Крон скрипт, который находит подписки, по которым надо создать заказ (заказы которые должны быть доставлены через 2 дня)
     */
    function cron_order_generator() {
        TB::m( '*start* cron `op_subscription_order_creator`' );
        TB::start( 'cron-order' );
        $orders_query = new WP_Query;

        $args = [
            'post_type'   => 'shop_order',
            'post_status' => 'wc-op-subscription',
            'nopaging'    => true,
            'meta_query'  => [
                [
                    'key'     => 'op_next_order_creation',
                    'compare' => '<=',
                    'value'   => ( new DateTime() )->format( "Y-m-d H:00:00" ),
                    'type'    => 'DATETIME'
                ],
            ],
            'fields'      => 'ids'
        ];

        $total = 0;

        $ready_orders = $orders_query->query( $args );
        foreach ( $ready_orders as $key => $subscription_id ) {
            $subscription = wc_get_order( $subscription_id );
            $subscription->create_order_from_cron();
            $total ++;
        }

        $new_orders         = TB::get_message( 'cron_order_m' );
        $new_orders_message = 'empty';
        if ( $new_orders ) {
            $new_orders_message = substr( $new_orders, 0, - 2 );
        }
        TB::m( "*end* cron `op_subscription_order_creator`\n Total subscriptions: *{$total}*. New Order(s): {$new_orders_message}", true, 'cron-order' );
    }

    /**
     * получает параметры крона
     */
    function get_cron_settings() {
        $settings       = [];
        $cron_active    = carbon_get_theme_option( "op_subscription_cron_active" );
        $cron_frequency = carbon_get_theme_option( "op_subscription_cron_frequency" );

        if ( ! empty( $cron_active ) && is_bool( $cron_active ) ) {
            $settings['active'] = $cron_active;
        } else {
            $settings['active'] = false;
        }

        if ( ! empty( $cron_frequency ) ) {
            $settings['frequency'] = $cron_frequency;
        } else {
            $settings['frequency'] = '';
        }

        return $settings;
    }

    /**
     * Check cron order generator on exist.
     */
    public function check_cron_order_generator() {
        if ( get_option( 'op_subscription_cron' ) ) {
            if ( ! wp_next_scheduled( 'op_subscription_order_creator', [] ) ) {
                $new_cron_settings = $this->get_cron_settings();

                if ( $new_cron_settings['active'] ) {
                    wp_schedule_event( time(), $new_cron_settings['frequency'],
                        'op_subscription_order_creator' );
                }
            }
        }
    }

    /**
     * хук который при обновлении настроек крона в админ-панели создает/удаляет cron events
     */
    function change_cron_settings( $user_data, $container ) {
        if ( $container->id === 'carbon_fields_container_subscriptions_addon' ) {

            $has_scheduled = wp_next_scheduled( 'op_subscription_order_creator', [] );

            $current_cron_settings = get_option( 'op_subscription_cron', [ 'active' => false, 'frequency' => '' ] );
            $new_cron_settings     = $this->get_cron_settings();

            if ( $current_cron_settings['active'] === $new_cron_settings['active'] ) {
                if ( $new_cron_settings['active'] ) {
                    // MB Change frequency
                    if ( $current_cron_settings['frequency'] !== $new_cron_settings['frequency'] ) {
                        // delete old cron
                        wp_clear_scheduled_hook( 'op_subscription_order_creator', [] );
                        // create new cron
                        $result = wp_schedule_event( time(), $new_cron_settings['frequency'],
                            "op_subscription_order_creator" );
                    }
                }
            } else {
                if ( $new_cron_settings['active'] ) {
                    // create cron
                    if ( $has_scheduled ) {
                        // delete old cron
                        wp_clear_scheduled_hook( 'op_subscription_order_creator', [] );
                    }

                    $result = wp_schedule_event( time(), $new_cron_settings['frequency'],
                        "op_subscription_order_creator" );

                } else {
                    if ( $has_scheduled ) {
                        // delete
                        wp_clear_scheduled_hook( 'op_subscription_order_creator', [] );
                    }
                }
            }

            update_option( "op_subscription_cron", $new_cron_settings );
        }
    }

    /**
     * хук который дополняет и изменяет заказ, который создается через страницу checkout
     * по факту превращает обычный заказ WC в кастомную подписку
     *
     * @param WC_Order $order
     * @param string $data
     * @param bool $is_update_status
     *
     * @return mixed
     */
    function modify_cart_order_to_subscription( $order, $data, $is_update_status = true ) {
        if ( $is_update_status ) {

            try {
                $delivery_time = op_help()->shop->get_session_cart( 'delivery_date', ( new DateTime() )->format( "Y-m-d" ) );
                $delivery_time = new DateTime( $delivery_time );
            } catch ( Exception $e ) {
                return false;
            }

            op_help()->shop->set_session_cart( 'delivery_date', '' ); // reset for future carts

            $delivery_date_offset = carbon_get_theme_option( 'op_subscription_order_offset' ) ? (integer) carbon_get_theme_option( 'op_subscription_order_offset' ) : 36;

            $order->update_meta_data( "op_next_week", '1' );
            $order->update_meta_data( "op_next_delivery", $delivery_time->format( "Y-m-d h:m:i" ) );
            $order->update_meta_data( "op_next_order_creation",
                $delivery_time->modify( '- ' . $delivery_date_offset . ' hour' )->format( "Y-m-d h:m:i" ) );
        }

        // // Save current shipping method to custom meta field
        // $shipping_method = @array_shift($order->get_shipping_methods());
        // $order->update_meta_data("initial_shipping_method_id", $shipping_method['method_id']);
        // $order->update_meta_data("initial_shipping_method_instance_id", $shipping_method['instance_id']);

        $items_by_category = op_help()->subscriptions->get_items_n_categories_for_order( $order );
        foreach ( $items_by_category as $category_key => $category_data ) {
            $items_frequency = carbon_get_term_meta( $category_data['category']->term_id,
                'op_categories_subscription_frequency_by_item' );
            $list_frequency  = carbon_get_term_meta( $category_data['category']->term_id,
                'op_categories_subscription_frequency' );

            $category_frequency = op_help()->shop->get_session_cart( 'frequency_' . $category_data['category']->term_id,
                $list_frequency[0] );

            if ( $is_update_status ) {
                op_help()->shop->set_session_cart( 'frequency_' . $category_data['category']->term_id,
                    '' ); // reset for future carts
            }
            $category_frequency = in_array( $category_frequency,
                $list_frequency ) ? $category_frequency : $list_frequency[0];
            // TODO set freq for categories

            foreach ( $category_data['products'] as $cart_product ) {
                if ( $items_frequency ) {
                    $chosen_frequency = op_help()->shop->get_session_cart( 'frequency_item_' . $cart_product->get_product_id(),
                        $list_frequency[0] );

                    if ( $is_update_status ) {
                        op_help()->shop->set_session_cart( 'frequency_item_' . $cart_product->get_product_id(),
                            '' ); // reset for future carts
                    }

                    $chosen_frequency = in_array( $chosen_frequency,
                        $list_frequency ) ? $chosen_frequency : $list_frequency[0];
                    $cart_product->update_meta_data( 'op_frequency', $chosen_frequency );
                } else {
                    $cart_product->update_meta_data( 'op_frequency', $category_frequency );
                }
            }
        }

        if ( $is_update_status ) {
            $order->set_status( 'wc-op-subscription', 'Subscription was created from cart' );
        }

        return $order;
    }

    /**
     * Change order addresses (swich data)
     */
    function update_order_addresses( $order_id ) {
        if ( get_post_meta( $order_id, '_addresses_updated', true ) !== 'true' ) {
            $order = wc_get_order( $order_id );

            $delivery_f_name    = $order->billing_first_name;
            $delivery_l_name    = $order->billing_last_name;
            $delivery_country   = $order->billing_country;
            $delivery_address_1 = $order->billing_address_1;
            $delivery_address_2 = $order->billing_address_2;
            $delivery_postcode  = $order->billing_postcode;
            $delivery_city      = $order->billing_city;
            $delivery_state     = $order->billing_state;
            $delivery_phone     = $order->billing_phone;

            $billing_f_name    = $order->shipping_first_name;
            $billing_l_name    = $order->shipping_last_name;
            $billing_country   = $order->shipping_country;
            $billing_address_1 = $order->shipping_address_1;
            $billing_address_2 = $order->shipping_address_2;
            $billing_postcode  = $order->shipping_postcode;
            $billing_city      = $order->shipping_city;
            $billing_state     = $order->shipping_state;
            $billing_phone     = $order->shipping_phone;

            // Save billing data
            update_post_meta( $order_id, '_billing_first_name', $billing_f_name );
            update_post_meta( $order_id, '_billing_last_name', $billing_l_name );
            update_post_meta( $order_id, '_billing_country', $billing_country );
            update_post_meta( $order_id, '_billing_address_1', $billing_address_1 );
            update_post_meta( $order_id, '_billing_address_2', $billing_address_2 );
            update_post_meta( $order_id, '_billing_postcode', $billing_postcode );
            update_post_meta( $order_id, '_billing_city', $billing_city );
            update_post_meta( $order_id, '_billing_state', $billing_state );
            update_post_meta( $order_id, '_billing_phone', $billing_phone );

            // Save shipping data
            update_post_meta( $order_id, '_shipping_first_name', $delivery_f_name );
            update_post_meta( $order_id, '_shipping_last_name', $delivery_l_name );
            update_post_meta( $order_id, '_shipping_country', $delivery_country );
            update_post_meta( $order_id, '_shipping_address_1', $delivery_address_1 );
            update_post_meta( $order_id, '_shipping_address_2', $delivery_address_2 );
            update_post_meta( $order_id, '_shipping_postcode', $delivery_postcode );
            update_post_meta( $order_id, '_shipping_city', $delivery_city );
            update_post_meta( $order_id, '_shipping_state', $delivery_state );
            update_post_meta( $order_id, '_shipping_phone', $delivery_phone );


            // Update user meta too
            $user_id = get_current_user_id();

            // Save billing data
            update_user_meta( $user_id, 'billing_first_name', $billing_f_name );
            update_user_meta( $user_id, 'billing_last_name', $billing_l_name );
            update_user_meta( $user_id, 'billing_country', $billing_country );
            update_user_meta( $user_id, 'billing_address_1', $billing_address_1 );
            update_user_meta( $user_id, 'billing_address_2', $billing_address_2 );
            update_user_meta( $user_id, 'billing_postcode', $billing_postcode );
            update_user_meta( $user_id, 'billing_city', $billing_city );
            update_user_meta( $user_id, 'billing_state', $billing_state );
            update_user_meta( $user_id, 'billing_phone', $billing_phone );

            // Save shipping data
            update_user_meta( $user_id, 'shipping_first_name', $delivery_f_name );
            update_user_meta( $user_id, 'shipping_last_name', $delivery_l_name );
            update_user_meta( $user_id, 'shipping_country', $delivery_country );
            update_user_meta( $user_id, 'shipping_address_1', $delivery_address_1 );
            update_user_meta( $user_id, 'shipping_address_2', $delivery_address_2 );
            update_user_meta( $user_id, 'shipping_postcode', $delivery_postcode );
            update_user_meta( $user_id, 'shipping_city', $delivery_city );
            update_user_meta( $user_id, 'shipping_state', $delivery_state );
            update_user_meta( $user_id, 'shipping_phone', $delivery_phone );


            // Save address updated flag
            add_post_meta( $order_id, '_addresses_updated', 'true' );
        }
    }

    /**
     * проверяет наличие товаров из категорий (настройка в админке)
     * если товаров недостаточно создаем нотисы-ошибки WC
     *
     * @param bool $is_notice
     *
     * @return bool
     */
    function check_cart_before_checkout( $is_notice = true ) {
        // echo '<pre>';
        // var_dump( WC()->cart->get_cart() );
        // echo '</pre>';
        // return false;

        if ( empty( $this->woo_categories ) ) {
            $prod_cat_args = array(
                'taxonomy'   => 'product_cat',
                'orderby'    => 'id',
                'hide_empty' => false,
                'parent'     => 0
            );

            $this->woo_categories = get_categories( $prod_cat_args );
        }

        if ( empty( $this->woo_categories ) ) {
            return false;
        }

        $cart_items = array_column( WC()->cart->get_cart(), 'data' );

        foreach ( $this->woo_categories as $key => $category ) {

            $category_items = $this->sort_items_by_category( $cart_items, $category );

            $errors = $this->check_category_for_allowed_item_numbers( $category_items, $category );

            if ( ! empty( $errors ) ) {
                foreach ( $errors as $key => $error_message ) {

                    if ( $is_notice ) {

                        wc_add_notice( $error_message, 'error' );
                    } else {
                        return true;
                    }
                }
            } else {
                wc_clear_notices();
            }

        }

        return false;
    }


    /**
     * обновление гипотетического заказа в подписке
     */
    function ajax_update_future_order() {

        parse_str( $_POST['form'], $form );
        // TODO сделать проверки прав

        $subscription = $this->get_subscription( $form['order_id'] );
        $week_num     = intval( $form['order_week'] );


        $available_future_orders = $subscription->get_order_variations();

        if ( empty( $form['op_order_pause'] ) ) {

            $subscription->delete_meta_data( 'op_future_pause_' . $week_num );

        } else {

            $subscription->update_meta_data( 'op_future_pause_' . $week_num, 'on' );

        }

        if ( isset( $available_future_orders[ $week_num ] ) ) {

            $week_data = [];

            if ( ! empty( $form['paused'] ) ) {

                $new_paused_data = array_reduce( $form['paused'], function ( $reducer, $items ) {
                    return $reducer + $items;
                }, [] );

            } else {

                $new_paused_data = [];

            }

            foreach ( $available_future_orders[ $week_num ] as $item_key => $item_id ) {

                // TODO validation required
                $week_data[ $item_id ] = [];
                if ( ! empty( $new_paused_data[ $item_id ] ) ) {
                    $week_data[ $item_id ]['op_pause'] = $new_paused_data[ $item_id ];
                } else {
                    $week_data[ $item_id ]['op_pause'] = '';
                }

                if ( ! empty( $form['quantity'][ $item_id ] ) ) {
                    $week_data[ $item_id ]['quantity'] = $form['quantity'][ $item_id ];
                }

            }

            $subscription->update_meta_data( 'op_future_items_' . $week_num, $week_data );

            $subscription->save();

            wp_send_json_success( 'Planned order was updated!' );

        } else {

            wp_send_json_error( 'This week hasn\'t planned!' );

        }


    }

    /**
     * обновление подписки
     */
    function ajax_update_subscription() {

        parse_str( $_POST['form'], $form );

        // TODO сделать проверки прав
        $subscription = $this->get_subscription( $form['order_id'] );

        // START TESTING PURPOSES
        if ( isset( $form['create_order'] ) && $form['create_order'] !== '' ) {

            $create_result = $subscription->create_order( $form['create_order'], $form['create_order_status'] );

            // wp_send_json_error( ['message' => 'Order created!', 'result' => $create_result ] );
            wp_send_json_success( 'Order created! Update page please.' );

        }
        // END TESTING PURPOSES'

        $items = $subscription->get_items_n_categories();

        $order_frequency = [];
        $items_frequency = [];

        foreach ( $form['frequency'] as $category_id => $frequency_values ) {

            $list_frequency[ $category_id ] = $list_frequency[ $category_id ] ?? carbon_get_term_meta( $category_id,
                    'op_categories_subscription_frequency' );


            foreach ( $frequency_values as $item_id => $frequency_item_value ) {

                $frequency_item_value_validated = in_array( $frequency_item_value,
                    $list_frequency[ $category_id ] ) ? $frequency_item_value : $list_frequency[ $category_id ][0];

                if ( $item_id == 'all' ) {

                    $order_frequency[ $category_id ] = $frequency_item_value_validated;

                } else {

                    $items_frequency[ $item_id ] = $frequency_item_value_validated;

                }

            }

        }

        $items_paused = [];
        $order_paused = [];

        if ( ! empty( $form['paused'] ) ) {

            foreach ( $form['paused'] as $category_id => $paused_values ) {

                foreach ( $paused_values as $item_id => $paused_item_value ) {

                    if ( $paused_item_value == 'on' ) {

                        $paused_item_value_validated = 'on';

                    } else {

                        continue;

                    }

                    if ( $item_id == 'all' ) {

                        $order_paused[ $category_id ] = $paused_item_value_validated;

                    } else {

                        $items_paused[ $item_id ] = $paused_item_value_validated;

                    }

                }

            }

        }

        foreach ( $items as $category_id => $order_category_data ) {

            $can_frequency_by_item[ $category_id ] = $can_frequency_by_item[ $category_id ] ?? carbon_get_term_meta( $category_id,
                    'op_categories_subscription_frequency_by_item' );
            $list_paused[ $category_id ]           = $list_paused[ $category_id ] ?? carbon_get_term_meta( $category_id,
                    'op_categories_subscription_pause' );

            $order_paused[ $category_id ] = $order_paused[ $category_id ] ?? 'off';

            if ( ! empty( $order_category_data['products'] ) ) {

                foreach ( $order_category_data['products'] as $key => $item ) {

                    // save frequency option
                    if ( $can_frequency_by_item[ $category_id ] ) {

                        $item->update_meta_data( 'op_frequency', $items_frequency[ $item->get_id() ] );

                    } else {

                        $item->update_meta_data( 'op_frequency', $order_frequency[ $category_id ] );

                    }


                    // save paused option
                    if ( $list_paused[ $category_id ] == 'pause-items' ) { // can pause items

                        $is_paused = empty( $items_paused[ $item->get_id() ] ) ? 'off' : 'on';

                    } elseif ( $list_paused[ $category_id ] == 'pause-category' ) { // can pause category

                        $is_paused = empty( $order_paused[ $category_id ] ) ? 'off' : 'on';

                    } else {

                        $is_paused = 'off';

                    }

                    $item->update_meta_data( 'op_paused', $is_paused );

                    // save all changes for item
                    $item->save();

                }

            }


        }

        if ( ! empty( $form['op_next_delivery'] ) ) {

            $next_delivery = new DateTime( $form['op_next_delivery'] );
            if ( ! empty( $form['op_next_delivery_time'] ) ) {
                $next_delivery->setTime( $form['op_next_delivery_time'], 0 );
            }
            $next_creation        = clone $next_delivery;
            $delivery_date_offset = carbon_get_theme_option( 'op_subscription_order_offset' ) ? (integer) carbon_get_theme_option( 'op_subscription_order_offset' ) : 36;
            $next_creation->modify( '- ' . $delivery_date_offset . ' hour' );

            $subscription->update_meta_data( 'op_next_delivery', $next_delivery->format( "Y-m-d H:i:s" ) );
            $subscription->update_meta_data( 'op_next_order_creation', $next_creation->format( "Y-m-d H:i:s" ) );

        }

        $subscription->update_meta_data( 'op_frequency', $order_frequency );

        $subscription->update_meta_data( 'op_paused', $order_paused );

        if ( $subscription->get_status() == 'op-paused' && empty( $form['order_paused'] ) ) {

            $subscription->set_status( 'wc-op-subscription', 'Subscription was paused by user' );

        }
        if ( $subscription->get_status() == 'op-subscription' && ! empty( $form['order_paused'] ) ) {

            $subscription->set_status( 'wc-op-paused', 'Subscription was resumed by user' );

        }

        if ( $subscription->has_future_modifications() ) {

            $subscription->clear_future_modifications();

        }

        $subscription->generate_order_variations(); // regenerate order variations

        $subscription->save();
        // update_meta_data
        // save_meta_data

        // wp_send_json_error( [
        //   '$next_delivery' => $next_delivery,
        //   '$form' => $form,
        //   '$list_paused' => $list_paused,
        //   '$items_paused' => $items_paused,
        //   '$order_paused' => $order_paused
        //   ] );

        wp_send_json_success( 'Order updated!' );

    }

    /**
     * изменяет запрос на получение заказов в личном кабинете
     * теперь получаем только подписки
     */
    function change_order_query( $args ) {
        $args['post_status'] = 'wc-op-subscription';

        return $args;
    }

    /**
     * заменяет класс, который используется для обработки заказов, если это подписка
     */
    function set_order_subscription_class( $classname, $order_type, $order_id ) {

        $suctom_statuses = [ 'wc-op-subscription', 'op-paused', 'wc-op-incomplete' ];

        if ( in_array( get_post_status( $order_id ), $suctom_statuses ) ) {

            $classname = 'SF_Subscription';

        }

        return $classname;

    }

    /**
     * разрешаем изменять подписки в админке
     */
    function do_subscription_editable( $editable, $order ) {

        if ( in_array( $order->get_status(), [ 'op-subscription', 'op-paused', 'op-incomplete' ] ) ) {
            return true;
        }

        return $editable;

    }

    /**
     * регистрируем статусы подписки для заказов WC
     */
    function register_subscription_status() {
        foreach ( $this->statuses as $slug => $status ) {
            register_post_status( $slug, array(
                'label'                     => _x( $status['name'], 'WooCommerce Order status', 'woocommerce' ),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( $status['label_count'], $status['label_count'], 'woocommerce' )
            ) );
        }
    }

    /**
     * делаем кастомные статусы заказов доступными в админке
     */
    function add_subscription_status( $order_statuses ) {
        foreach ( $this->statuses as $slug => $status ) {
            $order_statuses += [ $slug => _x( $status['name'], 'WooCommerce Order status', 'woocommerce' ) ];
        }

//      print_r($order_statuses); die;

        return $order_statuses;
    }

    /**
     * делаем статус подписки стандартным для WC
     */
    function change_default_order_status( $status ) {

        $status = 'wc-op-subscription';

        return $status;

    }

    /**
     * хелперская функция на получение подписки
     */
    function get_subscription( $order_id ) {

        return new OP_Subscription( $order_id );

    }

    /**
     * добавление настроек подпискам
     */
    function add_settings_subpage( $main_page ) {

        $cron_frequency = array_map( function ( $freq ) {
            return $freq['display'];
        }, wp_get_schedules() );

        Container::make( 'theme_options', 'Subscriptions Addon' )
                 ->set_page_parent( $main_page )// reference to a top level container

                 ->add_tab( __( 'Cron Settings' ), array(
                Field::make( 'checkbox', 'op_subscription_cron_active', __( 'Activate Cron' ) )
                     ->set_option_value( 'no' ),
                Field::make( 'select', 'op_subscription_cron_frequency', __( 'Frequency of Order Creation' ) )
                     ->add_options( $cron_frequency ),
            ) )->add_tab( __( 'Options' ), array(
                    Field::make( 'multiselect', 'op_subscription_available_delivery_days', __( 'Available delivery days' ) )
                         ->add_options( array(
                             1 => 'Monday',
                             2 => 'Tuesday',
                             3 => 'Wednesday',
                             4 => 'Thursday',
                             5 => 'Friday',
                             6 => 'Saturday',
                             7 => 'Sunday'
                         ) ),
                    Field::make( 'text', 'op_subscription_order_offset', __( 'Order offset' ) )
                         ->set_attribute( 'type', 'number' )
                         ->set_attribute( 'min', '0' )
                         ->set_attribute( 'placeholder', __( 'The number of hours for which the ability to change the delivery time is blocked' ) ),
                    Field::make( 'text', 'op_subscription_order_open_offset', __( 'Order open offset' ) )
                         ->set_attribute( 'type', 'number' )
                         ->set_attribute( 'min', '0' )
                         ->set_attribute( 'placeholder', '' ),
                    Field::make( 'text', 'op_subscription_order_schedule_offset', __( 'Order schedule offset' ) )
                         ->set_attribute( 'type', 'number' )
                         ->set_attribute( 'min', '0' )
                         ->set_attribute( 'placeholder', '' )
                )
            );
    }

    /**
     * добавление настроек для категория
     */
    function add_fields_to_categories() {
        Container::make( 'term_meta', 'Components Parametres' )
                 ->where( 'term_taxonomy', 'product_cat' )
                 ->add_fields( array(
                     Field::make( 'checkbox', 'sf_separeted', __( 'Separate this cat from Meals PLan on checkout' ) )
                          ->set_option_value( 'no' ),
                     Field::make( 'complex', 'op_categories_subscription_items', 'Allowed Items Number' )
                          ->add_fields( 'number', array(
                              Field::make( 'text', 'number' )->set_attribute( 'type', 'number' ),
                          ) ),
                     Field::make( 'checkbox', 'op_categories_subscription_frequency_by_item',
                         __( 'Allow set Frequency for each item' ) )
                          ->set_option_value( 'no' ),
                     Field::make( 'multiselect', 'op_categories_subscription_frequency', __( 'Available Frequency ' ) )
                          ->add_options( array(
                              'every_week'        => 'Every week',
                              'every_2_weeks'     => 'Every 2 weeks',
                              'every_3_weeks'     => 'Every 3 weeks',
                              'every_4_weeks'     => 'Every 4 weeks',
                              'one_time_purchase' => 'One time purchase',
                          ) )->set_default_value( [ 'every_week' ] )->set_required( true ),
                     Field::make( 'radio', 'op_categories_subscription_pause', __( 'Available Pause Options' ) )
                          ->set_options( array(
                              'not-pause'      => 'Cannot be paused',
                              'pause-category' => 'Can be paused whole category',
                              'pause-items'    => 'Can be paused by items',
                          ) )->set_default_value( 'not-pause' )->set_required( true ),
                 ) );

    }

    /**
     * Проверяет наличие товаров корзины в категории и выводит ошибки при их недостаточном количестве
     * при just_errors = false возвращает ошибки, количество товаров в категории и необходимое количество
     *
     * @param array $items товары
     * @param WP_TERM $category категория
     * @param boolean $just_errors категории
     * @param string $error_type
     *
     * @return null|array
     */
    function check_category_for_allowed_item_numbers( $items, $category, $just_errors = true, $error_type = 'default' ) {
        $errors        = [];
        $count_items   = array_reduce( $items, function ( $count, $item ) {
            foreach ( WC()->cart->get_cart() as $key => $cart_item ) {
                if ( ! method_exists( $item, 'get_id' ) ) {
                    return $count + $item['quantity'];
                }
                if ( $cart_item['data']->get_id() === $item->get_id() ) {
                    return $count + $cart_item['quantity'];
                }
            }
        }, 0 );
        $allowed_items = carbon_get_term_meta( $category->term_id, 'op_categories_subscription_items' );
        if ( empty( $allowed_items ) ) {
            return null;
        }
        $allowed_number_of_items = array_map( 'intval', array_column( $allowed_items, 'number' ) );
        $max_allowed_items       = 0;

        if ( in_array( $count_items, $allowed_number_of_items ) ) {
            if ( $just_errors ) {
                return null;
            }

            return $count_items;
        }
        foreach ( $allowed_number_of_items as $key => $allowed_number ) {

            $max_allowed_items = $max_allowed_items < $allowed_number ? $allowed_number : $max_allowed_items;

            if ( $count_items < $allowed_number ) {
                switch ( $error_type ) {
                    case 'default' :
                        $errors[] = 'We offer staples as an addition to your meal plan. You must <a href="' . get_term_link( $category ) . '">add at least ' . $allowed_number . ' ' . $category->name . '</a> to get this delivered';
                        break;
                    case 'short' :
                        $c        = $allowed_number - $count_items;
                        $errors[] = "Please add at least <b>" . ( $c ) . " more</b> meal" . ( ( $c > 1 ) ? 's' : '' ) . " to complete your meal plan";
                        break;
                }
                break;
            }

        }

        if ( empty( $errors ) ) {

            if ( $count_items > $max_allowed_items ) {

                $errors[] = 'You have to delete ' . ( $max_allowed_items - $count_items ) . ' ' . $category->name . ' from you cart';

                if ( $just_errors ) {
                    return $errors;
                }

                return [
                    'errors' => $errors,
                    'count'  => [
                        'current' => $count_items,
                        'max'     => $max_allowed_items,
                    ]
                ];

            }

        } else {

            if ( $just_errors ) {
                return $errors;
            }

            return [
                'errors' => $errors,
                'count'  => [
                    'current' => $count_items,
                    'max'     => $max_allowed_items,
                ]
            ];

        }


        if ( $just_errors ) {
            return null;
        }

        return $count_items;

    }

    /**
     * получение товаров заказа, разбитых на категории
     *
     * @param WC_Order $order
     *
     * @return array массив с данными о категории, списком товара и стоимости товаров в этой категории
     */
    public function get_items_n_categories_for_order( $order ) {

        if ( empty( $this->woo_categories ) ) {

            $prod_cat_args = array(
                'taxonomy'   => 'product_cat',
                'orderby'    => 'id',
                'hide_empty' => false,
                'parent'     => 0
            );

            $this->woo_categories = get_categories( $prod_cat_args );

        }

        $order_items_by_categories = [];

        foreach ( $this->woo_categories as $woo_cat ) {

            $woo_cat_data = [];


            $woo_cat_data['category'] = $woo_cat;

            $woo_cat_data['products'] = $this->get_items_by_category( $order, $woo_cat );

            $woo_cat_data['total'] = 0;

            foreach ( $woo_cat_data['products'] as $key => $this_cat_product ) {

                $woo_cat_data['total'] += floatval( $this_cat_product->get_total() );

            }

            $order_items_by_categories[ $woo_cat->term_id ] = $woo_cat_data;

        }

        return $order_items_by_categories;

    }

    /**
     * Получение товаров из переданной категории
     *  items 1 2 3 4 5
     *  cat meals
     *  result => 2 3 5
     *
     * @param mixed $items Список товаров
     * @param WP_Term $category Категория
     *
     * @return array товары переданной категории
     */
    function sort_items_by_category( $items, $category ) {

        $items_ids = array_map( function ( $item ) {
            if ( method_exists( $item, 'get_product_id' ) ) {
                return $item->get_product_id();
            }
            if ( method_exists( $item, 'get_parent_id' ) ) {
                return $item->get_parent_id();
            }
            if ( method_exists( $item, 'get_id' ) ) {
                return $item->get_id();
            }

            return $item['product_id'];
        }, $items );

        $custom_query = new WP_Query;
        $filtered     = $custom_query->query( [
            'post_in'        => $items_ids,
            'fields'         => 'ids',
            'posts_per_page' => - 1,
            'tax_query'      => [
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'id',
                    'terms'    => $category->term_id
                )
            ]
        ] );

        $category_items = array_filter( array_map( function ( $item ) use ( $filtered ) {

            if ( method_exists( $item, 'get_product_id' ) ) {

                $item_id = $item->get_product_id();

            } elseif ( method_exists( $item, 'get_parent_id' ) ) {

                $item_id = $item->get_parent_id();

            } elseif ( method_exists( $item, 'get_id' ) ) {

                $item_id = $item->get_id();

            } else {

                $item_id = $item['product_id'];

            }

            if ( in_array( $item_id, $filtered ) ) {
                return $item;
            }
        }, $items ) );

        return $category_items;

    }

    /**
     * Сортируем товары заказа по категории
     *
     * @param WC_Order $order
     * @param WP_Term $category
     *
     * @return array
     */
    function get_items_by_category( $order, $category ) {

        $cart_items = $order->get_items();

        return $this->sort_items_by_category( $cart_items, $category );

    }

    function change_max_product_count( $passed, $product_id, $quantity ) {

        $max_allowed = 14;

        $cart_items_count = WC()->cart->get_cart_contents_count();
        $total_count      = $cart_items_count + $quantity;

        if ( $cart_items_count >= $max_allowed || $total_count > $max_allowed ) {
            // Set to false
            $passed = false;
            // Display a message
            wc_add_notice( __( "You can't have more than {$max_allowed} items in cart", "woocommerce" ), "error" );
        }

        return $passed;

    }

    /**
     * Save updated subscription data
     *
     *
     * @return bool
     */
    function update_checkout_subscription_data() {
        $order   = $this->get_current_subscription();
        $user_id = get_current_user_id();

        $sf_checkout = new SF_Checkout;
        $errors      = $sf_checkout->sf_get_errors();

        $result_errors       = [];
        $current_step_errors = [];

        foreach ( $errors->errors as $code => $messages ) {
            $data = $errors->get_error_data( $code );
            foreach ( $messages as $message ) {
                $result_errors[] = $data['id'];
            }
        }

        // foreach ($errors->errors as $code => $messages) {
        //     $data = $errors->get_error_data($code);
        //     foreach ($messages as $message) {
        //         $result_errors[] = [
        //             'message' => $message,
        //             'field_name' => $data['id'],
        //         ];

        //     }
        // }

        // Step fields
        $fields = [];

        switch ( $_POST['step'] ) {
            case 'Delivery-Address':
                $fields = [
                    'billing_first_name',
                    'billing_last_name',
                    'billing_address_1',
                    'billing_address_2',
                    'billing_country',
                    'billing_postcode',
                    'billing_city',
                    'billing_state',
                    //'use_as_billing_address',
                    'billing_email',
                    'billing_email_offers',
                    'billing_phone',
                    'billing_phone_sms',
                    'order_comments'
                ];
                break;

            case 'Schedule-Your-First-Delivery':
                $delivery_time = op_help()->shop->get_session_cart( 'delivery_date', ( new DateTime() )->format( "Y-m-d" ) );
                $delivery_time = new DateTime( $delivery_time );

                $delivery_date_offset = carbon_get_theme_option( 'op_subscription_order_offset' ) ? (integer) carbon_get_theme_option( 'op_subscription_order_offset' ) : 36;

                update_post_meta( $order->get_id(), "op_next_delivery", $delivery_time->format( "Y-m-d h:m:i" ) );
                update_post_meta( $order->get_id(), "op_next_order_creation", $delivery_time->modify( '- ' . $delivery_date_offset . ' hour' )->format( "Y-m-d h:m:i" ) );
                break;

            case 'Payment-Method':
                $fields = [
                    'shipping_first_name',
                    'shipping_last_name',
                    'shipping_country',
                    'shipping_address_1',
                    'shipping_address_2',
                    'shipping_postcode',
                    'shipping_city',
                    'shipping_state',
                    'shipping_phone'
                ];

                // Validate shipping phone (since it is custom field)
                if ( $_POST['shipping_phone'] == '' ) {
                    wp_send_json_error( [
                        'step_errors' => 'shipping_phone',
                        'step'        => $_POST['step'],
                    ] );
                }

                // Create new token
                $payment_method = op_help()->payment->createToken( $_POST );

                if ( is_wp_error( $payment_method ) ) {
                    $result_errors[] = [
                        'message'    => $payment_method->get_error_message(),
                        'field_name' => 'checkout',
                    ];
                }
                break;
        }

        // Check if current step has error
        if ( ! empty( $result_errors ) ) {
            $current_step_errors = array_filter( $result_errors, function ( $error ) use ( $fields ) {
                return in_array( $error, $fields );
            } );
        }

        if ( ! empty( $current_step_errors ) ) {
            wp_send_json_error( [
                'step_errors' => $current_step_errors,
                'step'        => $_POST['step'],
            ] );
        } else {
            // Update $order metadata
            foreach ( $fields as $field ) {
                if ( $_POST['step'] == 'Delivery-Address' ) {
                    $fields_to_update = [
                        'billing_first_name',
                        'billing_last_name',
                        'billing_country',
                        'billing_address_1',
                        'billing_address_2',
                        'billing_postcode',
                        'billing_city',
                        'billing_state',
                        'billing_phone'
                    ];

                    // Save phone country code to phone number
                    if ( $field == 'billing_phone' ) {
                        $_POST[ $field ] = $_POST['phone_code'] . $_POST[ $field ];
                    }

                    if ( in_array( $field, $fields_to_update ) ) {
                        $field_name = str_replace( 'billing', 'shipping', $field );
                    } else {
                        $field_name = $field;
                    }

                    // Save updated first/last name to order
                    // if ( $field_name == 'billing_first_name' ) {
                    //     update_post_meta( $order->get_id(), '_billing_first_name', $_POST[ $field ] );
                    //     update_post_meta( $order->get_id(), '_shipping_first_name', $_POST[ $field ] );
                    // }
                    // if ( $field_name == 'billing_last_name' ) {
                    //     update_post_meta( $order->get_id(), '_billing_last_name', $_POST[ $field ] );
                    //     update_post_meta( $order->get_id(), '_shipping_last_name', $_POST[ $field ] );
                    // }

                    // Save comment as excerpt (Woo)
                    if ( $field_name == 'order_comments' ) {
                        wp_update_post( [ 'ID' => $order->get_id(), 'post_excerpt' => $_POST[ $field ] ] );
                    } else {
                        update_post_meta( $order->get_id(), '_' . $field_name, $_POST[ $field ] );
                    }
                } else if ( $_POST['step'] == 'Payment-Method' ) {
                    $fields_to_update = [
                        'shipping_first_name',
                        'shipping_last_name',
                        'shipping_country',
                        'shipping_address_1',
                        'shipping_address_2',
                        'shipping_postcode',
                        'shipping_city',
                        'shipping_state',
                        'shipping_phone'
                    ];

                    // Save phone country code to phone number
                    if ( $field == 'shipping_phone' ) {
                        $_POST[ $field ] = $_POST['phone_code'] . $_POST[ $field ];
                    }

                    if ( in_array( $field, $fields_to_update ) ) {
                        $field_name = str_replace( 'shipping', 'billing', $field );
                    } else {
                        $field_name = $field;
                    }

                    update_post_meta( $order->get_id(), '_' . $field_name, $_POST[ $field ] );
                }

                // update_user_meta($user_id, $field, $_POST[$field]);
                // Do we need to update user data?
            }

            wp_send_json_success();
        }
    }
}
