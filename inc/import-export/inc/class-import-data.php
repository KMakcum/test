<?php

namespace SfSync;

use WC_Product;

class ImportData {

    static $main_meal_name = 'Main Meal Product';
    static $taxonomies = [ 'pa_part-1', 'pa_part-2', 'pa_part-3' ];

    // auto set zones
    static $meals_zones = [ 'local', 'overnight' ];
    static $staples_zones = [ 'overnight' ];
    static $vitamins_zones = [ 'local', 'overnight', 'national' ];

    /**
     * @var int `sf_import` current line
     */
    static $insert_id = 0;

    /**
     * @var array
     */
    static $cache_terms = [
        'ingredient'    => [],
        'allergens'     => [],
        'fats'          => [],
        'calories'      => [],
        'carbohydrates' => [],
        'proteins'      => [],
    ];

    /**
     * @var string
     */
    static $export_type = '';

    static function importFromFile( $filename, $import_type, $user_id = false ) {
        return self::import( ParseFile::parse( $filename ), $import_type, $user_id, $filename );
    }

    static function checkHeader( $filename ) {
        $data = ParseFile::parseFile( $filename );
        $data = ParseFile::checkKeys( $data );

        ob_start();
        var_export( $data );
        $data = ob_get_clean();
        echo '<pre>';
        echo htmlspecialchars( $data );
        echo '</pre>';
        exit;
    }

    static function import( $data, $import_type, $user_id = false, $filename = '' ) {
        global $wpdb;

        $start = microtime( true );

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $wpdb->insert(
            "{$wpdb->prefix}sf_import",
            [
                'user_id' => $user_id,
                'type'    => $import_type,
                'status'  => 'in-progress',
//                'status'  => 'finished', //TODO remove it
            ]
        );
        self::$insert_id   = (int) $wpdb->insert_id;
        self::$export_type = '';
        $r                 = '';

        if ( $import_type === 'components' ) {
            self::$export_type = 'export_components';
            $r                 = self::importComponents( $data );

        } elseif ( $import_type === 'staples' ) {
            self::$export_type = 'export_staples';
            $r                 = self::importStaples( $data );

        } elseif ( $import_type === 'meals' ) {
            self::$export_type = 'export_meals';
            $r                 = self::importMeals( $data );
        }

        $delta = microtime( true ) - $start;

        $wpdb->update(
            "{$wpdb->prefix}sf_import",
            [
                'user_id'        => $user_id,
                'type'           => $import_type,
                'execution_time' => round( $delta, 2 ),
                'status'         => 'finished',
                'return'         => $r . "Import version " . \SFImport::$ver,
            ], [
                'id' => self::$insert_id,
            ]
        );

        unlink( $filename );

        $mes = \TB::get_message( 'import-message' );
        \TB::m( "*$import_type end*.\n$mes", true, 'import' );

        /*
         * Export start
         */
        self::export_by_type( $import_type, self::$export_type, $user_id );

        \TB::m( "*stop* cron `{$import_type} import`.", true, 'import' );
        do_action( 'plugins_finished_sync_2' );
        die( $r );
    }

    /**
     * Generate export file by type.
     *
     * @param string $type
     * @param int|bool $user_id
     */
    static function export_by_type( $type, $export_type, $user_id = false ) {
        global $wpdb;

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        \TB::start( 'export' );
        \TB::m( "*export start* by type `{$type}`." );

        // Create file export
        if ( $export_type ) {
            $start = microtime( true );
            $wpdb->insert(
                "{$wpdb->prefix}sf_import",
                [
                    'user_id' => $user_id,
                    'type'    => $export_type,
                    'status'  => 'in-progress',
                ]
            );
            self::$insert_id = (int) $wpdb->insert_id;

            // Create file
            $file_name = AdminPage::getPage( $export_type );

            $delta = microtime( true ) - $start;
            $wpdb->update(
                "{$wpdb->prefix}sf_import",
                [
                    'user_id'        => $user_id,
                    'type'           => $export_type,
                    'execution_time' => round( $delta, 2 ),
                    'status'         => 'finished',
                    'return'         => "<b>Done!</b><br>Export version " . \SFImport::$ver,
                ], [
                    'id' => self::$insert_id,
                ]
            );

            $file_name = get_site_url() . substr( $file_name, strpos( $file_name, '/wp-content' ) );
            \TB::m( "*export end* by type `{$type}`. [File link]($file_name).", true, 'export' );
        }
    }

    /**
     * Set error.
     *
     * @param $data
     *
     * @return int
     */
    static function error( $data ) {
        global $wpdb;

        $type = self::$export_type;
        \TB::m( "*error $type*: {$data}." );

        return $wpdb->update(
            "{$wpdb->prefix}sf_import",
            [
                'status' => 'error',
                'return' => $data,
            ], [
                'id' => self::$insert_id,
            ]
        );
    }

    static function checkNumber( $var ) {
        return is_numeric( $var ) ? $var : 0;
    }

