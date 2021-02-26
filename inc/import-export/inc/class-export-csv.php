<?php


namespace SfSync;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ExportCSV {
    public static function exportMeals( $target = 'php://output', $mode = 'export_meals' ) {
        global $wpdb;

        $headers = [
            'Meal NetSuite ID',
            'WP ID',
            'sync_version',
            'Status',
            'Item Name/Number',
            'Slug URL',
            'Description',
            'Base Price',
            'Sale price',
            'Base Multiplier',
            'MSRP Multiplier',
            'Item Weight',
            'Item Unit Weight',
            'Order by',
//            'Allergens',
            'Component 1 - Internal ID',
            'Component 1 - Item',
            'Component 2 - Internal ID',
            'Component 2 - Item',
            'Component 3 - Internal ID',
            'Component 3 - Item',
            'Servings Per Container',
            'Keep Refregirated',
//            'Suggested reorder frequency',
//			'Nutritional Info Image',
            'Stock status',
            'Diet Items',
            'Prepare instruction',
//            'About this dish',
            'Time for preparing',
            'Microwave',
            'storeType',
//            'version',
//			'Image - 1',
//			'Image - 2',
//			'Image - 3',
//			'Image - 4',
//			'Image - 5'
        ];

        //Main Variable Product id
        $main_product_id = ImportData::getPostByName( ImportData::$main_meal_name );
//		$variations_ids  = wc_get_product( $main_product_id )->get_children();
        $variations = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_status, post_name FROM {$wpdb->posts} as p WHERE (p.post_status='publish' OR p.post_status='trash') AND p.post_parent = %s", $main_product_id ) );
        $table_data = [];

        foreach ( $variations as $variation ) {
            $product         = wc_get_product( $variation->ID );
            $NetSuiteId      = $product->get_meta( 'internalid', true );
            $title           = "\"" . self::clear( $product->get_meta( 'op_post_title', true ) ) . "\"";
            $status          = "\"" . self::clear( $variation->post_status ) . "\"";
            $post_name       = "\"" . self::clear( $variation->post_name ) . "\"";
            $sync_version    = '0';/*$product->get_meta( 'sync_version', true )*/
            $description     = "\"" . self::clear( $product->get_description() ) . "\"";
            $base_price      = $product->get_regular_price();
            $sale_price      = $product->get_sale_price();
            $weight          = $product->get_weight();
            $base_multiplier = $product->get_meta( 'base_multiplier', true );
            $MSRP_multiplier = ImportData::getMSRPMultiplier( $product->get_id() );

            // Get data from DB
            $data_from_db = self::getMealsDataFromDB( $product->get_id() );

            $unit_weight = ( is_null( $data_from_db[0]['weight_unit'] ) ) ? '' : $data_from_db[0]['weight_unit'];
            $orderby     = get_post_meta( $product->get_id(), 'op_order_by', true );
//            $allergens   = get_post_meta( $product->get_id(), 'op_allergens', true );

            $table_data['headers']             = $headers;
            $table_data["id_{$variation->ID}"] = [
                $NetSuiteId,
                $product->get_id(),
                $sync_version,
                $status,
                $title,
                $post_name,
                $description,
                $base_price,
                $sale_price,
                $base_multiplier,
                $MSRP_multiplier,
                $weight,
                $unit_weight,
                $orderby,
//                $allergens,
            ];

            foreach ( $product->get_attributes() as $key => $attr ) {
                $term                                = get_term_by( 'slug', $attr, $key );
                $table_data["id_{$variation->ID}"][] = get_term_meta( $term->term_id, '_op_variations_component_sku',
                    true );
                $table_data["id_{$variation->ID}"][] = $term->name;
            }

            //Servings Per Container
            $table_data["id_{$variation->ID}"][] = $data_from_db[0]['servings_per_container'];
            //Keep Refregirated
            $table_data["id_{$variation->ID}"][] = ( $data_from_db[0]['keep_refrigerated'] ) ? 'Yes' : 'No';
//            //Suggested reorder frequency
//            $table_data["id_{$variation->ID}"][] = $data_from_db[0]['reorder_frequency'];
            // Stock status
            $table_data["id_{$variation->ID}"][] = $product->get_stock_status();
            // Diet Items
            $diets = get_post_meta( $product->get_id(), 'op_diet' );

            if ( ! empty( $diets ) ) {
                $string = '';
                foreach ( $diets as $diet ) {
                    $string .= $diet . ',';
                }
                $table_data["id_{$variation->ID}"][] = "\"" . self::clear( $string ) . "\"";
            } else {
                $table_data["id_{$variation->ID}"][] = '';
            }

            //Prepare instruction
            $table_data["id_{$variation->ID}"][] = "\"" . self::clear( get_post_meta( $product->get_id(), 'op_prepare_instructions',
                    true ) ) . "\"";
//            // About this dish
//            $table_data["id_{$variation->ID}"][] = "\"" . self::clear( get_post_meta( $product->get_id(), 'op_about_dish', true ) ) . "\"";
            // Time for preparing
            $table_data["id_{$variation->ID}"][] = get_post_meta( $product->get_id(), 'op_preparing_time', true );
            // microwave
            $table_data["id_{$variation->ID}"][] = get_post_meta( $product->get_id(), 'op_microwave', true );

            $table_data["id_{$variation->ID}"][] = get_post_meta( $product->get_id(), 'op_store_type', true );
//            // version date
//            $table_data["id_{$variation->ID}"][] = get_post_meta( $product->get_id(), 'op_version', true );

//			$images = $product->get_meta( 'op_variation_image_gallery', true );
//
//			if ( ! empty( $images ) ) {
//				$images_array = array_filter( explode( ',', $images ) );
//				foreach ( $images_array as $key => $image ) {
//					$post                            = get_post( $image );
//					$table_data["id_{$variation->ID}"][] = $post->guid;
//				}
//
//			}

        }

        self::createExportFile( $table_data, $mode, $target );
    }

