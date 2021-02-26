<?php

class CartPageClass
{
    private static $_instance = null;

    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function getURLSegments()
    {
        return explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    }

    private function getURLSegment($n)
    {
        $segs = $this->getURLSegments();
        return count($segs) > 0 && count($segs) >= ($n - 1) ? $segs[$n] : '';
    }

    public function init()
    {
        if ($this->getURLSegment(1) === 'cart') {
            add_action('wp_enqueue_scripts', function () {
                wp_enqueue_script('cart-validation', get_stylesheet_directory_uri() . '/assets/js/cart-validation.js', ['jquery']);
            });
        }
        add_action('wp_login', [$this, 'manage_user_cart_after_login'], 10, 2);

    }

    public function manage_user_cart_after_login($user_login, $user)
    {
        if (metadata_exists('user', $user->ID, '_woocommerce_persistent_cart_' . get_current_blog_id())
            && !empty(get_user_meta($user->ID, '_woocommerce_persistent_cart_' . get_current_blog_id()))) {
            WC()->session->__unset('cart');
        }
    }
}
