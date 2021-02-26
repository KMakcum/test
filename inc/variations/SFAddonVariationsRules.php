<?php

/**
 * Class SFAddonVariationsRules
 *
 * Свои урлы для вариаций
 * Подгрузка нужной вариации на соответствующих страницах
 *
 * @todo flush rules
 */
class SFAddonVariationsRules
{
    private $product_base;

    private static $_instance = null;

    private function __construct()
    {
    }

    /**
     * @return SFAddonVariationsRules
     */
    static public function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * init all hooks
     */
    static function init(): void
    {
        $self = self::getInstance();

        add_action('init', [$self, 'addRules']);
        add_action('template_redirect', [$self, 'templateRedirect']);
        add_filter('post_type_link', [$self, 'setVariationLink'], 20, 4);
    }

    /**
     * add rules for variations
     *
     * @todo динамическое создание ЧПУ под все типы товаров
     */
    function addRules(): void
    {
        add_rewrite_rule('^' . $this->productBase() . '/([^/]*)/([^/]*)/?', 'index.php?product=$matches[1]&variation=$matches[2]', 'top');

        add_filter('query_vars', function ($vars) {
            $vars[] = 'variation';
            return $vars;
        });
    }

    function productBase(): string
    {
        if (empty($this->product_base)) {
            $woocommerce_permalinks = get_option('woocommerce_permalinks');
            $this->product_base = trim($woocommerce_permalinks['product_base'], '/');
        }
        return $this->product_base;
    }

    /**
     * Устанавливает корректные ссылки для вариаций
     *
     * @param $post_link
     * @param $post
     * @param null $leavename
     * @param null $sample
     * @return string
     */
    function setVariationLink($post_link, $post, $leavename = null, $sample = null): string
    {
        if ($post->post_type === 'product_variation')
            $post_link = $this->variationLink($post->ID) ?: $post_link;
        return $post_link;
    }

    /**
     * Получение ссылки для вариации
     *
     * @param $variation_id
     * @return false|string
     */
    function variationLink($variation_id)
    {
        $product_variation = wc_get_product($variation_id);
        if (empty($product_variation))
            return false;
        $product = wc_get_product($product_variation->get_parent_id());
        if (empty($product))
            return false;

        $post_link = '/' . $this->productBase() . '/' . $product->get_slug() . '/' . $product_variation->get_slug() . '/';
        return home_url($post_link);
    }

    /**
     * Check the variation and 301/404 redirect if need
     */
    function templateRedirect(): void
    {
        $product_variation = $this->getCurrentVariation();

        if (is_array($product_variation) && $product_variation['not_found']) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
        } else if ($product_variation) {
            $variation_slug = get_query_var('variation', null);
            if ($variation_slug !== $product_variation->get_slug()) {
                wp_redirect($this->variationLink($product_variation->get_id()), 301);
                exit;
            }
        }
    }

    /**
     * Получение текущей вариации товара
     *
     * @return WC_Product_Variation|null|false|array
     */
    function getCurrentVariation()
    {

        global $op_finded_variation;

        if (!empty($op_finded_variation)) {

            return $op_finded_variation;

        }

        $current_object = get_queried_object();

        if (!is_object($current_object)) {
            return null;
        }

        if ($current_object->post_type !== 'product') {
            return null;
        }


        $product = wc_get_product($current_object->ID);

        $product_slug = get_query_var('product', null);

        if (empty($product_slug) || $current_object->post_name !== $product_slug) {

            return null;

        }
        $variation_slug = get_query_var('variation', null);

        if (empty($variation_slug)) {
            if (!method_exists($product, 'get_variation_attributes')) return null;

            foreach ($product->get_variation_attributes() as $attr_key => $attr_data) {
                if (!empty($_GET["attribute_" . $attr_key])) {
                    if (in_array($_GET["attribute_" . $attr_key], $attr_data)) {
                        $meta_query[] = [
                            'key' => "attribute_" . $attr_key,
                            'compare' => '=',
                            'value' => $_GET["attribute_" . $attr_key],
                        ];
                    }
                }
            }

            if (!empty($meta_query)) {

                $meta_query['relation'] = 'AND';

            } else {

                return null;

            }

            $variation_finder = new WP_Query;

            $query_to_find_variation = $variation_finder->query([
                'post_type' => 'product_variation',
                'post_parent' => $product->get_id(),
                'meta_query' => $meta_query,
                'posts_per_page' => 1,
            ]);

            if (!empty($query_to_find_variation)) {

                $op_finded_variation = wc_get_product($query_to_find_variation[0]->ID);

                return $op_finded_variation;

            }

        }

        $variation_finder = new WP_Query;

        $query_to_find_variation = $variation_finder->query([
            'post_type' => 'product_variation',
            'post_parent' => $product->get_id(),
            'name' => $variation_slug,
            'posts_per_page' => 1,
        ]);

        if (!empty($query_to_find_variation)) {

            $op_finded_variation = wc_get_product($query_to_find_variation[0]->ID);

            return $op_finded_variation;

        }

        return [
            'not_found' => true
        ];

    }
}