    public static function getMealsDataFromDB( $wp_id ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare( "SELECT external_id, weight_unit, servings_per_container, keep_refrigerated, reorder_frequency FROM {$wpdb->prefix}data_sync WHERE wp_id = %s AND type='variation'", $wp_id ),
            ARRAY_A );
    }

    public static function exportComponents( $target = 'php://output', $mode = 'export_components' ) {
        $headers = [
            'Component NetSuite ID', //1
            'WP ID', //1
            'Is published', // 1
//            'portion size oz', //1
            'Item Name/Number', //1
            'Description', //1
//            'warehouse location', //1
            'Base Price', //1
//            'Item Weight', //1
//            'Item Unit Weight', //1
//            'Servin Size Weight ()', //1
//            'Servings Per Container', //1
            'Calories', //1
            'Total Fat (g)', //1
            'Total Carbohydrate (g)', //1
            'Protein (g)', //1
            'Allergens', //?
            'Ingredients',
//            'Facility Allergens', //1
            'Preparation Instructions', //1
//            'Kit Type', //1
            'Warehouse Location type', //1
//            'Keep refregirated', //1
//            'Suggested reorder frequency', //1
            'Picture/Image 1', //1
//            'MSRP Multiplier', //1
//            'Display Multiplier', //1
            'Sort order',
            'Badges',
            'Image - 1',
            'Image - 2',
            'Image - 3',
            'Image - 4',
            'Image - 5'
        ];

        $table_data = [
            $headers
        ];

        // Получить все sku и убрать повторы?
        // Получить из одной таксономии все terms ?
        $terms = get_terms( [
            'taxonomy'   => 'pa_part-1',
            'hide_empty' => false,
        ] );

        foreach ( $terms as $term ) {
            $data_from_meta = get_term_meta( $term->term_id );
//            $data_from_db   = self::getComponentsDataFromDB( $data_from_meta['_op_variations_component_sku'][0] );

            $table_data["id_{$term->term_id}"][] = $data_from_meta['_op_variations_component_sku'][0];
            $table_data["id_{$term->term_id}"][] = $term->term_id;
            $table_data["id_{$term->term_id}"][] = $data_from_meta['is_published'][0];
//            $table_data["id_{$term->term_id}"][] = $data_from_db[0]['portion_size'];
            $table_data["id_{$term->term_id}"][] = "\"" . self::clear( $term->name ) . "\"";
            $table_data["id_{$term->term_id}"][] = "\"" . self::clear( $term->description ) . "\"";
//            $table_data["id_{$term->term_id}"][] = $data_from_db[0]['warehouse_location'];
            $table_data["id_{$term->term_id}"][] = $data_from_meta['_price'][0];
//            $table_data["id_{$term->term_id}"][] = $data_from_db[0]['weight'];
//            $table_data["id_{$term->term_id}"][] = $data_from_db[0]['weight_unit'];
//            $table_data["id_{$term->term_id}"][] = $data_from_db[0]['servings_size_weight'];
//            $table_data["id_{$term->term_id}"][] = $data_from_db[0]['servings_per_container'];
            $table_data["id_{$term->term_id}"][] = $data_from_meta['_op_variations_component_calories'][0];
            $table_data["id_{$term->term_id}"][] = $data_from_meta['_op_variations_component_fats'][0];
            $table_data["id_{$term->term_id}"][] = $data_from_meta['_op_variations_component_carbohydrates'][0];
            $table_data["id_{$term->term_id}"][] = $data_from_meta['_op_variations_component_proteins'][0];

            $allergens = carbon_get_term_meta( $term->term_id, 'op_variations_component_allergens' );
            if ( ! empty( $allergens ) ) {
                $table_data["id_{$term->term_id}"][] = "\"" . implode( ',', $allergens ) . "\"";
            } else {
                $table_data["id_{$term->term_id}"][] = ''; //allergens
            }

            //Ingridients
            $table_data["id_{$term->term_id}"][] = "\"" . self::clear( $data_from_meta['_op_variations_component_ingredients'][0] ) . "\"";

//            $table_data["id_{$term->term_id}"][] = "\"" . self::clear( $data_from_db[0]['facility_allergens'] ) . "\"";
            $table_data["id_{$term->term_id}"][] = "\"" . self::clear( $data_from_meta['_op_variations_component_instructions'][0] ) . "\"";
//            $table_data["id_{$term->term_id}"][] = $data_from_db[0]['kit_type'];
            $table_data["id_{$term->term_id}"][] = $data_from_meta['_op_variations_component_store_type'][0]; //store type
//            $table_data["id_{$term->term_id}"][] = ( $data_from_db[0]['keep_refrigerated'] ) ? 'Yes' : 'No';
//            $table_data["id_{$term->term_id}"][] = $data_from_db[0]['reorder_frequency'];
            $table_data["id_{$term->term_id}"][] = $data_from_meta['_op_variations_component_thumb'][0]; //picture
//            $table_data["id_{$term->term_id}"][] = $data_from_db[0]['msrp_multiplier'];
//            $table_data["id_{$term->term_id}"][] = $data_from_db[0]['display_multiplier'];
            $table_data["id_{$term->term_id}"][] = $data_from_meta['_op_variations_component_sort_order'][0];

            $badges = carbon_get_term_meta( $term->term_id, 'op_variations_component_badges' );
            if ( ! empty( $badges ) ) {
                $table_data["id_{$term->term_id}"][] = "\"" . self::clear( implode( ',', $badges ) ) . "\"";
            } else {
                $table_data["id_{$term->term_id}"][] = '';
            }

//			$table_data["id_{$term->term_id}"][] = $data_from_meta['_op_variations_component_sort_order'][0];
            //Gallery
            $gallery = carbon_get_term_meta( $term->term_id, 'op_variations_component__gallery' );
            if ( ! empty( $gallery ) ) {
                foreach ( $gallery as $image ) {
                    $table_data["id_{$term->term_id}"][] = self::getImageFromPost( $image );
                }
            }
        }

        self::createExportFile( $table_data, $mode, $target );
    }

    public static function getStraplesDataFromDB( $wp_id ) {
        global $wpdb;

        $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}data_sync WHERE wp_id = %s AND type='simple'", $wp_id ),
            ARRAY_A );

        return $result[0];
    }

    public static function exportStraples( $target = 'php://output', $mode = 'export_straples' ) {
        global $wpdb;

        $headers = [
            'Staple NetSuite ID', //1
            'SKU', //1
            'sync_version',
            'WP ID',
            'Item Name/Number', //1
            'Slug URL', //1
            'Description', //1
            'Warehouse Location type',
            'Calories',
            'Total Fat (g)',
            'Protein (g)',
            'Total Carbohydrate (g)',
            'Badges',
            'Allergens',
            'Item Weight',
            'Base Price',
            'Sale Price',
            'Base Multiplier',
            'MSRP Multiplier',
            'warehouse location',
            'Weight Unit',
            'keep refregirated',
            'Suggested reorder frequency',
            'Display Multiplier',
            'sub-category1',
            'Sort order',
            'Ingredients',
        ];

        $products = wc_get_products( array(
            'category'       => array( 'staples' ),
            'posts_per_page' => - 1
        ) );

        $table_data = [];

        foreach ( $products as $product ) {
            $product_id     = $product->get_id();
            $data_from_meta = get_post_meta( $product_id );
            $data_from_db   = self::getStraplesDataFromDB( $product_id );

            $NetSuiteId  = ( empty( $product->get_meta( 'internalid', true ) ) ) ? $product->get_meta( 'internalId',
                true ) : $product->get_meta( 'internalid', true );
            $sku         = $data_from_meta['_sku'][0];
            $title       = "\"" . self::clear( $product->get_title() ) . "\"";
            $post_name   = $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM $wpdb->posts WHERE ID = %s", $product->get_id() ) );
            $post_name   = "\"" . self::clear( $post_name ) . "\"";
            $description = "\"" . self::clear( $product->get_description() ) . "\"";
            //$sale_price  = $product->get_sale_price();
            $table_data['headers']            = $headers;
            $table_data["id_{$product_id}"]   = [
                $NetSuiteId,
                $sku,
                '0',
                $product_id,
                $title,
                $post_name,
                $description,
            ];
            $table_data["id_{$product_id}"][] = $data_from_meta['op_store_type'][0]; //Warehouse Location type
            $table_data["id_{$product_id}"][] = $data_from_meta['op_calories'][0]; //calories
            $table_data["id_{$product_id}"][] = $data_from_meta['op_fats'][0]; //fats
            $table_data["id_{$product_id}"][] = $data_from_meta['op_proteins'][0]; //protein
            $table_data["id_{$product_id}"][] = $data_from_meta['op_carbohydrates'][0]; //carbs
            $table_data["id_{$product_id}"][] = $data_from_meta['op_badges'][0]; //Badges
            $table_data["id_{$product_id}"][] = $data_from_meta['op_allergens'][0]; //Allergens

            $table_data["id_{$product_id}"][] = $data_from_meta['_weight'][0]; //weight
            $table_data["id_{$product_id}"][] = $data_from_meta['_regular_price'][0]; //price
            $table_data["id_{$product_id}"][] = $data_from_meta['_sale_price'][0];
            $table_data["id_{$product_id}"][] = $data_from_meta['base_multiplier'][0];
            $table_data["id_{$product_id}"][] = $data_from_db['msrp_multiplier'];

            $table_data["id_{$product_id}"][] = $data_from_db['warehouse_location'];
            $table_data["id_{$product_id}"][] = $data_from_db['weight_unit'];
            $table_data["id_{$product_id}"][] = $data_from_db['keep_refrigerated'];
            $table_data["id_{$product_id}"][] = $data_from_db['reorder_frequency'];
            $table_data["id_{$product_id}"][] = $data_from_db['display_multiplier'];

            // get product tags (subcategories)
            $tags = $product->get_tag_ids();

            $tags_data = '';
            if ( ! empty( $tags ) ) {
                $all_terms = get_terms( [
                    'taxonomy'   => 'product_tag',
                    'hide_empty' => false,
                    'include'    => $tags
                ] );

                $product_tags = array_map( function ( $item ) {
                    return $item->name;
                }, $all_terms );

                $tags_data = "\"" . self::clear( implode( ',', $product_tags ) ) . "\"";
            }
            $table_data["id_{$product_id}"][] = $tags_data;
            $table_data["id_{$product_id}"][] = $data_from_meta['op_order_by'][0];
            $table_data["id_{$product_id}"][] = "\"" . self::clear( $data_from_meta['op_ingredients'][0] ) . "\""; //Ingredients
        }

        self::createExportFile( $table_data, $mode, $target );

    }

    public static function clear( $data ) {
        $a = [
            '"'  => '&quot;',
            "\n" => '<br>',
        ];

        return strtr( $data, $a );
    }

    public static function getImageFromPost( $id ) {
        $post = get_post( $id );

        return $post->guid;
    }

    public static function createExportFile( $table_data, $type, $target ) {
        $spreadsheet = new Spreadsheet();

        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->fromArray( $table_data, null, 'A1' );

        $writer = new Csv( $spreadsheet );
        $writer->setUseBOM( true );
        $writer->setDelimiter( ',' );
        $writer->setEnclosure( '' );
        $writer->setLineEnding( "\r\n" );
        $writer->setSheetIndex( 0 );

        while ( ob_get_level() ) {
            ob_get_clean();
        }

//		header( 'Content-Disposition: attachment; filename="' . $filename . '.csv"' );

        self::removeOldExportFile( $type );

        update_option( "sf_{$type}", $target );
        $writer->save( $target );
    }

    public static function removeOldExportFile( $type ) {
        $file = get_option( "sf_{$type}" );

        if ( file_exists( $file ) ) {
            unlink( $file );
        }
    }

    public static function getComponentsDataFromDB( $id ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}data_sync WHERE external_id = %s AND type='term'", $id ),
            ARRAY_A );
    }
}