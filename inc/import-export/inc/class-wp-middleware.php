<?php

namespace SfSync;


class WpMiddleware {
    static function getAttributes() {
        return wc_get_attribute_taxonomies();
    }

    static function createOrUpdateProduct( $data ) {
        global $wpdb;

        if ( isset( $data['term'] ) ) {
            $sku  = $data['term'][0]['value'];
            $term = $wpdb->get_results(
                "SELECT * FROM $wpdb->termmeta WHERE meta_key = '_op_variations_component_sku' AND meta_value = {$sku}", );

            // Если term не пустой то создать видимо
            // Таксономию как опрделять? или все компоненты в одном месте ?
            // todo создание term
            $term_id = $term[ array_key_first( $term ) ]->term_id;

            UpdateProductTable::updateTermMeta( $term_id, $data['term'] );
            UpdateProductTable::updateTerm( $term_id, $data['termmain'] );

            $status = UpdateProductTable::insertOrUpdateDataSyncTerm( $term_id, $data['data_sync'], $sku );

//			echo '<pre>';
//			var_dump( $status );
//			echo '</pre>';
//			die();
        }

//	    echo '<pre>';
//	    var_dump( $data );
//	    echo '</pre>';

        die();
    }

}