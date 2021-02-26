<?php

class MealPlanModal
{
    private static $_instance = null;

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function init()
    {
        add_action('wp_ajax_meals_count_checker', [$this, 'meals_count_checker']);
        add_action('wp_ajax_nopriv_meals_count_checker', [$this, 'meals_count_checker']);
        add_action('wp_ajax_add_to_cart', [$this, 'add_to_cart']);
        add_action('wp_ajax_nopriv_add_to_cart', [$this, 'add_to_cart']);
        add_action('wp_ajax_add_to_cart_single', [$this, 'add_to_cart_single']);
        add_action('wp_ajax_nopriv_add_to_cart_single', [$this, 'add_to_cart_single']);
        add_action('wp_ajax_remove_from_cart', [$this, 'remove_from_cart']);
        add_action('wp_ajax_remove_from_cart_totally', [$this, 'remove_from_cart_totally']);
        add_action('wp_ajax_nopriv_remove_from_cart', [$this, 'remove_from_cart']);
        add_action('wp_ajax_nopriv_remove_from_cart_totally', [$this, 'remove_from_cart_totally']);
        add_filter('woocommerce_add_to_cart_validation', [$this, 'wc_ajax_added_to_cart_event_handler'], 10, 3);
        add_filter('woocommerce_update_cart_validation', [$this, 'update_cart_event_handler'], 5, 4);
        add_filter('woocommerce_cart_redirect_after_error', '__return_false');
        add_action('wp_enqueue_scripts', function () {
            if (stripos($_SERVER['REQUEST_URI'], 'product-category/meals')) {
                wp_enqueue_script('debounce', 'https://cdn.jsdelivr.net/npm/lodash@4.17.20/lodash.min.js', ['jquery']);
                wp_enqueue_script('nice-number-js', get_stylesheet_directory_uri() . '/assets/js/node_modules/jquery.nice-number/dist/jquery.nice-number.min.js', ['jquery']);
                wp_enqueue_script('meal-plan-bottom-nav', get_stylesheet_directory_uri() . '/assets/js/meal-plan-bottom-nav.js', ['jquery', 'nice-number-js', 'debounce']);
                wp_localize_script('meal-plan-bottom-nav', 'ajaxSettingsMealPlan', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'site_url' => get_site_url(),
                    'stylesheet_dir' => get_stylesheet_directory_uri()
                ]);
            }
        });
    }

    public function update_cart_event_handler($passed, $cart_item_key_prev, $values, $quantity)
    {
        $cart_items = WC()->cart->get_cart();
        $meals_quantity = 0;
        foreach ($cart_items as $cart_item_key => $cart_item) {
            if (wc_get_product_terms($cart_item['product_id'], 'product_cat')[0]->slug === 'meals' && $cart_item_key_prev !== $cart_item_key) {
                $meals_quantity += $cart_item['quantity'];
            }
        }
        if ($values['variation_id'] && wc_get_product_terms($values['product_id'], 'product_cat')[0]->slug === 'meals') {
            if($quantity > $values['quantity']) {
                $meals_quantity += $quantity;
            }
            if ($meals_quantity >= 15) {
                return $passed = false;
            } else {
                return $passed = true;
            }
        }
        return true;
    }

    public function meals_count_checker()
    {
        $request = (object)$_POST;
        $request->data = json_decode(stripslashes($request->data), true);
        $cart_items = WC()->cart->get_cart();
        $quantity = 0;
        foreach ($cart_items as $cart_item_key => $cart_item) {
            if (wc_get_product_terms($cart_item['product_id'], 'product_cat')[0]->slug === 'meals') {
                $quantity += $cart_item['quantity'];
            }
        }
        if ($request->data['product_id'] && wc_get_product_terms($request->data['product_id'], 'product_cat')[0]->slug === 'meals') {
            $quantity += $request->data['quantity'];
            if ($quantity >= 15) {
                self::returnError('Maximum meals reached');
            } else {
                self::returnData('OK');
            }
        }
    }


    public function wc_ajax_added_to_cart_event_handler($passed, $product_id, $quantity)
    {
        $product = wc_get_product($product_id);
        if ($product && 'variation' === $product->get_type()) {
            $product_id = $product->get_parent_id();
        }
        if (wc_get_product_terms($product_id, 'product_cat')[0]->slug === 'meals') {
            $cart_items = WC()->cart->get_cart();
            $all_quantity = 0;
            foreach ($cart_items as $cart_item_key => $cart_item) {
                if (wc_get_product_terms($cart_item['product_id'], 'product_cat')[0]->slug === 'meals') {
                    $all_quantity += $cart_item['quantity'];
                }
            }
            $all_quantity += $quantity;
            if ($all_quantity >= 15) {
                $passed = false;
            }
        }
        return $passed;
    }

    public function get_delivery_date_from_previous_order()
    {
        if (is_user_logged_in()) {
            $subscription = op_help()->subscriptions->get_current_subscription();
            if (!empty($subscription)) {
                return $subscription->get_meta('op_next_delivery');
            }
        }
    }

    public function remove_from_cart_totally()
    {
        $request = (object)$_POST;
        if (!$request->post_id) {
            self::returnError('empty post id');
        }
        $cart_items = WC()->cart->get_cart();
        foreach ($cart_items as $cart_item_key => $cart_item) {
            if ((integer)$cart_item['variation_id'] === (integer)$request->post_id) {
                WC()->cart->set_quantity($cart_item_key, 0);
            }
        }
        WC_AJAX::get_refreshed_fragments();
    }

    public function get_cart_items()
    {
        $cart_items = WC()->cart->get_cart();
        $prod_list = [];
        $i = 0;
        foreach ($cart_items as $cart_item) {
            $product_id = $cart_item['data']->get_id();
            $cached_product = op_help()->global_cache->get_cached_product( $product_id );

            if ( $cached_product['type'] == 'variation' ) {
                $prod_list[$i]['id'] = $product_id;
                $prod_list[$i]['permalink'] = get_permalink( $product_id );
                $prod_list[$i]['title'] = implode(', ', $cart_item['variation']);
                $prod_list[$i]['quantity'] = $cart_item['quantity'];
                $prod_list[$i]['price'] = $cart_item['data']->get_price();
                $prod_list[$i]['image_url'] = $cached_product['_thumbnail_id_url'];
                $i++;
            }

        }
        return $prod_list;
    }

    public function get_cart_all_items()
    {
        $cart_items = WC()->cart->get_cart();
        $products = [];

        foreach ($cart_items as $cart_item) {
            $products[] = [
                'id' => $cart_item['data']->get_id(),
                'title' => implode(', ', $cart_item['variation']),
                'quantity' => $cart_item['quantity']
            ];
        }

        return $products;
    }

    public function add_to_cart()
    {
        $request = (object)$_POST;
        if (!$request->post_id) {
            self::returnError('empty post id');
        }
        if (!$request->quantity) {
            self::returnError('empty post id');
        }

        $cart_items = WC()->cart->get_cart();
        foreach ($cart_items as $cart_item_key => $cart_item) {
            if ((integer)$cart_item['variation_id'] === (integer)$request->post_id) {
                WC()->cart->set_quantity($cart_item_key, (integer)$request->quantity);
            }
        }
        WC_AJAX::get_refreshed_fragments();
    }

    public function add_to_cart_single()
    {
        $request = (object)$_POST;
        if (!$request->post_id) {
            self::returnError('empty post id');
        }

        WC()->cart->add_to_cart($request->post_id);
        WC_AJAX::get_refreshed_fragments();
    }

    public function remove_from_cart()
    {
        $request = (object)$_POST;
        if (!$request->post_id) {
            self::returnError('empty post id');
        }

        $cart_items = WC()->cart->get_cart();
        foreach ($cart_items as $cart_item_key => $cart_item) {
            if ((integer)$cart_item['variation_id'] === (integer)$request->post_id) {
                WC()->cart->set_quantity($cart_item_key, (integer)$request->quantity);
            }
        }
        WC_AJAX::get_refreshed_fragments();
    }

    private function checkNonce($nonce, $action)
    {
        return wp_verify_nonce($nonce, $action);
    }

    private static function returnData($data = [])
    {
        header('Content-Type:application/json');
        echo json_encode(array('status' => true, 'data' => $data));
        wp_die();
    }

    private static function returnError($message)
    {
        header('Content-Type:application/json');
        wp_send_json_error($message, 400);
        wp_die();
    }
}
