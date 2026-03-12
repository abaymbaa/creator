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
        add_action( 'wp_ajax_wc_qpay_check_payment', array( $this, 'ajax_check_payment' ) );
        add_action( 'wp_ajax_nopriv_wc_qpay_check_payment', array( $this, 'ajax_check_payment' ) );
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
     * Display QR code and polling script on the Pay for Order page.
     */
    public function receipt_page( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        $qr_image = $order->get_meta( '_qpay_qr_image' );
        $urls     = $order->get_meta( '_qpay_urls' );

        echo '<style>
            .qpay-modal-overlay {
                position: fixed;
                top: 0; left: 0; width: 100vw; height: 100vh;
                background: rgba(0,0,0,0.6);
                backdrop-filter: blur(5px);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 999999;
            }
            .qpay-receipt-container {
                background: #ffffff;
                padding: 40px;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                max-width: 420px;
                width: 100%;
                text-align: center;
                position: relative;
                animation: qpayModalSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }
            @keyframes qpayModalSlideIn {
                0% { opacity: 0; transform: translateY(30px) scale(0.95); }
                100% { opacity: 1; transform: translateY(0) scale(1); }
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
            #qpay-payment-status {
                padding: 16px;
                border-radius: 8px;
                background: #e3f2fd;
                color: #1565c0;
                font-weight: 600;
                font-size: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }
            .qpay-spinner {
                width: 20px; height: 20px;
                border: 3px solid rgba(21,101,192,0.2);
                border-top-color: #1565c0;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            @keyframes spin { 100% { transform: rotate(360deg); } }
        </style>';

        echo '<div class="qpay-modal-overlay" id="qpay-modal-overlay-wrap">';
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

        echo '<div id="qpay-payment-status" style="display:flex; flex-direction:column; align-items:center;">';
        echo '<button type="button" onclick="window.qpayCheckStatus()" id="qpay-check-payment-btn" style="background:#1565c0; color:#fff; border:none; padding:12px 24px; border-radius:8px; font-weight:600; cursor:pointer; font-size:16px; margin-top:10px; transition:background 0.2s; position:relative; z-index:9999999; pointer-events:auto;">' . esc_html__( 'Check Payment', 'woocommerce-qpay' ) . '</button>';
        echo '<div id="qpay-payment-message" style="font-size:14px; font-weight:600; text-align:center;"></div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        $ajax_url = admin_url( 'admin-ajax.php' );
        $nonce    = wp_create_nonce( 'wc_qpay_check_payment' );
        $redirect = $this->get_return_url( $order );

        echo "<script>
            window.qpayCheckStatus = function() {
                var btn = document.getElementById('qpay-check-payment-btn');
                var msg = document.getElementById('qpay-payment-message');
                if (!btn) return;
                
                btn.disabled = true;
                btn.style.opacity = '0.7';
                btn.innerText = '" . esc_js( __( 'Checking...', 'woocommerce-qpay' ) ) . "';
                msg.innerText = '';
                msg.style.cssText = '';
                
                jQuery.post('" . esc_js( $ajax_url ) . "', {
                    action: 'wc_qpay_check_payment',
                    nonce: '" . esc_js( $nonce ) . "',
                    order_id: " . absint( $order_id ) . "
                }, function(response) {
                    console.log('QPay Check Response:', response);

                    if (response.success && response.data && response.data.status === 'paid') {
                        msg.innerHTML = '&#10003; " . esc_js( __( 'Payment confirmed! Redirecting...', 'woocommerce-qpay' ) ) . "';
                        msg.style.cssText = 'background:#e8f5e9; color:#2e7d32; padding:12px; border-radius:8px; width:100%; margin-top:15px; display:block;';
                        setTimeout(function() {
                            window.location.href = '" . esc_js( $redirect ) . "';
                        }, 1000);
                    } else if (response.success && response.data && response.data.status === 'pending') {
                        msg.innerText = '" . esc_js( __( 'Payment not received yet. Please try again.', 'woocommerce-qpay' ) ) . "';
                        msg.style.cssText = 'background:#fff3cd; color:#856404; padding:12px; border-radius:8px; width:100%; margin-top:15px; display:block;';
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        btn.innerText = '" . esc_js( __( 'Check Payment', 'woocommerce-qpay' ) ) . "';
                    } else if (!response.success && response.data && response.data.message) {
                        msg.innerText = 'Error: ' + response.data.message;
                        msg.style.cssText = 'background:#fcf0f0; color:#a00; padding:12px; border-radius:8px; width:100%; margin-top:15px; display:block;';
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        btn.innerText = '" . esc_js( __( 'Check Payment', 'woocommerce-qpay' ) ) . "';
                    }
                }).fail(function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    msg.innerText = '" . esc_js( __( 'Connection error. Please try again.', 'woocommerce-qpay' ) ) . "';
                    msg.style.cssText = 'background:#fcf0f0; color:#a00; padding:12px; border-radius:8px; width:100%; margin-top:15px; display:block;';
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.innerText = '" . esc_js( __( 'Check Payment', 'woocommerce-qpay' ) ) . "';
                });
            };
        </script>";
    }

    /**
     * AJAX handler to poll payment status.
     */
    public function ajax_check_payment() {
        check_ajax_referer( 'wc_qpay_check_payment', 'nonce' );

        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        if ( ! $order_id ) {
            wp_send_json_error( array( 'message' => 'Invalid order' ) );
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( array( 'message' => 'Order not found' ) );
        }

        // Check if already complete
        if ( in_array( $order->get_status(), array( 'processing', 'completed' ), true ) ) {
            wp_send_json_success( array( 'status' => 'paid' ) );
        }

        $invoice_id = $order->get_meta( '_qpay_invoice_id' );
        if ( ! $invoice_id ) {
            wp_send_json_error( array( 'message' => 'Invoice not found' ) );
        }

        $api = new WC_QPay_API(
            $this->get_option( 'username' ),
            $this->get_option( 'password' ),
            $this->get_option( 'invoice_code' )
        );

        $result = $api->check_payment( $invoice_id );

        $logger = wc_get_logger();
        $logger->debug( 'QPay Check Payment Result for Invoice ' . $invoice_id . ': ' . print_r( $result, true ), array( 'source' => 'qpay' ) );

        if ( is_wp_error( $result ) ) {
            $logger->error( 'QPay Check Payment Error: ' . $result->get_error_message(), array( 'source' => 'qpay' ) );
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        $count = isset( $result['count'] ) ? (int) $result['count'] : 0;
        $paid_amount = isset( $result['paid_amount'] ) ? (float) $result['paid_amount'] : 0;

        if ( $count > 0 && $paid_amount > 0 ) {
            error_log( 'QPay Check Payment Success! Amount paid: ' . $paid_amount );
            $order->payment_complete();
            
            $payment_id = '';
            if ( ! empty( $result['rows'] ) && isset( $result['rows'][0]['payment_id'] ) ) {
                $payment_id = $result['rows'][0]['payment_id'];
                $order->update_meta_data( '_qpay_payment_id', $payment_id );
                $order->update_meta_data( '_transaction_id', $payment_id );
                $order->save();
            }

            $order->add_order_note( sprintf( __( 'QPay payment confirmed. Payment ID: %s', 'woocommerce-qpay' ), $payment_id ) );

            wp_send_json_success( array( 'status' => 'paid' ) );
        }

        wp_send_json_success( array( 'status' => 'pending' ) );
    }
}
