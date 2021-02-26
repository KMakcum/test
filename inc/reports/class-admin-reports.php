<?php
/**
 * Reports class.
 *
 * @class   SFAdminReports
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * SFAdminReports class.
 */
class SFAdminReports extends WC_Admin_Report {

    /**
     * Get the current range and calculate the start and end dates with 'Last week'.
     */
    public function calculate_current_range_week() {
        $current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : 'week';

        if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', 'week' ) ) ) {
            $current_range = 'week';
        }

        $this->check_current_range_nonce( $current_range );
        if ( $current_range == 'week' ) {
            $ct = current_time( 'timestamp' );
            $this->start_date = strtotime( 'last sunday', strtotime( 'midnight', $ct ) );
            $this->end_date   = strtotime( 'midnight', $ct );
        } else {
            $this->calculate_current_range( $current_range );
        }
    }
}