<?php


class Notifications
{

    private static $_instance = null;

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function is_allowed_domain($url)
    {
        return $url === 'lifechef.com' || $url === 'stage.lifechef.com';
    }

    public function init()
    {
        add_action('admin_head', [$this, 'op_subscription_notifications']);
        add_action('notify_order_pause_event', [$this, 'op_subscription_order_pause_notifier']);
        add_action('notify_order_about_to_lock_event', [$this, 'op_subscription_order_about_to_lock_notifier']);
        add_action('notify_order_incomplete_event', [$this, 'op_subscription_order_incomplete_notifier']);
        add_action('woocommerce_order_status_changed', [$this, 'op_subscription_order_delivered_notifier'], 10, 4);
        add_action('notify_abandon_cart_event', [$this, 'op_subscription_abandon_cart_notifier']);
        add_action('notify_expiration_card_event', [$this, 'op_subscription_expiration_card_notifier']);
        add_action('notify_user_one_day_retention', [$this, 'check_user_one_day_retention']);
        add_action('sf_woo_thankyou', [$this, 'sf_send_order_confirmation_email']);
        add_action('wp_login', [$this, 'send_welcome_email'], 20, 2);
        add_action('wp_ajax_notifications_reset_cron_handler', [$this, 'notifications_reset_cron_tasks']);
        add_menu_page('Notifications options page',
            'Notifications options page',
            'manage_options',
            'notifications-options',
            [$this, 'render']);
        add_action('admin_enqueue_scripts', function ($hook_suffix) {
            if ($hook_suffix === 'toplevel_page_notifications-options') {
                wp_enqueue_script('notifications-script', get_stylesheet_directory_uri() . '/assets/js/notifications-admin.js', ['jquery']);
                wp_localize_script('notifications-script', 'settingsNotificationsPage',
                    [
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'ajax_nonce' => wp_create_nonce('life-chef-admin')
                    ]
                );
                wp_enqueue_style('bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css');
                wp_enqueue_script('boot1', 'https://code.jquery.com/jquery-3.3.1.slim.min.js', array('jquery'), '', true);
                wp_enqueue_script('boot2', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array('jquery'), '', true);
                wp_enqueue_script('boot3', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js', array('jquery'), '', true);
            }
        });
//        add_action('init', function () {
//            $this->sf_send_order_confirmation_email(97219);
//        });
    }

    public function notifications_reset_cron_tasks()
    {
        $request = (object)$_POST;
        $request->data = json_decode(stripslashes($request->data), true);
        if (!$this->checkNonce($request->nonce, 'life-chef-admin')) {
            self::returnError('wrong nonce');
        }
        wp_clear_scheduled_hook('notify_order_pause_event');
        wp_clear_scheduled_hook('notify_order_about_to_lock_event');
        wp_clear_scheduled_hook('notify_abandon_cart_event');
        wp_clear_scheduled_hook('notify_expiration_card_event');
        wp_clear_scheduled_hook('notify_order_incomplete_event');
        wp_clear_scheduled_hook('notify_user_one_day_retention');

        self::returnData('reseted');
    }

    public function render()
    {
        get_template_part('template-parts/notifications-admin/notifications-page');
    }

    public function op_subscription_order_delivered_notifier($id, $status_transition_from, $status_transition_to, $that)
    {
        if ($status_transition_to === 'delivered') {
            $this->send_subscription_delivered_email($id);
        }
        if ($status_transition_to === 'shipped') {
            $this->send_subscription_shipped_email($id);
        }
    }

    public function op_subscription_notifications()
    {
        $seven_am = new DateTime('07:00:00', new DateTimeZone('EST'));
        if (!wp_next_scheduled('notify_order_pause_event')) {
            wp_schedule_event(time(), 'hourly', 'notify_order_pause_event');
        }
        if (!wp_next_scheduled('notify_order_about_to_lock_event')) {
            wp_schedule_event($seven_am->getTimestamp(), 'daily', 'notify_order_about_to_lock_event');
        }
        if (!wp_next_scheduled('notify_order_incomplete_event')) {
            wp_schedule_event($seven_am->getTimestamp(), 'daily', 'notify_order_incomplete_event');
        }
        if (!wp_next_scheduled('notify_abandon_cart_event')) {
            wp_schedule_event($seven_am->getTimestamp(), 'daily', 'notify_abandon_cart_event');
        }
        if (!wp_next_scheduled('notify_expiration_card_event')) {
            wp_schedule_event($seven_am->getTimestamp(), 'daily', 'notify_expiration_card_event');
        }
        if (!wp_next_scheduled('notify_user_one_day_retention')) {
            wp_schedule_event($seven_am->getTimestamp(), 'hourly', 'notify_user_one_day_retention');
        }

    }

    public function check_user_one_day_retention()
    {
        if ($this->is_allowed_domain(get_site_url())) {
            $users_ids = get_users(['fields' => 'ID']);
            foreach ($users_ids as $user_id) {
                $user_data[] =
                    [
                        'user_id' => $user_id,
                        'activation_date' => get_user_meta($user_id, 'user_activation_date', true),
                        'persistent_cart' => get_user_meta($user_id, '_woocommerce_persistent_cart_' . get_current_blog_id(), true),
                        'one_day_retention' => get_user_meta($user_id, 'customer_one_day_retention', true)
                    ];
            }
            foreach ($user_data as $user_datum) {
                if ($user_datum['activation_date']
                    && $user_datum['user_id']
                    && $user_datum['persistent_cart']
                    && !$user_datum['one_day_retention']) {
                    $currentTime = new DateTime('now', new DateTimeZone('EST'));
                    if ($currentTime->getTimestamp() - $user_datum['activation_date'] > 86400) {
                        update_user_meta($user_datum['user_id'], 'customer_one_day_retention', true);
                        $acUserId = get_user_meta($user_datum['user_id'], 'activecampaign_user_id', true);
                        $apiUrl = 'https://frontrowlabs.api-us1.com/api/3/contactTags';
                        $apiKey = 'd7692ca0146eddda45220525bc08e7855e79c96dbd30930b0dff426cd114755b490e0838';
                        $params = [
                            'contactTag' => [
                                'contact' => $acUserId,
                                'tag' => get_option('active_campaign_one_day_retention')
                            ]
                        ];
                        $data = wp_remote_post($apiUrl, array(
                            'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Api-Token' => $apiKey],
                            'body' => json_encode($params),
                            'method' => 'POST',
                            'data_format' => 'body',
                        ));
                    }
                }
            }
        }
    }

