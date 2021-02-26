<?php
/**
 * WooCommerce shop class.
 *
 * @class   SFSortCache
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * SolutionFactoryShop class.
 */
class SolutionFactoryShop {
    private static $_instance = null;

    /**
     * default meals category id
     * @var int
     */
    public $meals_category = 15;

    static public function getInstance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function init() {
        add_action( 'wp_ajax_sf_cart_change_delivery_date', [ $this, 'cart_change_delivery_date' ] );
        add_action( 'wp_ajax_nopriv_sf_cart_change_delivery_date', [ $this, 'cart_change_delivery_date' ] );

        add_action( 'wp_ajax_sf_cart_change_frequency', [ $this, 'cart_change_frequency' ] );
        add_action( 'wp_ajax_nopriv_sf_cart_change_frequency', [ $this, 'cart_change_frequency' ] );

        add_action( 'wp_ajax_sf_cart_remove_category', [ $this, 'remove_cart_category' ] );
        add_action( 'wp_ajax_nopriv_sf_cart_remove_category', [ $this, 'remove_cart_category' ] );

        add_action( 'wp_ajax_save_order_customer_note', [ $this, 'save_order_customer_note' ] );
        add_action( 'wp_ajax_nopriv_save_order_customer_note', [ $this, 'save_order_customer_note' ] );

        add_action( 'wp_ajax_filter_customize_block', [ $this, 'filter_customize_block' ] );
        add_action( 'wp_ajax_nopriv_filter_customize_block', [ $this, 'filter_customize_block' ] );

        // Play/Pause Order (subscription)
        add_action( 'wp_ajax_sf_manage_order', [ $this, 'play_pause_order' ] );

        add_filter( 'woocommerce_checkout_fields', [ $this, 'rewrite_checkout_fields' ], 10, 1 );

        add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'header_add_to_cart_fragment' ] );

        add_action( 'woocommerce_after_checkout_form', [ $this, 'set_custom_checkout_field_postcode' ], 10, 1 );

        // Output the field value on the order editing page
        add_action( 'woocommerce_admin_order_data_after_billing_address',
            [ $this, 'display_admin_order_meta_after_billing' ], 10, 1 );
        add_action( 'woocommerce_admin_order_data_after_shipping_address',
            [ $this, 'display_admin_order_meta_after_shipping' ], 10, 1 );

        add_action( 'init', [ $this, 'manage_order_statuses' ] );
        add_filter( 'wc_order_statuses', [ $this, 'add_order_statuses' ], 10, 1 );


        add_filter( 'loop_shop_per_page', [ $this, 'change_count_products_on_page' ], 30 );

        remove_action( 'woocommerce_before_checkout_form_cart_notices', 'woocommerce_output_all_notices' );

        add_action( 'make_survey_default', function () {
            if ( intval( get_user_meta( get_current_user_id(), 'survey_default', true ) ) ) {
                add_filter( 'wp_nav_menu_objects', [ $this, 'survey_menu_items_rewrite' ], 10, 2 );
                add_action( 'post_type_link', [ $this, 'survey_products_items_rewrite' ], 30, 4 );
            }
        } );

        add_filter( 'kama_breadcrumbs_filter_elements', [ $this, 'sf_change_breadcrumbs_elements' ], 10, 3 );

        add_filter( 'woocommerce_sale_flash', [ $this, 'sf_hide_sale_flash' ] );

        // Create payment token
        add_action( 'wp_ajax_create_user_pay_token', [ $this, 'op_create_user_pay_token' ] );
        add_action( 'wp_ajax_nopriv_create_user_pay_token', [ $this, 'op_create_user_pay_token' ] );

        add_action( 'wp_ajax_load_catalog', [ $this, 'load_catalog_pagination' ] );
        add_action( 'wp_ajax_nopriv_load_catalog', [ $this, 'load_catalog_pagination' ] );

        add_action( 'wp_ajax_get_cart_data', [ $this, 'get_cart_data' ] );
        add_action( 'wp_ajax_nopriv_get_cart_data', [ $this, 'get_cart_data' ] );

        add_action( 'wp_ajax_add_frequency_to_item', [ $this, 'add_frequency_to_item' ] );
        add_action( 'wp_ajax_nopriv_add_frequency_to_item', [ $this, 'add_frequency_to_item' ] );

