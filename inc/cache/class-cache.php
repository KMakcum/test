<?php
/**
 * Cache class.
 *
 * @class   SFCache
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * SFCache class.
 */
class SFCache {

    protected $global_cache_id;

    protected $is_zero_user = false;

    protected $zone_default = 'national';

    /**
     * Sort answer array.
     *
     * @param array $data
     *
     * @return array
     */
    public function sort_answer( $data ) {
        ksort( $data );
        foreach ( $data as &$datum ) {
            ksort( $datum );
            foreach ( $datum as &$item ) {
                if ( is_array( $item ) ) {
                    sort( $item );
                }
            }
        }

        return $data;
    }

    /**
     * Combine answer arrays.
     *
     * @param array $answer1
     * @param array $answer2
     *
     * @return array
     */
    public function combine_answers( $answer1, $answer2 ) {
        foreach ( $answer2 as $key1 => $item1 ) {
            if ( ! isset( $answer1[ $key1 ] ) ) {
                $answer1[ $key1 ] = $item1;
            } else {
                foreach ( $answer2[ $key1 ] as $key2 => $item2 ) {
                    $answer1[ $key1 ][ $key2 ] = array_merge( $answer1[ $key1 ][ $key2 ], $answer2[ $key1 ][ $key2 ] );
                }
            }
        }

        return $answer1;
    }

    /**
     * Return user zone.
     *
     * @return string
     */
    public function zone() {
        $zone = op_help()->zip_codes->get_current_user_zone();
        if ( ! $zone ) {
            $zone = $this->zone_default;
        }

        return $zone;
    }

    /**
     * Return hash of structure.
     *
     * @param array $structure
     *
     * @return string
     */
    public function hash( $structure ) {
        return md5( json_encode( $structure ) );
    }

    /**
     * Array of score arrays sort by OrderBy.
     *
     * @param array $scores
     *
     * @return array
     */
    protected function extra_sort_order_by( $scores ) {
        foreach ( $scores as $score => &$values ) {
            usort( $values, [ $this, 'cmp_order_by' ] );
        }

        return $scores;
    }

    /**
     * Flatten the array.
     *
     * @param array $arr
     *
     * @return array
     */
    protected function flatten_array( $arr ) {
        $r = [];
        foreach ( $arr as $scores ) {
            foreach ( $scores as $item ) {
                array_push( $r, [
                    'id' => $item['var_id'],
                    'cs' => $item['chef_score'],
                ] );
            }
        }

        return $r;
    }
}