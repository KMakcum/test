<?php
/**
 * Order Receipt class.
 *
 * @class   SFOrderReceipt
 * @package LifeChef\Classes
 */

defined( 'ABSPATH' ) || exit;

use Dompdf\Dompdf;

/**
 * SFOrderReceipt class.
 */
class SFOrderReceipt {
    private static $_instance = null;

    /**
     * @return SFOrderReceipt
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
        add_action( 'init', [ $this, 'get_pdf' ] );
    }

    /**x
     * Show PDF file.
     */
    public function get_pdf() {
        if ( isset( $_GET['action'] ) AND $_GET['action'] == 'download_receipt' AND $_GET['order-id'] AND wp_verify_nonce( $_GET['_nonce'], 'order-receipt' ) ) {
            $data['order-id'] = (int) $_GET['order-id'];
            $this->generate( $data );
            die;
        }
    }

    /**
     * Generate PDF Order Receipt.
     *
     * @param array $data
     *
     * @return void
     */
    public function generate( $data ) {
        require_once get_template_directory() . '/inc/vendor/autoload.php';

        $data = $this->data_setup( $data );
        $this->generate_pdf( $data );
    }

    /**
     * Generate PDF.
     *
     * @param array $data
     *
     * @return void
     */
    public function generate_pdf( $data ) {
        $html_path = get_stylesheet_directory() . '/inc/pdf/html/letter-2.html';
        if ( ! file_exists( $html_path ) ) {
            echo 'Sorry:( <br>We have error with HTML template';

            return;
        }
        $html = file_get_contents( $html_path );
        $html = $this->html_preparation( $data, $html );

//        file_put_contents($html_path.'1', $html);
        $file_name = "Receipt-LifeChef-Order-{$data['order-id']}.pdf";

        $fonts   = str_replace( '\\', '/', get_template_directory() . '/inc/pdf/html/fonts' );
        $dompdf  = new Dompdf( [
            'fontDir'     => $fonts,
            'defaultFont' => 'Sora',
        ] );
        $options = $dompdf->getOptions();
        $options->getFontDir();
        $dompdf->loadHtml( $html, 'UTF-8' );
        $dompdf->setPaper( 'letter', 'portrait' );
        $dompdf->render();

        // clear buffer fix
        ob_end_clean();

        // Brother show
        $dompdf->stream( $file_name );
    }

    /**
     * Data replacement in HTML.
     *
     * @param array $data
     * @param string $html
     *
     * @return string
     */
    public function html_preparation( $data, $html ) {
        $replace = [
            '%ReceiptNumber%' => "{$data['order-id']}-01",
            '%OrderNumber%'   => $data['order-id'],
            '%ReceiptDate%'   => $data['ReceiptDate'],
            '%Bill%'          => $data['Bill'],
            '%Ship%'          => $data['Ship'],
            '%Payment%'       => $data['Payment'],
            '%ShipDate%'      => $data['ShipDate'],
            '%Products%'      => $data['Products'],
            '%Subtotal%'      => $data['Subtotal'],
            '%ShippingCost%'  => $data['ShippingCost'],
            '%Tax%'           => $data['Tax'],
            '%Total%'         => $data['Total'],
            '%Coupon%'        => $data['Coupon'],
        ];

        return strtr( $html, $replace );
    }

    /**
     * Setup data from Order.
     *
     * @param array $data
     *
     * @return array
     */
    public function data_setup( $data ) {
        $order = wc_get_order( $data['order-id'] );
        if ( ! $order ) {
            return $data;
        }

        $data['Bill'] = $order->get_billing_first_name() . '&nbsp;' . $order->get_billing_last_name() .
                        '<br>' . $order->get_billing_address_1() . '&nbsp;' . $order->get_billing_address_2() .
                        '<br>' . $order->get_billing_city() . ',&nbsp;' . $order->get_billing_state() . '&nbsp;' . $order->get_billing_postcode();

        $data['Ship'] = $order->get_shipping_first_name() . '&nbsp;' . $order->get_shipping_last_name() .
                        '<br>' . $order->get_shipping_address_1() . '&nbsp;' . $order->get_shipping_address_2() .
                        '<br>' . $order->get_shipping_city() . ',&nbsp;' . $order->get_shipping_state() . '&nbsp;' . $order->get_shipping_postcode();

        $data['Payment'] = '••••';
        $user_token      = WC_Payment_Tokens::get_customer_default_token( get_current_user_id() );
        if ( is_object( $user_token ) ) {
            $token_data = $user_token->get_data();
            if ( ! empty( $token_data ) AND isset( $token_data['last4'] ) ) {
                $data['Payment'] = $token_data['card_type'] . ', •••• ' . $token_data['last4'];
            }
        }

        $data['ReceiptDate'] = date( 'D d Y', time() );
        $data['ShipDate']    = date( 'D d Y', strtotime( $order->get_meta( 'op_next_delivery', 1 ) ) );

        $items         = $order->get_items();
        $i             = 0;
        $products_html = '';

        foreach ( $items as $item ) {
            $i ++;
            $product = $item->get_product();

            /** @var WC_Product $product */
            $title = $product->get_meta( 'op_post_title' );

            if ( ! $title ) {
                $title = $product->get_title();
            }

            $cost       = number_format( $product->get_price(), 2, '.', '' );
            $count      = $item->get_quantity();
            $cost_total = number_format( $cost * $count, 2, '.', '' );

            $products_html .= "<tr>
                            <td class='va--top'>{$i}</td>
                            <td>{$title}</td>
                            <td>\${$cost}</td>
                            <td>{$count}</td>
                            <td>\${$cost_total}</td>
                        </tr>";
        }

        $data['Products'] = $products_html;

        $data['Subtotal'] = number_format( $order->get_subtotal(), 2, '.', '' );

        $data['ShippingCost'] = number_format( $order->get_shipping_total(), 2, '.', '' );

        foreach ( $order->get_tax_totals() as $tax_total ) {
            $data['Tax'] = "<tr>
                            <th colspan=\"4\">Tax</th>
                            <td>\${$tax_total->amount}</td>
                        </tr>";
            break;
        }

        $data['Coupon'] = '';

        foreach ( $order->get_coupons() as $coupon ) {
            $discount = number_format( $coupon->get_discount(), 2, '.', '' );
            $data['Coupon'] .= "<tr>
                            <th colspan=\"4\">Promo code: '{$coupon->get_name()}'</th>
                            <td>−\${$discount}</td>
                        </tr>";
        }

        $data['Total'] = number_format( $order->get_total(), 2, '.', '' );

        return $data;
    }
}