    static function update_term( $row, $term_id, $current_sync_version, $NetSuiteId ) {
        update_term_meta( $term_id, 'sync_version', $current_sync_version );

        update_term_meta( $term_id, 'op_variations_component_description', $row['Description'] );
        update_term_meta( $term_id, 'is_published', $row['Is published'] );
        update_term_meta( $term_id, '_op_variations_component_store_type',
            $row['Warehouse Location type'] );
        update_term_meta( $term_id, '_op_variations_component_calories',
            self::checkNumber( $row['Calories'] ) );
        update_term_meta( $term_id, '_op_variations_component_fats',
            self::checkNumber( $row['Total Fat (g)'] ) );
        update_term_meta( $term_id, '_op_variations_component_carbohydrates',
            self::checkNumber( $row['Total Carbohydrate (g)'] ) );
        update_term_meta( $term_id, '_op_variations_component_proteins',
            self::checkNumber( $row['Protein (g)'] ) );
        update_term_meta( $term_id, '_op_variations_component_instructions',
            self::checkNumber( $row['Preparation Instructions'] ) );

        if ( ! empty( $row['Sort order'] ) ) {
            update_term_meta( $term_id, '_op_variations_component_sort_order',
                $row['Sort order'] );
        }

        if ( ! empty( $row['Allergens'] ) ) {
            $allergens = explode( ',', $row['Allergens'] );
            foreach ( $allergens as &$allergen ) {
                $allergen = strtolower( trim( $allergen ) );
            }
            carbon_set_term_meta( $term_id, 'op_variations_component_allergens',
                $allergens );
        } else {
            carbon_set_term_meta( $term_id, 'op_variations_component_allergens', '' );
        }

        if ( ! empty( $row['Ingredients'] ) && $row['Ingredients'] !== '#N/A' ) {
            carbon_set_term_meta( $term_id, 'op_variations_component_ingredients',
                $row['Ingredients'] );
        } else {
            carbon_set_term_meta( $term_id, 'op_variations_component_ingredients', '' );
        }

        // gallery
        // Migration. If current (item line) sync_version >= migration version then run.
        $migration = [
            '0.1' => function () use ( $NetSuiteId, $term_id ) {

                $images = self::createImagesUrls( $NetSuiteId, true );

                $gallery = [];
                if ( ! empty( $images['base'] ) ) {
                    $image_thumb = self::importImages( $images['base'] );
                    carbon_set_term_meta( $term_id, 'op_variations_component_thumb', $image_thumb );
                    $gallery[] = $image_thumb;
                }

                if ( ! empty( $images['nutrition'] ) ) {
                    $image_nutrition = self::importImages( $images['nutrition'] );
                    carbon_set_term_meta( $term_id, 'op_variations_component_nutrition_img', $image_nutrition );
                    $gallery[] = $image_nutrition;
                }

                carbon_set_term_meta( $term_id, 'op_variations_component__gallery', $gallery );
            },
        ];
        foreach ( $migration as $item_ver => $method ) {
            if ( $current_sync_version >= $item_ver ) {
                $method();
            }
        }

        if ( ! empty( $row['Badges'] ) ) {
            $badges = array_filter( explode( ',', $row['Badges'] ) );
            carbon_set_term_meta( $term_id, 'op_variations_component_badges', $badges );
        }
//
//        UpdateProductTable::insertOrUpdateDataSyncTerm( $term_id, $row, $NetSuiteId );
    }

    /**
     * Update meals information from components.
     *
     * @param array|bool $meals
     * @param WC_Product|bool $variation
     *
     * @return mixed
     */
    public static function update_meals_from_components( $meals = false, $variation = false ) {
        global $wpdb;

        $id_main_meal_product = \SfSync\ImportData::getPostByName( self::$main_meal_name );

        if ( empty( $meals ) ) {
            $meals = $wpdb->get_results( $wpdb->prepare( "SELECT `ID` FROM `{$wpdb->prefix}posts` WHERE `post_parent` = %d AND `post_status` = 'publish'", $id_main_meal_product ), ARRAY_A );
        }

        $result['total']  = count( $meals );
        $result['update'] = 0;

        foreach ( $meals as $meal ) {
            $id_components = $wpdb->get_results( $wpdb->prepare(
                "SELECT ts.term_id, ts.slug, tt.taxonomy FROM `{$wpdb->prefix}term_relationships` as tr
				 LEFT JOIN `{$wpdb->prefix}term_taxonomy` as tt
				 ON tt.term_id = tr.term_taxonomy_id
				 LEFT JOIN `{$wpdb->prefix}terms` as ts
				 ON ts.term_id = tr.term_taxonomy_id
				 WHERE tr.`object_id` = %d AND
				 (tt.taxonomy='pa_part-1' OR tt.taxonomy='pa_part-2' OR tt.taxonomy='pa_part-3')",
                $meal['ID'] )
            );

            $components_ids = [];
            $slugs          = [];

            foreach ( $id_components as $id_component ) {
                $NetSuiteId = get_term_meta( $id_component->term_id, '_op_variations_component_sku', 1 );

                $components_ids[ $NetSuiteId ]    = $id_component->term_id;
                $slugs[ $id_component->taxonomy ] = $id_component->slug;
            }

            $result['update'] += self::update_meals_post_meta( $meal['ID'], $components_ids, $slugs, $variation );
        }

        return $result;
    }

