<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class SFAddonVariations {

    /**
     * @var SFAddonVariationsRules
     */
    public $rules;

    private static $_instance = null;
    private $variation_cat;

    /**
     * @return SFAddonVariations
     */
    static public function getInstance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    function init() {
        // load required files
        require_once 'SFAddonVariationsRules.php';

        // init dubclasses
        $this->rules = SFAddonVariationsRules::getInstance();

        $this->rules->init();


        // add settings subpage
        add_action( 'sf_add_theme_suboption', [ $this, 'add_settings_subpage' ], 11 );

        add_action( 'carbon_fields_register_fields', [ $this, 'add_fields_to_components' ], 20 );
        add_action( 'carbon_fields_register_fields', [ $this, 'add_fields_to_categories' ], 20 );

        add_action( 'woocommerce_variation_options', [ $this, 'add_variation_fields' ], 30, 3 );
        add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'add_variation_fields_after' ], 30,
            3 );
        add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation_fields' ], 10, 1 );
        add_filter( 'woocommerce_available_variation', [ $this, 'add_attributes_to_available_variations' ], 10, 3 );


        add_action( 'woocommerce_product_options_pricing', [ $this, 'add_simple_item_fields' ], 10 );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_simple_item_fields' ], 10 );

        if ( ! is_admin() ) {
            add_action( 'pre_get_posts', [ $this, 'check_query_for_variations' ], 1000 );
            add_filter( 'post_limits_request', [ $this, 'change_limits_for_variations_parents' ], 1000, 2 );
            add_filter( 'posts_pre_query', [ $this, 'change_query_for_variations' ], 1000, 2 );
        }

        add_filter( 'the_title', [ $this, 'change_title_for_variations' ], 1000, 2 );

        add_action( 'woocommerce_variation_options', [ $this, 'add_gallery_to_variations' ], 10, 3 );

        add_action( 'woocommerce_variation_options', [ $this, 'add_nutrition_image_to_variations' ], 20, 3 );


        add_action( 'init', [ $this, 'register_qna' ] );
        add_action( 'init', [ $this, 'register_pr_instr' ] );

        add_filter( 'loop_shop_per_page', [ $this, 'set_shop_per_page' ], 20 );

        add_filter( 'woocommerce_catalog_orderby', [ $this, 'change_sort_options' ], 20 );

        return $this;
    }

    /**
     * Get components terms by field
     *
     * @param string $field name of meta field
     * @param bool|string|array $value bool will be null or not, array will be IN, string will be =
     * @param bool $terms return term objects
     *
     * @return [type]
     */
    function get_components_by_meta( $field, $value = false, $terms = false ) {
        global $wpdb;
        $query_string = "SELECT term_id FROM {$wpdb->termmeta}";
        $field        = esc_sql( $field );
        $query_string .= " WHERE meta_key LIKE '%$field%' AND ";
        if ( is_bool( $value ) ) {
            if ( $value ) {
                $query_string .= "meta_value IS NOT NULL";
            } else {
                $query_string .= "meta_value IS NULL";
            }
        } elseif ( is_array( $value ) ) {
            $value        = esc_sql( $value );
            $values       = implode( '\',\'', $value );
            $query_string .= "meta_value IN ('$values')";
        } else {
            $value        = esc_sql( $value );
            $query_string .= "meta_value = '$value'";
        }

        $query_ids      = $wpdb->get_results( $query_string, ARRAY_A );
        $components_ids = array_map( function ( $q ) {
            return intval( $q['term_id'] );
        }, $query_ids );

        return $components_ids;
    }

    function change_sort_options( $options ) {
        $default_title = "Chef's Choice";

        if ( op_help()->sf_user->check_survey_default() ) {
            $default_title = "Best For You & Chef's Choice";
        }

        $options['default'] = __( $default_title, 'woocommerce' );

//		if ( op_help()->sf_user->check_survey_exist() ) {
//			$options['menu_order'] = __( 'Sort by Survey result', 'woocommerce' );
//		}

        unset( $options['menu_order'] );
        unset( $options['popularity'] );
        unset( $options['rating'] ); // TODO delete after implementing rating
        unset( $options['date'] );

        return array_reverse( $options );
    }

    function set_shop_per_page( $cols ) {
        // $cols contains the current number of products per page based on the value stored on Options -> Reading
        // Return the number of products you wanna show per page.
        $cols = 12;

        return $cols;
    }

    function save_simple_item_fields( $item_id ) {

        if ( ! empty( $_POST['op_calories'] ) ) {
            update_post_meta( $item_id, 'op_calories', $_POST['op_calories'] );
        }

        if ( ! empty( $_POST['op_fats'] ) ) {
            update_post_meta( $item_id, 'op_fats', $_POST['op_fats'] );
        }

        if ( ! empty( $_POST['op_proteins'] ) ) {
            update_post_meta( $item_id, 'op_proteins', $_POST['op_proteins'] );
        }

        if ( ! empty( $_POST['op_carbohydrates'] ) ) {
            update_post_meta( $item_id, 'op_carbohydrates', $_POST['op_carbohydrates'] );
        }

        if ( ! empty( $_POST['op_zones'] ) ) {
            update_post_meta( $item_id, 'op_zones', $_POST['op_zones'] );
        }

        if ( ! empty( $_POST['op_store_type'] ) ) {
            update_post_meta( $item_id, 'op_store_type', $_POST['op_store_type'] );
        }

        if ( ! empty( $_POST['op_order_by'] ) ) {
            update_post_meta( $item_id, 'op_order_by', $_POST['op_order_by'] );
        }

        if ( ! empty( $_POST['op_simple_badges'] ) ) {
            update_post_meta( $item_id, 'op_simple_badges', $_POST['op_simple_badges'] );
        }

        if ( ! empty( $_POST['op_key_ingredients'] ) ) {
            update_post_meta( $item_id, 'op_key_ingredients', $_POST['op_key_ingredients'] );
        }

        if ( ! empty( $_POST['op_other_ingredients'] ) ) {
            update_post_meta( $item_id, 'op_other_ingredients', $_POST['op_other_ingredients'] );
        }

        if ( ! empty( $_POST['op_simple_badges_not_contain'] ) ) {
            update_post_meta( $item_id, 'op_simple_badges_not_contain', $_POST['op_simple_badges_not_contain'] );
        }

        if ( ! empty( $_POST['op_taking_pills'] ) ) {
            update_post_meta( $item_id, 'op_taking_pills', $_POST['op_taking_pills'] );
        }

        if ( ! empty( $_POST['op_instructions'] ) ) {
            update_post_meta( $item_id, 'op_instructions', $_POST['op_instructions'] );
        }

        if ( ! empty( $_POST['op_warnings'] ) ) {
            update_post_meta( $item_id, 'op_warnings', $_POST['op_warnings'] );
        }

    }

    function change_title_for_variations( $title, $id ) {

        $this_object = get_post( $id );
        if ( $this_object->post_type === 'product_variation' ) {
            $title = $this_object->op_post_title;
        }

        return $title;

    }

    function check_query_for_variations( $query ) {

        $query->set( 'op_variation_logic', false ); // for all other queries
        if ( is_admin() ) {
            return $query;
        }
        // do it only for front pages
        if ( empty( $this->variation_cat ) ) {
            $this->variation_cat = carbon_get_theme_option( 'op_shop_variationcat' );
        }

        $variation_cats_options = $this->variation_cat;
        $variation_cats_ids     = array_column( $variation_cats_options, 'id' );
        if ( ! empty( $query->query['product_cat'] ) ) {
            $current_product_cat = get_term_by( 'slug', $query->query['product_cat'], 'product_cat' );
            if ( in_array( $current_product_cat->term_id, $variation_cats_ids ) ) {
                $query->set( 'op_variation_logic', true ); // for cats chosen as variations
            }
//            $query->set( 'op_old_posts_per_page', $query->query_vars['posts_per_page'] );
//			$query->set( 'posts_per_page', - 1 );
        }

        return $query;
    }

    function change_limits_for_variations_parents( $limits, $query ) {
        if ( $query->query_vars['op_variation_logic'] ) {

            // no limit for parent variations
            // make sure you set limit for child items
            return '';

        }

        return $limits;
    }

    /**
     * сортировка в нужном порядке
     *
     * @param $all_products
     * @param $user_sort_cache
     *
     * @return array
     */
    public function get_sort_order( $all_products, $user_sort_cache ) {

        $result = [];

        foreach ( $all_products as $item ) {
            $key            = array_search( $item['var_id'], $user_sort_cache );
            $result[ $key ] = $item;
        }

        ksort( $result );

        return $result;
    }

    /**
     * Pagination products
     *
     * @param $page
     * @param $order
     * @param $catId
     * @param $taxonomy
     *
     * @return array
     */
    public function get_pagination_variations_items( $page, $order, $catId, $taxonomy ) {
        global $sf_sort_cache;

        $default_products   = get_option( 'posts_per_page' );
        $offset             = $default_products * $page;
        $user_id            = 0;
        $logged_in          = is_user_logged_in();
        $use_survey_results = (bool) op_help()->sf_user->check_survey_default();
        $meals_cat_id       = 15;

        $ordering = empty( $order ) ? 'default' : sanitize_text_field( $order );

        if ( $logged_in ) {
            $user_id = get_current_user_id();
        }

        if ( $meals_cat_id == $catId ) {
            $sort_cache = op_help()->sort_cache->get_sort_cache( $default_products, $offset, $ordering, $user_id, $use_survey_results, 'variation' );
        } else {

            if ( $taxonomy == 'product_cat' ) {
                $sort_cache = op_help()->sort_cache->get_sort_cache( $default_products, $offset, $ordering, $user_id, 0, 'simple', $catId );
            }
            if ( $taxonomy == 'product_tag' ) {
                $sort_cache = op_help()->sort_cache->get_sort_cache( $default_products, $offset, $ordering, $user_id, 0, 'simple', false, $catId );
            }
        }

        //set global var
        $sf_sort_cache = $sort_cache;

        return $sort_cache['ids_with_chef_score'];
    }

    function change_query_for_variations( $posts, $query ) {
        global $sf_results_all_items;
        global $sf_results_filtered_items;
        global $sf_sort_cache;
        global $pagination_num_pages;

        if ( empty( $query->query['product_cat'] ) AND empty( $query->query['product_tag'] ) ) {
            return $posts;
        }

        // if Meals
        $recommended = op_help()->sf_user->check_survey_default();
        $ordering    = empty( $_GET['orderby'] ) ? 'default' : sanitize_text_field( $_GET['orderby'] );

        if ( $query->query_vars['op_variation_logic'] ) {

            $sort_cache = op_help()->sort_cache->get_sort_cache( $query->query_vars['posts_per_page'], 0, $ordering, false, $recommended, 'variation' );
        } else {

            if ( ! empty( $query->query['product_tag'] ) ) {
                $sort_cache = op_help()->sort_cache->get_sort_cache( $query->query_vars['posts_per_page'], 0, $ordering, false, $recommended, 'simple', false, get_term_by( 'slug', $query->query['product_tag'], 'product_tag' )->term_id );
            } else {
                $sort_cache = op_help()->sort_cache->get_sort_cache( $query->query_vars['posts_per_page'], 0, $ordering, false, $recommended, 'simple', get_term_by( 'slug', $query->query['product_cat'], 'product_cat' )->term_id );
            }
        }

        $sf_results_all_items      = $sort_cache['total'];
        $sf_results_filtered_items = $sort_cache['filtered_total'];
        $sf_sort_cache             = $sort_cache;

        $query->found_posts = $sf_results_all_items;

        $pagination_num_pages = intdiv( $sort_cache['filtered_total'], $query->query_vars['posts_per_page'] );

        return $sort_cache['ids'];
    }

    public function filtering_survey_and_allergens_from_components() {

    }

    // for offerings
    // TODO проверить работу с заполненым survey
    public function filtered_products( $needed_variations_items, $use_survey_results = true ) {

        $filter_score = op_help()->survey->calculate_survey_score();

        $filters           = [];
        $allergies_options = carbon_get_theme_option( "op_variations_allergens" );

        if ( $use_survey_results ) {
            $survey_allergies = [];
            if ( ! empty( $filter_score['allergen_scoring'] ) ) {
                foreach ( $allergies_options as $allergen ) {
                    if ( ! empty( $filter_score['allergen_scoring'][ $allergen['slug'] ] ) ) {
                        if ( $filter_score['allergen_scoring'][ $allergen['slug'] ] === 'remove' ) {
                            $survey_allergies[] = $allergen['slug'];
                        }
                    }
                }
            }
            if ( ! empty( $survey_allergies ) ) {
                global $allergies_filter;
                $survey_allergies_component_ids = op_help()->variations->get_components_by_meta( 'op_variations_component_allergens',
                    $allergies_filter );
                if ( empty( $filters['component']['allergies'] ) ) {
                    $filters['component']['allergies'] = [
                        'compare' => 'exclude',
                        'value'   => $survey_allergies_component_ids,
                    ];
                } else {
                    $filters['component']['allergies']['value'] = array_merge( $filters['component']['allergies']['value'],
                        $survey_allergies_component_ids );
                    $filters['component']['allergies']['value'] = array_unique( $filters['component']['allergies']['value'] );
                }
            }
        }

        $filtered_cached_items = op_help()->variations->get_filtered( $filters, $needed_variations_items );
        $items_with_score      = op_help()->survey->calculate_score_for_items( $filtered_cached_items, $filter_score );
        // проверка zip зон для товара
        $items_with_score = $this->zipCheck( $items_with_score );
        // конец zip проверки
        if ( $use_survey_results ) {
            $prepared_items = array_filter( $items_with_score, function ( $item ) {
                return $item['score'] !== 'remove';
            } );
        } else {
            $prepared_items = $items_with_score;
        }

        return $prepared_items;
    }

    public function zipCheck( $items_with_score, $current_user_zip = false, $user_zone = false ) {

        if ( ! $current_user_zip ) {
            $current_user_zip = op_help()->zip_codes->get_current_user_zip();
        }

        if ( ! $user_zone ) {
            $user_zone = op_help()->zip_codes->get_current_user_zone();
        }

        if ( empty( $current_user_zip ) ) {
            $items_with_score = [];
        } else {
            //Если зип код в Local, то ему доступны товары из Local, Overnight и National
            //Если зип код Overnight, то ему доступны только товары из Local и National
            //Если зип код человека в National, то ему доступны товары только National

            $locationAreas = [
                'local'     => [
                    'local',
                    'overnight',
                    'national'
                ],
                'overnight' => [
                    'local',
                    'national'
                ],
                'national'  => [
                    'national'
                ]
            ];

            if ( array_key_exists( $user_zone, $locationAreas ) ) {
                $locationZone = $locationAreas[ $user_zone ];

                $items_with_score = array_filter( $items_with_score, function ( $item ) use ( $locationZone ) {
                    $product_zone = $item['data']['zones'][0];

                    if ( $item['type'] == 'variation' ) {
                        return in_array( $product_zone, $locationZone );
                    }

                    return array_intersect( $product_zone, $locationZone ) ? $item : false;
                } );

            }
        }

        return $items_with_score;
    }

    function add_fields_to_categories() {

        Container::make( 'term_meta', 'Components Parametres' )
                 ->where( 'term_taxonomy', 'product_cat' )
                 ->add_fields( array(
                     Field::make( 'text', 'op_categories_empty_catr_text', 'Empty Cart Text' ),
                 ) );

    }

    function add_fields_to_components() {

        $all_attributes = wc_get_attribute_taxonomies();

        if ( ! empty( $all_attributes ) ) {

            $all_attributes_slugs = array_column( $all_attributes, 'attribute_name' );

            $attributes_taxes = array_map( function ( $attribute_name ) {
                return 'pa_' . $attribute_name;
            }, $all_attributes_slugs );

        } else {

            $attributes_taxes = [];

        }

        $allergens   = carbon_get_theme_option( 'op_variations_allergens' );
        $ingredients = carbon_get_theme_option( 'op_variations_ingredients' );
        $badges      = carbon_get_theme_option( 'op_variations_badges' );

        $allergens_as_values = array_column( $allergens, 'title', 'slug' );
//		$ingredients_as_values = array_column( $ingredients, 'title', 'slug' );
        $badges_as_values = array_column( $badges, 'title', 'slug' );

        Container::make( 'term_meta', 'Components Parametres' )
                 ->where( 'term_taxonomy', 'IN', $attributes_taxes )
                 ->add_fields( array(
                     Field::make( 'text', 'op_variations_component_sku',
                         __( 'SKU' ) )->set_attribute( 'placeholder',
                         'SKU' ),
                     Field::make( 'image', 'op_variations_component_thumb', 'Thumbnail' ),
                     Field::make( 'media_gallery', 'op_variations_component__gallery', __( 'Gallery' ) )
                          ->set_type( 'image' ),
                     Field::make( 'image', 'op_variations_component_nutrition_img', 'Nutrition image' ),
                     Field::make( 'complex', 'op_variations_component_cooking_steps',
                         __( 'Tray steps' ) )->add_fields( [
                         Field::make( 'text', 'op_variations_step_text', 'Step text' )
                     ] ),
                     Field::make( 'text', 'op_variations_component_steps_header', __( 'Steps header' ) ),
                     Field::make( 'textarea', 'op_variations_component_instructions',
                         'Prepare instructions' )->set_rows( 4 ),
                     Field::make( 'text', 'op_variations_component_calories',
                         'Calories' )->set_attribute( 'placeholder', 'kC' )->set_attribute( 'type', 'number' ),
                     Field::make( 'text', 'op_variations_component_fats', 'Fats' )->set_attribute( 'placeholder',
                         'g' )->set_attribute( 'type', 'number' ),
                     Field::make( 'text', 'op_variations_component_proteins',
                         'Proteins' )->set_attribute( 'placeholder', 'g' )->set_attribute( 'type', 'number' ),
                     Field::make( 'text', 'op_variations_component_carbohydrates',
                         'Carbohydrates' )->set_attribute( 'placeholder', 'g' )->set_attribute( 'type', 'number' ),
                     Field::make( 'multiselect', 'op_variations_component_allergens',
                         __( 'Allergens' ) )->add_options( $allergens_as_values ),
                     Field::make( 'multiselect', 'op_variations_component_badges',
                         __( 'Badges' ) )->add_options( $badges_as_values ),
                     Field::make( 'textarea', 'op_variations_component_ingredients',
                         __( 'Ingredients' ) )->set_rows( 4 ),
                     Field::make( 'text', 'op_variations_component_store_type',
                         __( 'Store type' ) )->set_attribute( 'placeholder',
                         'warehouse' ),
                     Field::make( 'text', 'op_variations_component_sort_order',
                         __( 'Sort order' ) )->set_attribute( 'placeholder',
                         '10' )->set_attribute( 'type', 'number' )
                 ) );
    }

    function add_attributes_to_available_variations( $attributes, $class, $variation ) {
        $attributes['post_meta'] = get_post_meta( $variation->get_id() );

        return $attributes;
    }

    function add_variation_fields( $loop, $variation_data, $variation ) {

        woocommerce_wp_text_input(
            array(
                'id'          => 'op_post_title[' . $loop . ']',
                'label'       => __( 'Variation title', 'woocommerce' ),
                'placeholder' => 'Variation title',
                'value'       => empty( $variation->op_post_title ) ? $variation->post_title : $variation->op_post_title
            )
        );
        woocommerce_wp_text_input(
            array(
                'id'          => 'op_post_slug[' . $loop . ']',
                'label'       => __( 'Variation slug', 'woocommerce' ),
                'placeholder' => 'Variation slug',
                'value'       => $variation->post_name
            )
        );
        woocommerce_wp_text_input(
            array(
                'id'          => 'op_order_by[' . $loop . ']',
                'label'       => __( 'Sort Order', 'woocommerce' ),
                'placeholder' => 'Sort order',
                'value'       => get_post_meta( $variation->ID, 'op_order_by', true ),
                'type'        => 'number'
            )
        );
        woocommerce_wp_text_input(
            array(
                'id'                => 'op_version[' . $loop . ']',
                'label'             => __( 'Version date', 'woocommerce' ),
                'placeholder'       => 'Version date',
                'value'             => get_post_meta( $variation->ID, 'op_version', true ),
                'custom_attributes' => [
                    'readonly' => 'readonly'
                ]
            )
        );


    }

    function add_simple_item_fields() {

        global $product_object, $product, $post;

        if ( $product_object->get_type() === 'simple' ) {

            woocommerce_wp_text_input(
                array(
                    'id'          => 'op_calories',
                    'label'       => __( 'Calories', 'woocommerce' ),
                    'placeholder' => 'Calories',
                    'value'       => $product_object->get_meta( 'op_calories' )
                )
            );


            woocommerce_wp_text_input(
                array(
                    'id'          => 'op_fats',
                    'label'       => __( 'Fats', 'woocommerce' ),
                    'placeholder' => 'Fats',
                    'value'       => $product_object->get_meta( 'op_fats' )
                )
            );


            woocommerce_wp_text_input(
                array(
                    'id'          => 'op_proteins',
                    'label'       => __( 'Proteins', 'woocommerce' ),
                    'placeholder' => 'Proteins',
                    'value'       => $product_object->get_meta( 'op_proteins' )
                )
            );


            woocommerce_wp_text_input(
                array(
                    'id'          => 'op_carbohydrates',
                    'label'       => __( 'Carbohydrates', 'woocommerce' ),
                    'placeholder' => 'Carbohydrates',
                    'value'       => $product_object->get_meta( 'op_carbohydrates' )
                )
            );

            // Select Zones
            $options_zones = [];
            $zones         = carbon_get_theme_option( 'op_zones' );
            foreach ( (array) $zones as $zone ) {
                $options_zones[ $zone['slug_op_zones'] ] = $zone['title_op_zones'];
            }

            $this->woocommerce_wp_select_multiple(
                array(
                    'id'      => 'op_zones',
                    'label'   => __( 'Zones', 'woocommerce' ),
                    'options' => $options_zones,
                )
            );

            woocommerce_wp_text_input(
                array(
                    'id'          => 'op_store_type',
                    'label'       => __( 'Store type', 'woocommerce' ),
                    'placeholder' => 'e.g. warehouse',
                    'value'       => $product_object->get_meta( 'op_store_type' )
                )
            );

            woocommerce_wp_text_input(
                array(
                    'id'          => 'op_order_by',
                    'label'       => __( 'Sort Order', 'woocommerce' ),
                    'placeholder' => '',
                    'value'       => $product_object->get_meta( 'op_order_by' ),
                    'type'        => 'number'
                )
            );

            // Select Badges
            $options_badges = [];
            $badges         = carbon_get_theme_option( 'op_variations_badges' );

            foreach ( (array) $badges as $badge ) {
                $options_badges[ $badge['slug'] ] = $badge['title'];
            }

            $this->woocommerce_wp_select_multiple(
                array(
                    'id'      => 'op_simple_badges',
                    'label'   => __( 'Badges', 'woocommerce' ),
                    'options' => $options_badges,
                )
            );

            //Key Ingredients
            woocommerce_wp_textarea_input(
                array(
                    'id'    => 'op_key_ingredients',
                    'label' => __( 'Key Ingredients', 'woocommerce' ),
                    'value' => $product_object->get_meta( 'op_key_ingredients' )
                )
            );

            //Other Ingredients
            woocommerce_wp_textarea_input(
                array(
                    'id'    => 'op_other_ingredients',
                    'label' => __( 'Other Ingredients', 'woocommerce' ),
                    'value' => $product_object->get_meta( 'op_other_ingredients' )
                )
            );

            // Select Badges Not Contain
            $options_badges_not_contain = [];
            foreach ( (array) $badges as $badge ) {
                $options_badges_not_contain[ $badge['slug'] ] = $badge['title'];
            }

            $this->woocommerce_wp_select_multiple(
                array(
                    'id'      => 'op_simple_badges_not_contain',
                    'label'   => __( 'Badges "Not Contain"', 'woocommerce' ),
                    'options' => $options_badges_not_contain,
                )
            );

            // taking pills
            woocommerce_wp_text_input(
                array(
                    'id'    => 'op_taking_pills',
                    'label' => __( 'Taking pills', 'woocommerce' ),
                    'value' => $product_object->get_meta( 'op_taking_pills' )
                )
            );

            //Instructions
            woocommerce_wp_textarea_input(
                array(
                    'id'    => 'op_instructions',
                    'label' => __( 'Instructions', 'woocommerce' ),
                    'value' => $product_object->get_meta( 'op_instructions' )
                )
            );

            //Warnings
            woocommerce_wp_textarea_input(
                array(
                    'id'    => 'op_warnings',
                    'label' => __( 'Warnings', 'woocommerce' ),
                    'value' => $product_object->get_meta( 'op_warnings' )
                )
            );
        }

    }

    function woocommerce_wp_select_multiple( $field ) {

        global $thepostid, $post, $woocommerce;

        $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
        $thepostid              = ! empty( $field['variation_id'] ) ? $field['variation_id'] : $thepostid;
        $field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
        $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
        $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
        $field['value']         = isset( $field['value'] ) ? $field['value'] : ( get_post_meta( $thepostid,
            $field['id'], true ) ? get_post_meta( $thepostid, $field['id'], true ) : array() );

        echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '[]" class="' . esc_attr( $field['class'] ) . '" multiple="multiple">';

        foreach ( $field['options'] as $key => $value ) {

            echo '<option value="' . esc_attr( $key ) . '" ' . ( in_array( $key,
                    (array) $field['value'] ) ? 'selected="selected"' : '' ) . '>' . esc_html( $value ) . '</option>';

        }

        echo '</select> ';

        if ( ! empty( $field['description'] ) ) {

            if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
                echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
            } else {
                echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
            }

        }
        echo '</p>';
        ?>
        <script>
            jQuery(document).ready(function () {
                jQuery('.wp-core-ui select#<?php echo $thepostid; ?> option').mousedown(function (e) {
                    e.preventDefault();
                    console.log('Hey');
                    jQuery(this).prop('selected', !jQuery(this).prop('selected'));
                    return false;
                });
            });
        </script>
        <?php
    }

    /**
     * @param mixed $filters
     * @param integer $paged
     *
     * @return array IDs of products
     */
    function get_filtered( $filters, $items = [], $paged = 1 ) {

        if ( empty( $items ) ) {
            $all_products = apply_filters( "sf_pre_cache_all_products", op_help()->global_cache->getAll() );
        } else {
            $all_products = apply_filters( "sf_pre_cache_passed_products", $items );
        }
        $filtered_products_final = $all_products;
        $products_by_filter      = [];

        if ( ! empty( $filters ) ) {
            foreach ( $filters as $filter_key => $filter_data ) {
                $products_by_filter[ $filter_key ] = [];
                if ( $filter_key === 'components' ) {
                    continue;
                }
                // zones and diets
                $products_by_filter[ $filter_key ] = array_filter( $all_products,
                    function ( $product ) use ( $filter_key, $filter_data ) {
                        if ( empty( $filter_data['value'] ) ) {
                            return true;
                        }
                        if ( empty( $product['data'][ $filter_key ] ) ) {
                            return false;
                        }
                        if ( is_array( $filter_data['value'] ) ) {
                            $filter_values = array_filter( $filter_data['value'] );
                        } else {
                            $filter_values = [ $filter_data['value'] ];
                        }
                        if ( is_array( $product['data'][ $filter_key ] ) ) {
                            $product_values = array_filter( $product['data'][ $filter_key ] );
                        } else {
                            $product_values = [ $filter_data['value'] ];
                        }

                        foreach ( $filter_values as $f_val ) {
                            if ( in_array( $f_val, $product_values ) ) {
                                return true;
                            }
                        }

                        return false;
                    } );
            }

            foreach ( $filters as $filter_key => $filter_data ) {
                if ( $filter_key === 'components' ) {
                    continue;
                }
                $filter_compare = empty( $filter_data['compare'] ) ? 'include' : $filter_data['compare'];
                if ( ! in_array( $filter_data['compare'], [ "include", "exclude" ] ) ) {
                    $filter_compare = 'include';
                }

                $filtered_ids            = array_column( $products_by_filter[ $filter_key ], 'var_id' );
                $filtered_products_final = array_filter( $filtered_products_final,
                    function ( $item ) use ( $filtered_ids, $filter_compare ) {
                        if ( $filter_compare == 'include' ) {
                            return in_array( $item['var_id'], $filtered_ids );
                        }
                        if ( $filter_compare == 'exclude' ) {
                            return ! in_array( $item['var_id'], $filtered_ids );
                        }
                    } );
            }

            if ( ! empty( $filters['component'] ) ) {

                $filtered_products_final = $this->filter_by_components( $filtered_products_final,
                    $filters['component'] );

            }

        }

        // $filtered_products_ids = array_column( $filtered_products_final, 'var_id' );

        return $filtered_products_final;

    }

    function filter_by_components( $all_products, $component_filter_data ) {

        $passed_products = $all_products;

        foreach ( $component_filter_data as $filter_key => $filter_data ) {
            $compare_method     = $filter_data['compare'];
            $allowed_components = $filter_data['value'];
            $passed_products    = array_filter( $passed_products,
                function ( $item ) use ( $compare_method, $allowed_components ) {
                    if ( empty( $item['data'] ) ) {
                        return false;
                    }
                    if ( empty( $item['data']['components'] ) ) {
                        return false;
                    }

                    foreach ( $item['data']['components'] as $components_slug => $component_data ) {
                        if ( is_array( $component_data ) ) {
                            foreach ( $component_data as $comp_key => $comp_id ) {
                                if ( $compare_method == 'include' ) {
                                    if ( in_array( $comp_id, $allowed_components ) ) {
                                        return true;
                                    }
                                } elseif ( $compare_method == 'exclude' ) {
                                    if ( in_array( $comp_id, $allowed_components ) ) {
                                        return false;
                                    }
                                }
                            }
                        } else {
                            if ( $compare_method == 'include' ) {
                                if ( in_array( $component_data, $allowed_components ) ) {
                                    return true;
                                }
                            } elseif ( $compare_method == 'exclude' ) {
                                if ( in_array( $component_data, $allowed_components ) ) {
                                    return false;
                                }
                            }
                        }
                    }
                    if ( $compare_method == 'include' ) {
                        return false;
                    } elseif ( $compare_method == 'exclude' ) {
                        return true;
                    }
                } );
        }

        return $passed_products;
    }

    function add_variation_fields_after( $loop, $variation_data, $variation ) {

        // Select Zones
        /*$options_zones = [];
        $zones = carbon_get_theme_option('op_zones');
        foreach ( (array)$zones as $zone ) {
          $options_zones[ $zone['slug_op_zones'] ] = $zone['title_op_zones'];
        }
        $zones_value = get_post_meta( $variation->ID, 'op_zones', true );

        $this->woocommerce_wp_select_multiple(
          array(
           'id'           => 'op_zones['.$loop.']',
           'variation_id' => $variation->ID,
           'label'        => __( 'Zones', 'woocommerce' ),
           'options'      => $options_zones,
           'value'        => $zones_value,
          )
        );*/

        $this->show_zones_selector( $loop, $variation_data );

        $this->show_diet_selector( $loop, $variation_data );


        woocommerce_wp_textarea_input(
            array(
                'id'          => 'op_meal_ingredients[' . $loop . ']',
                'label'       => __( 'Ingredients', 'woocommerce' ),
                'placeholder' => 'Ingredients',
                'value'       => empty( $variation_data['op_meal_ingredients'][0] ) ? '' : $variation_data['op_meal_ingredients'][0]
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => 'op_calories[' . $loop . ']',
                'label'       => __( 'Calories', 'woocommerce' ),
                'placeholder' => 'Calories',
                'value'       => empty( $variation_data['op_calories'][0] ) ? '' : $variation_data['op_calories'][0]
            )
        );


        woocommerce_wp_text_input(
            array(
                'id'          => 'op_fats[' . $loop . ']',
                'label'       => __( 'Fats', 'woocommerce' ),
                'placeholder' => 'Fats',
                'value'       => empty( $variation_data['op_fats'][0] ) ? '' : $variation_data['op_fats'][0]
            )
        );


        woocommerce_wp_text_input(
            array(
                'id'          => 'op_proteins[' . $loop . ']',
                'label'       => __( 'Proteins', 'woocommerce' ),
                'placeholder' => 'Proteins',
                'value'       => empty( $variation_data['op_proteins'][0] ) ? '' : $variation_data['op_proteins'][0]
            )
        );


        woocommerce_wp_text_input(
            array(
                'id'          => 'op_carbohydrates[' . $loop . ']',
                'label'       => __( 'Carbohydrates', 'woocommerce' ),
                'placeholder' => 'Carbohydrates',
                'value'       => empty( $variation_data['op_carbohydrates'][0] ) ? '' : $variation_data['op_carbohydrates'][0]
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => 'op_preparing_time[' . $loop . ']',
                'label'       => __( 'Time for preparing', 'woocommerce' ),
                'placeholder' => 'Time for preparing',
                'value'       => empty( $variation_data['op_preparing_time'][0] ) ? '' : $variation_data['op_preparing_time'][0]
            )
        );

        woocommerce_wp_text_input(
            array(
                'id'          => 'op_store_type[' . $loop . ']',
                'label'       => __( 'Store type', 'woocommerce' ),
                'placeholder' => 'e.g. warehouse',
                'value'       => empty( $variation_data['op_store_type'][0] ) ? '' : $variation_data['op_store_type'][0]
            )
        );

        $this->show_qna_selector( $loop, $variation_data );

        $this->show_pr_instr_selector( $loop, $variation_data );

    }

    function show_zones_selector( $loop, $variation_data ) {

        $zones = carbon_get_theme_option( 'op_zones' );
        // $zones_value = get_post_meta( $variation_id, 'op_zones', true );
        $zones_value = empty( $variation_data['op_zones'] ) ? [] : $variation_data['op_zones'];
        ?>

        <div id="op_zones_select_container_<?php echo $loop; ?>">
            <p class="form-field variable_zoneslist<?php echo $loop; ?> form-row form-row-full">
                <label for="variable_description<?php echo $loop; ?>">Zones</label>
            </p>

            <?php
            if ( ! empty( $zones_value ) ) {

                foreach ( $zones as $zone ) {

                    if ( in_array( $zone['slug_op_zones'], $zones_value ) ) {

                        echo '<div class="op_zones_block inline notice woocommerce-message" data-select="op_zones_select_' . $loop . '">';
                        echo '<input type="hidden" name="op_zones[' . $loop . '][]" value="' . esc_attr( $zone['slug_op_zones'] ) . '">';
                        echo '<p>' . $zone['title_op_zones'] . '</p>';
                        echo '<a data-zonesid="' . esc_attr( $zone['slug_op_zones'] ) . '" class="notice-dismiss"></a>';
                        echo '</div>';

                    }
                }
            } else {
                echo '<p>You should choose cards in dropdown.</p>';
            }

            ?>

            <select name="op_zones[<?php echo $loop; ?>]" style="width: 100%;" class="select2"
                    id="op_zones_select_<?php echo $loop; ?>">
                <option></option>
                <?php foreach ( $zones as $zone ) {
                    echo '<option value="' . $zone['slug_op_zones'] . '">' . $zone['title_op_zones'] . '</option>';
                } ?>
            </select>
            <script>
                (function ($) {
                    $("#op_zones_select_<?php echo $loop; ?>").select2();

                    $(document).on("click", "#op_zones_select_container_<?php echo $loop; ?> .notice-dismiss", function (e) {
                        e.preventDefault()
                        let $closer = $(this)
                        // enable option
                        console.log($closer.data("zonesid"));
                        $("#op_zones_select_<?php echo $loop; ?> option[value=" + $closer.data("zonesid") + "]").attr("disabled", false)
                        $closer.parents(".op_zones_block").remove();
                    })

                    $("#op_zones_select_<?php echo $loop; ?>").on('change', function (e) {
                        let $select = $(this)
                        for (opt of e.target.children) {
                            if (opt.selected && !opt.disabled) {

                                // add card with zones Item info
                                $select.before(`<div class="op_zones_block inline notice woocommerce-message">
                                  <input type="hidden" name="op_zones[<?php echo $loop; ?>][]" value="${opt.value}">
                                  <p>${opt.innerHTML}</p>
                                  <a href="#" data-zonesid="${opt.value}" class="notice-dismiss"></a>
                                </div>`);

                                opt.disabled = true // disable option
                                break;
                            }
                        }
                    })
                })(jQuery)
            </script>
        </div>
        <?php

    }

    function show_diet_selector( $loop, $variation_data ) {

        $diet_groups = carbon_get_theme_option( "sf_diet_groups" );
        if ( empty( $diet_groups ) ) {
            return false;
        }

        $diets_options = [];
        foreach ( $diet_groups as $diet ) {
            $diets_options[ sanitize_title_with_dashes( $diet['slug'] ) ] = $diet['title'];
        }


        $selected_diet_list = empty( $variation_data['op_diet'] ) ? [] : $variation_data['op_diet'];

        ?>
        <div id="op_qna_select_container_<?php echo $loop; ?>">
            <p class="form-field variable_qnalist<?php echo $loop; ?> form-row form-row-full">
                <label for="variable_description<?php echo $loop; ?>">Diet Items</label>
            </p>

            <?php
            if ( ! empty( $selected_diet_list ) ) {

                foreach ( $selected_diet_list as $selected_diet_item ) {

                    if ( empty( $diets_options[ $selected_diet_item ] ) ) {
                        echo '<div class="inline notice woocommerce-message">';
                        echo '<p>Diet with SLUG ' . $selected_diet_item . ' was deleted. Please resave variations.</p>';
                        echo '</div>';
                    } else {

                        echo '<div class="op_diet_block inline notice woocommerce-message" data-select="op_qna_select_' . $loop . '">';
                        echo '<input type="hidden" name="op_diet[' . $loop . '][]" value="' . esc_attr( $selected_diet_item ) . '">';
                        echo '<p>' . esc_html( $diets_options[ $selected_diet_item ] ) . '</a></p>';
                        echo '<a data-dietid="' . esc_attr( $selected_diet_item ) . '" class="notice-dismiss"></a>';
                        echo '</div>';
                    }
                }
            } else {
                echo '<p>You should choose cards in dropdown.</p>';
            }

            ?>

            <select style="width: 100%;" class="select2" id="op_diet_select_<?php echo $loop; ?>">
                <option></option>
                <?php foreach ( $diets_options as $diet_item_key => $diet_item_title ) {
                    $disabled = in_array( $diet_item_key, $selected_diet_list ) ? ' disabled' : '';
                    echo '<option value="' . $diet_item_key . '" ' . $disabled . '>' . esc_html( $diet_item_title ) . '</option>';
                } ?>
            </select>
            <script>
                (function ($) {
                    $("#op_diet_select_<?php echo $loop; ?>").select2();

                    $(document).on("click", "#op_diet_select_container_<?php echo $loop; ?> .notice-dismiss", function (e) {
                        e.preventDefault()
                        let $closer = $(this)
                        // enable option
                        console.log($closer.data("qnaid"));
                        $("#op_diet_select_<?php echo $loop; ?> option[value=" + $closer.data("qnaid") + "]").attr("disabled", false)
                        $closer.parents(".op_diet_block").remove();
                    })

                    $("#op_diet_select_<?php echo $loop; ?>").on('change', function (e) {
                        let $select = $(this)
                        for (opt of e.target.children) {
                            if (opt.selected && !opt.disabled) {

                                // add card with QNA Item info
                                $select.before(`<div class="op_diet_block inline notice woocommerce-message">
                                  <input type="hidden" name="op_diet[<?php echo $loop; ?>][]" value="${opt.value}">
                                  <p>${opt.innerHTML}</p>
                                  <a href="#" data-qnaid="${opt.value}" class="notice-dismiss"></a>
                                </div>`);

                                opt.disabled = true // disable option
                                break;
                            }
                        }
                    })
                })(jQuery)
            </script>
        </div>
        <?php

    }

    function show_pr_instr_selector( $loop, $variation_data ) {

        $existed_pr_instr_query = new WP_Query;

        // делаем запрос
        $existed_pr_instr_items = $existed_pr_instr_query->query( array(
            'post_type'      => 'op_pr_instr',
            'posts_per_page' => 300,
            'no_found_rows'  => true,
        ) );

        $selected_pr_instr_list = empty( $variation_data['op_pr_instr_list'] ) ? [] : $variation_data['op_pr_instr_list'];

        ?>
        <div id="op_pr_instr_select_container_<?php echo $loop; ?>">
            <p class="form-field variable_pr_instrlist<?php echo $loop; ?> form-row form-row-full">
                <label for="variable_description<?php echo $loop; ?>">Prepare Instructions Items</label>
            </p>

            <?php
            if ( ! empty( $selected_pr_instr_list ) ) {

                foreach ( $selected_pr_instr_list as $selected_pr_instr_item ) {

                    if ( ! empty( $selected_pr_instr_item ) ) {

                        $pr_instr_item = get_post( $selected_pr_instr_item );

                    }

                    if ( is_null( $pr_instr_item ) ) {
                        echo '<div class="inline notice woocommerce-message">';
                        echo '<p>Instruction with ID#' . $pr_instr_item . ' was deleted. Please resave variations.</p>';
                        echo '</div>';
                    } else {

                        echo '<div class="op_pr_instr_block inline notice woocommerce-message" data-select="op_pr_instr_select_' . $loop . '">';
                        echo '<input type="hidden" name="op_pr_instr_list[' . $loop . '][]" value="' . esc_attr( $pr_instr_item->ID ) . '">';
                        echo '<p><a href="' . get_edit_post_link( $pr_instr_item->ID ) . '" target="_blank">' . esc_html( $pr_instr_item->post_title ) . '</a></p>';
                        echo '<a data-pr_instrid="' . esc_attr( $pr_instr_item->ID ) . '" class="notice-dismiss"></a>';
                        echo '</div>';
                    }
                }
            } else {
                echo '<p>You should choose cards in dropdown.</p>';
            }

            ?>

            <select style="width: 100%;" class="select2" id="op_pr_instr_select_<?php echo $loop; ?>">
                <option></option>
                <?php foreach ( $existed_pr_instr_items as $pr_instr_item ) {
                    $disabled = in_array( $pr_instr_item, $selected_pr_instr_list ) ? ' disabled' : '';
                    echo '<option value="' . $pr_instr_item->ID . '" data-url="' . get_edit_post_link( $pr_instr_item->ID ) . '" ' . $disabled . '>' . esc_html( $pr_instr_item->post_title ) . '</option>';
                } ?>
            </select>
            <script>
                (function ($) {
                    $("#op_pr_instr_select_<?php echo $loop; ?>").select2();

                    $(document).on("click", "#op_pr_instr_select_container_<?php echo $loop; ?> .notice-dismiss", function (e) {
                        e.preventDefault()
                        let $closer = $(this)
                        // enable option
                        console.log($closer.data("pr_instrid"));
                        $("#op_pr_instr_select_<?php echo $loop; ?> option[value=" + $closer.data("pr_instrid") + "]").attr("disabled", false)
                        $closer.parents(".op_pr_instr_block").remove();
                    })

                    $("#op_pr_instr_select_<?php echo $loop; ?>").on('change', function (e) {
                        let $select = $(this)
                        for (opt of e.target.children) {
                            if (opt.selected && !opt.disabled) {

                                // add card with pr_instr Item info
                                $select.before(`<div class="op_pr_instr_block inline notice woocommerce-message">
                                  <input type="hidden" name="op_pr_instr_list[<?php echo $loop; ?>][]" value="${opt.value}">
                                  <p><a href="${opt.dataset.url}" target="_blank" >${opt.innerHTML}</a></p>
                                  <a href="#" data-pr_instrid="${opt.value}" class="notice-dismiss"></a>
                                </div>`);

                                opt.disabled = true // disable option
                                break;
                            }
                        }
                    })
                })(jQuery)
            </script>
        </div>
        <?php

    }


    function show_qna_selector( $loop, $variation_data ) {

        $existed_qna_query = new WP_Query;

        // делаем запрос
        $existed_qna_items = $existed_qna_query->query( array(
            'post_type'      => 'op_qna',
            'posts_per_page' => 300,
            'no_found_rows'  => true,
        ) );

        $selected_qna_list = empty( $variation_data['op_qna_list'] ) ? [] : $variation_data['op_qna_list'];

        ?>
        <div id="op_qna_select_container_<?php echo $loop; ?>">
            <p class="form-field variable_qnalist<?php echo $loop; ?> form-row form-row-full">
                <label for="variable_description<?php echo $loop; ?>">Q&A Items</label>
            </p>

            <?php
            if ( ! empty( $selected_qna_list ) ) {

                foreach ( $selected_qna_list as $selected_qna_item ) {

                    if ( ! empty( $selected_qna_item ) ) {

                        $qna_item = get_post( $selected_qna_item );

                    }

                    if ( is_null( $qna_item ) ) {
                        echo '<div class="inline notice woocommerce-message">';
                        echo '<p>QNA with ID#' . $qna_item . ' was deleted. Please resave variations.</p>';
                        echo '</div>';
                    } else {

                        echo '<div class="op_qna_block inline notice woocommerce-message" data-select="op_qna_select_' . $loop . '">';
                        echo '<input type="hidden" name="op_qna_list[' . $loop . '][]" value="' . esc_attr( $qna_item->ID ) . '">';
                        echo '<p><a href="' . get_edit_post_link( $qna_item->ID ) . '" target="_blank">' . esc_html( $qna_item->post_title ) . '</a></p>';
                        echo '<a data-qnaid="' . esc_attr( $qna_item->ID ) . '" class="notice-dismiss"></a>';
                        echo '</div>';
                    }
                }
            } else {
                echo '<p>You should choose cards in dropdown.</p>';
            }

            ?>

            <select style="width: 100%;" class="select2" id="op_qna_select_<?php echo $loop; ?>">
                <option></option>
                <?php foreach ( $existed_qna_items as $qna_item ) {
                    $disabled = in_array( $qna_item, $selected_qna_list ) ? ' disabled' : '';
                    echo '<option value="' . $qna_item->ID . '" data-url="' . get_edit_post_link( $qna_item->ID ) . '" ' . $disabled . '>' . esc_html( $qna_item->post_title ) . '</option>';
                } ?>
            </select>
            <script>
                (function ($) {
                    $("#op_qna_select_<?php echo $loop; ?>").select2();

                    $(document).on("click", "#op_qna_select_container_<?php echo $loop; ?> .notice-dismiss", function (e) {
                        e.preventDefault()
                        let $closer = $(this)
                        // enable option
                        console.log($closer.data("qnaid"));
                        $("#op_qna_select_<?php echo $loop; ?> option[value=" + $closer.data("qnaid") + "]").attr("disabled", false)
                        $closer.parents(".op_qna_block").remove();
                    })

                    $("#op_qna_select_<?php echo $loop; ?>").on('change', function (e) {
                        let $select = $(this)
                        for (opt of e.target.children) {
                            if (opt.selected && !opt.disabled) {

                                // add card with QNA Item info
                                $select.before(`<div class="op_qna_block inline notice woocommerce-message">
                                  <input type="hidden" name="op_qna_list[<?php echo $loop; ?>][]" value="${opt.value}">
                                  <p><a href="${opt.dataset.url}" target="_blank" >${opt.innerHTML}</a></p>
                                  <a href="#" data-qnaid="${opt.value}" class="notice-dismiss"></a>
                                </div>`);

                                opt.disabled = true // disable option
                                break;
                            }
                        }
                    })
                })(jQuery)
            </script>
        </div>
        <?php

    }

    function save_variation_fields( $post_id ) {

        if ( ! empty( $_POST['variable_post_id'] ) ) {


            $key = array_search( $post_id, $_POST['variable_post_id'] );


            $new_variation_data['ID'] = $post_id;
            $need_update              = false;

            if ( ! empty( $_POST['op_zones'][ $key ] ) ) {

                delete_post_meta( $new_variation_data['ID'], 'op_zones' );

                foreach ( $_POST['op_zones'][ $key ] as $qna_key => $diet_slug ) {

                    add_post_meta( $new_variation_data['ID'], 'op_zones', $diet_slug );

                }

            }


            if ( ! empty( $_POST['op_diet'][ $key ] ) ) {

                delete_post_meta( $new_variation_data['ID'], 'op_diet' );

                foreach ( $_POST['op_diet'][ $key ] as $qna_key => $diet_slug ) {

                    add_post_meta( $new_variation_data['ID'], 'op_diet', $diet_slug );

                }

            }


            if ( ! empty( $_POST['op_post_title'][ $key ] ) ) {

                update_post_meta( $new_variation_data['ID'], 'op_post_title', $_POST['op_post_title'][ $key ] );

            }

            if ( ! empty( $_POST['op_qna_list'][ $key ] ) ) {

                delete_post_meta( $new_variation_data['ID'], 'op_qna_list' );

                foreach ( $_POST['op_qna_list'][ $key ] as $qna_key => $qna_id ) {

                    add_post_meta( $new_variation_data['ID'], 'op_qna_list', $qna_id );

                }

            }

            if ( ! empty( $_POST['op_pr_instr_list'][ $key ] ) ) {

                delete_post_meta( $new_variation_data['ID'], 'op_pr_instr_list' );

                foreach ( $_POST['op_pr_instr_list'][ $key ] as $pr_instr_key => $pr_instr_id ) {

                    add_post_meta( $new_variation_data['ID'], 'op_pr_instr_list', $pr_instr_id );

                }

            }

            if ( ! empty( $_POST['op_meal_ingredients'][ $key ] ) ) {

                update_post_meta( $new_variation_data['ID'], 'op_meal_ingredients',
                    $_POST['op_meal_ingredients'][ $key ] );

            } else {

                delete_post_meta( $new_variation_data['ID'], 'op_meal_ingredients' );

            }

            if ( ! empty( $_POST['op_post_slug'][ $key ] ) ) {

                $new_variation_data['post_name'] = $_POST['op_post_slug'][ $key ];
                $need_update                     = true;

            } else {

                if ( ! empty( $_POST['variable_sku'][ $key ] ) ) {

                    $new_variation_data['post_name'] = $_POST['variable_sku'][ $key ];
                    $need_update                     = true;

                } else {

                    if ( isset( $_POST['op_post_slug'][ $key ] ) && $post_id != $_POST['op_post_slug'][ $key ] ) {

                        $new_variation_data['post_name'] = $post_id;
                        $need_update                     = true;

                    }

                }
            }

            if ( ! empty( $_POST['op_calories'][ $key ] ) ) {

                update_post_meta( $new_variation_data['ID'], 'op_calories', $_POST['op_calories'][ $key ] );

            } else {

                delete_post_meta( $new_variation_data['ID'], 'op_calories' );

            }

            if ( ! empty( $_POST['op_fats'][ $key ] ) ) {

                update_post_meta( $new_variation_data['ID'], 'op_fats', $_POST['op_fats'][ $key ] );

            } else {

                delete_post_meta( $new_variation_data['ID'], 'op_fats' );

            }

            if ( ! empty( $_POST['op_proteins'][ $key ] ) ) {

                update_post_meta( $new_variation_data['ID'], 'op_proteins', $_POST['op_proteins'][ $key ] );

            } else {

                delete_post_meta( $new_variation_data['ID'], 'op_proteins' );

            }

            if ( ! empty( $_POST['op_carbohydrates'][ $key ] ) ) {

                update_post_meta( $new_variation_data['ID'], 'op_carbohydrates',
                    $_POST['op_carbohydrates'][ $key ] );

            } else {

                delete_post_meta( $new_variation_data['ID'], 'op_carbohydrates' );

            }


            if ( ! empty( $_POST['op_preparing_time'][ $key ] ) ) {

                update_post_meta( $new_variation_data['ID'], 'op_preparing_time',
                    $_POST['op_preparing_time'][ $key ] );

            } else {
                delete_post_meta( $new_variation_data['ID'], 'op_preparing_time' );
            }

            if ( ! empty( $_POST['op_store_type'][ $key ] ) ) {

                update_post_meta( $new_variation_data['ID'], 'op_store_type',
                    $_POST['op_store_type'][ $key ] );

            } else {
                delete_post_meta( $new_variation_data['ID'], 'op_store_type' );
            }

            if ( ! empty( $_POST['product_image_gallery'] ) ) {
                foreach ( $_POST['product_image_gallery'] as $key => $gallery ) {
                    if ( ! empty( $gallery ) ) {
                        update_post_meta( $key, 'op_variation_image_gallery', $gallery );
                    } else {
                        delete_post_meta( $key, 'op_variation_image_gallery' );
                    }
                }
            }

            if ( ! empty( $_POST['product_nutrition_image'] ) ) {

                update_post_meta( $new_variation_data['ID'], 'op_variation_nutrition_image',
                    $_POST['product_nutrition_image'][ $key ] );

            } else {
                delete_post_meta( $new_variation_data['ID'], 'op_variation_nutrition_image' );
            }

            if ( ! empty( $_POST['op_order_by'] ) ) {
                update_post_meta( $new_variation_data['ID'], 'op_order_by', $_POST['op_order_by'][0] );
            } else {
                delete_post_meta( $new_variation_data['ID'], 'op_order_by' );
            }

            if ( $need_update ) {

                wp_update_post( wp_slash( $new_variation_data ) );

            }

        }

    }

    function register_pr_instr() {

        register_post_type( 'op_pr_instr', [
            'label'               => null,
            'labels'              => [
                'name'               => 'Prepare Instruction',
                // основное название для типа записи
                'singular_name'      => 'Prepare Instruction Item',
                // название для одной записи этого типа
                'add_new'            => 'Add Prepare Instruction',
                // для добавления новой записи
                'add_new_item'       => 'Adding Prepare Instruction',
                // заголовка у вновь создаваемой записи в админ-панели.
                'edit_item'          => 'Edit Prepare Instruction',
                // для редактирования типа записи
                'new_item'           => 'New Prepare Instruction',
                // текст новой записи
                'view_item'          => 'View Prepare Instruction',
                // для просмотра записи этого типа.
                'search_items'       => 'Find Prepare Instruction',
                // для поиска по этим типам записи
                'not_found'          => 'Not found',
                // если в результате поиска ничего не было найдено
                'not_found_in_trash' => 'Not found in trash',
                // если не было найдено в корзине
                'parent_item_colon'  => '',
                // для родителей (у древовидных типов)
                'menu_name'          => 'Prepare Instruction',
                // название меню
            ],
            'description'         => '',
            'public'              => false,
            'exclude_from_search' => true,
            // зависит от public
            'show_ui'             => true,
            // зависит от public
            'show_in_menu'        => true,
            // показывать ли в меню адмнки
            'show_in_rest'        => null,
            // добавить в REST API. C WP 4.7
            'rest_base'           => null,
            // $post_type. C WP 4.7
            'menu_icon'           => 'dashicons-media-text',
            //'capability_type'   => 'post',
            //'capabilities'      => 'post', // массив дополнительных прав для этого типа записи
            //'map_meta_cap'      => null, // Ставим true чтобы включить дефолтный обработчик специальных прав
            'hierarchical'        => false,
            'supports'            => [ 'title', 'editor' ],
            // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
            'has_archive'         => false,
            'rewrite'             => true,
            'query_var'           => true,
        ] );
    }

    function register_qna() {

        register_post_type( 'op_qna', [
            'label'               => null,
            'labels'              => [
                'name'               => 'Q&A', // основное название для типа записи
                'singular_name'      => 'Q&A Item', // название для одной записи этого типа
                'add_new'            => 'Add Q&A', // для добавления новой записи
                'add_new_item'       => 'Adding Q&A', // заголовка у вновь создаваемой записи в админ-панели.
                'edit_item'          => 'Edit Q&A', // для редактирования типа записи
                'new_item'           => 'New Q&A', // текст новой записи
                'view_item'          => 'View Q&A', // для просмотра записи этого типа.
                'search_items'       => 'Find Q&A', // для поиска по этим типам записи
                'not_found'          => 'Not found', // если в результате поиска ничего не было найдено
                'not_found_in_trash' => 'Not found in trash', // если не было найдено в корзине
                'parent_item_colon'  => '', // для родителей (у древовидных типов)
                'menu_name'          => 'Q&A', // название меню
            ],
            'description'         => '',
            'public'              => false,
            'exclude_from_search' => true,
            // зависит от public
            'show_ui'             => true,
            // зависит от public
            'show_in_menu'        => true,
            // показывать ли в меню адмнки
            'show_in_rest'        => null,
            // добавить в REST API. C WP 4.7
            'rest_base'           => null,
            // $post_type. C WP 4.7
            'menu_icon'           => 'dashicons-format-chat',
            //'capability_type'   => 'post',
            //'capabilities'      => 'post', // массив дополнительных прав для этого типа записи
            //'map_meta_cap'      => null, // Ставим true чтобы включить дефолтный обработчик специальных прав
            'hierarchical'        => false,
            'supports'            => [ 'title', 'editor' ],
            // 'title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments','revisions','page-attributes','post-formats'
            'has_archive'         => false,
            'rewrite'             => true,
            'query_var'           => true,
        ] );
    }

    /**
     * @param mixed $variation_id
     *
     * @return array
     */
    function get_variation_attributes( $variation_id, $variation_data = [] ) {

        if ( ! empty( $variation_data ) ) {
            $attributes = array_filter( $variation_data, function ( $attr_key ) {
                return strpos( '', $attr_key ) !== false;
            }, ARRAY_FILTER_USE_KEY );
        }


        if ( empty( $attributes ) ) {
            $variation  = wc_get_product( $variation_id );
            $attributes = $variation->get_variation_attributes();
        }

        $tax_names = [];
        $tax_slugs = [];

        foreach ( $attributes as $attr_tax_name => $tax_slug ) {
            $tax_names[] = str_replace( 'attribute_', '', $attr_tax_name );
            $tax_slugs[] = $tax_slug;
        }

        $variation_attributes = [];
        for ( $i = 0; $i < count( $tax_names ); $i ++ ) {
            $variation_attributes[] = get_terms( array(
                'taxonomy'   => [ $tax_names[ $i ] ],
                'hide_empty' => false,
                'fields'     => 'all',
                'slug'       => [ $tax_slugs[ $i ] ],
            ) )[0];
        }

        return $variation_attributes;
    }

    /**
     * Берет данные из компонента и суммирует между собой
     * @return array
     */
    function gen_nutrition_information( $variation_id, $variation_data = [] ) {

        $needed_data     = [
            'calories'      => 'calories',
            'fats'          => 'fats',
            'proteins'      => 'proteins',
            'carbohydrates' => 'carbohydrates',
        ];
        $final_nutrition = array_map( function ( $info_slug ) use ( $variation_data ) {
            return empty( $variation_data[ 'op_' . $info_slug ] ) ? 0 : floatval( $variation_data[ 'op_' . $info_slug ] );
        }, $needed_data );

        $variation_attributes = $this->get_variation_attributes( $variation_id, $variation_data );
        foreach ( $final_nutrition as $n_slug => $n_value ) {
            if ( empty( $n_value ) ) {
                foreach ( $variation_attributes as $attr_obj ) {

                    $final_nutrition[ $n_slug ] += floatval( carbon_get_term_meta( $attr_obj->term_id,
                        'op_variations_component_' . $n_slug ) );

                }
            }
        }

        return $final_nutrition;

    }

    function add_settings_subpage( $main_page ) {
        $allergens_labels = array(
            'plural_name'   => 'Allergens',
            'singular_name' => 'Allergen',
        );

        $badges_labels = array(
            'plural_name'   => 'Badges',
            'singular_name' => 'Badge',
        );

        $attribute_taxonomies = wc_get_attribute_taxonomies();

        if ( $attribute_taxonomies ) {

            $components_fields = Field::make( 'complex', 'components', __( 'Components effect' ) )
                                      ->set_layout( 'tabbed-horizontal' )
                                      ->add_fields( [
                                          Field::make( 'multiselect', 'items', __( 'Choose component or group' ) )
                                               ->add_options( op_help()->survey->get_components() ),
                                          Field::make( 'radio', 'mode', __( 'Effect' ) )
                                               ->set_options( array(
                                                   'exclude' => 'Exclude',
                                                   'include' => 'Include',
                                               ) ),
                                      ] );
        } else {
            $components_fields = Field::make( 'html', 'components' )
                                      ->set_html( '<h3>You have to create attributes first</h3>' );
        }
        Container::make( 'theme_options', 'Catalog Filters' )
                 ->set_page_parent( $main_page ) // reference to a top level container
                 ->add_fields( [
                Field::make( 'complex', 'sf_catalog_filters', 'Allergens' )
                     ->set_layout( 'tabbed-vertical' )
                     ->add_fields( 'diets', array(
                         Field::make( 'text', 'title', __( 'Title' ) )->set_default_value( "Diets" ),
                     ) )
                     ->set_header_template( '
            <% if (title) { %>
              <%- title %>
            <% } %> (diets)
          ' )
                     ->add_fields( 'allergies', array(
                         Field::make( 'text', 'title', __( 'Title' ) )->set_default_value( "Allergies" ),
                     ) )
                     ->set_header_template( '
            <% if (title) { %>
              <%- title %>
            <% } %> (allergies)
          ' )
                     ->add_fields( 'custom', array(
                         Field::make( 'text', 'title', __( 'Title' ) ),
                         Field::make( 'text', 'slug', __( 'Unique slug' ) )->set_required( true ),
                         Field::make( 'complex', 'variants', 'Variants' )
                              ->set_layout( 'tabbed-vertical' )
                              ->add_fields( array(
                                  Field::make( 'image', 'icon', __( 'Icon if contains' ) ),
                                  Field::make( 'text', 'title', __( 'Title' ) ),
                                  Field::make( 'text', 'slug', __( 'Slug' ) )->set_required( true ),
                                  $components_fields
                              ) )
                              ->set_header_template( '
                      <%- (title) ? title : "Not set" %>
                      <%- (icon) ? " (with icon)" : " (text)" %>
                    ' )
                     ) )
                     ->set_header_template( '
            <% if (title) { %>
              <%- title %> <%- slug ? " (" + slug + ")" : " (You must type slug)" %>
            <% } %>
          ' )
            ] );

        Container::make( 'theme_options', 'Variations Addon' )
                 ->set_page_parent( $main_page ) // reference to a top level container

                 ->add_tab( __( 'Shop Settings' ), array(
                Field::make( 'association', 'op_shop_variationcat', __( 'Shop category for Meals' ) )
                     ->set_types( array(
                         array(
                             'type'     => 'term',
                             'taxonomy' => 'product_cat',
                         )
                     ) )
            ) )
                 ->add_tab( __( 'Meals panel Settings' ), array(
                     Field::make( 'association', 'op_maels_panelcat', __( 'Shop category for Meals Panel' ) )
                          ->set_types( array(
                              array(
                                  'type'     => 'term',
                                  'taxonomy' => 'product_cat',
                              )
                          ) )
                 ) )
                 ->add_tab( __( 'View Settings' ), array(
                     Field::make( 'radio', 'op_variations_components-design', 'How to show components' )
                          ->set_default_value( 'thumbnails' )
                          ->add_options( array(
                              'dropdown'    => 'As dropdown',
                              'thumbnails'  => 'As thumnails',
                              'radiobutton' => 'As radio buttons',
                          ) )
                 ) )
                 ->add_tab( __( 'List of allergens' ), array(
                     Field::make( 'complex', 'op_variations_allergens', 'Allergens' )
                          ->set_layout( 'tabbed-vertical' )
                          ->setup_labels( $allergens_labels )
                          ->add_fields( array(
                              Field::make( 'text', 'title', __( 'Title' ) ),
                              Field::make( 'text', 'slug', __( 'Unique slug' ) )->set_required( true ),
                              Field::make( 'image', 'icon_contains', __( 'Icon if contains' ) ),
                              Field::make( 'image', 'icon_none', __( 'Icon if none' ) ),
                          ) )
                          ->set_header_template( '
            <% if (title) { %>
              <%- title %> <%- slug ? " (" + slug + ")" : " (You must type slug)" %>
            <% } %>
          ' )
                 ) )
                 ->add_tab( __( 'List of badges' ), array(
                     Field::make( 'complex', 'op_variations_badges', 'Badges' )
                          ->set_layout( 'tabbed-vertical' )
                          ->setup_labels( $badges_labels )
                          ->add_fields( array(
                              Field::make( 'text', 'title', __( 'Title' ) ),
                              Field::make( 'text', 'slug', __( 'Unique slug' ) )->set_required( true ),
                              Field::make( 'image', 'icon_contains', __( 'Icon if contains' ) ),
                              Field::make( 'image', 'icon_none', __( 'Icon if none' ) ),
                          ) )
                          ->set_header_template( '
            <% if (title) { %>
              <%- title %> <%- slug ? " (" + slug + ")" : " (You must type slug)" %>
            <% } %>
          ' )
                 ) )
                 ->add_tab( __( 'List of ingredients' ), array(
                     Field::make( 'complex', 'op_variations_ingredients', 'Ingredients' )
                          ->set_layout( 'tabbed-vertical' )
                          ->setup_labels( $allergens_labels )
                          ->add_fields( array(
                              Field::make( 'text', 'title', __( 'Title' ) ),
                              Field::make( 'text', 'slug', __( 'Unique slug' ) )->set_required( true ),
                          ) )
                          ->set_header_template( '
            <% if (title) { %>
              <%- title %> <%- slug ? " (" + slug + ")" : " (You must type slug)" %>
            <% } %>
          ' )
                 ) );
    }

    function add_gallery_to_variations( $loop, $variation_data, $variation ) {
        $variation_id   = absint( $variation->ID );
        $gallery_images = get_post_meta( $variation_id, 'op_variation_image_gallery', true );
        ?>
        <div class="form-row form-row-full">
            <div id="op-variation-gallery-image-container_<?php echo $loop; ?>">
                <ul class="op-variation-gallery-images">
                    <?php
                    if ( ! empty( $gallery_images ) ) :
                        $current_images = array_filter( explode( ',', $gallery_images ) ); ?>
                        <?php foreach ( $current_images as $image ):
                        $image_data = wp_get_attachment_image_src( $image );
                        ?>
                        <li class="image" data-attachment_id="<?php echo $image; ?>">
                            <img src="<?php echo $image_data[0]; ?>" width="150"/>
                            <ul class="actions">
                                <li>
                                    <a href="#"
                                       class="delete tips"
                                       data-tip="<?php esc_attr_e( 'Delete image', 'woocommerce' ); ?>">
                                        <?php esc_html_e( 'Delete', 'woocommerce' ); ?></a>
                                </li>
                            </ul>
                        </li>
                    <?php endforeach; ?>

                    <?php endif; ?>
                </ul>

                <input type="hidden"
                       class="op-var-product_image_gallery_<?php echo $loop; ?>"
                       name="product_image_gallery[<?php echo $variation_id; ?>]"
                       value="<?php echo ( ! empty ( $gallery_images ) ) ? $gallery_images : ''; ?>">
            </div>
            <p class="hide-if-no-js">
                <a href="#"
                   id="add-op-variation-gallery-image_<?php echo $loop; ?>"
                   data-product_variation_loop="<?php echo absint( $loop ) ?>"
                   data-product_variation_id="<?php echo esc_attr( $variation_id ) ?>"
                   class="button"
                   data-choose="<?php esc_attr_e( 'Add images to product gallery', 'woocommerce' ); ?>"
                   data-update="<?php esc_attr_e( 'Add to gallery', 'woocommerce' ); ?>"
                   data-delete="<?php esc_attr_e( 'Delete image', 'woocommerce' ); ?>"
                   data-text="<?php esc_attr_e( 'Delete', 'woocommerce' ); ?>">
                    <?php esc_html_e( 'Add product gallery images', 'woocommerce' ); ?></a>
            </p>
        </div>

        <script>
            (function ($) {
                var imageContainer = '#op-variation-gallery-image-container_<?php echo $loop; ?>';

                var product_gallery_frame;
                var $image_gallery_ids = $('.op-var-product_image_gallery_<?php echo $loop; ?>');
                var $product_images = $(imageContainer).find('ul.op-variation-gallery-images');

                $('#add-op-variation-gallery-image_<?php echo $loop; ?>').on('click', function (event) {
                    var $el = $(this);
                    event.preventDefault();

                    // If the media frame already exists, reopen it.
                    if (product_gallery_frame) {
                        product_gallery_frame.open();
                        return;
                    }

                    // Create the media frame.
                    product_gallery_frame = wp.media.frames.product_gallery = wp.media({
                        // Set the title of the modal.
                        title: $el.data('choose'),
                        button: {
                            text: $el.data('update')
                        },
                        states: [
                            new wp.media.controller.Library({
                                title: $el.data('choose'),
                                filterable: 'all',
                                multiple: true
                            })
                        ]
                    });

                    // When an image is selected, run a callback.
                    product_gallery_frame.on('select', function () {
                        var selection = product_gallery_frame.state().get('selection');
                        var attachment_ids = $image_gallery_ids.val();

                        selection.map(function (attachment) {
                            attachment = attachment.toJSON();

                            if (attachment.id) {
                                attachment_ids = attachment_ids ? attachment_ids + ',' + attachment.id : attachment.id;
                                var attachment_image = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

                                $product_images.append(
                                    '<li class="image" data-attachment_id="' + attachment.id + '"><img src="' + attachment_image +
                                    '" width="150" /><ul class="actions"><li><a href="#" class="delete" title="' + $el.data('delete') + '">' +
                                    $el.data('text') + '</a></li></ul></li>'
                                );
                            }
                        });

                        $image_gallery_ids.val(attachment_ids);
                        $image_gallery_ids.trigger('change');
                    });

                    // Finally, open the modal.
                    product_gallery_frame.open();
                });

                // Image ordering.
                $product_images.sortable({
                    items: 'li.image',
                    cursor: 'move',
                    scrollSensitivity: 40,
                    forcePlaceholderSize: true,
                    forceHelperSize: false,
                    helper: 'clone',
                    opacity: 0.65,
                    placeholder: 'wc-metabox-sortable-placeholder',
                    start: function (event, ui) {
                        ui.item.css('background-color', '#f6f6f6');
                    },
                    stop: function (event, ui) {
                        ui.item.removeAttr('style');
                    },
                    update: function () {
                        var attachment_ids = '';

                        $(imageContainer).find('ul li.image').css('cursor', 'default').each(function () {
                            var attachment_id = $(this).attr('data-attachment_id');
                            attachment_ids = attachment_ids + attachment_id + ',';
                            $("input").trigger("change")
                        });

                        $image_gallery_ids.val(attachment_ids);
                        $image_gallery_ids.trigger('change');
                    }
                });

                // Remove images.
                $(imageContainer).on('click', 'a.delete', function () {
                    $(this).closest('li.image').remove();

                    var attachment_ids = '';

                    $(imageContainer).find('ul li.image').css('cursor', 'default').each(function () {
                        var attachment_id = $(this).attr('data-attachment_id');
                        attachment_ids = attachment_ids + attachment_id + ',';
                    });

                    $image_gallery_ids.val(attachment_ids);
                    $image_gallery_ids.trigger('change');

                    // Remove any lingering tooltips.
                    $('#tiptip_holder').removeAttr('style');
                    $('#tiptip_arrow').removeAttr('style');

                    return false;
                });
            })(jQuery);
        </script>

        <?php
    }

    function add_nutrition_image_to_variations( $loop, $variation_data, $variation ) {
        $variation_id = absint( $variation->ID );
        $image        = get_post_meta( $variation_id, 'op_variation_nutrition_image', true );
        ?>
        <h4>Nutrition image</h4>
        <div class="form-row form-row-full">
            <div id="op-variation-nutrition-image-container_<?php echo $loop; ?>">

                <?php $image_data = wp_get_attachment_image_src( $image ); ?>
                <div class="nutritional-image">
                    <img src="<?php echo ( empty( $image_data[0] ) ) ? wc_placeholder_img_src() : $image_data[0]; ?>"
                         width="150"/>
                </div>

                <input type="hidden"
                       class="op-var-product_nutrition_image_<?php echo $loop; ?>"
                       name="product_nutrition_image[<?php echo $variation_id; ?>]"
                       value="<?php echo ( ! empty ( $image ) ) ? $image : ''; ?>">
            </div>
            <p class="hide-if-no-js">
                <a href="#"
                   id="add-op-variation-nutrition-image_<?php echo $loop; ?>"
                   data-product_variation_loop="<?php echo absint( $loop ) ?>"
                   data-product_variation_id="<?php echo esc_attr( $variation_id ) ?>"
                   class="button"
                   data-choose="<?php esc_attr_e( 'Add nutrition image', 'woocommerce' ); ?>"
                   data-update="<?php esc_attr_e( 'Add image', 'woocommerce' ); ?>"
                   data-delete="<?php esc_attr_e( 'Delete image', 'woocommerce' ); ?>"
                   data-text="<?php esc_attr_e( 'Delete', 'woocommerce' ); ?>">
                    <?php esc_html_e( 'Add nutrition image', 'woocommerce' ); ?></a>
            </p>
        </div>

        <script>
            (function ($) {
                var imageContainer = '#op-variation-nutrition-image-container_<?php echo $loop; ?>';

                var product_gallery_frame;
                var $image_gallery_ids = $('.op-var-product_nutrition_image_<?php echo $loop; ?>');
                //var $product_images = $(imageContainer).find('ul.op-nutrition-variation-image');
                var $product_images = $(imageContainer).find('.nutritional-image');

                $('#add-op-variation-nutrition-image_<?php echo $loop; ?>').on('click', function (event) {
                    var $el = $(this);
                    event.preventDefault();

                    // If the media frame already exists, reopen it.
                    if (product_gallery_frame) {
                        product_gallery_frame.open();
                        return;
                    }

                    // Create the media frame.
                    product_gallery_frame = wp.media.frames.product_gallery = wp.media({
                        // Set the title of the modal.
                        title: $el.data('choose'),
                        button: {
                            text: $el.data('update')
                        },
                        states: [
                            new wp.media.controller.Library({
                                title: $el.data('choose'),
                                filterable: 'all',
                                multiple: true
                            })
                        ]
                    });

                    // When an image is selected, run a callback.
                    product_gallery_frame.on('select', function () {
                        var selection = product_gallery_frame.state().get('selection');
                        var attachment_ids = $image_gallery_ids.val();

                        selection.map(function (attachment) {
                            attachment = attachment.toJSON();

                            if (attachment.id) {
                                // attachment_ids = attachment_ids ? attachment_ids + ',' + attachment.id : attachment.id;
                                attachment_ids = attachment.id;
                                var attachment_image = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

                                $im = $product_images.find('img');
                                $im[0].src = attachment_image;
                                $product_images.data('attachment_id', attachment.id);
                                $product_images.append('<a href="#" class="delete" title="' + $el.data('delete') + '">' + $el.data('text') + '</a>');

                                // $product_images.append(
                                // 	'<li class="image" data-attachment_id="' + attachment.id + '"><img src="' + attachment_image +
                                // 	'" /><ul class="actions"><li><a href="#" class="delete" title="' + $el.data('delete') + '">' +
                                //	$el.data('text') + '</a></li></ul></li>'
                                // );
                            }
                        });

                        $image_gallery_ids.val(attachment_ids);
                        $image_gallery_ids.trigger('change');
                    });

                    // Finally, open the modal.
                    product_gallery_frame.open();
                });

                // Image ordering.
                $product_images.sortable({
                    items: 'li.image',
                    cursor: 'move',
                    scrollSensitivity: 40,
                    forcePlaceholderSize: true,
                    forceHelperSize: false,
                    helper: 'clone',
                    opacity: 0.65,
                    placeholder: 'wc-metabox-sortable-placeholder',
                    start: function (event, ui) {
                        ui.item.css('background-color', '#f6f6f6');
                    },
                    stop: function (event, ui) {
                        ui.item.removeAttr('style');
                    },
                    update: function () {
                        var attachment_ids = '';

                        $(imageContainer).find('ul li.image').css('cursor', 'default').each(function () {
                            var attachment_id = $(this).attr('data-attachment_id');
                            attachment_ids = attachment_ids + attachment_id + ',';
                            $("input").trigger("change")
                        });

                        $image_gallery_ids.val(attachment_ids);
                        $image_gallery_ids.trigger('change');
                    }
                });

                // Remove images.
                $(imageContainer).on('click', 'a.delete', function () {
                    $(this).remove();

                    var attachment_ids = '';
                    var image = $(imageContainer).find('.nutritional-image img');
                    image[0].src = '<?php echo wc_placeholder_img_src(); ?>';
                    //$('.nutritional-image img').src('<?php //echo wc_placeholder_img_src(); ?>//');

                    // $(imageContainer).find('.nutritional-image').css('cursor', 'default').each(function () {
                    // 	var attachment_id = $(this).attr('data-attachment_id');
                    // 	attachment_ids = attachment_ids + attachment_id + ',';
                    // });

                    $image_gallery_ids.val(attachment_ids);
                    $image_gallery_ids.trigger('change');

                    // Remove any lingering tooltips.
                    $('#tiptip_holder').removeAttr('style');
                    $('#tiptip_arrow').removeAttr('style');

                    return false;
                });
            })(jQuery);
        </script>

        <?php
    }
}