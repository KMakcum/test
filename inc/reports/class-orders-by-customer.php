<?php
/**
 * Orders By Customer report class.
 *
 * @class   SFOrdersByCustomer
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * SFOrdersByCustomer class.
 */
class SFOrdersByCustomer extends SFAdminReports {

    /**
     * @var array excluded order statuses.
     */
    protected $excluded_order_statuses = [
        'wc-op-subscription',
        'wc-op-incomplete',
        'wc-op-paused',
    ];

    /**
     * List of Orders by date range.
     *
     * @return array
     */
    public function order_list() {
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
            AND pm.meta_value <= %s
            ORDER BY pm.meta_value asc", date( 'Y-m-d H:i:s', $this->start_date ), date( 'Y-m-d H:i:s', $this->end_date ) ) );

        return $orders;
    }

    /**
     * Return ['Number Of Item', 'Meal IDs', 'Staple IDs', 'Vitamin IDs'].
     *
     * @param  WC_Order $order
     * @return array
     */
    public function order_items( $order ) {
        $result = [
            'meal' => '',
            'staple' => '',
            'vitamin' => '',
        ];
        $total = 0;

        foreach ( $order->get_items() as $order_item ) {
            if ( !$order_item ) {
                continue;
            }
            $product = $order_item->get_product();
            if ( !$product ) {
                continue;
            }
            $post_id = $product->get_ID();
            $product_quantity = $order_item->get_quantity();
            $cache = op_help()->global_cache->get( $post_id );
            $type = '';

            $total += $product_quantity;

            if ( $cache['type'] == 'variation' ) {
                $type = 'meal';
            } else {
                if ( has_term( 'Staples', 'product_cat', $post_id ) ) {
                    $type = 'staple';
                } elseif ( has_term( 'Vitamins', 'product_cat', $post_id ) ) {
                    $type = 'vitamin';
                }
            }
            if ( $type ) {
                for ( $i = 0; $i < $product_quantity; $i++ ) {
                    $result[$type] .= $post_id . ', ';
                }
            }
        }

        foreach ( $result as $val => &$item ) {
            if ( $result[$val] ) {
                $result[$val] = substr( $item, 0, -2 );
            }
        }
        $result['total'] = $total;

        return $result;
    }

    /**
     * Page template.
     *
     * @return string
     */
    public function page() {
        $this->calculate_current_range_week();

        ob_start();
        if ( $this->start_date AND $this->end_date ) {
            ?>
            <h3>Orders By Customer</h3>
            <div>Date from <b><?php echo date( 'Y-m-d', $this->start_date ); ?></b> to <b><?php echo date( 'Y-m-d', $this->end_date ); ?></b></div>
            <?php
            $orders = $this->order_list();
            if ( empty( $orders ) ) {
                echo "<div class='sf-no-orders'>No Orders</div>";
            } else {
                ?>
                <table class="sf-report-table">
                    <tr class="sf-report-table__header">
                        <th>Order #</th>
                        <th>Order Status</th>
                        <th>Ship Date</th>
                        <th>Number Of Item</th>
                        <th>Meal IDs</th>
                        <th>Staple IDs</th>
                        <th>Vitamin IDs</th>
                        <th>Customer ID</th>
                        <th>Customer Login</th>
                        <th>Customer Name</th>
                        <th>Delivery Address</th>
                        <th>Delivery Note</th>
                    </tr>
                    <?php
                    foreach ( $orders as $order ) {
                        $order_obj = wc_get_order( $order->ID );
                        $user_data = get_userdata( $order_obj->get_customer_id() );
                        if ( !$user_data ) {
                            continue;
                        }
                        $order_items = $this->order_items( $order_obj );
                        echo "<tr>";
                        echo "<td>" . ( ( $order->post_status == 'trash' ) ? $order->ID : "<a href='/wp-admin/post.php?post={$order->ID}&action=edit' target='_blank'>{$order->ID}</a>" ) . "</td>";
                        echo "<td>{$order->post_status}</td>"; // Order Status
                        echo "<td>{$order->meta_value}</td>"; // Ship Date
                        echo "<td>{$order_items['total']}</td>"; // Number Of Item
                        echo "<td>{$order_items['meal']}</td>"; // Meal IDs
                        echo "<td>{$order_items['staple']}</td>"; // Staple IDs
                        echo "<td>{$order_items['vitamin']}</td>"; // Vitamin IDs
                        echo "<td>{$user_data->ID}</td>"; // Customer ID
                        echo "<td>{$user_data->user_login}</td>"; // Customer Login
                        echo "<td>{$user_data->first_name} {$user_data->last_name}</td>"; // Customer Name
                        echo "<td>{$order_obj->get_formatted_shipping_address()}</td>"; // Delivery Address
                        echo "<td>{$order_obj->get_customer_note()}</td>"; // Delivery Note
                        echo "</tr>";
                    }
                    ?>
                </table>
                <div class="sf-info">
                    Excluded Order statuses: <b><?php echo implode( $this->excluded_order_statuses, ', ' ); ?></b>.<br>
                </div>
                <?php
            }
            ?>
            <style>
                .sf-info {
                    border-left: 4px solid #ca4a1f;
                    margin: 1.6em 0;
                    padding-left: 0.5em;
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