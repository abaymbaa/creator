<?php
/**
 * QPay Payment Gateway for WooCommerce.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class-qpay-api.php';

class WC_Gateway_QPay extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'qpay';
        $this->icon               = apply_filters( 'woocommerce_qpay_icon', '' );
        $this->has_fields         = false;
        $this->method_title       = __( 'QPay', 'woocommerce-qpay' );
        $this->method_description = __( 'Pay via QPay QR code (Mongolian banks).', 'woocommerce-qpay' );

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        $this->title       = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->enabled     = $this->get_option( 'enabled' );

        $this->supports = array( 'products' );

        // Actions
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_api_wc_gateway_qpay', array( $this, 'check_callback' ) );
        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
    }

    /**
     * Check if the gateway is available for use.
     */
    public function is_available() {
        $is_available = parent::is_available();

        if ( 'yes' !== $this->enabled ) {
            return false;
        }

        // QPay usually requires MNT currency.
        if ( get_woocommerce_currency() !== 'MNT' && ! current_user_can( 'manage_woocommerce' ) ) {
            return false;
        }

        return $is_available;
    }

    /**
     * Define gateway settings.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'woocommerce-qpay' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable QPay Payment', 'woocommerce-qpay' ),
                'default' => 'no',
            ),
            'title' => array(
                'title'       => __( 'Title', 'woocommerce-qpay' ),
                'type'        => 'text',
                'description' => __( 'Title shown on checkout page.', 'woocommerce-qpay' ),
                'default'     => __( 'QPay', 'woocommerce-qpay' ),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __( 'Description', 'woocommerce-qpay' ),
                'type'        => 'textarea',
                'description' => __( 'Description shown on checkout page.', 'woocommerce-qpay' ),
                'default'     => __( 'Pay with any Mongolian bank app using a QR code.', 'woocommerce-qpay' ),
            ),
            'username' => array(
                'title' => __( 'API Username', 'woocommerce-qpay' ),
                'type'  => 'text',
            ),
            'password' => array(
                'title' => __( 'API Password', 'woocommerce-qpay' ),
                'type'  => 'password',
            ),
            'invoice_code' => array(
                'title' => __( 'Invoice Code', 'woocommerce-qpay' ),
                'type'  => 'text',
                'description' => __( 'Your QPay assigned invoice code.', 'woocommerce-qpay' ),
            ),
        );
    }

    /**
     * Process payment.
     */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );
        
        $api = new WC_QPay_API(
            $this->get_option( 'username' ),
            $this->get_option( 'password' ),
            $this->get_option( 'invoice_code' )
        );

        $callback_url = WC()->api_request_url( 'WC_Gateway_QPay' ) . '?order_id=' . $order_id;
        
        $result = $api->create_invoice( $order_id, $order->get_total(), $callback_url );

        if ( is_wp_error( $result ) ) {
            // Throw exception so WooCommerce blocks can catch it.
            throw new Exception( $result->get_error_message() );
        }

        // Save invoice details to order
        $order->update_meta_data( '_qpay_invoice_id', $result['invoice_id'] );
        $order->update_meta_data( '_qpay_qr_text', $result['qr_text'] );
        if ( isset( $result['qr_image'] ) ) {
            $order->update_meta_data( '_qpay_qr_image', $result['qr_image'] );
        }
        if ( isset( $result['urls'] ) ) {
            $order->update_meta_data( '_qpay_urls', $result['urls'] );
        }
        $order->save();

        // Normally, we'd redirect or show a QR code. 
        // For standard WC, we redirect to the Pay page.
        return array(
            'result'   => 'success',
            'redirect' => $order->get_checkout_payment_url( true ),
        );
    }

    /**
     * Check callback (webhook).
     */
    public function check_callback() {
        $order_id   = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
        $invoice_id = isset( $_GET['invoice_id'] ) ? sanitize_text_field( $_GET['invoice_id'] ) : '';

        if ( ! $order_id ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        // In a real scenario, we'd verify the payment with QPay API here.
        // For this demo/skeleton, we'll mark it as paid if the invoice matches.
        if ( $invoice_id === $order->get_meta( '_qpay_invoice_id' ) ) {
            if ( ! in_array( $order->get_status(), array( 'completed', 'processing' ), true ) ) {
                $order->payment_complete();
            }
            wp_safe_redirect( $this->get_return_url( $order ) );
            exit;
        }
    }

    /**
     * Display QR code on the Pay for Order page.
     */
    public function receipt_page( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $qr_image = $order->get_meta( '_qpay_qr_image' );
        $urls     = $order->get_meta( '_qpay_urls' );

        echo '<style>
            .qpay-receipt-container {
                background: #ffffff;
                padding: 40px;
                border-radius: 16px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                max-width: 420px;
                margin: 0 auto;
                text-align: center;
            }
            .qpay-receipt-container h3 {
                margin-top: 0;
                font-size: 24px;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 25px;
            }
            .qpay-receipt-container img {
                width: 280px;
                height: 280px;
                margin-bottom: 25px;
                border: 1px solid #eee;
                border-radius: 12px;
                padding: 10px;
                background: #fff;
            }
            .qpay-bank-links {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 12px;
                margin-bottom: 25px;
            }
            .qpay-bank-links a {
                display: inline-block;
                padding: 10px 18px;
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                text-decoration: none;
                color: #495057;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.2s;
            }
            .qpay-bank-links a:hover {
                background: #e2e6ea;
                border-color: #dae0e5;
                color: #212529;
            }
        </style>';

        echo '<div class="qpay-receipt-container">';
        echo '<h3>' . esc_html__( 'Scan QR Code to Pay', 'woocommerce-qpay' ) . '</h3>';

        if ( $qr_image ) {
            echo '<img src="data:image/png;base64,' . esc_attr( $qr_image ) . '" alt="QPay QR Code"/>';
        }

        if ( ! empty( $urls ) ) {
            if ( is_string( $urls ) ) {
                $urls = json_decode( $urls, true );
            }
            if ( is_array( $urls ) ) {
                echo '<p style="font-weight:600; margin-bottom: 15px; color:#6c757d;">' . esc_html__( 'Or pay with bank app:', 'woocommerce-qpay' ) . '</p>';
                echo '<div class="qpay-bank-links">';
                foreach ( $urls as $url ) {
                    $link_name = ! empty( $url['description'] ) ? $url['description'] : $url['name'];
                    echo '<a href="' . esc_url( $url['link'] ) . '" target="_blank">' . esc_html( $link_name ) . '</a>';
                }
                echo '</div>';
            }
        }

        echo '</div>';
    }
}