    /**
     * Update meals post_meta from components.
     *
     * @param int $meal_id
     * @param array $components_ids
     * @param array $slugs
     * @param WC_Product|bool $variation
     *
     * @return int
     */
    protected static function update_meals_post_meta( $meal_id, $components_ids, $slugs, $variation = false ) {
        if ( ! empty( $components_ids ) ) {
            $data = [
                'ingredient'    => [],
                'allergens'     => [],
                'fats'          => 0,
                'calories'      => 0,
                'carbohydrates' => 0,
                'proteins'      => 0,
            ];

            foreach ( $components_ids as $components_id ) {
                if ( ! isset( self::$cache_terms['ingredient'][ $components_id ] ) ) {
                    self::$cache_terms['ingredient'][ $components_id ]    = get_term_meta( $components_id, '_op_variations_component_ingredients', true );
                    self::$cache_terms['allergens'][ $components_id ]     = carbon_get_term_meta( $components_id, 'op_variations_component_allergens' );
                    self::$cache_terms['fats'][ $components_id ]          = get_term_meta( $components_id, '_op_variations_component_fats', true );
                    self::$cache_terms['calories'][ $components_id ]      = get_term_meta( $components_id, '_op_variations_component_calories', true );
                    self::$cache_terms['carbohydrates'][ $components_id ] = get_term_meta( $components_id, '_op_variations_component_carbohydrates', true );
                    self::$cache_terms['proteins'][ $components_id ]      = get_term_meta( $components_id, '_op_variations_component_proteins', true );
                }

                array_push( $data['ingredient'], self::$cache_terms['ingredient'][ $components_id ] );

                $data['allergens']     += self::$cache_terms['allergens'][ $components_id ];
                $data['fats']          += self::$cache_terms['fats'][ $components_id ];
                $data['calories']      += self::$cache_terms['calories'][ $components_id ];
                $data['carbohydrates'] += self::$cache_terms['carbohydrates'][ $components_id ];
                $data['proteins']      += self::$cache_terms['proteins'][ $components_id ];
            }

            $ingredient_string = implode( ';<br>', array_unique( $data['ingredient'] ) );
            update_post_meta( $meal_id, 'op_meal_ingredients', $ingredient_string );

            $allergens_string = implode( ',', array_unique( $data['allergens'] ) );
            update_post_meta( $meal_id, 'op_allergens', $allergens_string );

            update_post_meta( $meal_id, 'op_fats', $data['fats'] );
            update_post_meta( $meal_id, 'op_calories', $data['calories'] );
            update_post_meta( $meal_id, 'op_carbohydrates', $data['carbohydrates'] );
            update_post_meta( $meal_id, 'op_proteins', $data['proteins'] );

            if ( ! $variation ) {
                $variation = wc_get_product( $meal_id );
            }

            if ( ! empty( $slugs ) ) {
                $variation->set_attributes( $slugs );
            }
            echo $meal_id;

            return 1;
        }

        return 0;
    }

    static function importComponents( $data ) {
        global $wpdb;
        $table         = $wpdb->prefix . 'termmeta';
        $taxonomies    = [ 'pa_part-1', 'pa_part-2', 'pa_part-3' ];
        $count_of_line = 0;
        $update_lines  = 0;
        $insert_lines  = 0;

        foreach ( $data as $row ) {
            $count_of_line ++;
            $NetSuiteId = $row['Component NetSuite ID'];
//            var_export( $NetSuiteId );
//            var_export( $row );
            if ( ! $NetSuiteId ) {
                self::error( 'Incorrect file' );
                die ( '<h3 style="color:red;">Incorrect file. You should use "Component NetSuite ID" in first field.</h3>' );
            }
            $term_ids             = $wpdb->get_results( $wpdb->prepare( "SELECT term_id FROM $table WHERE meta_key='_op_variations_component_sku' AND meta_value = %s", $NetSuiteId ) );
            $current_sync_version = $row['sync_version'];

            if ( $term_ids ) {
                $is_success = 0;
                foreach ( $term_ids as $term_obj ) {
                    foreach ( $taxonomies as $taxonomy ) {
                        $term = get_term_by( 'id', $term_obj->term_id, $taxonomy );
                        if ( $term ) {
                            wp_update_term( $term->term_id, $taxonomy, [
                                'description' => $row['Description'],
                                'name'        => $row['Item Name/Number']
                            ] );
                            self::update_term( $row, $term->term_id, $current_sync_version, $NetSuiteId );
                            $is_success = 1;
                        }
                    }
                }
                $update_lines += $is_success;
            } else {
                foreach ( $taxonomies as $taxonomy ) {
                    $term_arr = wp_insert_term( $row['Item Name/Number'], $taxonomy, [
                        'description' => $row['Description'],
                        'parent'      => 0,
                        'slug'        => '',
                    ] );

                    if ( empty( $term_arr->errors ) && ! empty( $term_arr['term_id'] ) ) {
                        update_term_meta( $term_arr['term_id'], '_op_variations_component_sku', $NetSuiteId );
                        self::update_term( $row, $term_arr['term_id'], $current_sync_version, $NetSuiteId );
                        $insert_lines ++;
                    }
                }
            }
        }

        \TB::start( 'meal data' );
        \TB::m( "*start* `update data for meals from components`." );
        $result = self::update_meals_from_components();
        \TB::m( "*end* `update data for meals from components`.\n Total: {$result['total']}\n Update: {$result['update']}", true, 'meal data' );

        \TB::add_message( "Total count: *{$count_of_line}*\n Update lines: *{$update_lines}*\n Insert count: *$insert_lines*.", 'import-message' );

        return ( "<h4>Components has been done!</h4><p>Total count: <b>$count_of_line</b></p><p>Update lines: <b>$update_lines</b></p><p>Insert count: <b>$insert_lines</b></p>" );
    }

