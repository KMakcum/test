<?php
/**
 * Report Orders class.
 *
 * @class   SFReportOrders
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * SFReportOrders class.
 */
class SFReportOrders {
    private static $_instance = null;

    /**
     * @return SFReportOrders
     */
    static public function getInstance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * init all hooks.
     */
    public function init() {
        add_filter( 'woocommerce_reports_charts', [$this, 'report_page_filter'], 10, 1 );
    }

    /**
     * Page reports filter.
     *
     * @param  array $reports
     * @return array
     */
    public function report_page_filter( $reports ) {
        if ( isset( $reports['orders'] ) ) {
            include_once WC()->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php';
            include_once 'class-admin-reports.php';
            include_once 'class-orders-by-customer.php';
            include_once 'class-assembly-list.php';

            $reports['orders']['reports']['orders_by_customer'] = [
                'title'       => __( 'Orders By Customer', 'woocommerce' ),
                'description' => '',
                'hide_title'  => false,
                'callback'    => [$this, 'report_page'],
            ];
            $reports['orders']['reports']['assembly_list'] = [
                'title'       => __( 'Assembly List', 'woocommerce' ),
                'description' => '',
                'hide_title'  => false,
                'callback'    => [$this, 'assembly_list_page'],
            ];
        }

        return $reports;
    }

    /**
     * Orders By Customer page report.
     */
    public function report_page() {
        $orders_by_customer = new SFOrdersByCustomer();
        echo $orders_by_customer->page();
    }

    /**
     * Assembly list page report.
     *
     * @throws Exception
     */
    public function assembly_list_page() {
        $assembly_list = new SFAssemblyList();
        echo $assembly_list->page();
    }

    /**
     * Page template.
     *
     * @param  string $content
     * @return string
     */
    public static function page( $content ) {
        ob_start();
        ?>
        <div id="poststuff" class="woocommerce-reports-wide">
            <div class="postbox">
                <div class="stats_range">
                    <ul>
                        <?php
                        $ranges = array(
                            'year'       => __( 'Year', 'woocommerce' ),
                            'last_month' => __( 'Last month', 'woocommerce' ),
                            'month'      => __( 'This month', 'woocommerce' ),
                            'week'       => __( 'This week', 'woocommerce' ),
                        );
                        $current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : 'week';

                        foreach ( $ranges as $range => $name ) {
                            echo '<li class="' . ( $current_range == $range ? 'active' : '' ) . '"><a href="' . esc_url( remove_query_arg( array( 'start_date', 'end_date' ), add_query_arg( 'range', $range ) ) ) . '">' . esc_html( $name ) . '</a></li>';

                        }
                        ?>
                        <li class="custom <?php echo ( 'custom' === $current_range ) ? 'active' : ''; ?>">
                            <?php esc_html_e( 'Custom:', 'woocommerce' ); ?>
                            <form method="GET">
                                <div>
                                    <?php
                                    // Maintain query string.
                                    foreach ( $_GET as $key => $value ) {
                                        if ( is_array( $value ) ) {
                                            foreach ( $value as $v ) {
                                                echo '<input type="hidden" name="' . esc_attr( sanitize_text_field( $key ) ) . '[]" value="' . esc_attr( sanitize_text_field( $v ) ) . '" />';
                                            }
                                        } else {
                                            echo '<input type="hidden" name="' . esc_attr( sanitize_text_field( $key ) ) . '" value="' . esc_attr( sanitize_text_field( $value ) ) . '" />';
                                        }
                                    }
                                    ?>
                                    <input type="hidden" name="range" value="custom" />
                                    <input type="text" size="11" placeholder="yyyy-mm-dd" value="<?php echo ( ! empty( $_GET['start_date'] ) ) ? esc_attr( wp_unslash( $_GET['start_date'] ) ) : ''; ?>" name="start_date" class="range_datepicker from" autocomplete="off" /><?php //@codingStandardsIgnoreLine ?>
                                    <span>&ndash;</span>
                                    <input type="text" size="11" placeholder="yyyy-mm-dd" value="<?php echo ( ! empty( $_GET['end_date'] ) ) ? esc_attr( wp_unslash( $_GET['end_date'] ) ) : ''; ?>" name="end_date" class="range_datepicker to" autocomplete="off" /><?php //@codingStandardsIgnoreLine ?>
                                    <button type="submit" class="button" value="<?php esc_attr_e( 'Go', 'woocommerce' ); ?>"><?php esc_html_e( 'Go', 'woocommerce' ); ?></button>
                                    <?php wp_nonce_field( 'custom_range', 'wc_reports_nonce', false ); ?>
                                </div>
                            </form>
                        </li>
                    </ul>
                </div>
                <div class="inside"><?php echo $content; ?></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}