    public function sf_send_order_confirmation_email($order_id)
    {
        if ($this->is_allowed_domain(get_site_url())) {
            if (!$order_id) {
                return;
            }
            if (get_post_meta($order_id, '_thankyou_action_done', true)) return;
            $order = wc_get_order($order_id);
            $orderNumber = $order->get_order_number();
            $customerName = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            $customerEmail = $order->get_billing_email();
            $op_next_delivery = get_post_meta($order_id, 'op_next_delivery', true);
            $shipDate = date("D M d,Y", strtotime($op_next_delivery));
            $now = new DateTime();
            $op_next_delivery = date_create($op_next_delivery);
            $address = $order->shipping_address_1 . ', ' .
                $order->shipping_address_2 . ' ' .
                $order->shipping_city . ', ' .
                $order->shipping_state . ' ' .
                $order->shipping_postcode;
            $customerPhone = $order->billing_phone;
            $orderNote = $order->get_customer_note();
            $token_data = WC_Payment_Tokens::get_customer_default_token($order->get_customer_id())->get_data();
            $cardNumber = $token_data['last4'];
            $cardExp = $token_data['expiry_month'] . '/' . mb_substr($token_data['expiry_year'], 2);
            $card_type = $token_data['card_type'];
            switch ($card_type) {
                case 'Mastercard':
                    break;
                case 'American Express':
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/amex.png';
                    break;
                case 'Visa':
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/visa.png';
                    break;
                case 'JCB':
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/jcb.png';
                    break;
                case 'Discover':
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/discover.png';
                    break;
                case 'Diners Club':
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/dinersclub.png';
                    break;
                case 'China Union Pay':
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/unionpay.png';
                    break;
                default:
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/card-placeholder.png';
                    break;
            }
            $order_subtotal = number_format($order->get_subtotal(), 2);
            $order_total = is_callable(array($order, 'get_total')) ? $order->get_total() : $order->order_total;
            $order_tax = $order->get_total_tax();
            $order_shipp_total = $order->get_shipping_total();
            $order_edit = get_site_url() . '/cart/';
            $qty = [];
            foreach ($order->get_items() as $item_id => $item) {
                $qty[$item->get_product()->get_id()] = $item->get_quantity();
            }
            $prod_cat_args = array(
                'taxonomy' => 'product_cat',
                'orderby' => 'id',
                'hide_empty' => false,
                'parent' => 0
            );
            $woo_categories = get_categories($prod_cat_args);
            $cart_cat_data = [];
            foreach ($woo_categories as $key => $woo_cat) {
                $new_products = op_help()->subscriptions->sort_items_by_category($order->get_items(), $woo_cat);
                if (empty($new_products)) {
                    continue;
                }
                foreach ($new_products as $product) {
                    $image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_product()->get_id()));
                    $productName = !empty($product->get_variation_id()) ? get_post_meta($product->get_variation_id(), 'op_post_title', 1) : $product->get_name();
                    $cart_cat_data[$woo_cat->name][$product->get_id()] = [
                        'id' => $product->get_id(),
                        'name' => $productName,
                        'price' => '$' . number_format($product->get_product()->get_price(), 2),
                        'image' => $image ? $image[0] : wc_placeholder_img_src(),
                        'qty' => $qty[$product->get_product()->get_id()]
                    ];
                }
            }

            $is_user_in_local_zone = op_help()->zip_codes->get_current_user_zone($order->get_customer_id()) === 'local';

            // footer links
            $open_chat_url = get_bloginfo('url') . '/offerings?chat-open=open';
            $ask_question_url = get_bloginfo('url') . '/faq?ask-question=open';
            $terms_url = get_bloginfo('url') . '/terms-conditions/';
            $privacy_url = get_bloginfo('url') . '/privacy-policy/';
            $contact_us_url = get_bloginfo('url') . '/contact-us';

            $social_fb_url = 'https://google.com';
            $social_insta_url = 'https://google.com';
            $social_twitter_url = 'https://google.com';
            $unsubscribe_url = 'https://google.com';

            // send order confirmation
            $apiUrl = 'https://api.sendgrid.com/v3/mail/send';
            $apiKey = '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';
            $templateId = 'd-9e6eba6b04c945d38777e3da0d917425';

        $params = array(
            'from' =>
                array(
                    'email' => 'hello@lifechef.com',
                    'name' => 'LifeChef™ Team',
                ),
            'personalizations' =>
                array(
                    0 =>
                        array(
                            'to' =>
                                array(
                                    0 =>
                                        array(
                                            'email' => $customerEmail,
                                        ),
                                ),
                            'dynamic_template_data' =>
                                array(
                                    'customer_name' => $customerName,
                                    'order_number' => $orderNumber,
                                    'ship_date' => $shipDate,
                                    'address' => $address,
                                    'customer_phone' => $customerPhone,
                                    'customer_email' => $customerEmail,
                                    'order_note' => $orderNote,
                                    'card_type' => $card_type,
                                    'card_icon' => $card_icon,
                                    'days_left' => date_diff($now, $op_next_delivery)->days,
                                    'order_edit' => $order_edit,
                                    'card_number' => $cardNumber,
                                    'card_exp' => $cardExp,
                                    'order_subtotal' => $order_subtotal,
                                    'order_total' => $order_total,
                                    'order_tax' => $order_tax,
                                    'order_shipp_total' => $order_shipp_total,
                                    'meals_exist' => array_key_exists('Meals', $cart_cat_data) && $is_user_in_local_zone,
                                    'meals' => array_key_exists('Meals', $cart_cat_data) && $is_user_in_local_zone ? $cart_cat_data['Meals'] : [],
                                    'staples_exist' => array_key_exists('Groceries', $cart_cat_data) && $is_user_in_local_zone,
                                    'staples' => array_key_exists('Groceries', $cart_cat_data) ? $cart_cat_data['Groceries'] : [],
                                    'vitamins_exist' => array_key_exists('Vitamins', $cart_cat_data),
                                    'vitamins' => array_key_exists('Vitamins', $cart_cat_data) ? $cart_cat_data['Vitamins'] : [],
                                    'open_chat_url' => $open_chat_url,
                                    'ask_question_url' => $ask_question_url,
                                    'terms_url' => $terms_url,
                                    'privacy_url' => $privacy_url,
                                    'contact_us_url' => $contact_us_url,
                                    'social_fb_url' => $social_fb_url,
                                    'social_insta_url' => $social_insta_url,
                                    'social_twitter_url' => $social_twitter_url,
                                    'unsubscribe_url' => $unsubscribe_url,
                                ),
                        ),
                ),
            'template_id' => $templateId,
        );

            wp_remote_post($apiUrl, array(
                'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => 'Bearer ' . $apiKey],
                'body' => json_encode($params),
                'method' => 'POST',
                'data_format' => 'body',
            ));