//        add_action( 'init', [ $this, 'check_thank_you_page_access' ] );
        $this->check_thank_you_page_access();

        // Save updated subscription address
        add_action( 'wp_ajax_zipcode_update_delivery', [ $this, 'saveModalAddress' ] );
        add_action( 'wp_ajax_nopriv_zipcode_update_delivery', [ $this, 'saveModalAddress' ] );
        add_action( 'wp_ajax_zipcode_update_billing', [ $this, 'saveModalAddress' ] );
        add_action( 'wp_ajax_nopriv_zipcode_update_billing', [ $this, 'saveModalAddress' ] );
    }

    /**
     * Check thank you page access.
     */
    function check_thank_you_page_access() {
        if ( strpos( $_SERVER['REQUEST_URI'], 'order-received' ) !== false ) {
            $user_id = get_current_user_id();

            if ( $user_id and isset( $_GET['key'] ) and $_GET['key'] ) {
                global $wpdb;

                $order = $wpdb->get_var( $wpdb->prepare(
                    "SELECT count(*) FROM {$wpdb->posts} as p LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id WHERE p.post_password = %s AND p.post_status != 'trash' AND pm.meta_key = '_customer_user' AND pm.meta_value = %s",
                    $_GET['key'], $user_id
                ) );

                if ( ! $order ) {
                    wp_safe_redirect( '/cart/' );
                    die;
                }
            } else {
                wp_safe_redirect( '/cart/' );
                die;
            }
        }
    }

    /**
     * Play/Pause Order (subscription) by AJAX.
     */
    function play_pause_order() {
        $type = $_POST['type'];

        if ( $type == 'pause' ) {
            op_help()->subscriptions->set_subscribe_status( 'pause' );
        }
        if ( $type == 'play' ) {
            op_help()->subscriptions->set_subscribe_status( 'play' );
        }
        die;
    }

    /**
     * Output the field value on the order editing page after billing address.
     *
     * @param object $order
     */
    function display_admin_order_meta_after_billing( $order ) {
        echo $this->meta_field_html( $order->id, '_payment_method', 'Payment method' );
        echo $this->meta_field_html( $order->id, '_card_number', 'Card number' );
        echo $this->meta_field_html( $order->id, '_cc_expiry', 'Card expiry data' );
        echo $this->meta_field_html( $order->id, '_authorization_num', 'authorization_num' );
    }

    /**
     * Output the field value on the order editing page after shipping.
     *
     * @param object $order
     */
    function display_admin_order_meta_after_shipping( $order ) {
        echo "<h3>Carrier information</h3>";
        echo $this->meta_field_html( $order->id, '_pp_carrier', 'Carrier' );
        echo $this->meta_field_html( $order->id, '_pp_tracking_number', 'Tracking number' );
        echo $this->meta_field_html( $order->id, '_pp_delivery_cost', 'Cost of delivery' );
        echo $this->meta_field_html( $order->id, '_pp_boxes', 'Boxes' );
        echo $this->meta_field_html( $order->id, '_pp_insolation', 'Insolation' );
        echo $this->meta_field_html( $order->id, '_pp_ice_packs', 'Ice packs' );
    }

    /**
     * Output the separate meta field.
     *
     * @param int $id
     * @param string $field_name
     * @param string $title
     *
     * @return string
     */
    function meta_field_html( $id, $field_name, $title ) {
        $meta = get_post_meta( $id, $field_name, true );
        $meta = ( $meta ) ? $meta : '—';

        return "<p><strong>$title:</strong> {$meta}</p>";
    }

    function remove_cart_category() {

        $woo_cat = get_term( $_POST['cat_id'] );
        // $woo_cat  = get_term( 28 );
        $delete_results = [];
        $new_products   = op_help()->subscriptions->sort_items_by_category( WC()->cart->get_cart(), $woo_cat );
        if ( ! empty( $new_products ) ) {
            foreach ( $new_products as $cart_item_key => $cart_item ) {
                if ( ! WC()->cart->remove_cart_item( $cart_item_key ) ) {
                    $delete_results[ $cart_item_key ] = 'Some problem with deleting "' . get_the_title( $cart_item['data']->get_id() ) . '"';
                }
            }
        }
        if ( empty( $delete_results ) ) {
            wp_send_json_success();
        } else {
            wp_send_json_error( $delete_results );
        }

    }

    function get_session_cart( $key, $default = false ) {
        if ( ! empty( $default ) ) {
            if ( empty( $_SESSION[ 'sf_cart_' . $key ] ) ) {
                return $default;
            }
        }

        return $_SESSION[ 'sf_cart_' . $key ];
    }

    function set_session_cart( $key, $value = false ) {
        $_SESSION[ 'sf_cart_' . $key ] = $value;
    }

    function cart_change_frequency() {
        $this->set_session_cart( $_POST['key'], $_POST['frequency'] );

        $order = op_help()->subscriptions->get_current_subscription();
        if ( is_object( $order ) and method_exists( $order, 'get_id' ) and $order->get_id() ) {
            op_help()->subscriptions->modify_cart_order_to_subscription( $order, '', false );
            $order->save();
        }
        wp_send_json_success();
    }

    function cart_change_delivery_date() {

        // TODO safe checks
        $this->set_session_cart( 'delivery_date', $_POST['delivery_date'] );
        wp_send_json_success( $this->get_session_cart( 'delivery_date' ) );

    }

    function rewrite_checkout_fields( $fields ) {

        $fields['billing']['billing_first_name']['class'] = [ "fields-list__item--half" ];
        $fields['billing']['billing_last_name']['class']  = [ "fields-list__item--half" ];
        $fields['billing']['billing_country']['class']    = [ "checkout-country-field" ];

        unset( $fields['billing']['billing_company'] );

        $fields['billing']['billing_last_name']['priority'] = 11;
        $fields['billing']['billing_address_1']['priority'] = 15;
        $fields['billing']['billing_address_2']['priority'] = 20;

        $fields['billing']['billing_postcode']['class']             = [ "fields-list__item--third" ];
        $fields['billing']['billing_city']['class']                 = [ "fields-list__item--two-thirds" ];
        $fields['billing']['billing_city']['label']                 = 'City';
        $fields['billing']['billing_postcode']['custom_attributes'] = array( 'readonly' => 'readonly' );
        $fields['billing']['billing_address_1']['label']            = 'Address Line 1';
        $fields['billing']['billing_address_2']['label']            = 'Address Line 2';

        $fields['billing']['billing_postcode']['priority'] = 65;
        $fields['billing']['billing_state']['priority']    = 67;
        $fields['billing']['billing_city']['priority']     = 66;

        $fields['billing']['billing_address_1']['placeholder'] = '';
        $fields['billing']['billing_address_2']['placeholder'] = '';


        $fields['billing']['billing_address_2']['class'] = [ "optional-bottom-label" ];
        // $fields['billing']['billing_company']['class'] = [ "optional-bottom-label" ];

        // Disable delivery fields
        // $fields['billing']['billing_city']['custom_attributes'] = array( 'readonly' => 'readonly' );


        // Shipping form
        // $fields['shipping']['shipping_first_name']['class'] = [ "checkout-form-hidden-field" ];
        // $fields['shipping']['shipping_last_name']['class']  = [ "checkout-form-hidden-field" ];
        $fields['shipping']['shipping_first_name']['class'] = [ "fields-list__item--half" ];
        $fields['shipping']['shipping_last_name']['class']  = [ "fields-list__item--half" ];

        $fields['shipping']['shipping_city']['class']     = [ "fields-list__item--two-thirds" ];
        $fields['shipping']['shipping_postcode']['class'] = [ "fields-list__item--third" ];

        $fields['shipping']['shipping_state']['priority']    = 91;
        $fields['shipping']['shipping_postcode']['priority'] = 80;
        $fields['shipping']['shipping_city']['priority']     = 90;

        $fields['shipping']['shipping_country']['label']   = 'Country';
        $fields['shipping']['shipping_address_1']['label'] = 'Address Line 1';
        $fields['shipping']['shipping_address_2']['label'] = 'Address Line 2';
        $fields['shipping']['shipping_postcode']['label']  = 'ZIP Code';
        $fields['shipping']['shipping_city']['label']      = 'City';

        $fields['shipping']['shipping_address_1']['placeholder'] = '';
        $fields['shipping']['shipping_address_2']['placeholder'] = '';

        $fields['shipping']['shipping_country']['class']   = [ "checkout-country-field" ];
        $fields['shipping']['shipping_address_2']['class'] = [ "optional-bottom-label" ];
        $fields['shipping']['shipping_phone']['class']     = [ 'checkout-form-hidden-field' ];

        // Billing zip classes
        $fields['shipping']['shipping_postcode']['input_class']   = [ "postal_code", "pac-target-input" ];
        $fields['shipping']['shipping_state']['input_class']      = [ "administrative_area_level_1" ];
        $fields['shipping']['shipping_city']['input_class']       = [ "locality" ];
        // $fields['shipping']['shipping_city']['custom_attributes'] = array( 'readonly' => 'readonly' );

        return $fields;
    }

    function woocommerce_form_field( $key, $args, $value = null, $disabled = false ) {
        $defaults = array(
            'type'              => 'text',
            'label'             => '',
            'description'       => '',
            'placeholder'       => '',
            'maxlength'         => false,
            'required'          => false,
            'autocomplete'      => false,
            'id'                => $key,
            'class'             => array(),
            'label_class'       => array(),
            'input_class'       => array(),
            'return'            => false,
            'options'           => array(),
            'custom_attributes' => array(),
            'validate'          => array(),
            'default'           => '',
            'autofocus'         => '',
            'priority'          => '',
        );

        $args = wp_parse_args( $args, $defaults );
        $args = apply_filters( 'woocommerce_form_field_args', $args, $key, $value );

        if ( $args['required'] ) {
            $args['class'][] = 'validate-required';
            $required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required',
                    'woocommerce' ) . '">*</abbr>';
        } else {
            $required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
        }

        if ( is_string( $args['label_class'] ) ) {
            $args['label_class'] = array( $args['label_class'] );
        }

        if ( is_null( $value ) ) {
            $value = $args['default'];
        }

        // Custom attribute handling.
        $custom_attributes         = array();
        $args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );

        if ( $args['maxlength'] ) {
            $args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
        }

        if ( ! empty( $args['autocomplete'] ) ) {
            $args['custom_attributes']['autocomplete'] = $args['autocomplete'];
        }

        if ( true === $args['autofocus'] ) {
            $args['custom_attributes']['autofocus'] = 'autofocus';
        }

        if ( $args['description'] ) {
            $args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
        }

        if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
            foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
                $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
            }
        }

        if ( ! empty( $args['validate'] ) ) {
            foreach ( $args['validate'] as $validate ) {
                $args['class'][] = 'validate-' . $validate;
            }
        }

        $field           = '';
        $label_id        = $args['id'];
        $sort            = $args['priority'] ? $args['priority'] : '';
        $field_container = '<li class="fields-list__item field-box %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '" data-name="' . esc_attr( $key ) . '">%3$s</li>';

        switch ( $args['type'] ) {
            case 'country':
                $countries = 'shipping_country' === $key ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();
                $status    = ( $disabled ) ? 'select-disabled' : '';

                if ( 1 === count( $countries ) ) {

                    $args['class'] = [ 'visually-hidden' ];

                    $field .= '<strong>' . current( array_values( $countries ) ) . '</strong>';

                    $field .= '<input type="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . current( array_keys( $countries ) ) . '" ' . implode( ' ',
                            $custom_attributes ) . ' class="country_to_state" readonly="readonly" />';

                } else {
                    $status = ( $disabled ) ? 'select-disabled' : '';

                    $field = '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="field-box__field field-box__field--select field-box__field--entered ' . $status . ' ' . esc_attr( implode( ' ',
                            $args['input_class'] ) ) . '" ' . implode( ' ',
                            $custom_attributes ) . '><option value="">' . esc_html__( 'Select a country / region&hellip;',
                            'woocommerce' ) . '</option>';

                    foreach ( $countries as $ckey => $cvalue ) {
                        $field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey,
                                false ) . '>' . esc_html( $cvalue ) . '</option>';
                    }

                    $field .= '</select>';

                    $field .= '<svg class="field-box__select-icon" width="24" height="24" fill="#BEC1C4"><use href="#icon-angle-down-light"></use></svg>';

                }

                break;
            case 'state':
                /* Get country this state field is representing */
                $for_country = isset( $args['country'] ) ? $args['country'] : WC()->checkout->get_value( 'billing_state' === $key ? 'billing_country' : 'shipping_country' );
                $states      = WC()->countries->get_states( $for_country );

                if ( is_array( $states ) && empty( $states ) ) {
                    $args['class'] = [ 'visually-hidden' ];

                    $field_container = '<p class="form-row %1$s" id="%2$s" style="display: none">%3$s</p>';

                    $field .= '<input type="hidden" class="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="" ' . implode( ' ',
                            $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '" readonly="readonly" data-input-classes="' . esc_attr( implode( ' ',
                            $args['input_class'] ) ) . '"/>';

                } elseif ( ! is_null( $for_country ) && is_array( $states ) ) {

                    $status = ( $disabled ) ? 'select-disabled' : '';

                    $field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="field-box__field field-box__field--select field-box__field--entered ' . $status . ' ' . esc_attr( implode( ' ',
                            $args['input_class'] ) ) . '" ' . implode( ' ',
                            $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ? $args['placeholder'] : esc_html__( 'Select an option&hellip;',
                            'woocommerce' ) ) . '"  data-input-classes="' . esc_attr( implode( ' ',
                            $args['input_class'] ) ) . '">';
                    // <option value="">' . esc_html__( 'Select an option&hellip;', 'woocommerce' ) . '</option>';

                    foreach ( $states as $ckey => $cvalue ) {
                        $field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey,
                                false ) . '>' . esc_html( $cvalue ) . '</option>';
                    }

                    $field .= '</select>';

                    $field .= '<svg class="field-box__select-icon" width="24" height="24" fill="#BEC1C4"><use href="#icon-angle-down-light"></use></svg>';

                } else {

                    $field .= '<input type="text" class="field-box__field ' . esc_attr( implode( ' ',
                            $args['input_class'] ) ) . '" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ',
                            $custom_attributes ) . ' data-input-classes="' . esc_attr( implode( ' ',
                            $args['input_class'] ) ) . '"/>';

                }

                break;
            case 'textarea':
                $field .= '<textarea name="' . esc_attr( $key ) . '" class="input-text ' . esc_attr( implode( ' ',
                        $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ',
                        $custom_attributes ) . '>' . esc_textarea( $value ) . '</textarea>';

                break;
            case 'checkbox':
                $field = '<label class="checkbox ' . implode( ' ', $args['label_class'] ) . '" ' . implode( ' ',
                        $custom_attributes ) . '>
						<input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ',
                        $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" ' . checked( $value,
                        1, false ) . ' /> ' . $args['label'] . $required . '</label>';

                break;
            case 'text':
                $status = ( $disabled ) ? 'disabled="disabled"' : '';

                $field .= '<input type="' . esc_attr( $args['type'] ) . '" class="field-box__field ' . esc_attr( implode( ' ',
                        $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ',
                        $custom_attributes ) . ' ' . $status . ' />';

                break;
            case 'password':
            case 'datetime':
            case 'datetime-local':
            case 'date':
            case 'month':
            case 'time':
            case 'week':
            case 'number':
            case 'email':
            case 'url':
            case 'tel':
                $field .= '<input type="' . esc_attr( $args['type'] ) . '" class="field-box__field ' . esc_attr( implode( ' ',
                        $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ',
                        $custom_attributes ) . ' />';

                break;
            case 'select':
                $field   = '';
                $options = '';

                if ( ! empty( $args['options'] ) ) {
                    foreach ( $args['options'] as $option_key => $option_text ) {
                        if ( '' === $option_key ) {
                            // If we have a blank option, select2 needs a placeholder.
                            if ( empty( $args['placeholder'] ) ) {
                                $args['placeholder'] = $option_text ? $option_text : __( 'Choose an option',
                                    'woocommerce' );
                            }
                            $custom_attributes[] = 'data-allow_clear="true"';
                        }
                        $options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key,
                                false ) . '>' . esc_html( $option_text ) . '</option>';
                    }

                    $field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="field-box__field field-box__field--select field-box__field--entered ' . esc_attr( implode( ' ',
                            $args['input_class'] ) ) . '" ' . implode( ' ',
                            $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
							' . $options . '
						</select>';
                }

                break;
            case 'radio':
                $label_id .= '_' . current( array_keys( $args['options'] ) );

                if ( ! empty( $args['options'] ) ) {
                    foreach ( $args['options'] as $option_key => $option_text ) {
                        $field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ',
                                $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" ' . implode( ' ',
                                $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value,
                                $option_key, false ) . ' />';
                        $field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ',
                                $args['label_class'] ) . '">' . esc_html( $option_text ) . '</label>';
                    }
                }

                break;
        }

        if ( ! empty( $field ) ) {
            $field_html = '';

            $field_html .= $field;

            if ( $args['label'] && 'checkbox' !== $args['type'] ) {
                $field_html .= '<label for="' . esc_attr( $label_id ) . '" class="field-box__label ' . esc_attr( implode( ' ',
                        $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
            }


            $container_class = esc_attr( implode( ' ', $args['class'] ) );
            $container_id    = esc_attr( $args['id'] ) . '_field';
            $field           = sprintf( $field_container, $container_class, $container_id, $field_html );
        }

        /**
         * Filter by type.
         */
        $field = apply_filters( 'woocommerce_form_field_' . $args['type'], $field, $key, $args, $value );

        /**
         * General filter on form fields.
         *
         * @since 3.4.0
         */
        $field = apply_filters( 'woocommerce_form_field', $field, $key, $args, $value );

        if ( $args['return'] ) {
            return $field;
        } else {
            echo $field; // WPCS: XSS ok.
        }
    }
    function header_cart_html() {
        ?>
        <a class="main-nav__cart header-cart <?php echo ( ! WC()->cart->is_empty() ) ? 'header-cart--filled' : '' ?>"
           href="<?php echo wc_get_cart_url(); ?>">

            <?php
//            $totals = WC()->session->get( 'cart_totals' );
//            $price  = $totals['cart_contents_total'];
            ?>

            <span class="header-cart__icon"
                  data-quantity="<?php echo esc_attr( WC()->cart->get_cart_contents_count() ); ?>">
                <svg width="24" height="24" fill="#252728">
                    <use xlink:href="#icon-cube"></use>
                </svg>
            </span>
<!--            <span class="header-cart__price">$--><?php //echo number_format( $price, 2 ); ?><!--</span>-->
        </a>
        <?php
    }

    function header_add_to_cart_fragment( $fragments ) {
        ob_start();

        $this->header_cart_html();

        $fragments['a.main-nav__cart'] = ob_get_clean();
        return $fragments;
    }

    function customize_add_to_cart_fragment( $fragments ) {
        ob_start();

        $this->filter_customize_block_products();

        $fragments['ul.option-list'] = ob_get_clean();

        return $fragments;
    }

    function set_custom_checkout_field_postcode() {
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $zip_code     = trim( get_user_meta( $current_user->ID, 'sf_zipcode', true ) );
        } else {
            $zip_code = op_help()->sf_user::op_get_zip_cookie();
        }

        ?>
        <script>
            (function ($) {
                $('#billing_postcode').val('<?php echo $zip_code; ?>');
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Save customer feedaback to order notes
     *
     * @param
     */
    function save_order_customer_note() {
        $data = array();
        parse_str( $_POST['form'], $data );

        $message = 'Rate - ' . $data['rate-assessment']['rating'] . '<br>';
        $message .= $data['rate-assessment']['message'];

        $order = wc_get_order( $data['rate-assessment']['order_id'] );
        $order->add_order_note( $message );

        echo 'done';
        wp_die();
    }

    /**
     * Add required order statuses
     *
     * @param
     */
    function manage_order_statuses() {
        register_post_status( 'wc-shipped', array(
            'label'                     => _x( 'Shipped', 'WooCommerce Order status', 'woocommerce' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Shipped (%s)', 'Shipped (%s)', 'woocommerce' )
        ) );
        register_post_status( 'wc-delivered', array(
            'label'                     => _x( 'Delivered', 'WooCommerce Order status', 'woocommerce' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop( 'Delivered (%s)', 'Delivered (%s)', 'woocommerce' )
        ) );
    }

    function add_order_statuses( $order_statuses ) {
        $order_statuses = $order_statuses + [
                'wc-shipped' => _x( 'Shipped', 'WooCommerce Order status', 'woocommerce' )
            ]
                          + [
                              'wc-delivered' => _x( 'Delivered', 'WooCommerce Order status', 'woocommerce' )
                          ];

        return $order_statuses;
    }

    /**
     * Filter for order statuses "to show"
     *
     * @param $order - object
     */
    public function order_statuses_to_show_on_front( $order ) {
        if ( $order ) {
            $names = [ 'Processing', 'Shipped', 'Delivered' ];

            $current_date = time();
            $locked_time  = ( new DateTime( $order->get_meta( "op_next_delivery" ) ) )->modify( '-2 days' )->getTimestamp();

            // Initial status
            $statuses_list[] = [
                'name'   => 'Open',
                'active' => true
            ];
            // Locked status
            $statuses_list[] = [
                'name'   => 'Locked',
                'active' => ( $current_date > $locked_time ) ? true : false
            ];
            // Other statuses
            $admin_statuses = wc_get_order_statuses();
            foreach ( $admin_statuses as $key => $status ) {
                if ( in_array( $status, $names ) ) {
                    $statuses_list[] = [
                        'name'   => $status,
                        'key'    => $key,
                        'active' => ( $order->get_status() == strtolower( $status ) ) ? true : false
                    ];
                }
            }

            return $statuses_list;
        }
    }

    public function change_count_products_on_page() {
        if ( op_help()->sf_user->check_survey_exist() ) {
            return 12;
        }

        return 11;
    }

    /**
     * Show chef hats to product
     *
     * @param $product_id
     *
     * @return mixed
     */
    public function show_chef_hats( $product_id ) {
        $survey_score     = op_help()->survey->calculate_survey_score();
        $cached_item      = op_help()->global_cache->get( [ $product_id ] );
        $items_with_score = op_help()->survey->calculate_score_for_items( $cached_item, $survey_score );

        // SET chef_score = 0 if it`s not recommended
        $prepared_items = array_map( function ( $item ) {
            if ( $item['score'] === 'remove' ) {
                $item['chef_score'] = 0;
            }

            return $item;
        }, $items_with_score );

        return $prepared_items[ array_key_last( $prepared_items ) ]['chef_score'];
    }

    public function survey_menu_items_rewrite( $items, $args ) {
        foreach ( $items as $item ) {
            $meals = strstr( $item->url, 'meals' );

            if ( ! empty( $meals ) ) {
                $item->url = add_query_arg( 'use_survey', 'on', $item->url );
            }
        }

        return $items;
    }

    public function survey_products_items_rewrite( $post_link, $post, $leavename = null, $sample = null ) {
        if ( $post->post_type === 'product_variation' ) {
            $post_link = add_query_arg( 'use_survey', 'true',
                op_help()->variations->rules->variationLink( $post->ID ) );
        }

        return $post_link;
    }

    public function sf_change_breadcrumbs_elements( $elms, $class, $ptype ) {
        global $wp;

        if ( is_product() ) {
            global $product;
            $product_cats = $product->get_category_ids();

            //meals
            if ( $product_cats[ array_key_first( $product_cats ) ] === 15 ) {
                $cat_id  = $product_cats[ array_key_first( $product_cats ) ];
                $tag_obj = get_term_by( 'id', $product_cats[ array_key_first( $product_cats ) ], 'product_cat' );
                $link    = get_term_link( $cat_id );

                if ( intval( get_user_meta( get_current_user_id(), 'survey_default', true ) ) ) {
                    $link = add_query_arg( 'use_survey', 'on', $link );
                }

                $elms['single']['product_cat__tax_crumbs'] = '<a class="breadcrumbs__link" href="' . $link . '" itemprop="item" itemscope="" itemtype="http://schema.org/ListItem">
															<span itemprop="name">' . $tag_obj->name . '</span>
														  </a>';
            }

            // staples
            if ( $product_cats[ array_key_first( $product_cats ) ] === 27 ) {
                $tags    = $product->get_tag_ids();
                $tag_id  = $tags[ array_key_last( $tags ) ];
                $tag_obj = get_term_by( 'id', $tag_id, 'product_tag' );

                $elms['single']['product_cat__tax_crumbs'] = '<a class="breadcrumbs__link" href="' . get_term_link( $tag_id ) . '" itemprop="item" itemscope="" itemtype="http://schema.org/ListItem">
															<span itemprop="name">' . $tag_obj->name . '</span>
														  </a>';
            }
        }

        if ( is_product_tag() ) {
            $request = $wp->request;
            $req     = explode( '/', $request );
            $tag_obj = get_term_by( 'slug', $req[ array_key_last( $req ) ], 'product_tag' );

            $elms['tax_tag']['title'] = '<span class="breadcrumbs__current">' . $tag_obj->name . '</span>';
        }

        return $elms;
    }

    public function filter_customize_block() {
        if ( isset( $_POST['survey_status'] ) && ! empty( $_POST['survey_status'] ) ) {
            if ( $_POST['survey_status'] === 'true' ) {
                update_user_meta( get_current_user_id(), 'survey_default', 1 );
            } else {
                update_user_meta( get_current_user_id(), 'survey_default', 0 );
            }
        }

        wp_send_json_success([
            'status' => (bool) get_user_meta( get_current_user_id(), 'survey_default', 1 )
        ]);
    }

    public function sf_hide_sale_flash() {
        return false;
    }

    public function get_tax_rates_by_zip() {
        // Get current zip
        $zip_code = '';
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $zip_code     = trim( get_user_meta( $current_user->ID, 'sf_zipcode', true ) );
        } else {
            $zip_code = op_help()->sf_user::op_get_zip_cookie();
        }

        // Get taxes list
        $all_tax_rates = [];
        $tax_classes   = WC_Tax::get_tax_classes();
        if ( ! in_array( '', $tax_classes ) ) {
            array_unshift( $tax_classes, '' );
        }
        foreach ( $tax_classes as $tax_class ) {
            $taxes         = WC_Tax::get_rates_for_tax_class( $tax_class );
            $all_tax_rates = array_merge( $all_tax_rates, $taxes );
        }

        if ( $zip_code !== '' ) {
            // Find rate for zip
            $tax_by_zip = [];
            foreach ( $all_tax_rates as $tax ) {
                if ( in_array( $zip_code . '*', $tax->postcode ) ) {
                    $tax_by_zip = $tax;
                }
            }
        }

        return round( $tax_by_zip->tax_rate, 2 );
    }

    public function calc_total_tax( $tax_rate ) {
        // Get subtotal from cart
        $total = WC()->cart->subtotal;

        // Get coupons
        foreach ( WC()->cart->get_applied_coupons() as $coupon_code ) {
            $coupon_total = WC()->cart->coupon_discount_totals[ $coupon_code ];

            // Get the WC_Coupon object
            // $coupon = new WC_Coupon($coupon_code);

            // $discount_type = $coupon->get_discount_type(); // Get coupon discount type: fixed_cart, percent, fixed_product
            // $coupon_amount = $coupon->get_amount(); // Get coupon amount
        }

        // Update total using discount
        if ( ! is_null( $coupon_total ) ) {
            $total = $total - $coupon_total;
        }

        return $total / 100 * $tax_rate;
    }

    public function cart_shipping_cost() {
        foreach ( WC()->cart->get_shipping_packages() as $package_id => $package ) {
            // Check if a shipping for the current package exist
            if ( WC()->session->__isset( 'shipping_for_package_' . $package_id ) ) {
                // Loop through shipping rates for the current package
                foreach ( WC()->session->get( 'shipping_for_package_' . $package_id )['rates'] as $shipping_rate_id => $shipping_rate ) {
                    $cost = $shipping_rate->get_cost(); // The cost without tax
                }
            }
        }

        return $cost;
    }

    public function remove_all_meals_from_cart() {
        global $woocommerce;

        $user_cart = $woocommerce->cart->get_cart();
        $count     = 0;

        foreach ( $user_cart as $key => $cart_item ) {
            if ( $cart_item['variation_id'] !== 0 ) {
                WC()->cart->remove_cart_item( $cart_item['key'] );
                $count += $cart_item['quantity'];
            }
        }

        return $count;
    }

    public function remove_all_groceries_from_cart() {
        global $woocommerce;

        $user_cart = $woocommerce->cart->get_cart();
        $count     = 0;
        foreach ( $user_cart as $cart_item ) {
            if ( get_the_terms( $cart_item['product_id'], 'product_cat' )[0]->slug === 'staples' ) {
                WC()->cart->remove_cart_item( $cart_item['key'] );
                $count += $cart_item['quantity'];
            }
        }

        return $count;
    }

    public function change_products_customize( $survey, $product_id, $variation_id ) {

        $survey = $survey === 'true';

        $product_can_be = op_help()->global_cache->getByType( 'variation' );

        $cache_user_info = op_help()->user_cache->get( get_current_user_id() );

        $product_can_be = array_map( function ( $product ) use ( $cache_user_info ) {
            foreach ( $cache_user_info as $item ) {
                if ( $item['var_id'] === $product['var_id'] ) {
                    $product['order_by']   = $item['order_by'];
                    $product['chef_score'] = $item['chef_score'];
                    $product['score']      = $item['score'];
                }
            }

            return $product;
        }, $product_can_be );

        $product_can_be = array_filter( $product_can_be, function ( $item ) {
            return $item['score'] !== 'remove';
        } );

        if ( op_help()->sf_user->check_survey_exist() && $survey ) {
            // recommended meals
            $product_can_be = array_filter( $product_can_be, function ( $variation ) {
                return $variation['chef_score'] > 2;
            } );

        }

        $product   = wc_get_product( $product_id );
        $variation = wc_get_product( $variation_id );

        $product_has_attributes       = $product->get_variation_attributes(); // все компоненты
        $this_variation_attributes_db = $variation->get_attributes(); // компоненты текущие
        $this_variation_attributes    = [];

        foreach ( $this_variation_attributes_db as $key => $value ) {
            $term                              = get_term_by( 'slug', $value, $key );
            $this_variation_attributes[ $key ] = $term->term_id;
        }

        $other_variations           = [];
        $other_variations_items_ids = [ $variation->get_id() ];
        $this_variation_term_ids    = [];

        foreach ( $product_has_attributes as $attr_slug => $attr_variation ) {

            $this_variation_attr_value = $variation->get_attribute( $attr_slug );
            $this_variation_term       = get_term_by( 'slug', $this_variation_attr_value, $attr_slug );

            $this_variation_term_ids[] = $this_variation_term->term_id;
            // find all variations with other option for this component group
            $other_variations[ $attr_slug ] = array_filter( $product_can_be,
                function ( $variation ) use ( $attr_slug, $this_variation_attributes ) {

                    foreach ( $variation['data']['components'] as $other_variation_attr_slug => $other_variation_attr_value ) {

                        // if current group different - its ok
                        if ( $other_variation_attr_slug == $attr_slug ) {
                            continue;
                        }

                        // but other attributes must be same
                        if ( $this_variation_attributes[ $other_variation_attr_slug ] != $other_variation_attr_value ) {
                            return false;
                        }
                    }

                    return true;
                } );

            $other_variations[ $attr_slug ] = array_map( function ( $item ) use (
                $attr_slug,
                &$other_variations_items_ids
            ) {
                $this_variation_attr_value    = $item['data']['components'][ $attr_slug ];
                $other_variations_items_ids[] = $item['var_id'];

                return [
                    'item'     => $item,
                    'term'     => get_term_by( 'id', $this_variation_attr_value, $attr_slug ),
                    'termmeta' => get_term_meta( $this_variation_attr_value )
                ];

            }, $other_variations[ $attr_slug ] );

        }

        return $other_variations;

    }

    // Create user payment token
    public function op_create_user_pay_token() {
        // Create new token
        $payment_method = op_help()->payment->createToken( $_POST );

        if ( is_wp_error( $payment_method ) ) {
            wp_send_json_error( [
                'debug' => $payment_method->get_error_message(),
            ] );
        } else {
            wp_send_json_success();
        }
    }

    public function load_catalog_pagination() {
        $data = sanitize_post( $_POST, 'db' );

        $products = op_help()->variations->get_pagination_variations_items( $data['nextPage'], $data['order'],
            $data['catId'], $data['taxonomy'] );

        get_template_part( 'template-parts/catalog/pagination', 'products', $products );

        wp_die();
    }

    public function get_cart_data() {
        $data = op_help()->meal_plan_modal->get_cart_items();

        if ( ! empty( $data ) ) {
            wp_send_json_success( [
                'cart' => $data
            ] );
        }

        wp_die();
    }

    public function is_meals_category() {
        return is_product_category( $this->meals_category );
    }

    public function saveModalAddress() {
        parse_str( $_POST['form'], $data );
        $address = $data['checkout'];

        // Save new zip code
        if ($data['address_type'] == 'shipping') {
            $user_zone = op_help()->zip_codes->get_zip_zone( $address['_shipping_postcode'] );
            op_help()->zip_codes->set_user_data( $address['_shipping_postcode'], $user_zone );

            $is_zip_national = op_help()->zip_codes->is_zip_zone_national( $address['_shipping_postcode'], $user_zone );
            $show = ($is_zip_national) ? 'updated-subscription' : $user_zone.'-subscription';
        }else {
            $user_zone = op_help()->zip_codes->get_zip_zone( $data['delivery_zip'] );

            $is_zip_national = op_help()->zip_codes->is_zip_zone_national( $data['delivery_zip'], $user_zone );
            $show = ($is_zip_national) ? 'updated-subscription' : $user_zone.'-subscription';
        }

        // Save new address
        $order = op_help()->subscriptions->get_current_subscription();
        $subscription_id = $order->get_id();
        // $user_id = get_current_user_id();

        if ($order === false) {
            wp_send_json_error( array(
                'message' => "Subscription doesn't exist"
            ) );
        }

        // Update city and state
        $city_state = explode(', ', $address['city_state']);

        $address['_'.$data['address_type'].'_city']  = $city_state[0];
        $address['_'.$data['address_type'].'_state'] = $city_state[1];
        // Remove combine field
        unset($address['city_state']);

        foreach ( $address as $key => $value ) {
            if ( $key !== 'use_this_as_my_billing_address' ) {
                $this->updateSubscriptionField( $key, $value, $subscription_id, $address['use_this_as_my_billing_address'] );
            }
        }

        if ( $data['address_type'] == 'shipping' ) {
            $success = array(
                'shipping_address' => $address['_shipping_address_1'] . ' ' . $address['_shipping_city'] . ', ' . $address['_shipping_state'] . ' ' . $address['_shipping_postcode'],
                'show' => $show,
            );

            if ( $address[ 'use_this_as_my_billing_address' ] == 'on' ) {
                $success[ 'billing_address' ] = $address['_shipping_address_1'] . ' ' . $address['_shipping_city'] . ', ' . $address['_shipping_state'] . ' ' . $address['_shipping_postcode'];
            }

            // Remove all meals & groceries from cart
            if ($is_zip_national) {
                $update_result          = $this->remove_all_meals_from_cart();
                $grocery_remove_results = $this->remove_all_groceries_from_cart();

                $update_result            += $grocery_remove_results;
                $success['update_result'] = $update_result;
            }
        }else {
            $success = [
                'show' => $show,
                'shipping_address' => $data[ 'delivery_address' ],
                'billing_address'  => $address[ '_billing_address_1' ] . ' ' . $address[ '_billing_city' ] . ', ' . $address[ '_billing_state' ] . ' ' . $address[ '_billing_postcode' ],
                'update_result'    => $data[ 'update_result' ]
            ];
        }

        // Check is "billing" address flag unchecked
        if ($data[ 'address_type' ] == 'shipping' && is_null( $address[ 'use_this_as_my_billing_address' ] ) ) {
            wp_send_json_error( array(
                'update_result'    => ( ! is_null( $update_result ) ) ? $update_result : 0,
                'shipping_address' => $address[ '_shipping_address_1' ] . ' ' . $address[ '_shipping_city' ] . ', ' . $address[ '_shipping_state' ] . ' ' . $address[ '_shipping_postcode' ],
                'zip' 			   => $address[ '_shipping_postcode' ],
                'show' 			   => 'billing-address'
            ) );
        }

        wp_send_json_success( $success );
    }

    // Update field
    public function updateSubscriptionField( $key, $val, $oid, $billing_flag ) {
        // if ($val !== '') { // TODO: uncomment, if empty values shouldn't replace existing data
        if ( $key == 'order_comments' ) {
            wp_update_post( [ 'ID' => $oid, 'post_excerpt' => $val ] );
        } else {
            update_post_meta( $oid, $key, $val );
            // Update second address too, if flag checked
            if ( $billing_flag == 'on' ) {
                update_post_meta( $oid, str_replace( 'shipping', 'billing', $key ), $val );
            }
        }
        // }
    }

    // Get subscription billing details
    public function getSubscriptionBillingAddress() {
        $subscription = op_help()->subscriptions->get_current_subscription();

        if ( $subscription === false ) {
            return [];
        }

        return array(
            'first_name'   => $subscription->get_billing_first_name(),
            'last_name'    => $subscription->get_billing_last_name(),
            'address_1'    => $subscription->get_billing_address_1(),
            'address_2'    => $subscription->get_billing_address_2(),
            'city'         => $subscription->get_billing_city(),
            'state'        => WC()->countries->states[ $subscription->billing_country ][ $subscription->billing_state ],
            'state_code'   => $subscription->get_billing_state(),
            'country'      => WC()->countries->countries[ $subscription->get_billing_country() ],
            'country_code' => $subscription->get_billing_country(),
            'zip_code'     => $subscription->get_billing_postcode(),
        );
    }

    public function get_similar_meals( $var_id ) {

        $product_info = op_help()->global_cache->get_cached_product( $var_id );
        $all_products = op_help()->global_cache->getAll();
        $sort_cache   = op_help()->sort_cache->get_sort_cache( - 1, 0, 'default', false, 1, 'variation' );
        $sort_cache   = $sort_cache['ids_with_chef_score'];
        $products     = op_help()->variations->get_sort_order( $all_products, array_column( $sort_cache, 'id' ) );
        $hats         = array_column( $sort_cache, 'cs', 'id' );

        $similar_meals = [];

        foreach ( $products as $item ) {
            if ( $item['var_id'] != $product_info['var_id'] ) {
                if ( $item['data']['components']['pa_part-1'] === $product_info['components']['pa_part-1'] &&
                     $item['data']['components']['pa_part-2'] === $product_info['components']['pa_part-2'] ) {
                    $temp               = $item;
                    $temp['chef_score'] = $hats[ $item['var_id'] ];
                    $similar_meals[]    = $temp;

                    if ( count( $similar_meals ) >= 20 ) {
                        return $similar_meals;
                    }
                }
            }
        }

        return $similar_meals;
    }

    public function get_recommended_meals( $meals_count ) {
        $recommended = op_help()->sf_user->check_survey_default();
        $sort_cache = op_help()->sort_cache->get_sort_cache( $meals_count, 0, 'default', false, $recommended, 'variation', false, false );

        $products = [];

        foreach ( $sort_cache['ids_with_chef_score'] as $item ) {
            $temp               = op_help()->global_cache->get_cached_product( $item['id'] );
            $temp['chef_score'] = $item['cs'];
            $products[]         = $temp;
        }

        return $products;
    }

    public function get_offerings_vitamins( $meals_count ) {
        $vitamins_term = get_term_by( 'slug', 'vitamins', 'product_cat' );
        $sort_cache    = op_help()->sort_cache->get_sort_cache( $meals_count, 0, 'default', false, false, 'simple', $vitamins_term->term_id );

        $vitamins = [];
        foreach ( $sort_cache['ids'] as $id ) {
            $vitamins[] = op_help()->global_cache->get_cached_product( $id );
        }

        return $vitamins;
    }

    public function get_similar_groceries( $product_id, $meals_count = 20 ) {
        $current_tag = get_the_terms( $product_id, 'product_tag' )[0]->term_id;
        $sort_cache  = op_help()->sort_cache->get_sort_cache( $meals_count, 0, 'default', false, false, 'simple', false, $current_tag );

        $groceries = [];
        foreach ( $sort_cache['ids'] as $id ) {
            $groceries[] = op_help()->global_cache->get_cached_product( $id );
        }

        return $groceries;
    }

    function add_frequency_to_item() {
        $request = (object) $_POST;
        if ( isset( $request ) ) {
            $_SESSION[ 'sf_cart_frequency_item_' . $request->product_id ] = $request->frequency;
        }
    }

    public function is_page_have_loader() {
    	$current_slug = trim( parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/' );

    	if ( in_array( $current_slug, carbon_get_theme_option('op_main_loader_pages') ) ) {
		    return true;
		}

		return false;
    }

    public function check_zone_for_catalog( $taxonomy ) {
        $current_zone = op_help()->zip_codes->get_current_user_zone();

        switch ( $current_zone ) {
            case 'national':
                $show = ( $taxonomy->slug === 'vitamins' );
                break;
            case 'overnight':
                $show = ( $taxonomy->slug === 'meals' || $taxonomy->slug === 'vitamins' );
                break;
            case 'local':
                $show = true;
                break;
            default:
                $show = false;
                break;
        }

        return $show;
    }

    /**
     * Check is current product - meal
     *
     * @return bool
     */
    public function is_meal_product() {
        global $post;

        $is_meal = false;
        $categories = get_the_terms( $post->ID, 'product_cat' );

        foreach ($categories as $category) {
            if ($category->term_id == $this->meals_category) {
                $is_meal = true;
                break;
            }
        }

        return $is_meal;
    }
}
