<?php

function sf_add_woocommerce_support() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'title-tag' );
}

add_action( 'after_setup_theme', 'sf_add_woocommerce_support' );

add_filter( 'woocommerce_enqueue_styles', '__return_false' );

function get_discount( $regular_price, $sale_price ) {
    $diff = floatval( $regular_price ) - floatval( $sale_price );

    return floor( $diff / floatval( $regular_price ) * 100 );
}

if ( 'disable_gutenberg' ) {
    remove_theme_support( 'core-block-patterns' ); // WP 5.5

    add_filter( 'use_block_editor_for_post_type', '__return_false', 100 );

    // отключим подключение базовых css стилей для блоков
    // ВАЖНО! когда выйдут виджеты на блоках или что-то еще, эту строку нужно будет комментировать
    remove_action( 'wp_enqueue_scripts', 'wp_common_block_scripts_and_styles' );

    // Move the Privacy Policy help notice back under the title field.
    add_action( 'admin_init', function () {
        remove_action( 'admin_notices', [ 'WP_Privacy_Policy_Content', 'notice' ] );
        add_action( 'edit_form_after_title', [ 'WP_Privacy_Policy_Content', 'notice' ] );
    } );
}

// Отключил проверку от плагина user-verification -- ломает checkout
remove_action( 'woocommerce_checkout_process', 'uv_woocommerce_on_checkout_protect_username' );

function change_path_to_s3( $image ) {
    $uploads_url = wp_upload_dir();

    if ( is_array( $image ) ) {
        return array_map( function ( $item ) use ( $uploads_url ) {
            if ( ! empty( $item ) && strpos( $item, 'amazonaws.com' ) ) {
                $item = str_replace( $uploads_url['baseurl'] . '/', '', $item );
            }

            return $item;
        }, $image );
    }

    return $image;
}

add_filter( 'wp_get_attachment_image_src', 'change_path_to_s3', 15, 1 );

// Pre-check shipping address checkbox
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_true' );

// Update label for optional fields on checkout page
function remove_checkout_optional_fields_label( $field, $key, $args, $value ) {
    // Only on checkout page
    if ( is_checkout() && ! is_wc_endpoint_url() ) {
        $optional         = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
        $optional_updated = '&nbsp;<span class="optional">' . esc_html__( 'optional', 'woocommerce' ) . '</span>';
        $field            = str_replace( $optional, $optional_updated, $field );
    }

    return $field;
}

add_filter( 'woocommerce_form_field', 'remove_checkout_optional_fields_label', 10, 4 );

// Add shipping phone field to admin area
function additional_admin_shipping_fields( $fields ) {
    $fields['phone'] = array(
        'label' => __( 'Shipping phone', 'woocommerce' ),
    );

    return $fields;
}

add_filter( 'woocommerce_admin_shipping_fields', 'additional_admin_shipping_fields' );

// Add shipping phone fields to user data
function filter_add_customer_meta_fields( $args ) {
    $args['shipping']['fields']['shipping_phone'] = array(
        'label'       => __( 'Shipping phone', 'woocommerce' ),
        'description' => '',
    );

    return $args;
}

add_filter( 'woocommerce_customer_meta_fields', 'filter_add_customer_meta_fields', 10, 1 );

// Redirect to cart
add_action( 'template_redirect', 'back_to_cart' );
function back_to_cart() {
    if ( is_checkout() ) {
        // Redirect to cart if user has subscription with status "Locked"
        $status_data = op_help()->subscriptions->get_subscribe_status();
        if ( $status_data['label'] == 'locked' ) {
            wp_redirect( wc_get_cart_url(), 302 );

            exit();
        }
    }
}

// Return checkout steps list
function checkout_steps_hashes() {
    return [ '#Delivery-Address', '#Schedule-Your-First-Delivery', '#Payment-Method', '#Confirmation' ];
}

// Remove shipping label from cart/checkout
add_filter( 'woocommerce_cart_shipping_method_full_label', 'remove_row_shipping_label', 99, 2 );
function remove_row_shipping_label( $label, $method ) {
    $new_label = preg_replace( '/^.+:/', '', $label );

    return $new_label;
}