            $order->update_meta_data('_thankyou_action_done', true);
            $order->save();
        }
    }

    public function send_welcome_email($user_login, $user)
    {
        if ($this->is_allowed_domain(get_site_url())) {
            $social_fb_url = 'https://google.com';
            $social_insta_url = 'https://google.com';
            $social_twitter_url = 'https://google.com';
            $unsubscribe_url = 'https://google.com';

            $isEmailSend = get_user_meta($user->ID, 'welcom_email_sent', true);

            if ($user && !$isEmailSend) {
                // footer links
                $open_chat_url = get_bloginfo('url') . '/offerings?chat-open=open';
                $ask_question_url = get_bloginfo('url') . '/faq?ask-question=open';
                $terms_url = get_bloginfo('url') . '/terms-conditions/';
                $privacy_url = get_bloginfo('url') . '/privacy-policy/';
                $contact_us_url = get_bloginfo('url') . '/contact-us';
                $baseUrl = get_bloginfo('url') . '/offerings';

                $apiUrl = 'https://api.sendgrid.com/v3/mail/send';
                $apiKey = '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';
                $templateId = 'd-67f4b642832f470a8f48ac8ff11684b5';

                $params = array(
                    'from' =>
                        array(
                            'email' => 'hello@lifechef.com',
                            'name' => 'LifeChef™ Team',
                        ),
                    'personalizations' =>
                        array(
                            0 =>
                                array(
                                    'to' =>
                                        array(
                                            0 =>
                                                array(
                                                    'email' => $user->user_email,
                                                ),
                                        ),
                                    'dynamic_template_data' =>
                                        array(
                                            'customer_name' => $user->user_firstname . ' ' . $user->user_lastname,
                                            'open_chat_url' => $open_chat_url,
                                            'ask_question_url' => $ask_question_url,
                                            'terms_url' => $terms_url,
                                            'privacy_url' => $privacy_url,
                                            'contact_us_url' => $contact_us_url,
                                            'welcome_url' => $baseUrl,
                                            'social_fb_url' => $social_fb_url,
                                            'social_insta_url' => $social_insta_url,
                                            'social_twitter_url' => $social_twitter_url,
                                            'unsubscribe_url' => $unsubscribe_url
                                        ),
                                ),
                        ),
                    'template_id' => $templateId,
                );

                $responce = wp_remote_post($apiUrl, array(
                    'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => 'Bearer ' . $apiKey],
                    'body' => json_encode($params),
                    'method' => 'POST',
                    'data_format' => 'body',
                ));

                update_user_meta($user->ID, 'welcom_email_sent', true);
            }
        }
    }


    function op_subscription_expiration_card_notifier()
    {
        if ($this->is_allowed_domain(get_site_url())) {
            $orders = wc_get_orders(
                [
                    'numberposts' => -1,
                    'post_status' =>
                        [
                            'wc-op-subscription',
                            'wc-op-paused',
                            'wc-op-incomplete'
                        ]
                ]);
            foreach ($orders as $order) {
                $cardExp = get_post_meta($order->get_id(), '_cc_expiry', 1);
                $expYear = substr($cardExp, 2, 2);
                $expMonth = substr($cardExp, 0, 2);
                $expDate = date_create('01-' . $expMonth . '-' . '20' . $expYear);
                $now = new DateTime(date('Y-m-d'));
                $exp_date = $expDate->getTimestamp();
                $now_date = $now->getTimestamp();
                $now_date = date_create('01-01-2021')->getTimestamp();
                $exp_2months = date_create()->setTimestamp($exp_date)->modify('-2months')->getTimestamp();
                $exp_2months_2week = date_create()->setTimestamp($exp_date)->modify('-2months')->modify('+2week')->getTimestamp();
                $exp_2months_3week = date_create()->setTimestamp($exp_date)->modify('-2months')->modify('+3week')->getTimestamp();
                if ($exp_2months === $now_date ||
                    $exp_2months_2week === $now_date ||
                    $exp_2months_3week === $now_date) {
                    $user = new WP_User($order->get_customer_id());
                    $this->send_subscription_card_expired($user);
                }
            }
        }
    }

    function op_subscription_abandon_cart_notifier()
    {
        if ($this->is_allowed_domain(get_site_url())) {
            $users = get_users();
            foreach ($users as $user) {
                $persistent_cart = get_user_meta($user->ID, '_woocommerce_persistent_cart_' . get_current_blog_id(), true);
                if ($persistent_cart) {
                    $orders = wc_get_orders(['customer_id' => $user->ID]);
                    if ($orders) {
                        $subscribed = false;
                        foreach ($orders as $order) {
                            $status = $order->get_status();
                            if ($status === 'op-subscription'
                                || $status === 'wc-op-paused'
                                || $status === 'wc-op-incomplete'
                                || $status === 'wc-shipped'
                                || $status === 'wc-shipping'
                                || $status === 'wc-delivered') {
                                $subscribed = true;
                            }
                        }
                        if (!$subscribed) {
                            $products = [];
                            foreach ($persistent_cart['cart'] as $item) {
                                if ($item['variation_id']) {
                                    $product = wc_get_product($item['variation_id']);
                                    if ($product->get_attribute('pa_part-1')) {
                                        $products[$product->get_id()]['id'] = $product->get_id();
                                        $products[$product->get_id()]['name'] = get_the_title($product->get_id());
                                        $products[$product->get_id()]['price'] = number_format($product->get_price(), 2);
                                        $products[$product->get_id()]['image'] = wp_get_attachment_image_url($product->get_image_id(), 'op_archive_thumbnail');
                                        $products[$product->get_id()]['qty'] = $item['quantity'];
                                    }
                                }
                            }
                            $this->send_subscription_abandon_cart_email($user, $products);
                        }
                    }
                }
            }
        }
    }

    function send_subscription_card_expired($user)
    {
        if ($this->is_allowed_domain(get_site_url())) {
            $apiUrl = 'https://api.sendgrid.com/v3/mail/send';
            $apiKey = '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';
            $templateId = 'd-1bf4aec0f0e040d89b0c94ad187c66e2';

            $params = array(
                'from' =>
                    array(
                        'email' => 'hello@lifechef.com',
                        'name' => 'LifeChef™ Team',
                    ),
                'personalizations' =>
                    array(
                        0 =>
                            array(
                                'to' =>
                                    array(
                                        0 =>
                                            array(
                                                'email' => $user->user_email,
                                            ),
                                    ),
                                'dynamic_template_data' =>
                                    array(
                                        'customer_name' => $user->user_firstname . ' ' . $user->user_lastname,
                                    ),
                            ),
                    ),
                'template_id' => $templateId,
            );

            wp_remote_post($apiUrl, array(
                'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => 'Bearer ' . $apiKey],
                'body' => json_encode($params),
                'method' => 'POST',
                'data_format' => 'body',
            ));
        }
    }


    function send_subscription_shipped_email($order_id)
    {
        if ($this->is_allowed_domain(get_site_url())) {
            if (!$order_id)
                return;

            $order = wc_get_order($order_id);

            $orderNote = $order->get_customer_note();
            $orderNumber = $order->get_order_number();
            $customerName = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            $customerEmail = $order->get_billing_email();

            $shipDate = date("D M d,Y", strtotime(get_post_meta($order_id, 'op_next_delivery', true)));
            $address = $order->shipping_address_1 . ', ' .
                $order->shipping_address_2 . ' ' .
                $order->shipping_city . ', ' .
                $order->shipping_state . ' ' .
                $order->shipping_postcode;
            $customerPhone = $order->billing_phone;
            $order_edit = get_site_url() . '/cart/';

            // get order items
            $qty = [];
            foreach ($order->get_items() as $item_id => $item) {
                $qty[$item->get_product()->get_id()] = $item->get_quantity();
            }

            $prod_cat_args = array(
                'taxonomy' => 'product_cat',
                'orderby' => 'id',
                'hide_empty' => false,
                'parent' => 0
            );

            $woo_categories = get_categories($prod_cat_args);
            $cart_cat_data = [];
            foreach ($woo_categories as $key => $woo_cat) {
                $new_products = op_help()->subscriptions->sort_items_by_category($order->get_items(), $woo_cat);
                if (empty($new_products)) {
                    continue;
                }
                foreach ($new_products as $product) {
                    $image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_product()->get_id()));
                    $productName = !empty($product->get_variation_id()) ? get_post_meta($product->get_variation_id(), 'op_post_title', 1) : $product->get_name();
                    $cart_cat_data[$woo_cat->name][$product->get_id()] = [
                        'id' => $product->get_id(),
                        'name' => $productName,
                        'price' => '$' . number_format($product->get_product()->get_price(), 2),
                        'image' => $image ? $image[0] : wc_placeholder_img_src(),
                        'qty' => $qty[$product->get_product()->get_id()]
                    ];
                }
            }
            // footer links
            $open_chat_url = get_bloginfo('url') . '/offerings?chat-open=open';
            $ask_question_url = get_bloginfo('url') . '/faq?ask-question=open';
            $terms_url = get_bloginfo('url') . '/terms-conditions/';
            $privacy_url = get_bloginfo('url') . '/privacy-policy/';
            $contact_us_url = get_bloginfo('url') . '/contact-us';
            $show_tracking = true;
            $fedex_tracking_numbers_string = get_post_meta($order_id, '_pp_tracking_number', true);
            $fedex_tracking_numbers = explode(',', $fedex_tracking_numbers_string);
            if (get_post_meta($order_id, '_pp_carrier', true) === 'Route4me') {
                $show_tracking = false;
                $track_shipper_icon = get_stylesheet_directory_uri() . '/assets/img/shippers/local.png';
            }
            if (get_post_meta($order_id, '_pp_carrier', true) === 'EasyPost (UPS)') {
                $track_delivery_url = 'https://www.ups.com/track?tracknum=' . $fedex_tracking_numbers[0];
                $track_shipper_icon = get_stylesheet_directory_uri() . '/assets/img/shippers/ups.png';
            }
            if (get_post_meta($order_id, '_pp_carrier', true) === 'EasyPost (FedEx)') {
                $track_delivery_url = 'https://www.fedex.com/fedextrack/?tracknumbers=' . $fedex_tracking_numbers[0] . '&cntry_code=in';
                $track_shipper_icon = get_stylesheet_directory_uri() . '/assets/img/shippers/fedex.png';
            }
            $order_subtotal = number_format($order->get_subtotal(), 2);
            $order_total = is_callable(array($order, 'get_total')) ? $order->get_total() : $order->order_total;
            $order_tax = $order->get_total_tax();
            $order_shipp_total = $order->get_shipping_total();

            $token_data = WC_Payment_Tokens::get_customer_default_token($order->get_customer_id())->get_data();
            $cardNumber = $token_data['last4'];
            $cardExp = $token_data['expiry_month'] . '/' . mb_substr($token_data['expiry_year'], 2);
            $card_type = $token_data['card_type'];
            switch ($card_type) {
                case 'Mastercard':
                    break;
                case 'American Express':
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/amex.png';
                    break;
                case 'Visa':
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/visa.png';
                    break;
                case 'JCB':
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/jcb.png';
                    break;
                case 'Discover':
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/discover.png';
                    break;
                case 'Diners Club':
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/dinersclub.png';
                    break;
                case 'China Union Pay':
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/unionpay.png';
                    break;
                default:
                    $card_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/card-placeholder.png';
                    break;
            }

            // send order confirmation
            $apiUrl = 'https://api.sendgrid.com/v3/mail/send';
            $apiKey = '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';
            $templateId = 'd-0c27df1e101b4475a8126dc4dee4f023';

            $social_fb_url = 'https://google.com';
            $social_insta_url = 'https://google.com';
            $social_twitter_url = 'https://google.com';
            $unsubscribe_url = 'https://google.com';

            $is_user_in_local_zone = op_help()->zip_codes->get_current_user_zone($order->get_customer_id()) === 'local';

        $params = array(
            'from' =>
                array(
                    'email' => 'hello@lifechef.com',
                    'name' => 'LifeChef™ Team',
                ),
            'personalizations' =>
                array(
                    0 =>
                        array(
                            'to' =>
                                array(
                                    0 =>
                                        array(
                                            'email' => $customerEmail,
                                        ),
                                ),
                            'dynamic_template_data' =>
                                array(
                                    'customer_name' => $customerName,
                                    'order_number' => $orderNumber,
                                    'ship_date' => $shipDate,
                                    'address' => $address,
                                    'track_delivery_number' => $fedex_tracking_numbers_string,
                                    'track_delivery_url' => $track_delivery_url,
                                    'shipper_icon' => $track_shipper_icon,
                                    'show_tracking' => $show_tracking,
                                    'card_type' => $card_type,
                                    'card_icon' => $card_icon,
                                    'customer_phone' => $customerPhone,
                                    'customer_email' => $customerEmail,
                                    'order_edit' => $order_edit,
                                    'order_note' => $orderNote,
                                    'meals_exist' => array_key_exists('Meals', $cart_cat_data) && $is_user_in_local_zone,
                                    'meals' => array_key_exists('Meals', $cart_cat_data)  ? $cart_cat_data['Meals'] : [],
                                    'staples_exist' => array_key_exists('Groceries', $cart_cat_data) && $is_user_in_local_zone,
                                    'staples' => array_key_exists('Groceries', $cart_cat_data) ? $cart_cat_data['Groceries'] : [],
                                    'vitamins_exist' => array_key_exists('Vitamins', $cart_cat_data),
                                    'vitamins' => array_key_exists('Vitamins', $cart_cat_data) ? $cart_cat_data['Vitamins'] : [],
                                    'open_chat_url' => $open_chat_url,
                                    'ask_question_url' => $ask_question_url,
                                    'terms_url' => $terms_url,
                                    'privacy_url' => $privacy_url,
                                    'social_fb_url' => $social_fb_url,
                                    'social_insta_url' => $social_insta_url,
                                    'social_twitter_url' => $social_twitter_url,
                                    'unsubscribe_url' => $unsubscribe_url,
                                    'contact_us_url' => $contact_us_url,
                                    'order_subtotal' => $order_subtotal,
                                    'order_total' => $order_total,
                                    'order_tax' => $order_tax,
                                    'card_number' => $cardNumber,
                                    'card_exp' => $cardExp,
                                    'order_shipp_total' => $order_shipp_total,
                                ),
                        ),
                ),
            'template_id' => $templateId,
        );

            wp_remote_post($apiUrl, array(
                'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => 'Bearer ' . $apiKey],
                'body' => json_encode($params),
                'method' => 'POST',
                'data_format' => 'body',
            ));
        }
    }

    function send_subscription_abandon_cart_email($user, $meals)
    {
        if ($this->is_allowed_domain(get_site_url())) {
            // footer links
            $baseUrl = get_bloginfo('url');
            $open_chat_url = $baseUrl . '/offerings?chat-open=open';
            $ask_question_url = $baseUrl . '/faq?ask-question=open';
            $terms_url = $baseUrl . '/terms-conditions/';
            $privacy_url = $baseUrl . '/privacy-policy/';
            $contact_us_url = $baseUrl . '/contact-us';

            // custom email template variable
            $updateCartLink = $baseUrl . '/cart/';
            $meals_total_quantity = 0;
            foreach ($meals as $meal) {
                $meals_total_quantity += $meal['qty'];
            }
            if ($meals_total_quantity <= 6) {
                $cart_meals_max = 6;
            }
            if ($meals_total_quantity > 6 && $meals_total_quantity <= 10) {
                $cart_meals_max = 10;
            }
            if ($meals_total_quantity > 10 && $meals_total_quantity <= 14) {
                $cart_meals_max = 14;
            }
            $cartMealsCount = $meals_total_quantity;
            $manageOrderLink = $baseUrl . '/cart/';
            $exploreMoreMeals = $baseUrl . '/product-category/meals/';

            $social_fb_url = 'https://google.com';
            $social_insta_url = 'https://google.com';
            $social_twitter_url = 'https://google.com';
            $unsubscribe_url = 'https://google.com';

            $apiUrl = 'https://api.sendgrid.com/v3/mail/send';
            $apiKey = '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';
            $templateId = 'd-7b884aeed8b440fe8ad14c6acd83ab3f';

            $params = array(
                'from' =>
                    array(
                        'email' => 'hello@lifechef.com',
                        'name' => 'LifeChef™ Team',
                    ),
                'personalizations' =>
                    array(
                        0 =>
                            array(
                                'to' =>
                                    array(
                                        0 =>
                                            array(
                                                'email' => $user->user_email,
                                            ),
                                    ),
                                'dynamic_template_data' =>
                                    array(
                                        'customer_name' => $user->user_firstname . ' ' . $user->user_lastname,
                                        'open_chat_url' => $open_chat_url,
                                        'ask_question_url' => $ask_question_url,
                                        'terms_url' => $terms_url,
                                        'privacy_url' => $privacy_url,
                                        'update_cart_link' => $updateCartLink,
                                        'cart_meals_count' => $cartMealsCount,
                                        'manage_order_link' => $manageOrderLink,
                                        'explore_more_meals' => $exploreMoreMeals,
                                        'social_fb_url' => $social_fb_url,
                                        'social_insta_url' => $social_insta_url,
                                        'social_twitter_url' => $social_twitter_url,
                                        'unsubscribe_url' => $unsubscribe_url,
                                        'contact_us_url' => $contact_us_url,
                                        'meals' => $meals,
                                        'cart_meals_max' => $cart_meals_max
                                    ),
                            ),
                    ),
                'template_id' => $templateId,
            );

            wp_remote_post($apiUrl, array(
                'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => 'Bearer ' . $apiKey],
                'body' => json_encode($params),
                'method' => 'POST',
                'data_format' => 'body',
            ));
        }
    }


    function send_subscription_delivered_email($order_id)
    {
        if ($this->is_allowed_domain(get_site_url())) {
            if (!$order_id)
                return;

            $order = wc_get_order($order_id);

            $orderNumber = $order->get_order_number();
            $customerName = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            $customerEmail = $order->get_billing_email();

            $shipDate = date("D M d,Y", strtotime(get_post_meta($order_id, 'op_next_delivery', true)));
            $address = $order->shipping_address_1 . ', ' .
                $order->shipping_address_2 . ' ' .
                $order->shipping_city . ', ' .
                $order->shipping_state . ' ' .
                $order->shipping_postcode;
            $customerPhone = $order->billing_phone;
            $orderNote = $order->get_customer_note();
            $token_data = WC_Payment_Tokens::get_customer_default_token($order->get_customer_id())->get_data();
            $cardNumber = $token_data['last4'];
            $cardExp = $token_data['expiry_month'] . '/' . mb_substr($token_data['expiry_year'], 2);
            $card_type = $token_data['card_type'];
            switch ($card_type) {
                case 'Mastercard':
                    break;
                case 'American Express':
                    $cart_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/amex.svg';
                    break;
                case 'Visa':
                    $cart_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/visa.svg';
                    break;
                case 'JCB':
                    $cart_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/jcb.svg';
                    break;
                case 'Discover':
                    $cart_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/discover.svg';
                    break;
                case 'Diners Club':
                    $cart_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/dinersclub.svg';
                    break;
                case 'China Union Pay':
                    $cart_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/unionpay.svg';
                    break;
                default:
                    $cart_icon = get_stylesheet_directory_uri() . '/assets/img/payment-systems/card-placeholder.svg';
                    break;
            }

            $order_subtotal = number_format($order->get_subtotal(), 2);
            $order_total = is_callable(array($order, 'get_total')) ? $order->get_total() : $order->order_total;
            $order_tax = $order->get_total_tax();
            $order_shipp_total = $order->get_shipping_total();
            $order_edit = get_site_url() . '/cart/';

            $qty = [];
            foreach ($order->get_items() as $item_id => $item) {
                $qty[$item->get_product()->get_id()] = $item->get_quantity();
            }

            $prod_cat_args = array(
                'taxonomy' => 'product_cat',
                'orderby' => 'id',
                'hide_empty' => false,
                'parent' => 0
            );

            $woo_categories = get_categories($prod_cat_args);
            $cart_cat_data = [];
            foreach ($woo_categories as $key => $woo_cat) {
                $new_products = op_help()->subscriptions->sort_items_by_category($order->get_items(), $woo_cat);
                if (empty($new_products)) {
                    continue;
                }


                foreach ($new_products as $product) {
                    $image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_product()->get_id()));
                    $productName = !empty($product->get_variation_id()) ? get_post_meta($product->get_variation_id(), 'op_post_title', 1) : $product->get_name();

                    $cart_cat_data[$woo_cat->name][$product->get_id()] = [
                        'id' => $product->get_id(),
                        'name' => $productName,
                        'price' => '$' . number_format($product->get_product()->get_price(), 2),
                        'image' => $image ? $image[0] : wc_placeholder_img_src(),
                        'qty' => $qty[$product->get_product()->get_id()]
                    ];
                }
            }

            // footer links
            $open_chat_url = get_bloginfo('url') . '/offerings?chat-open=open';
            $ask_question_url = get_bloginfo('url') . '/faq?ask-question=open';
            $terms_url = get_bloginfo('url') . '/terms-conditions/';
            $privacy_url = get_bloginfo('url') . '/privacy-policy/';
            $contact_us_url = get_bloginfo('url') . '/contact-us';

            $show_tracking = true;
            $fedex_tracking_numbers_string = get_post_meta($order_id, '_pp_tracking_number', true);
            $fedex_tracking_numbers = explode(',', $fedex_tracking_numbers_string);
            if (get_post_meta($order_id, '_pp_carrier', true) === 'Route4me') {
                $track_shipper_icon = get_stylesheet_directory_uri() . '/assets/img/shippers/local.png';
            }
            if (get_post_meta($order_id, '_pp_carrier', true) === 'EasyPost (UPS)') {
                $track_delivery_url = 'https://www.ups.com/track?tracknum=' . $fedex_tracking_numbers[0];
                $track_shipper_icon = get_stylesheet_directory_uri() . '/assets/img/shippers/ups.png';
            }
            if (get_post_meta($order_id, '_pp_carrier', true) === 'EasyPost (FedEx)') {
                $track_delivery_url = 'https://www.fedex.com/fedextrack/?tracknumbers=' . $fedex_tracking_numbers[0] . '&cntry_code=in';
                $track_shipper_icon = get_stylesheet_directory_uri() . '/assets/img/shippers/fedex.png';
            }

            $boxes_count = count($fedex_tracking_numbers);

            $social_fb_url = 'https://google.com';
            $social_insta_url = 'https://google.com';
            $social_twitter_url = 'https://google.com';
            $unsubscribe_url = 'https://google.com';

            // send order confirmation
            $apiUrl = 'https://api.sendgrid.com/v3/mail/send';
            $apiKey = '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';
            $templateId = 'd-daa3a24c2bdd4389a0171136e5442993';

            $is_user_in_local_zone = op_help()->zip_codes->get_current_user_zone($order->get_customer_id()) === 'local';

        $params = array(
            'from' =>
                array(
                    'email' => 'hello@lifechef.com',
                    'name' => 'LifeChef™ Team',
                ),
            'personalizations' =>
                array(
                    0 =>
                        array(
                            'to' =>
                                array(
                                    0 =>
                                        array(
                                            'email' => $customerEmail,
                                        ),
                                ),
                            'dynamic_template_data' =>
                                array(
                                    'customer_name' => $customerName,
                                    'order_number' => $orderNumber,
                                    'track_delivery_number' => $fedex_tracking_numbers_string,
                                    'track_delivery_url' => $track_delivery_url,
                                    'show_tracking' => $show_tracking,
                                    'shipper_icon' => $track_shipper_icon,
                                    'boxes_count' => $boxes_count . ' boxes',
                                    'box_images_count' => $boxes_count,
                                    'card_type' => $card_type,
                                    'card_icon' => $cart_icon,
                                    'ship_date' => $shipDate,
                                    'address' => $address,
                                    'customer_phone' => $customerPhone,
                                    'customer_email' => $customerEmail,
                                    'order_note' => $orderNote,
                                    'order_edit' => $order_edit,
                                    'card_number' => $cardNumber,
                                    'card_exp' => $cardExp,
                                    'order_subtotal' => $order_subtotal,
                                    'order_total' => $order_total,
                                    'order_tax' => $order_tax,
                                    'order_shipp_total' => $order_shipp_total,
                                    'meals_exist' => array_key_exists('Meals', $cart_cat_data) && $is_user_in_local_zone,
                                    'meals' => array_key_exists('Meals', $cart_cat_data) ? $cart_cat_data['Meals'] : [],
                                    'staples_exist' => array_key_exists('Groceries', $cart_cat_data) && $is_user_in_local_zone,
                                    'staples' => array_key_exists('Groceries', $cart_cat_data) ? $cart_cat_data['Groceries'] : [],
                                    'vitamins_exist' => array_key_exists('Vitamins', $cart_cat_data),
                                    'vitamins' => array_key_exists('Vitamins', $cart_cat_data) ? $cart_cat_data['Vitamins'] : [],
                                    'open_chat_url' => $open_chat_url,
                                    'ask_question_url' => $ask_question_url,
                                    'terms_url' => $terms_url,
                                    'privacy_url' => $privacy_url,
                                    'social_fb_url' => $social_fb_url,
                                    'social_insta_url' => $social_insta_url,
                                    'social_twitter_url' => $social_twitter_url,
                                    'unsubscribe_url' => $unsubscribe_url,
                                    'contact_us_url' => $contact_us_url
                                ),
                        ),
                ),
            'template_id' => $templateId,
        );

            wp_remote_post($apiUrl, array(
                'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => 'Bearer ' . $apiKey],
                'body' => json_encode($params),
                'method' => 'POST',
                'data_format' => 'body',
            ));
        }
    }

    function op_subscription_order_incomplete_notifier()
    {
        if ($this->is_allowed_domain(get_site_url())) {
            $orders_query = new WP_Query;
            $args = [
                'post_type' => 'shop_order',
                'post_status' => ['wc-op-incomplete'],
                'nopaging' => true,
                'fields' => 'ids'
            ];
            $ready_orders_ids = $orders_query->query($args);
            foreach ($ready_orders_ids as $ready_order_id) {
                $sended = get_post_meta($ready_order_id, 'order_incomplete_notifier_sended', true);
                if (!$sended) {
                    $order = wc_get_order($ready_order_id);
                    $delivery_date = op_help()->subscriptions->get_delivery_date($order);
                    $delivery_date_offset = carbon_get_theme_option('op_subscription_order_schedule_offset') ? (integer)carbon_get_theme_option('op_subscription_order_schedule_offset') : 77;
                    if (!$delivery_date) {
                        return false;
                    }
                    try {
                        $lock_date = $delivery_date->sub(new DateInterval("PT{$delivery_date_offset}H"));
                        $now = new DateTime();
                    } catch (Exception $e) {
                        return false;
                    }

                    if ($now->getTimestamp() < $lock_date->getTimestamp()) {
                        $order_items = $order->get_items();
                        foreach ($order_items as $item) {
                            if ($item['variation_id']) {
                                $product = wc_get_product($item->get_variation_id());
                                if ($product->get_attribute('pa_part-1')) {
                                    $products[$product->get_id()]['id'] = $product->get_id();
                                    $products[$product->get_id()]['name'] = get_the_title($product->get_id());
                                    $products[$product->get_id()]['price'] = number_format($product->get_price(), 2);
                                    $products[$product->get_id()]['image'] = wp_get_attachment_image_url($product->get_image_id(), 'op_archive_thumbnail');
                                    $products[$product->get_id()]['qty'] = $item->get_quantity();
                                }
                            }
                        }
                        $next_delivery = new DateTime(get_post_meta($order->get_id(), 'op_next_delivery', true));
                        $next_delivery = date('D, F j', $next_delivery->getTimestamp());
                        $user = new WP_User($order->get_user_id());
                    }
                }
                if ($user && $products) {
                    $this->send_subscription_incomplete_email($products, $user, $next_delivery);
                    update_post_meta($ready_order_id, 'order_incomplete_notifier_sended', true);
                }
            }
        }
    }