    public static function importStaples( $data ) {
        global $wpdb;
        $table         = $wpdb->prefix . 'postmeta';
        $count_of_line = 0;
        $update_lines  = 0;
        $insert_lines  = 0;

        foreach ( $data as $row ) {
            $count_of_line ++;
            $NetSuiteId = $row['Staple NetSuite ID'];
            if ( ! $NetSuiteId ) {
                self::error( 'Incorrect file' );
                die ( '<h3 style="color:red;">Incorrect file. You should use "Staple NetSuite ID" in first field.</h3>' );
            }
            $product_id           = $wpdb->get_var( $wpdb->prepare( "SELECT pm.post_id FROM {$wpdb->posts} as p LEFT JOIN $table as pm ON p.ID = pm.post_id WHERE p.post_status='publish' AND pm.meta_key='internalid' AND pm.meta_value = %s", $NetSuiteId ) );
            $current_sync_version = $row['sync_version'];

            $base_multiplier = ( $row['Base Multiplier'] ) ? $row['Base Multiplier'] : 1;
            $msrp_multiplier = ( $row['MSRP Multiplier'] ) ? $row['MSRP Multiplier'] : 1;
            $_sale_price     = $row['Base Price'] * $base_multiplier;
            $_regular_price  = $_sale_price * $msrp_multiplier;

            if ( $product_id ) {
                $update_lines ++;
                $product = wc_get_product( $product_id );
            } else {
                $insert_lines ++;
                $post = array(
                    'post_author'  => 1,
                    'post_status'  => 'publish',
                    'post_title'   => $row['Item Name/Number'],
                    'post_type'    => 'product',
                    'post_content' => $row['Description'],
                    'post_excerpt' => $row['Description']
                );

                $post_id = wp_insert_post( $post );
                $product = wc_get_product( $post_id );

                wp_set_object_terms( $post_id, 'Staples', 'product_cat' );
                wp_set_object_terms( $post_id, 'simple', 'product_type' );

                $product->update_meta_data( '_visibility', 'visible' );
                $product->update_meta_data( '_stock_status', 'instock' );
            }

            if ( $product ) {
                $wpdb->update( $wpdb->posts, [ 'post_title' => $row['Item Name/Number'] ], [ 'ID' => $product->get_id() ] ); // post_title
                $product->set_description( $row['Description'] );
                $product->set_short_description( $row['Description'] );
                update_post_meta( $product->get_id(), '_regular_price', $_regular_price );
                if ( $_regular_price > $_sale_price ) {
                    update_post_meta( $product->get_id(), '_price', $_regular_price );
                    delete_post_meta( $product->get_id(), '_sale_price' );
                } else {
                    update_post_meta( $product->get_id(), '_price', $_sale_price );
                    update_post_meta( $product->get_id(), '_sale_price', $_sale_price );
                }
                update_post_meta( $product->get_id(), '_weight', $row['Item Weight'] );
                $product->update_meta_data( 'op_zones', self::$staples_zones );

                if ( $row['Slug URL'] ) {
                    $wpdb->update( $wpdb->posts, [ 'post_name' => trim( $row['Slug URL'] ) ], [ 'ID' => $product->get_id() ] ); // post_name
                }
                $product->update_meta_data( 'op_store_type', $row['Warehouse Location type'] );
                $product->update_meta_data( 'op_calories', $row['Calories'] );
                $product->update_meta_data( 'op_fats', $row['Total Fat (g)'] );
                $product->update_meta_data( 'op_proteins', $row['Protein (g)'] );
                $product->update_meta_data( 'op_carbohydrates', $row['Total Carbohydrate (g)'] );
                $product->update_meta_data( 'op_badges', $row['Badges'] );
                $product->update_meta_data( 'op_allergens', $row['Allergens'] );
                $product->update_meta_data( 'op_ingredients', $row['Ingredients'] );
                if ( ! empty( $row['Sort order'] ) ) {
                    $product->update_meta_data( 'op_sort_by', $row['Sort order'] );
                }

                if ( isset( $row['sub-category1'] ) && ! empty( $row['sub-category1'] ) ) {
                    $product_tags = array_filter( explode( ',', $row['sub-category1'] ) );
                    $tags_ids     = [];

                    foreach ( $product_tags as $tag ) {
                        if ( $tag_info = term_exists( $tag, 'product_tag' ) ) {
                            array_push( $tags_ids, $tag_info['term_id'] );
                        }
                    }

                    $product->set_tag_ids( $tags_ids );
                }

                if ( $row['SKU'] ) {
                    $wpdb->update( $wpdb->postmeta, [ 'meta_value' => $row['SKU'] ], [
                        'meta_key' => '_sku',
                        'post_id'  => $product->get_id()
                    ] ); // _sku
                }

                $product->save_meta_data();
                $id = $product->save();
//
//                UpdateProductTable::insertOrUpdateDataSyncStraples( $id, $row, $NetSuiteId );
            }

            update_post_meta( $id, 'sync_version', $current_sync_version );

            // gallery
            // Migration. If current (item line) sync_version > migration version then run.
            $migration = [
                '0.1' => function () use ( $NetSuiteId, $id ) {
                    $images = self::createImagesUrls( $NetSuiteId, false, true );
                    delete_post_meta( $id, '_product_image_gallery' );
                    delete_post_meta( $id, '_thumbnail_id' );
                    $ids = array_map( function ( $item ) use ( $id ) {
                        return self::importImages( $item );
                    }, $images );

                    update_post_meta( $id, '_thumbnail_id', array_shift( $ids ) );
                    update_post_meta( $id, '_product_image_gallery', implode( ',', $ids ) );
                },
            ];
            foreach ( $migration as $item_ver => $method ) {
                if ( $current_sync_version >= $item_ver ) {
                    $method();
                }
            }
        }

        \TB::add_message( "Total count: *{$count_of_line}*\n Update lines: *{$update_lines}*\n Insert count: *$insert_lines*.", 'import-message' );

        return ( "<h4>Done!</h4><p>Total count: <b>$count_of_line</b></p><p>Update lines: <b>$update_lines</b></p><p>Insert count: <b>$insert_lines</b></p>" );
    }