// Get taxes info for current subscription
function calc_subscription_taxes() {
    $order = op_help()->subscriptions->get_current_subscription();

    if ( ! $order || empty( $order->get_items( 'tax' ) ) ) {
        return false;
    }

    // Get all order taxes
    foreach ( $order->get_items( 'tax' ) as $item ) {
        $tax_rate = $item->get_rate_percent();

        $tax_total  = $item->get_tax_total(); // Get tax total amount (for this rate)
        $ship_total = $item->get_shipping_tax_total(); // Get shipping tax total amount (for this rate)
    }

    return [
        'tax-total' => $tax_total + $ship_total,
        'tax-rate'  => $tax_rate
    ];
}

// Update cart total according to order taxes
function add_taxes_for_cart_total( $total, $cart ) {
    $order = op_help()->subscriptions->get_current_subscription();
    if ( $order ) {
        return $order->get_total();
    } else {
        // Condition for coupons (fix issue with wrong total, when subscription don't exist)
        if ( empty( WC()->cart->get_applied_coupons() ) ) {
            $taxes_by_zip = op_help()->shop->get_tax_rates_by_zip();
            $total        = $total + ( $total / 100 * $taxes_by_zip ) - ( op_help()->shop->cart_shipping_cost() / 100 * $taxes_by_zip );

            return round( $total, $cart->dp );
        } else {
            if ( (float)op_help()->shop->cart_shipping_cost() == 0 ) {
                return $total;
            }else {
                $taxes_by_zip = op_help()->shop->get_tax_rates_by_zip();
                return $total + ( $total / 100 * $taxes_by_zip ) - ( op_help()->shop->cart_shipping_cost() / 100 * $taxes_by_zip );
            }
        }
    }
}

add_filter( 'woocommerce_calculated_total', 'add_taxes_for_cart_total', 100, 2 );

function sf_is_free_shipping( $available_methods ) {
    $result = [];

    foreach ( $available_methods as $key => $method ) {
        if ( $method->method_id == 'free_shipping' ) {
            $result[ $key ] = $method;
        }
    }

    return ( empty( $result ) ) ? $available_methods : $result;
}

// remove woocommerce breadcrumbs
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

// remove product item
function op_breadcrumbs_add_elements( $elms, $class, $ptype ) {
    if ( is_product() ) {
        unset( $elms['home_after'] );
        global $post, $product;
        $is_variation              = false;
        $mb_know_variation_already = get_query_var( 'variation_id' );
        global $variation;
        $variation = op_help()->variations->rules->getCurrentVariation();
        if ( ! empty( $variation ) ) {
            $is_variation = true;
        }
        $this_object = function () use ( $is_variation, $product, $variation ) {
            if ( $is_variation ) {
                return $variation;
            }

            return $product;
        };

        // $elms['single']['title'] = $class->makelink( get_permalink($post), get_the_title( $post->ID ) );
        $elms['single']['title'] = $class->maketitle( get_the_title( $this_object()->get_id() ) );

    } else if ( is_tax( 'product_cat' ) ) {

        $elms['home_after'] = $class->makelink( op_get_permalik_by_template( 'page-offerings.php' ), op_get_title_by_template( 'page-offerings.php' ) );
    }

    return $elms;
}

add_filter( 'kama_breadcrumbs_filter_elements', 'op_breadcrumbs_add_elements', 10, 3 );

// set default parammeters
add_filter( 'kama_breadcrumbs_args', function ( $args ) {

    return [
               'sep'    => '|',
               'markup' => [
                   'wrappatt'  => '<div class="breadcrumbs" itemtype="http://schema.org/BreadcrumbList">%s</div>',
                   'linkpatt'  => '<a class="breadcrumbs__link" href="%s" itemprop="item" itemscope="" itemtype="http://schema.org/ListItem"><span itemprop="name">%s</span></a>',
                   'titlepatt' => '<span class="breadcrumbs__current">%s</span>',
                   'seppatt'   => '<span class="breadcrumbs__separator">%s</span>',
               ],
           ]
           + $args;

} );

