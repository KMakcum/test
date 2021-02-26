<?php
/**
 * Work with subscription and create orders
 *
 * @class   SF_Subscription
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class SF_Subscription
 */
// class SF_Subscription extends WC_Order
class SF_Subscription extends \Automattic\WooCommerce\Admin\Overrides\Order
{

    private $woo_categories = [];
    private $order_items_by_categories = [];

    protected $orders = [];
    protected $orders_total = 0;
    protected $next_order_date = '';
    protected $frequency_groups = [];
    protected $active = true;
    protected $active_groups = [];
    protected $active_items = [];

    protected $active_payment_method;
    protected $active_delivery_method;
    protected $active_billing_address;
    protected $active_delivery_address; // if empty will be taken from billing

    function __construct($order_id)
    {

        parent::__construct($order_id);

    }

    /**
     * подсчитывает максимальное количество вариаций заказов у подписки на основе периодичности товаров в ней
     */
    function calc_count_of_available_order_variations()
    {

        // TODO need algorithm

        return 22;

    }

    /**
     * перевод периодичности в число
     */
    function get_frequency_num($frequency_name)
    {
        switch ($frequency_name) {
            case 'one_time_purchase' :
                return 'once';
            case 'every_week':
                return 1;
            case 'every_2_weeks':
                return 2;
            case 'every_3_weeks':
                return 3;
            case 'every_4_weeks':
                return 4;
            default:
                return 0;
        }
    }

    /**
     * генерация и сохранение всех вариаций заказа на основе периодичности
     *
     * @param bool $is_save
     * @return array
     */
    function generate_order_variations( $is_save = true )
    {
        $items = $this->get_items_n_categories();
        // maybe need to save this results and regen after order update
        $max_variations = $this->calc_count_of_available_order_variations();
        $order_variations = [];

        foreach ($items as $category_id => $order_category_data) {

            if (empty($order_category_data['products'])) {
                continue;
            } // no need to do something here if there are not any items

            $category_paused = $this->get_meta('op_paused')[$order_category_data['category']->term_id] == 'on';
            if ($category_paused) {
                continue;
            } // no need to do something here if category paused

            $pause_option[$category_id] = $pause_option[$category_id] ?? carbon_get_term_meta($category_id,
                    'op_categories_subscription_pause');

            if ($pause_option[$category_id] == 'pause-items') {
                $items_canbe_paused = true; // we have to check items activity
            } else {
                $items_canbe_paused = false;
            }

            foreach ($order_category_data['products'] as $key => $item) {

                if ($items_canbe_paused) {
                    if ($item->get_meta('op_paused') == 'on') {
                        continue;
                    } // item is paused
                }
                // todo once
                $item_frequency_num = $this->get_frequency_num($item->get_meta('op_frequency'));
                for ($week = 0; $week < $max_variations; $week++) {

                    if ($item_frequency_num === 'once') {
                        if ($week === 0) {
                            $order_variations[$week][] = $item->get_id();
                        }
                    } elseif ($week % $item_frequency_num === 0) {
                        $order_variations[$week][] = $item->get_id();
                    }

                }

            }

        }

        $this->update_meta_data("order_variations", $order_variations);

        if ( $is_save ) {
            $this->save();
        } else {
            update_post_meta( $this->get_id(), "order_variations", $order_variations );
        }

        return $order_variations;
    }


    /**
     * Получает сохраненные вариации. Опционально можно принудительно сгенерировать актуальные
     *
     * @param bool $fresh сгенерировать свежие данные
     * @param bool $is_save
     *
     * @return array
     */
    function get_order_variations($fresh = false, $is_save = true)
    {

        if ($fresh) {
            return $this->generate_order_variations( $is_save );
        }

        $variations = $this->get_meta("order_variations");

        if (empty($variations)) {

            $variations = $this->generate_order_variations( $is_save );

        }

        return $variations;

    }


    /**
     * проверяет наличие изменений будущих заказов
     */
    function has_future_modifications()
    {

        $weeks = $this->get_order_variations();

        foreach ($weeks as $week_num => $value) {

            if (!empty($this->get_meta("op_future_items_" . $week_num))) {
                return true;
            }

        }

        return false;

    }

    /**
     * очищает изменения будущих заказов
     */
    function clear_future_modifications()
    {

        $weeks = $this->get_order_variations();

        foreach ($weeks as $week_num => $value) {

            $this->delete_meta_data("op_future_items_" . $week_num);
            $this->delete_meta_data("op_future_pause_" . $week_num);

        }

        $this->save();

    }


