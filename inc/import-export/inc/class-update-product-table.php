<?php

namespace SfSync;

class UpdateProductTable {
    //DB
    //portion_size
    //warehouse_location
    //base_price
    //weight
    //weight_unit
    //servings_size_weight
    //servings_per_container
    //facility_allergens
    //kit_type
    //keep_refrigerated
    //reorder_frequency
    //msrp_multiplier
    //display_multiplier

    static public function updateTerm( $term_id, $data ) {
        $term = get_term( $term_id );
        $args = [];

        foreach ( $data as $item ) {

            if ( $item['code'] == 'title' ) {
                $args['name'] = $item['value'];
            }

            if ( $item['code'] == 'description' ) {
                $args['description'] = $item['value'];
            }

        }

        wp_update_term( $term_id, $term->taxonomy, $args );
    }

    static public function checkInt( $var ) {
        return ( empty( $var ) ) ? 0 : $var;
    }

    /**
     * @param $wp_id
     * @param $data
     * @param $NetSuiteId
     *
     * @return bool|int
     */

    static public function insertOrUpdateDataSyncTerm( $wp_id, $data, $NetSuiteId ) {
        global $wpdb;

        if ( $NetSuiteId > 0 ) {
            $old_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}data_sync WHERE external_id = %d", $NetSuiteId ) );
        }

        $insert_data                           = [];
        $insert_data['type']                   = 'term';
        $insert_data['wp_id']                  = $wp_id;
        $insert_data['external_id']            = $NetSuiteId;
        $insert_data['portion_size']           = $data['portion size oz'];
        $insert_data['warehouse_location']     = $data['warehouse location'];
        $insert_data['base_price']             = self::checkInt( $data['Base Price'] );
        $insert_data['weight']                 = self::checkInt( $data['Item Weight'] );
        $insert_data['weight_unit']            = $data['Item Unit Weight'];
        $insert_data['servings_size_weight']   = self::checkInt( $data['Servin Size Weight ()'] );
        $insert_data['servings_per_container'] = self::checkInt( $data['Servings Per Container'] );
        $insert_data['facility_allergens']     = $data['Facility Allergens'];
        $insert_data['kit_type']               = $data['Kit Type'];
        $insert_data['keep_refrigerated']      = $data['Keep refregirated'];
        $insert_data['reorder_frequency']      = $data['Suggested reorder frequency'];
        $insert_data['msrp_multiplier']        = $data['MSRP Multiplier'];
        $insert_data['display_multiplier']     = $data['Display Multiplier'];


        if ( isset( $old_row ) && ! empty( $old_row ) ) {
            $result = $wpdb->update( "{$wpdb->prefix}data_sync", $insert_data, [ 'id' => $old_row->id ] );
        } else {
            $result = $wpdb->insert( "{$wpdb->prefix}data_sync", $insert_data );
        }

        return $result;
    }

    static public function insertOrUpdateDataSyncMeals( $wp_id, $data, $NetSuiteId ) {
        global $wpdb;

        if ( $NetSuiteId > 0 ) {
            $old_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}data_sync WHERE external_id = %s", $NetSuiteId ) );
        }

        $insert_data                           = [];
        $insert_data['type']                   = 'variation';
        $insert_data['wp_id']                  = $wp_id;
        $insert_data['external_id']            = $NetSuiteId;
        $insert_data['weight_unit']            = $data['Item Unit Weight'];
        $insert_data['servings_per_container'] = self::checkInt( $data['Servings Per Container'] );
        $insert_data['keep_refrigerated']      = ( $data['Keep Refregirated'] == 'Yes' ) ? 1 : 0;
        $insert_data['reorder_frequency']      = $data['Suggested reorder frequency'];
        $insert_data['msrp_multiplier']        = $data['MSRP Multiplier'];

        if ( isset( $old_row ) && ! empty( $old_row ) ) {
            $result = $wpdb->update( "{$wpdb->prefix}data_sync", $insert_data, [ 'id' => $old_row->id ] );
        } else {
            $result = $wpdb->insert( "{$wpdb->prefix}data_sync", $insert_data );
        }

        return $result;
    }

    static public function insertOrUpdateDataSyncStraples( $wp_id, $data, $NetSuiteId ) {
        global $wpdb;

        if ( $NetSuiteId > 0 ) {
            $old_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}data_sync WHERE external_id = %s", $NetSuiteId ) );
        }

        $insert_data                       = [];
        $insert_data['type']               = 'simple';
        $insert_data['wp_id']              = $wp_id;
        $insert_data['external_id']        = $NetSuiteId;
        $insert_data['warehouse_location'] = $data['warehouse location'];
        //$insert_data['weight']             = self::checkInt( $data['Item Weight'] );
        $insert_data['weight_unit']       = $data['Weight Unit'];
        $insert_data['keep_refrigerated'] = ( $data['keep refregirated'] == 'Yes' ) ? 1 : 0;;
        $insert_data['reorder_frequency']  = $data['Suggested reorder frequency'];
        $insert_data['msrp_multiplier']    = $data['MSRP Multiplier'];
        $insert_data['display_multiplier'] = $data['Display Multiplier'];

        if ( isset( $old_row ) && ! empty( $old_row ) ) {
            $result = $wpdb->update( "{$wpdb->prefix}data_sync", $insert_data, [ 'id' => $old_row->id ] );
        } else {
            $result = $wpdb->insert( "{$wpdb->prefix}data_sync", $insert_data );
        }

        return $result;
    }


    static function getHeaders() {
        static $result = false;

        if ( $result ) {
            return $result;
        }

        $data   = FileStructure::getHeaders();
        $result = [];
        foreach ( $data as $row ) {
            if ( $row['category'] === 'variate_param' && ! isset( $row['type'] ) ) {
                $result[ $row['code'] ] = $row;
            }
        }

        return $result;
    }
}