// edit strings translate
add_filter( 'kama_breadcrumbs_l10n', function ( $l10n ) {
    return [ 'home' => 'Home' ] + $l10n;
} );

/**
 * Remove product from cart
 */
function op_remove_from_cart() {
    check_ajax_referer( 'op_check', 'ajax_nonce' );

    $product_id    = sanitize_text_field( $_POST['product_id'] );
    $cart_item_key = sanitize_text_field( $_POST['cart_product_key'] );

    if ( $cart_item_key ) {
        $data = [
            'product_id'    => $product_id,
            'cart_item_key' => $cart_item_key,
        ];
        WC()->cart->remove_cart_item( $cart_item_key );
        do_action( 'woocommerce_ajax_removed_from_cart', $data );
        wp_send_json_success( [
            'message' => 'Success remove',
        ] );

    }

    wp_send_json_error( [
        'message' => 'Error remove',
    ] );
}

if ( wp_doing_ajax() ) {
    add_action( 'wp_ajax_op_remove_from_cart', 'op_remove_from_cart' );
    add_action( 'wp_ajax_nopriv_op_remove_from_cart', 'op_remove_from_cart' );
}

// add custom fields for product
function op_woo_add_custom_fields() {

    global $product, $post;
    $product = get_product( $post->ID );

    if ( $product->is_type( 'simple' ) ) {
        woocommerce_wp_text_input( [
            'id'    => '_company_name',
            'label' => __( 'Company name', 'woocommerce' ),
        ] );
    }

    woocommerce_wp_checkbox( [
        'id'          => '_show_modal',
        'label'       => 'Show modal',
        'description' => 'Before redirect to product page show zipcode modal',
    ] );
}

add_action( 'woocommerce_product_options_general_product_data', 'op_woo_add_custom_fields' );


function op_woo_custom_fields_save( $post_id ) {

    if ( isset( $_POST['_company_name'] ) ) {
        update_post_meta( $post_id, '_company_name', esc_attr( $_POST['_company_name'] ) );
    }

    if ( isset( $_POST['_show_modal'] ) ) {
        $checkbox_field = isset( $_POST['_show_modal'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_show_modal', $checkbox_field );
    }
}

add_action( 'woocommerce_process_product_meta', 'op_woo_custom_fields_save', 10 );

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

/**
 * AJAX Load More
 */
function be_ajax_load_more() {
    return null;
}

if ( wp_doing_ajax() ) {
    add_action( 'wp_ajax_be_ajax_load_more', 'be_ajax_load_more' );
    add_action( 'wp_ajax_nopriv_be_ajax_load_more', 'be_ajax_load_more' );
}

function op_offer_card() {


    if ( ! op_help()->sf_user->check_survey_exist() ) { ?>

        <li class="product-list__item">
            <a class="offer-card <?php echo is_user_logged_in() ? ' sf_open_survey' : 'btn-modal'; ?>"
               href="<?php echo is_user_logged_in() ? '#' : '#js-modal-sign-up'; ?>">
                <div class="offer-card__body">
                    <div class="offer-card__txt content">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/base/icon-planning.svg"
                             width="64" height="64" alt="">
                        <h3>Personalize your experience</h3>
                        <p>Take a few minutes to complete our nutritional survey to build your personalized menu.</p>
                    </div>

                    <?php if ( is_user_logged_in() ) { ?>
                        <span class="offer-card__button button" href="#">
                            <?php _e( 'Take a Survey' ) ?>
                        </span>
                    <?php } else { ?>
                        <span class="offer-card__button button" href="#js-modal-sign-up">
                            <?php _e( 'Take a Survey' ) ?>
                        </span>
                    <?php } ?>

                </div>
                <picture>
                    <source
                        srcset="<?php echo get_template_directory_uri(); ?>/assets/img/base/personalize-your-experience.webp"
                        type="image/webp">
                    <img class="offer-card__bg"
                         src="<?php echo get_template_directory_uri(); ?>/assets/img/base/personalize-your-experience.jpg"
                         alt="">
                </picture>
            </a>
        </li>

        <?php
    }
}

add_action( 'woocommerce_offer_card', 'op_offer_card' );