    /**
     * создает заказ из подписки
     * @throws WC_Data_Exception
     */
    function create_order_from_cron()
    {
        $current_time = new DateTime();
        $creation_time = new DateTime($this->get_meta("op_next_order_creation"));
        $delivery_time = new DateTime($this->get_meta("op_next_delivery"));

        // check for time
        if ($current_time < $creation_time) {
            return false;
        } // strange situation but need to check

        if (empty($this->get_meta("op_next_week"))) {
            $op_next_week = 0;
        } else {
            $op_next_week = intval($this->get_meta("op_next_week"));
        }

        $new_order = $this->create_order($op_next_week, 'wc-processing');

        if ( $new_order ) {
            TB::add_message( "[#{$new_order}](" .  get_site_url() . "/wp-admin/post.php?post=$new_order&action=edit), ", 'cron_order_m' );
        }

        $op_next_week++;
        $delivery_date_offset = carbon_get_theme_option('op_subscription_order_offset') ? (integer)carbon_get_theme_option('op_subscription_order_offset') : 36;

        $this->update_meta_data("op_next_week", $op_next_week);
        $this->update_meta_data("op_next_delivery", $delivery_time->modify("+1 weeks")->format("Y-m-d h:m:i"));
        $this->update_meta_data("op_next_order_creation",
            $delivery_time->modify('- ' . $delivery_date_offset . ' hour')->format("Y-m-d h:m:i"));
        $this->save();

        return $new_order;
    }

    /**
     * создает заказ на основе недели и присваивает статус
     *
     * @param mixed $week номер недели (вариации заказа)
     * @param mixed $finish_status
     *
     * @return string ID of new Order
     * @throws WC_Data_Exception
     */
	function create_order( $week, $finish_status ) { // in real life week should be taked from subscription meta
		$week_num      = intval( $week );
		$items_by_week = $this->get_order_variations( true, true );
		$needed_items  = $items_by_week[ $week_num ];

        $new_order = clone $this;
        $new_order->set_id( 0 );

//        TB::m($needed_items);
        foreach ( $this->get_items() as $key => $item ) {
            if ( in_array( $item->get_id(), $needed_items ) ) {
                $new_item = clone $item;
				$new_item->set_id( 0 );
				$new_item->save();

				$new_order->add_item( $new_item );
			}
		}

		foreach ( $this->get_shipping_methods() as $item ) {
            $new_item = clone $item;
            $new_item->set_id( 0 );
            $new_item->save();

            $new_order->add_item( $new_item );
		}

		$new_order->set_date_created( time() );
        $new_order->set_parent_id( $this->get_id() );
//        $new_order->calculate_totals();

        $new_order->set_status( $finish_status, 'Order created from Subscription#' . $this->get_order_number() );
        $new_order->save();

        // Оплата переехала на событие смены статуса Ордера на shipped. ./index.php:payment_by_hook

		return $new_order->get_order_number();
	}

    /**
     * Get orders for this subscription
     *
     * @return array
     */
    function get_created_orders($fields = 'all')
    {

        if (!isset($this->child_order_ids)) {

            $child_query = new WP_Query;

            $this->child_order_ids = $child_query->query([
                'post_type' => 'shop_order',
                'post_parent' => $this->get_id(),
                'orderby' => 'modified', // get orders by last update
                'order' => 'ASC',
                'post_status' => [
                    'wc-pending',
                    'wc-processing',
                    'wc-on-hold',
                    'wc-completed',
                    'wc-cancelled',
                    'wc-refunded',
                    'wc-failed',
                ],
                'fields' => 'ids'
            ]);

        }

        if ($fields == 'all') {

            if (!isset($this->child_orders)) {

                $this->child_orders = array_map('wc_get_order', $this->child_order_ids);

            }

            return $this->child_orders;

        }

        return $this->child_order_ids;

    }


    /**
     * получение заказов подписки с группировкой по неделям
     */
    function get_created_orders_by_weeks()
    {
        $orders = $this->get_created_orders();

        $weeks_constructor = [];
        foreach ($orders as $key => $order) {
            // $order->get_date_modified()->get_wee
            $order_date = $order->get_date_modified();

            if (!isset($weeks_constructor[$order_date->format('W')])) {
                $weeks_constructor[$order_date->format('W')] = [];

                // $order_date_start = clone $order_date;
                // $order_date_finish = clone $order_date;
                // $order_date->modify('Monday this week');
                // $order_date_finish->modify('Sunday this week');
                $week_title = $order_date->modify('Monday this week')->format('M, d') . ' - ' . $order_date->modify('Sunday this week')->format('M, d');

                $weeks_constructor[$order_date->format('W')]['title'] = $week_title;
                $weeks_constructor[$order_date->format('W')]['orders'] = [];

            }

            $weeks_constructor[$order_date->format('W')]['orders'][] = $order;

        }

        return $weeks_constructor;
    }

    /**
     * Checks subscription to be published
     * - checks number of meals in cart
     *
     * @return boolean|array
     */
    function ready_for_creation()
    {

        // var_dump( $this );

        // $this->check_category_for_allowed_item_numbers();

    }

    /**
     * получение товаров в подписке с группировкой по категориям
     */
    function get_items_n_categories()
    {

        if (!empty($this->order_items_by_categories)) {
            return $this->order_items_by_categories;
        }

        $this->order_items_by_categories = op_help()->subscriptions->get_items_n_categories_for_order($this);

        return $this->order_items_by_categories;

    }

}