    public static function getTermsIdByTaxonomy( $taxonomy ) {
        $terms = get_terms( [
            'taxonomy' => $taxonomy,
        ] );

        if ( ! empty( $terms ) ) {
            return array_map( function ( $item ) {
                return $item->term_id;
            }, $terms );
        }

        return false;
    }

    public static function getPostByName( $post_name ) {
        global $wpdb;
        $post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type='product'", $post_name ) );

        if ( $post ) {
            return $post;
        }

        return null;
    }

    public static function importMeals( $data ) {
        global $wpdb;

        $product_title = 'Main Meal Product';
        $count_of_line = 0;
        $update_lines  = 0;
        $insert_lines  = 0;

        $data_attr = [
            'attribute_names'      => self::$taxonomies,
            'attribute_position'   => [ 0, 1, 2 ],
            'attribute_visibility' => [ 1, 1, 1 ],
            'attribute_variation'  => [ 1, 1, 1 ]
        ];

        foreach ( self::$taxonomies as $item ) {
            $data_attr['attribute_values'][] = self::getTermsIdByTaxonomy( $item );
        }

        if ( ! $parent_product_id = self::getPostByName( $product_title ) ) {
            $product = new \WC_Product_Variable();
            $product->set_name( $product_title );
            $attributes = \WC_Meta_Box_Product_Data::prepare_attributes( $data_attr );
            $product->set_attributes( $attributes );
            $parent_product_id = $product->save();
        }

        foreach ( $data as $row ) {
            $count_of_line ++;
            $current_sync_version = $row['sync_version'];

            $NetSuiteId = $row['Meal NetSuite ID'];
            if ( ! $NetSuiteId ) {
                self::error( 'Incorrect file' );
                die ( '<h3 style="color:red;">Incorrect file. You should use "Meal NetSuite ID" in first field.</h3>' );
            }
            $post = $wpdb->get_row( $wpdb->prepare( "SELECT p.* FROM {$wpdb->posts} as p LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id WHERE (p.post_status='publish' OR p.post_status='trash') AND meta_key='internalid' AND meta_value = %s", $NetSuiteId ) );

            $result_id = $post->ID;

            if ( ! empty( $result_id ) && ! empty( get_post_status( $result_id ) ) ) {
//                $update_lines--;
//                continue;

                // The post exists
                $variation = wc_get_product( $result_id );
                $update_lines ++;
            } else {
                // The post variation not exists
                $variation = new \WC_Product_Variation();
                $insert_lines ++;
            }

            try {
                $variation->set_sku( $NetSuiteId );
            } catch ( \WC_Data_Exception $e ) {
                $insert_lines --;
                $update_lines --;
                continue;
            }


            $cashed_product = op_help()->global_cache->get_cached_product( $variation->get_id() );

            //changed to auto
//			$variation->set_regular_price( $row['Base Price'] );
//			$variation->set_sale_price( $row['Sale price'] );

            if ( $variation->get_weight() != $row['Item Weight'] ) {
                $variation->set_weight( $row['Item Weight'] );
            }

            if ( $variation->get_stock_status() != $row['Stock status'] ) {
                $variation->set_stock_status( $row['Stock status'] );
            }

            if ( $row['Slug URL'] AND $post->post_name != $row['Slug URL'] ) {
                $wpdb->update( $wpdb->posts, [ 'post_name' => trim( $row['Slug URL'] ) ], [ 'ID' => $variation->get_id() ] ); // post_name
            }

            $args_attributes = [];

            $calories      = [];
            $fats          = [];
            $carbohydrates = [];
            $proteins      = [];

//			$component_prices = [];
//            $components_ids     = [];
//            $title       = '';
//            $description = '';

            $components_ids_all = [];

            $component_id_1 = $row['Component 1 - Internal ID'];
            if ( ! empty( $component_id_1 ) ) {
                array_push( $components_ids_all, $component_id_1 );
//                $term_ids = $wpdb->get_results( $wpdb->prepare( "SELECT term_id FROM $wpdb->termmeta WHERE meta_key='_op_variations_component_sku' AND meta_value = %s", $component_id_1 ) );
//
//                if ( ! empty( $term_ids ) ) {
//                    $term                         = get_term( $term_ids[0]->term_id );
//                    $args_attributes['pa_part-1'] = $term->slug;
//
//                    array_push( $calories, get_term_meta( $term->term_id, '_op_variations_component_calories',
//                        true ) );
//                    array_push( $fats, get_term_meta( $term->term_id, '_op_variations_component_fats',
//                        true ) );
//                    array_push( $carbohydrates, get_term_meta( $term->term_id, '_op_variations_component_carbohydrates',
//                        true ) );
//                    array_push( $proteins, get_term_meta( $term->term_id, '_op_variations_component_proteins',
//                        true ) );
//
//                    $components_ids[ $component_id_1 ] = $term_ids[0]->term_id;
//
////                    $title       .= "{$term->name}, ";
////                    $description .= "{$term->description} ";
//                }
            }

            $component_id_2 = $row['Component 2 - Internal ID'];
            if ( ! empty( $component_id_2 ) ) {
                array_push( $components_ids_all, $component_id_2 );
//                $term_ids = $wpdb->get_results( $wpdb->prepare( "SELECT term_id FROM $wpdb->termmeta WHERE meta_key='_op_variations_component_sku' AND meta_value = %s", $component_id_2 ) );
//
//                if ( ! empty( $term_ids ) ) {
//                    $term                         = get_term( $term_ids[1]->term_id );
//                    $args_attributes['pa_part-2'] = $term->slug;
//
//                    array_push( $calories, get_term_meta( $term->term_id, '_op_variations_component_calories',
//                        true ) );
//                    array_push( $fats, get_term_meta( $term->term_id, '_op_variations_component_fats',
//                        true ) );
//                    array_push( $carbohydrates, get_term_meta( $term->term_id, '_op_variations_component_carbohydrates',
//                        true ) );
//                    array_push( $proteins, get_term_meta( $term->term_id, '_op_variations_component_proteins',
//                        true ) );
//
//                    $components_ids[ $component_id_2 ] = $term_ids[1]->term_id;
//
////                    $title       .= "{$term->name}, ";
////                    $description .= "{$term->description} ";
//                }
            }

            $component_id_3 = $row['Component 3 - Internal ID'];
            if ( ! empty( $component_id_3 ) ) {
                array_push( $components_ids_all, $component_id_3 );
//                $term_ids = $wpdb->get_results( $wpdb->prepare( "SELECT term_id FROM $wpdb->termmeta WHERE meta_key='_op_variations_component_sku' AND meta_value = %s", $component_id_3 ) );
//
//                if ( ! empty( $term_ids ) ) {
//                    $term                         = get_term( $term_ids[2]->term_id );
//                    $args_attributes['pa_part-3'] = $term->slug;
//
//                    array_push( $calories, get_term_meta( $term->term_id, '_op_variations_component_calories',
//                        true ) );
//                    array_push( $fats, get_term_meta( $term->term_id, '_op_variations_component_fats',
//                        true ) );
//                    array_push( $carbohydrates, get_term_meta( $term->term_id, '_op_variations_component_carbohydrates',
//                        true ) );
//                    array_push( $proteins, get_term_meta( $term->term_id, '_op_variations_component_proteins',
//                        true ) );
//
//                    $components_ids[ $component_id_3 ] = $term_ids[2]->term_id;
//
////                    $title       .= "{$term->name}, ";
////                    $description .= "{$term->description} ";
//                }
            }

//            self::update_meals_post_meta( $meal_id, $components_ids );
            self::update_meals_from_components( [ [ 'ID' => $variation->get_id() ] ], $variation );
//
//            die;

//            if ( ! $row['Item Name/Number'] ) {
//                $row['Item Name/Number'] = $title;
//            }
//            if ( ! $row['Description'] ) {
//                $row['Description'] = $description;
//            }

            if ( $cashed_product['op_post_title'] != $row['Item Name/Number'] ) {
                update_post_meta( $variation->get_id(), 'op_post_title', $row['Item Name/Number'] );
                $wpdb->update( $wpdb->posts, [ 'post_title' => $row['Item Name/Number'] ], [ 'ID' => $variation->get_id() ] ); // post_title
            }

            if ( $row['Status'] AND $post->post_status != $row['Status'] ) {
                $wpdb->update( $wpdb->posts, [ 'post_status' => $row['Status'] ], [ 'ID' => $variation->get_id() ] ); // post_status
            }

            if ( $variation->get_description() != $row['Description'] ) {
                $variation->set_description( $row['Description'] );
            }

            $variation->set_parent_id( $parent_product_id );

            $zones = $variation->get_meta( 'op_zones', false );

            if ( ! empty( $zones ) ) {
                $variation->delete_meta_data( 'op_zones' );

                $variation->add_meta_data( 'op_zones', self::$meals_zones[0] );
                $variation->add_meta_data( 'op_zones', self::$meals_zones[1] );
            } else {
                if ( ! empty( array_diff( $zones, self::$meals_zones ) ) ) {
                    $variation->add_meta_data( 'op_zones', self::$meals_zones[0] );
                    $variation->add_meta_data( 'op_zones', self::$meals_zones[1] );
                }
            }

            $id = $variation->save();

            for ( $i = 1; $i < 4; $i ++ ) {
                $component = $row["Component {$i} - Internal ID"];
                $taxonomy  = "pa_part-{$i}";

                wp_delete_object_term_relationships( $id, $taxonomy );

                $term_id = $wpdb->get_var( $wpdb->prepare(
                    "SELECT tm.term_id FROM {$wpdb->termmeta} as tm 
					 LEFT JOIN {$wpdb->term_taxonomy} as tt ON tt.term_id = tm.term_id
					 WHERE tm.meta_key='_op_variations_component_sku' AND tm.meta_value = %s AND tt.taxonomy = %s",
                    $component, $taxonomy ) );

                wp_set_post_terms( $id, [ ( $term_id * 1 ) ], $taxonomy );
            }

            if ( ! empty( $row['Diet Items'] ) ) {
                delete_post_meta( $id, 'op_diet' );

                $diets = array_filter( explode( ',', $row['Diet Items'] ) );

                foreach ( $diets as $diet ) {
                    add_post_meta( $id, 'op_diet', $diet );
                }
            }

            // gallery
            // Migration. If current (item line) sync_version > migration version then run.
            $migration = [
                '0.1' => function () use ( $components_ids_all, $id ) {
                    if ( ! empty( $components_ids_all ) ) {
                        $gallery_urls = self::createImagesUrls( $components_ids_all );

                        $ids = array_map( function ( $item ) use ( $id ) {
                            delete_post_meta( $id, 'op_variation_image_gallery' );
                            delete_post_meta( $id, '_thumbnail_id' );

                            return self::importImages( $item );
                        }, $gallery_urls['all'] );

                        update_post_meta( $id, '_thumbnail_id', array_shift( $ids ) );
                        update_post_meta( $id, 'op_variation_image_gallery', implode( ',', $ids ) );
                        update_post_meta( $id, 'op_variation_nutrition_image', self::importImages( $gallery_urls['nutritional_image'] ) );
                    }
                },
            ];
            foreach ( $migration as $item_ver => $method ) {
                if ( $current_sync_version >= $item_ver ) {
                    $method();
                }
            }

            // Migration nutritional and calories
//            $nutritional = [
//                'calories'      => $calories,
//                'fats'          => $fats,
//                'carbohydrates' => $carbohydrates,
//                'proteins'      => $proteins,
//            ];

//            if ( get_post_meta( $id, 'op_calories', 1 ) != array_sum( $calories ) ) {
//                update_post_meta( $id, 'op_calories', array_sum( $calories ) );
//            }
//
//            if ( get_post_meta( $id, 'op_fats', 1 ) != array_sum( $fats ) ) {
//                update_post_meta( $id, 'op_fats', array_sum( $fats ) );
//            }
//
//            if ( get_post_meta( $id, 'op_carbohydrates', 1 ) != array_sum( $carbohydrates ) ) {
//                update_post_meta( $id, 'op_carbohydrates', array_sum( $carbohydrates ) );
//            }
//
//            if ( get_post_meta( $id, 'op_proteins', 1 ) != array_sum( $proteins ) ) {
//                update_post_meta( $id, 'op_proteins', array_sum( $proteins ) );
//            }

            if ( ! empty( $row['storeType'] ) AND $cashed_product['op_store_type'] != $row['storeType'] ) {
                update_post_meta( $id, 'op_store_type', strtolower( $row['storeType'] ) );
            }

            if ( ! empty( $row['Item Name/Number'] ) AND $cashed_product['op_post_title'] != $row['Item Name/Number'] ) {
                update_post_meta( $id, 'op_post_title', trim( $row['Item Name/Number'] ) );
            }

//            if ( ! empty( $row['Suggested reorder frequency'] ) ) {
//                update_post_meta( $id, 'op_suggested_reorder_frequency', trim( $row['Suggested reorder frequency'] ) );
//            }
//            if ( ! empty( $row['Allergens'] ) ) {
//                update_post_meta( $id, 'op_allergens', trim( $row['Allergens'] ) );
//            }
//            update_post_meta( $id, 'op_about_dish', $row['About this dish'] );
//            update_post_meta( $id, 'op_version', $row['version'] );
//            update_post_meta( $id, 'sync_version', $current_sync_version );

            if ( $cashed_product['internalid'] != $NetSuiteId ) {
                update_post_meta( $id, 'internalid', $NetSuiteId );
            }

            if ( $cashed_product['op_preparing_time'] != $row['Time for preparing'] ) {
                update_post_meta( $id, 'op_preparing_time', trim( str_replace( 'min', '', $row['Time for preparing'] ) ) );
            }

            if ( $cashed_product['op_prepare_instructions'] != $row['Order by'] ) {
                update_post_meta( $id, 'op_order_by', $row['Order by'] );
            }

            if ( $cashed_product['order_by'] != $row['Prepare instruction'] ) {
                update_post_meta( $id, 'op_prepare_instructions', $row['Prepare instruction'] );
            }


            if ( $cashed_product['op_microwave'] != $row['Microwave'] ) {
                update_post_meta( $id, 'op_microwave', $row['Microwave'] );
            }

            update_post_meta( $id, 'base_multiplier', $row['Base Multiplier'] );

//            $uniq_components = array_unique( array_keys( $components_ids ) );
//
//            if ( ! empty( $uniq_components ) ) {
//                $data = [];
//                foreach ( $uniq_components as $key ) {
//                    array_push( $data,
//                        get_term_meta( $components_ids[ $key ], '_op_variations_component_ingredients', true ) );
//                }
//                $string = implode( ';<br>', $data );
//
//                update_post_meta( $id, 'op_meal_ingredients', $string );
//            }
//
//            // Custom table
//            UpdateProductTable::insertOrUpdateDataSyncMeals( $id, $row, $NetSuiteId );

            // get Multiplier
//			$base_multiplier = self::getMSRPMultiplier( $id );
//			$regular_price   = array_sum( $component_prices ) * $base_multiplier;
//			update_post_meta( $id, '_regular_price', round( $regular_price, 2 ) );
        }

        \TB::add_message( "Total count: *{$count_of_line}*\n Update lines: *{$update_lines}*\n Insert count: *$insert_lines*.", 'import-message' );

        return ( "<h4>Done!</h4><p>Total count: <b>$count_of_line</b></p><p>Update lines: <b>$update_lines</b></p><p>Insert count: <b>$insert_lines</b></p>" );
    }

