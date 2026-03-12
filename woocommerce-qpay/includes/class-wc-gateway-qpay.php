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
            'merchant_id' => array(
                'title' => __( 'Merchant ID', 'woocommerce-qpay' ),
                'type'  => 'text',
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
            $this->get_option( 'merchant_id' )
        );

        $callback_url = WC()->api_request_url( 'WC_Gateway_QPay' ) . '?order_id=' . $order_id;
        
        $result = $api->create_invoice( $order_id, $order->get_total(), $callback_url );

        if ( is_wp_error( $result ) ) {
            wc_add_notice( $result->get_error_message(), 'error' );
            return;
        }

        // Save invoice details to order
        $order->update_meta_data( '_qpay_invoice_id', $result['invoice_id'] );
        $order->update_meta_data( '_qpay_qr_text', $result['qr_text'] );
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
            $order->payment_complete();
            wc_reduce_stock_levels( $order_id );
            
            wp_safe_redirect( $this->get_return_url( $order ) );
            exit;
        }
    }
}