// send subscription incomplete
    function send_subscription_incomplete_email($meals, $user, $delivery_date)
    {
        if ($this->is_allowed_domain(get_site_url())) {
            $meals_total_quantity = 0;
            foreach ($meals as $meal) {
                $meals_total_quantity += $meal['qty'];
            }
            // footer links
            $baseUrl = get_bloginfo('url');
            $open_chat_url = $baseUrl . '/offerings?chat-open=open';
            $ask_question_url = $baseUrl . '/faq?ask-question=open';
            $terms_url = $baseUrl . '/terms-conditions/';
            $privacy_url = $baseUrl . '/privacy-policy/';
            $contact_us_url = $baseUrl . '/contact-us';

            // custom email template variable
            $updateCartLink = $baseUrl . '/cart/';
            $cartMealsCount = $meals_total_quantity;
            $manageOrderLink = $baseUrl . '/cart/';
            $exploreMoreMeals = $baseUrl . '/product-category/meals/';

            $social_fb_url = 'https://google.com';
            $social_insta_url = 'https://google.com';
            $social_twitter_url = 'https://google.com';
            $unsubscribe_url = 'https://google.com';

            if ($meals_total_quantity <= 6) {
                $cart_meals_max = 6;
            }
            if ($meals_total_quantity > 6 && $meals_total_quantity <= 10) {
                $cart_meals_max = 10;
            }
            if ($meals_total_quantity > 10 && $meals_total_quantity <= 14) {
                $cart_meals_max = 14;
            }

            $apiUrl = 'https://api.sendgrid.com/v3/mail/send';
            $apiKey = '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';
            $templateId = 'd-7104dfd67b704779ad9fc4a5d4aeb3ce';

            $params = array(
                'from' =>
                    array(
                        'email' => 'hello@lifechef.com',
                        'name' => 'LifeChef™ Team',
                    ),
                'personalizations' =>
                    array(
                        0 =>
                            array(
                                'to' =>
                                    array(
                                        0 =>
                                            array(
                                                'email' => $user->user_email,
                                            ),
                                    ),
                                'dynamic_template_data' =>
                                    array(
                                        'customer_name' => $user->user_firstname . ' ' . $user->user_lastname,
                                        'open_chat_url' => $open_chat_url,
                                        'ask_question_url' => $ask_question_url,
                                        'terms_url' => $terms_url,
                                        'privacy_url' => $privacy_url,
                                        'update_cart_link' => $updateCartLink,
                                        'cart_meals_count' => $cartMealsCount,
                                        'manage_order_link' => $manageOrderLink,
                                        'explore_more_meals' => $exploreMoreMeals,
                                        'social_fb_url' => $social_fb_url,
                                        'social_insta_url' => $social_insta_url,
                                        'social_twitter_url' => $social_twitter_url,
                                        'unsubscribe_url' => $unsubscribe_url,
                                        'contact_us_url' => $contact_us_url,
                                        'meals' => $meals,
                                        'cart_meals_max' => $cart_meals_max,
                                        'delivery_date' => $delivery_date
                                    ),
                            ),
                    ),
                'template_id' => $templateId,
            );

            wp_remote_post($apiUrl, array(
                'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => 'Bearer ' . $apiKey],
                'body' => json_encode($params),
                'method' => 'POST',
                'data_format' => 'body',
            ));
        }
    }


    function op_subscription_order_about_to_lock_notifier()
    {
        if ($this->is_allowed_domain(get_site_url())) {
            $orders_query = new WP_Query();
            $args =
                [
                    'post_type' => 'shop_order',
                    'post_status' => ['wc-op-subscription'],
                    'nopaging' => true,
                    'fields' => 'ids'
                ];
            $ready_orders_ids = $orders_query->query($args);
            foreach ($ready_orders_ids as $ready_order_id) {

                $sended = get_post_meta($ready_order_id, 'about_to_lock_notification_sended', true);
                if (!$sended) {
                    $order = wc_get_order($ready_order_id);
                    $delivery_date = op_help()->subscriptions->get_delivery_date($order);
                    $order_schedule_offset = carbon_get_theme_option('op_subscription_order_schedule_offset') ? (integer)carbon_get_theme_option('op_subscription_order_schedule_offset') : 77;
                    if (!$delivery_date) {
                        return false;
                    }
                    try {
                        $lock_date = $delivery_date->sub(new DateInterval("PT{$order_schedule_offset}H"));

                        $lock_date = $lock_date->getTimestamp();
                        $now = new DateTime();

                        $now = $now->getTimestamp();
                    } catch (Exception $e) {
                        return false;
                    }

                    if (($lock_date - $now) > 0 && ($lock_date - $now) < 86400) {
                        $this->op_send_order_about_to_lock_notifier($order->get_id());
                        update_post_meta($ready_order_id, 'about_to_lock_notification_sended', true);
                    }
                }
            }
        }
    }


    function op_send_order_about_to_lock_notifier($order_id) {
	    if ( $this->is_allowed_domain( get_site_url() ) ) {
		    if ( ! $order_id ) {
			    return;
		    }

		    $order = wc_get_order( $order_id );

		    $orderNumber   = $order->get_order_number();
		    $customerName  = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
		    $customerEmail = $order->get_billing_email();

		    $shipDate      = date( "D M d,Y", strtotime( get_post_meta( $order_id, 'op_next_delivery', true ) ) );
		    $address       = $order->shipping_address_1 . ', ' .
		                     $order->shipping_address_2 . ' ' .
		                     $order->shipping_city . ', ' .
		                     $order->shipping_state . ' ' .
		                     $order->shipping_postcode;
		    $customerPhone = $order->billing_phone;
		    $orderNote     = $order->get_customer_note();
		    $token_data    = WC_Payment_Tokens::get_customer_default_token( $order->get_customer_id() )->get_data();
		    $cardNumber    = $token_data['last4'];
		    $cardExp       = $token_data['expiry_month'] . '/' . mb_substr( $token_data['expiry_year'], 2 );

		    $order_subtotal    = number_format( $order->get_subtotal(), 2 );
		    $order_total       = is_callable( array(
			    $order,
			    'get_total'
		    ) ) ? $order->get_total() : $order->order_total;
		    $order_tax         = $order->get_total_tax();
		    $order_shipp_total = $order->get_shipping_total();
		    $order_edit        = get_site_url() . '/cart/?from-email-template=true';

		    $qty = [];
		    foreach ( $order->get_items() as $item_id => $item ) {
			    $qty[ $item->get_product()->get_id() ] = $item->get_quantity();
		    }

		    $prod_cat_args = array(
			    'taxonomy'   => 'product_cat',
			    'orderby'    => 'id',
			    'hide_empty' => false,
			    'parent'     => 0
		    );

		    $woo_categories = get_categories( $prod_cat_args );
		    $cart_cat_data  = [];
		    foreach ( $woo_categories as $key => $woo_cat ) {
			    $new_products = op_help()->subscriptions->sort_items_by_category( $order->get_items(), $woo_cat );
			    if ( empty( $new_products ) ) {
				    continue;
			    }


			    foreach ( $new_products as $product ) {
				    $image       = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_product()->get_id() ) );
				    $productName = ! empty( $product->get_variation_id() ) ? get_post_meta( $product->get_variation_id(),
					    'op_post_title', 1 ) : $product->get_name();

				    $cart_cat_data[ $woo_cat->name ][ $product->get_id() ] = [
					    'id'    => $product->get_id(),
					    'name'  => $productName,
					    'price' => '$' . number_format( $product->get_product()->get_price(), 2 ),
					    'image' => $image ? $image[0] : wc_placeholder_img_src(),
					    'qty'   => $qty[ $product->get_product()->get_id() ]
				    ];
			    }
		    }

		    $groceries_query = wc_get_products( [ 'category' => 'groceries', 'limit' => 3 ] );
		    $groceries_param = [];
		    foreach ( $groceries_query as $grocery ) {
			    $image        = wp_get_attachment_image_src( get_post_thumbnail_id( $grocery->get_id() ) );
			    $product_name = $grocery->get_name();
			    if ( $grocery->get_sale_price() && $grocery->get_regular_price() ) {
				    $discount = 100 - round( number_format( (float) $grocery->get_sale_price(),
							    2 ) / number_format( (float) $grocery->get_regular_price(),
							    2 ) * 100 );
			    } else {
				    $discount = false;
			    }
			    $groceries_param[ $grocery->get_id() ] = [
				    'id'            => $grocery->get_id(),
				    'name'          => $product_name,
				    'old_price'     => '$' . $grocery->get_regular_price(),
				    'current_price' => '$' . $grocery->get_sale_price(),
				    'discount'      => ! is_nan( $discount ) && $discount ? $discount . '% off' : false,
				    'image'         => $image ? $image[0] : wc_placeholder_img_src(),
				    'link'          => get_permalink( $grocery->get_id() )
			    ];
		    }

		    // footer links
		    $open_chat_url    = get_bloginfo( 'url' ) . '/offerings?chat-open=open';
		    $ask_question_url = get_bloginfo( 'url' ) . '/faq?ask-question=open';
		    $terms_url        = get_bloginfo( 'url' ) . '/terms-conditions/';
		    $privacy_url      = get_bloginfo( 'url' ) . '/privacy-policy/';
		    $contact_us_url   = get_bloginfo( 'url' ) . '/contact-us';

		    $social_fb_url      = 'https://google.com';
		    $social_insta_url   = 'https://google.com';
		    $social_twitter_url = 'https://google.com';
		    $unsubscribe_url    = 'https://google.com';

		    // send order confirmation
		    $apiUrl     = 'https://api.sendgrid.com/v3/mail/send';
		    $apiKey     = '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';
		    $templateId = 'd-b123449f343a4e46a12e21a7b3a30d90';

            $is_user_in_local_zone = op_help()->zip_codes->get_current_user_zone($order->get_customer_id()) === 'local';


            $params = array(
			    'from'             =>
				    array(
					    'email' => 'hello@lifechef.com',
					    'name'  => 'LifeChef™ Team',
				    ),
			    'personalizations' =>
				    array(
					    0 =>
						    array(
							    'to'                    =>
								    array(
									    0 =>
										    array(
											    'email' => $customerEmail,
										    ),
								    ),
							    'dynamic_template_data' =>
								    array(
									    'customer_name'      => $customerName,
									    'order_number'       => $orderNumber,
									    'ship_date'          => $shipDate,
									    'address'            => $address,
									    'groceries'          => $groceries_param,
									    'customer_phone'     => $customerPhone,
									    'customer_email'     => $customerEmail,
									    'order_note'         => $orderNote,
									    'order_edit'         => $order_edit,
									    'card_number'        => $cardNumber,
									    'card_exp'           => $cardExp,
									    'order_subtotal'     => $order_subtotal,
									    'order_total'        => $order_total,
									    'order_tax'          => $order_tax,
									    'order_shipp_total'  => $order_shipp_total,
									    'meals_exist'        => array_key_exists( 'Meals', $cart_cat_data ) && $is_user_in_local_zone,
									    'meals'              => array_key_exists( 'Meals', $cart_cat_data ) ? $cart_cat_data['Meals'] : [],
									    'staples_exist'      => array_key_exists( 'Groceries', $cart_cat_data ) && $is_user_in_local_zone,
									    'staples'            => array_key_exists( 'Groceries', $cart_cat_data ) ? $cart_cat_data['Groceries'] : [],
									    'vitamins_exist'     => array_key_exists( 'Vitamins', $cart_cat_data ),
									    'vitamins'           => array_key_exists( 'Vitamins', $cart_cat_data ) ? $cart_cat_data['Vitamins'] : [],
									    'open_chat_url'      => $open_chat_url,
									    'ask_question_url'   => $ask_question_url,
									    'terms_url'          => $terms_url,
									    'privacy_url'        => $privacy_url,
									    'social_fb_url'      => $social_fb_url,
									    'social_insta_url'   => $social_insta_url,
									    'social_twitter_url' => $social_twitter_url,
									    'unsubscribe_url'    => $unsubscribe_url,
									    'contact_us_url'     => $contact_us_url
								    ),
						    ),
				    ),
			    'template_id'      => $templateId,
		    );
		    wp_remote_post( $apiUrl, array(
			    'headers'     => [
				    'Content-Type'  => 'application/json; charset=utf-8',
				    'Authorization' => 'Bearer ' . $apiKey
			    ],
			    'body'        => json_encode( $params ),
			    'method'      => 'POST',
			    'data_format' => 'body',
		    ) );
	    }
    }


    function op_subscription_order_pause_notifier($order_id = false)
    {
        if ($this->is_allowed_domain(get_site_url())) {
            if (!$order_id) {
                $orders_query = new WP_Query;
                $args = [
                    'post_type' => 'shop_order',
                    'post_status' => ['wc-op-paused'],
                    'meta_query' =>
                        [
                            [
                                'key' => 'op_subscription_notify_pause',
                                'value' => 'true'
                            ]
                        ],
                    'nopaging' => true,
                    'fields' => 'ids'
                ];

                $ready_orders_ids = $orders_query->query($args);
                $users = [];
            } else {
                $ready_orders_ids[] = $order_id;
            }
            foreach ($ready_orders_ids as $ready_order_id) {
                $order = wc_get_order($ready_order_id);
                $order_items = $order->get_items();
                foreach ($order_items as $item) {
                    if ($item['variation_id']) {
                        $product = wc_get_product($item->get_variation_id());
                        if ($product->get_attribute('pa_part-1')) {
                            $products[$product->get_id()]['id'] = $product->get_id();
                            $products[$product->get_id()]['name'] = get_the_title($product->get_id());
                            $products[$product->get_id()]['price'] = number_format($product->get_price(), 2);
                            $products[$product->get_id()]['image'] = wp_get_attachment_image_url($product->get_image_id(), 'op_archive_thumbnail');
                            $products[$product->get_id()]['qty'] = $item->get_quantity();
                        }
                    }
                }
                update_post_meta($ready_order_id, 'op_subscription_notify_pause', 'false');
                $users[] = ['user' => new WP_User($order->get_user_id()), 'products' => $products];
            }

            foreach ($users as $user) {
                $this->send_subscription_paused_email(get_bloginfo('url') . '/cart/', $user['user'], $user['products']);
            }
        }

    }

    /// send subscription pause
    function send_subscription_paused_email($reactivateLink, $user_obj, $meals)
    {
        if ($this->is_allowed_domain(get_site_url())) {
            $baseUrl = get_bloginfo('url');
            $open_chat_url = $baseUrl . '/offerings?chat-open=open';
            $ask_question_url = $baseUrl . '/faq?ask-question=open';
            $terms_url = $baseUrl . '/terms-conditions/';
            $privacy_url = $baseUrl . '/privacy-policy/';
            $contact_us_url = $baseUrl . '/contact-us';

            $apiUrl = 'https://api.sendgrid.com/v3/mail/send';
            $apiKey = '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';
            $templateId = 'd-845efb769806440a8f6675abdc02839c';

            $social_fb_url = 'https://google.com';
            $social_insta_url = 'https://google.com';
            $social_twitter_url = 'https://google.com';
            $unsubscribe_url = 'https://google.com';

            $params = array(
                'from' =>
                    array(
                        'email' => 'hello@lifechef.com',
                        'name' => 'LifeChef™ Team',
                    ),
                'personalizations' =>
                    array(
                        0 =>
                            array(
                                'to' =>
                                    array(
                                        0 =>
                                            array(
                                                'email' => $user_obj->user_email,
                                            ),
                                    ),
                                'dynamic_template_data' =>
                                    array(
                                        'customer_name' => $user_obj->user_firstname . ' ' . $user_obj->user_lastname,
                                        'open_chat_url' => $open_chat_url,
                                        'ask_question_url' => $ask_question_url,
                                        'terms_url' => $terms_url,
                                        'privacy_url' => $privacy_url,
                                        'reactivate_link' => $reactivateLink,
                                        'social_fb_url' => $social_fb_url,
                                        'social_insta_url' => $social_insta_url,
                                        'social_twitter_url' => $social_twitter_url,
                                        'unsubscribe_url' => $unsubscribe_url,
                                        'contact_us_url' => $contact_us_url,
                                        'meals' => $meals,
                                        'meals_exist' => true,
                                        'cart_url' => get_site_url() . '/cart/'
                                    ),
                            ),
                    ),
                'template_id' => $templateId,
            );

            wp_remote_post($apiUrl, array(
                'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => 'Bearer ' . $apiKey],
                'body' => json_encode($params),
                'method' => 'POST',
                'data_format' => 'body',
            ));
        }
    }

    private function checkNonce($nonce, $action)
    {
        return wp_verify_nonce($nonce, $action);
    }

    private static function returnData($data = [])
    {
        header('Content-Type:application/json');
        echo json_encode(['status' => true, 'data' => $data]);
        wp_die();
    }

    private static function returnError($message)
    {
        header('Content-Type:application/json');
        wp_send_json_error($message, 400);
        wp_die();
    }
}