    public static function getMSRPMultiplier( $id ) {
        global $wpdb;

        return $wpdb->get_var( $wpdb->prepare( "SELECT msrp_multiplier FROM {$wpdb->prefix}data_sync WHERE wp_id = %d AND type='variation';", $id ) );
    }

    public static function getComponentBasePrice( $id ) {
        global $wpdb;

        return $wpdb->get_var( $wpdb->prepare( "SELECT base_price FROM {$wpdb->prefix}data_sync WHERE external_id = %d AND type='term';", $id ) );
    }

    public static function createImagesUrls( $components_ids, $component = false, $is_staple = false ) {
        $basic_url                   = 'https://stage-media.s3.us-east-2.amazonaws.com/';
        $product_images              = [ 'wp', 'bb', 'gp' ];
        $components_prefix           = 'op';
        $components_nutrition_prefix = 'cnl';

        if ( is_array( $components_ids ) ) {
            sort( $components_ids );
        }

        if ( $component ) {
            return [
                'base'      => $basic_url . $components_prefix . '/' . $components_ids . '.jpg',
                'nutrition' => $basic_url . $components_nutrition_prefix . '/' . $components_ids . '-cnl.jpg',
            ];
        }

        if ( $is_staple ) {
            $staple_img = [];
            for ( $i = 1; $i < 4; $i ++ ) {
                $u = $basic_url . 'staple/' . $components_ids . "-0{$i}.jpg";
                if ( ( @get_headers( $u ) )[0] == 'HTTP/1.1 200 OK' ) {
                    $staple_img[] = $u;
                }
            }

            return $staple_img;
        }

        // .png
        $nutritional_image = implode( '-', $components_ids ) . '-nl.png';

        $main_images = array_map( function ( $item ) use ( $components_ids ) {
            $concat_components_ids = implode( '-', $components_ids ) . '-' . $item . '.jpg';

            return $item . '/' . $concat_components_ids;
        }, $product_images );

        $uniq_components = array_unique( $components_ids );

        $components_images = array_map( function ( $item ) use ( $components_prefix ) {
            return $components_prefix . '/' . $item . '.jpg';
        }, $uniq_components );

        $all_images = array_merge( $main_images, $components_images, [ $nutritional_image ] );

        $r = [
            'nutritional_image' => $basic_url . $nutritional_image,
            'all'               => array_map( function ( $item ) use ( $basic_url ) {
                return $basic_url . $item;
            }, $all_images )
        ];

        return $r;
    }

    public static function importImages( $filename ) {
        $filetype = wp_check_filetype( basename( $filename ), null );

        $attachment = array(
            'guid'           => $filename,
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        $attach_id = wp_insert_attachment( $attachment, $filename );

        // Include image.php
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Define attachment metadata
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );

        // Assign metadata to attachment
        wp_update_attachment_metadata( $attach_id, $attach_data );

        return $attach_id;
    }

    private static function importVitamins( $data ) {
    }
}