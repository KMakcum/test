<?php
/**
 * Assembly List report class.
 *
 * @class   SFAssemblyList
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * SFAssemblyList class.
 */
class SFAssemblyList extends SFAdminReports {

    /**
     * @var array excluded order statuses.
     */
    protected $excluded_order_statuses = [
        'wc-op-subscription',
        'wc-op-incomplete',
        'wc-op-paused',
        'trash',
    ];

    /**
     * List of Orders by date range.
     *
     * @param  string $date
     * @return array
     */
    public function order_list( $date ) {
        global $wpdb;

        $where_post_status = '';
        foreach ( $this->excluded_order_statuses as $excluded_order_status ) {
            $where_post_status .= "p.`post_status`!='wc-op-subscription' AND ";
        }

        $orders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} as p 
            LEFT JOIN {$wpdb->postmeta} as pm 
            ON p.ID = pm.post_id
            WHERE $where_post_status pm.meta_key = 'op_next_delivery'
            AND pm.meta_value >= %s
            AND pm.meta_value <= %s", "$date 00:00:00", "$date 23:59:59" ) );

        return $orders;
    }

    /**
     * Return array of ['Component name', 'count'] by day from Orders.
     *
     * @param  string $date
     * @return array
     */
    public function get_day_items( $date ) {
        $orders = $this->order_list( $date );
        $result = [];

        foreach ( $orders as $order ) {
            $order_obj = wc_get_order( $order->ID );
            if ( !$order_obj ) {
                continue;
            }

            foreach ( $order_obj->get_items() as $order_item ) {
                if ( !$order_item ) {
                    continue;
                }
                $product = $order_item->get_product();
                if ( !$product ) {
                    continue;
                }

                $post_id = $product->get_ID();
                $cache = op_help()->global_cache->get( $post_id );

                if ( $cache['type'] != 'variation' ) {
                    continue;
                }

                $components = $cache['data']['components'];
                $product_quantity = $order_item->get_quantity();

                foreach ( $components as $key => $component ) {
                    $term = get_term_by( 'id', $component, $key, ARRAY_A );
                    $result[$term['name']] += $product_quantity;
                }
            }
        }

        return $result;
    }

    /**
     * Page template.
     *
     * @return string
     * @throws Exception
     */
    public function page() {
        $this->calculate_current_range_week();

        ob_start();
        if ( $this->start_date AND $this->end_date ) {
            ?>
            <h3>Assembly List</h3>
            <div>Date from <b><?php echo date( 'Y-m-d', $this->start_date ); ?></b> to <b><?php echo date( 'Y-m-d', $this->end_date ); ?></b></div>
            <?php

            try {
                $date_start = new DateTime();
                $date_start->setTimestamp( $this->start_date );

                $date = $date_start;

                $date_end = new DateTime();
                $date_end->setTimestamp( $this->end_date );
            } catch ( Exception $e ) {
                return '<div class=\'sf-no-orders\'>Data error</div>';
            }

            //array of days of components
            $items_by_days = [];
            while ( $date_end >= $date ) {
                $day = $date->format( 'Y-m-d' );
                $day_items = $this->get_day_items( $day );

                $items_by_days[$day] = $day_items;
                $date->add( new DateInterval( 'P1D' ) );
            }

            // array of components
            $components = [];
            foreach ( $items_by_days as $items_by_day ) {
                foreach ( $items_by_day as $component_name => $item_by_day ) {
                    $components[$component_name] += $item_by_day;
                }
            }
            arsort( $components );
            ?>
            <div class="sf-report-block">
                <table class="sf-report-table">
                    <tr class="sf-report-table__header">
                        <th>Ship By</th>
                        <?php
                        foreach ( $components as $component_name => $count_total ) {
                            echo "<th>{$component_name}</th>";
                        }
                        ?>
                    </tr>
                    <?php
                    foreach ( $items_by_days as $day => $items_by_day ) {
                        echo "<tr>";
                        echo "<td>{$day}</td>";
                        foreach ( $components as $component_name => $count_total ) {
                            echo "<td>{$items_by_day[$component_name]}</td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
            <div class="sf-info">
                Excluded Order statuses: <b><?php echo implode( $this->excluded_order_statuses, ', ' ); ?></b>.<br>
            </div>
            <style>
                .sf-info {
                    border-left: 4px solid #ca4a1f;
                    margin: 1.6em 0;
                    padding-left: 0.5em;
                }
                .sf-report-block {
                    overflow: auto;
                    width: 100%;
                }
                .sf-report-table {
                    border: 1px solid #ccd0d4;
                    border-collapse: collapse;
                    border-left: none;
                    margin-top: 1.6em;
                    width: 100%;
                }
                .sf-report-table tr:hover {
                    background: #f3f5f6;
                }
                .sf-report-table__header {
                    background: transparent !important;
                }
                .sf-report-table td, .sf-report-table th {
                    border-left: 1px solid #ccd0d4;
                    border-bottom: 1px solid #ccd0d4;
                    padding: 4px;
                }
                .sf-no-orders {
                    font-size: 130%;
                    margin: 2em;
                }
            </style>
            <?php
        }
        $content = ob_get_clean();

        return SFReportOrders::page( $content );
    }
}