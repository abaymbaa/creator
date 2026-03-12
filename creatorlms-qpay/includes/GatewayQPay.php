<?php
/**
 * QPay Payment Gateway for CreatorLMS
 *
 * @package CreatorLMS_QPay
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

use CodeRex\Ecommerce\Abstracts\PaymentGateway;

/**
 * QPay Payment Gateway.
 *
 * Integrates QPay (qpay.mn) QR code payments into CreatorLMS.
 *
 * @since 1.0.0
 */
class GatewayQPay extends PaymentGateway {

    /**
     * QPay invoice code assigned by QPay.
     *
     * @var string
     */
    private $invoice_code;

    /**
     * QPay Client ID.
     *
     * @var string
     */
    private $client_id;

    /**
     * QPay Client Secret.
     *
     * @var string
     */
    private $client_secret;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id                   = 'qpay';
        $gateway_settings_key        = 'creatorlms_' . $this->id . '_settings';
        $this->settings              = get_option( $gateway_settings_key, array() );
        $this->title                 = $this->get_setting( 'title', __( 'QPay', 'creatorlms-qpay' ) );
        $this->description           = $this->get_setting( 'instruction', __( 'Pay via QPay QR code using your bank app.', 'creatorlms-qpay' ) );
        $this->has_fields            = false;
        $this->order_button_text     = __( 'Pay with QPay', 'creatorlms-qpay' );

        // CreatorLMS stores switch values inconsistently (e.g. "on", 1, true). Normalize to yes/no.
        $this->enabled  = $this->normalize_yesno( $this->get_setting( 'enabled', 'no' ), 'no' );
        $this->testmode = $this->normalize_yesno( $this->get_setting( 'testmode', 'no' ), 'no' );

        $this->subscription_support = false;

        // Credentials based on mode.
        $is_test             = 'yes' === $this->testmode;
        $this->client_id     = $is_test ? $this->get_setting( 'test_client_id', '' ) : $this->get_setting( 'live_client_id', '' );
        $this->client_secret = $is_test ? $this->get_setting( 'test_client_secret', '' ) : $this->get_setting( 'live_client_secret', '' );
        $this->invoice_code  = $this->get_setting( 'invoice_code', '' );

        // Set API credentials.
        if ( ! empty( $this->client_id ) && ! empty( $this->client_secret ) ) {
            QPayAPI::set_credentials( $this->client_id, $this->client_secret, $is_test );
        }

        // Hooks.
        add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
        add_action( 'wp_ajax_crlms_qpay_check_payment', array( $this, 'ajax_check_payment' ) );
        add_action( 'wp_ajax_nopriv_crlms_qpay_check_payment', array( $this, 'ajax_check_payment' ) );
        add_action( 'rest_api_init', array( $this, 'register_callback_route' ) );
        add_action( 'creator_lms_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Normalize switch/checkbox values to CreatorLMS yes/no.
     *
     * CreatorLMS UI can store switches as "on", booleans, or 0/1.
     */
    private function normalize_yesno( $value, $default = 'no' ) {
        if ( is_bool( $value ) ) {
            return $value ? 'yes' : 'no';
        }

        if ( is_numeric( $value ) ) {
            return ( (int) $value ) === 1 ? 'yes' : 'no';
        }

        $value = strtolower( trim( (string) $value ) );
        if ( '' === $value ) {
            $value = strtolower( trim( (string) $default ) );
        }

        return in_array( $value, array( 'yes', 'true', '1', 'on' ), true ) ? 'yes' : 'no';
    }
    public function get_settings() {
            $fields = array(
                array(
                    'title'             => __( 'Enable', 'creatorlms-qpay' ),
                    'short_description' => __( 'Enable QPay at checkout.', 'creatorlms-qpay' ),
                    'input_type'        => 'switch',
                    'default_value'     => 'no',
                    'option_name'       => 'enabled',
                    'value'             => $this->enabled,
                ),
                array(
                    'title'             => __( 'Title', 'creatorlms-qpay' ),
                    'short_description' => __( 'The title displayed during checkout.', 'creatorlms-qpay' ),
                    'input_type'        => 'text',
                    'default_value'     => __( 'QPay', 'creatorlms-qpay' ),
                    'option_name'       => 'title',
                    'value'             => $this->title,
                ),
                array(
                    'title'             => __( 'Instruction', 'creatorlms-qpay' ),
                    'short_description' => __( 'Instructions shown to students during checkout.', 'creatorlms-qpay' ),
                    'input_type'        => 'textarea',
                    'default_value'     => __( 'Pay via QPay QR code using your bank app.', 'creatorlms-qpay' ),
                    'option_name'       => 'instruction',
                    'value'             => $this->description,
                ),
                array(
                    'title'             => __( 'Invoice Code', 'creatorlms-qpay' ),
                    'short_description' => __( 'QPay-assigned invoice code for your merchant account.', 'creatorlms-qpay' ),
                    'input_type'        => 'text',
                    'default_value'     => '',
                    'option_name'       => 'invoice_code',
                    'value'             => $this->invoice_code,
                ),
                array(
                    'title'             => __( 'Test Mode', 'creatorlms-qpay' ),
                    'short_description' => __( 'Use QPay sandbox environment for testing. Turn this off to use your live merchant credentials.', 'creatorlms-qpay' ),
                    'input_type'        => 'switch',
                    'default_value'     => 'no',
                    'option_name'       => 'testmode',
                    'value'             => $this->testmode,
                    'conditional_logic' => array(
                        'type'     => 'control',
                        'controls' => array( 'test_client_id', 'test_client_secret', 'live_client_id', 'live_client_secret' ),
                    ),
                ),
                array(
                    'title'             => __( 'Test Client ID', 'creatorlms-qpay' ),
                    'short_description' => __( 'QPay sandbox Client ID.', 'creatorlms-qpay' ),
                    'input_type'        => 'text',
                    'default_value'     => '',
                    'option_name'       => 'test_client_id',
                    'value'             => $this->get_setting( 'test_client_id', '' ),
                    'conditional_logic' => array(
                        'type'       => 'dependent',
                        'depends_on' => 'testmode',
                        'show_when'  => 'yes',
                    ),
                ),
                array(
                    'title'             => __( 'Test Client Secret', 'creatorlms-qpay' ),
                    'short_description' => __( 'QPay sandbox Client Secret.', 'creatorlms-qpay' ),
                    'input_type'        => 'text',
                    'default_value'     => '',
                    'option_name'       => 'test_client_secret',
                    'value'             => $this->get_setting( 'test_client_secret', '' ),
                    'conditional_logic' => array(
                        'type'       => 'dependent',
                        'depends_on' => 'testmode',
                        'show_when'  => 'yes',
                    ),
                ),
                array(
                    'title'             => __( 'Live Client ID', 'creatorlms-qpay' ),
                    'short_description' => __( 'QPay production Client ID.', 'creatorlms-qpay' ),
                    'input_type'        => 'text',
                    'default_value'     => '',
                    'option_name'       => 'live_client_id',
                    'value'             => $this->get_setting( 'live_client_id', '' ),
                    'conditional_logic' => array(
                        'type'       => 'dependent',
                        'depends_on' => 'testmode',
                        'show_when'  => 'no',
                    ),
                ),
                array(
                    'title'             => __( 'Live Client Secret', 'creatorlms-qpay' ),
                    'short_description' => __( 'QPay production Client Secret.', 'creatorlms-qpay' ),
                    'input_type'        => 'text',
                    'default_value'     => '',
                    'option_name'       => 'live_client_secret',
                    'value'             => $this->get_setting( 'live_client_secret', '' ),
                    'conditional_logic' => array(
                        'type'       => 'dependent',
                        'depends_on' => 'testmode',
                        'show_when'  => 'no',
                    ),
                ),
            );
    
            return array(
                'id'                 => $this->id,
                'title'              => __( 'QPay', 'creatorlms-qpay' ),
                'description'        => __( 'QPay QR Code Payment Gateway', 'creatorlms-qpay' ),
                'icon'               => '<svg width="23" height="18" viewBox="0 0 23 18" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="21" height="16" rx="2" stroke="var(--crlms-primary-color)" stroke-width="2"/><rect x="4" y="4" width="4" height="4" fill="var(--crlms-primary-color)"/><rect x="10" y="4" width="4" height="4" fill="var(--crlms-primary-color)"/><rect x="4" y="10" width="4" height="4" fill="var(--crlms-primary-color)"/><rect x="10" y="10" width="2" height="2" fill="var(--crlms-primary-color)"/><rect x="14" y="10" width="2" height="2" fill="var(--crlms-primary-color)"/><rect x="14" y="14" width="2" height="2" fill="var(--crlms-primary-color)"/></svg>',
                'has_config'         => true,
                'subscription_support' => false,
                'settings_fields'    => $fields,
                'enabled'            => $this->enabled,
            );
        }
    
        /**
         * Enqueue scripts on checkout page.
         */
        public function payment_scripts() {
            if ( ! function_exists( 'is_creator_lms_checkout' ) || ! is_creator_lms_checkout() ) {
                return;
            }
    
            if ( 'no' === $this->enabled ) {
                return;
            }
    
            if ( empty( $this->client_id ) || empty( $this->client_secret ) ) {
                return;
            }
    
            // CSS.
            wp_enqueue_style(
                'crlms-qpay-checkout',
                CRLMS_QPAY_PLUGIN_URL . 'assets/css/qpay-checkout.css',
                array(),
                CRLMS_QPAY_VERSION
            );
    
            // JS.
            wp_register_script(
                'crlms-qpay-checkout',
                CRLMS_QPAY_PLUGIN_URL . 'assets/js/qpay-checkout.js',
                array( 'jquery' ),
                CRLMS_QPAY_VERSION,
                true
            );
    
            wp_localize_script( 'crlms-qpay-checkout', 'crlms_qpay_params', array(
                'ajax_url'            => admin_url( 'admin-ajax.php' ),
                'check_payment_nonce' => wp_create_nonce( 'crlms_qpay_check_payment_nonce' ),
                'check_action'        => 'crlms_qpay_check_payment',
                'poll_interval'       => 3000,
                'poll_timeout'        => 300000, // 5 minutes.
                'i18n'                => array(
                    'scanning_title'    => __( 'Scan QR Code to Pay', 'creatorlms-qpay' ),
                    'waiting'           => __( 'Waiting for payment...', 'creatorlms-qpay' ),
                    'expired'           => __( 'Payment session expired. Please try again.', 'creatorlms-qpay' ),
                    'error'             => __( 'Payment verification failed. Please try again.', 'creatorlms-qpay' ),
                    'pay_with_app'      => __( 'Or pay with bank app:', 'creatorlms-qpay' ),
                    'close'             => __( 'Cancel', 'creatorlms-qpay' ),
                ),
            ) );
    
            wp_enqueue_script( 'crlms-qpay-checkout' );
        }
    
        /**
         * Process payment ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã¢â‚¬Â¦Ãƒâ€šÃ‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â create QPay invoice.
         *
         * @param int  $order_id Order ID.
         * @param bool $is_subscription Whether this is a subscription.
         * @return array Result array for checkout JS.
         */
        public function process_payment( $order_id, $is_subscription = false ) {
            QPayAPI::set_credentials( $this->client_id, $this->client_secret, 'yes' === $this->testmode );
    
            $order = ecommerce_get_order( $order_id );
    
            if ( ! $order ) {
                error_log( "QPay Error: Could not retrieve order for ID: {$order_id}" );
                return array(
                    'result'  => 'failure',
                    'message' => __( 'Order not found. Please contact support.', 'creatorlms-qpay' ),
                );
            }
    
            $order_total = (float) $order->get_total();
    
            if ( $order_total <= 0 ) {
                $order->payment_complete();
                return array(
                    'result'   => 'success',
                    'redirect' => $this->get_return_url( $order ),
                );
            }
    
            // Build the callback URL for QPay to notify us.
            $callback_url = rest_url( 'creatorlms/v1/qpay/callback' );
            $callback_url = add_query_arg( 'order_id', $order_id, $callback_url );
    
            // Build invoice payload.
            $invoice_data = array(
                'invoice_code'          => $this->invoice_code,
                'sender_invoice_no'     => (string) $order_id,
                'invoice_receiver_code' => 'terminal',
                'invoice_description'   => sprintf( __( 'Order #%s', 'creatorlms-qpay' ), $order_id ),
                'amount'                => $order_total,
                'callback_url'          => $callback_url,
            );
    
            $invoice_response = QPayAPI::create_invoice( $invoice_data );
    
            if ( is_wp_error( $invoice_response ) ) {
                $error_message = $invoice_response->get_error_message();
                error_log( "QPay Invoice Error for Order {$order_id}: " . $error_message );
                $order->add_order_note( sprintf( __( 'QPay invoice creation failed: %s', 'creatorlms-qpay' ), $error_message ) );
                return array(
                    'result'  => 'failure',
                    'message' => sprintf( __( 'Could not create QPay invoice. %s', 'creatorlms-qpay' ), $error_message ),
                );
            }
    
            if ( empty( $invoice_response['invoice_id'] ) ) {
                error_log( "QPay Error: No invoice_id returned for Order {$order_id}" );
                return array(
                    'result'  => 'failure',
                    'message' => __( 'QPay invoice ID missing. Please try again.', 'creatorlms-qpay' ),
                );
            }
    
            $qpay_invoice_id = $invoice_response['invoice_id'];
    
            // Store QPay data in order meta.
            update_post_meta( $order_id, '_qpay_invoice_id', $qpay_invoice_id );
            update_post_meta( $order_id, '_payment_method', $this->id );
            update_post_meta( $order_id, '_payment_method_title', $this->title );
    
            $order->add_order_note( sprintf( __( 'QPay invoice created. Invoice ID: %s', 'creatorlms-qpay' ), $qpay_invoice_id ) );
    
            // Return data for the frontend JS to display QR code.
            return array(
                'result'           => 'success',
                'payment_method'   => $this->id,
                'order_id'         => $order_id,
                'qpay_invoice_id'  => $qpay_invoice_id,
                'qr_image'         => isset( $invoice_response['qr_image'] ) ? $invoice_response['qr_image'] : '',
                'qr_text'          => isset( $invoice_response['qr_text'] ) ? $invoice_response['qr_text'] : '',
                'urls'             => isset( $invoice_response['urls'] ) ? $invoice_response['urls'] : array(),
            );
        }
    
        /**
         * AJAX handler for polling payment status.
         */
        public function ajax_check_payment() {
            check_ajax_referer( 'crlms_qpay_check_payment_nonce', 'nonce' );
    
            $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
    
            if ( ! $order_id ) {
                wp_send_json_error( array( 'message' => __( 'Invalid order.', 'creatorlms-qpay' ) ) );
                return;
            }
    
            QPayAPI::set_credentials( $this->client_id, $this->client_secret, 'yes' === $this->testmode );
    
            $qpay_invoice_id = get_post_meta( $order_id, '_qpay_invoice_id', true );
    
            if ( empty( $qpay_invoice_id ) ) {
                wp_send_json_error( array( 'message' => __( 'QPay invoice not found for this order.', 'creatorlms-qpay' ) ) );
                return;
            }
    
            $order = ecommerce_get_order( $order_id );
    
            if ( ! $order ) {
                wp_send_json_error( array( 'message' => __( 'Order not found.', 'creatorlms-qpay' ) ) );
                return;
            }
    
            // Check if already completed.
            if ( in_array( $order->get_status(), array( 'completed', 'processing' ), true ) ) {
                wp_send_json_success( array(
                    'status'       => 'paid',
                    'redirect_url' => $this->get_return_url( $order ),
                ) );
                return;
            }
    
            // Check payment with QPay API.
            $payment_result = QPayAPI::check_payment( $qpay_invoice_id );
    
            if ( is_wp_error( $payment_result ) ) {
                wp_send_json_error( array(
                    'status'  => 'error',
                    'message' => $payment_result->get_error_message(),
                ) );
                return;
            }
    
            $count       = isset( $payment_result['count'] ) ? (int) $payment_result['count'] : 0;
            $paid_amount = isset( $payment_result['paid_amount'] ) ? (float) $payment_result['paid_amount'] : 0;
    
            if ( $count > 0 && $paid_amount > 0 ) {
                // Payment received.
                $payment_id = '';
                if ( ! empty( $payment_result['rows'] ) && isset( $payment_result['rows'][0]['payment_id'] ) ) {
                    $payment_id = $payment_result['rows'][0]['payment_id'];
                }
    
                // Complete the order.
                $order->payment_complete( $payment_id );
    
                // Store payment details.
                update_post_meta( $order_id, '_qpay_payment_id', $payment_id );
                update_post_meta( $order_id, '_transaction_id', $payment_id );
                update_post_meta( $order_id, '_qpay_paid_amount', $paid_amount );
    
                $order->add_order_note( sprintf(
                    __( 'QPay payment confirmed. Payment ID: %1$s, Amount: %2$s MNT', 'creatorlms-qpay' ),
                    $payment_id,
                    number_format( $paid_amount, 2 )
                ) );
    
                // Trigger the same action Razorpay uses for deferred enrollment.
                do_action( 'creator_lms_checkout_after_create_order', $order, array() );
    
                wp_send_json_success( array(
                    'status'       => 'paid',
                    'redirect_url' => $this->get_return_url( $order ),
                ) );
            } else {
                // Still waiting.
                wp_send_json_success( array(
                    'status' => 'pending',
                ) );
            }
    
            wp_die();
        }
    
        /**
         * Register REST API callback route for QPay server-to-server notifications.
         */
        public function register_callback_route() {
            register_rest_route( 'creatorlms/v1', '/qpay/callback', array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'handle_callback' ),
                'permission_callback' => '__return_true',
            ) );
        }
    
        /**
         * Handle QPay callback notification.
         *
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response
         */
        public function handle_callback( $request ) {
            $order_id = $request->get_param( 'order_id' );
    
            if ( empty( $order_id ) ) {
                return new \WP_REST_Response( array( 'message' => 'Missing order_id' ), 400 );
            }
    
            $order_id = absint( $order_id );
            $order    = ecommerce_get_order( $order_id );
    
            if ( ! $order ) {
                return new \WP_REST_Response( array( 'message' => 'Order not found' ), 404 );
            }
    
            // Already completed.
            if ( in_array( $order->get_status(), array( 'completed', 'processing' ), true ) ) {
                return new \WP_REST_Response( array( 'message' => 'Order already completed' ), 200 );
            }
    
            $qpay_invoice_id = get_post_meta( $order_id, '_qpay_invoice_id', true );
    
            if ( empty( $qpay_invoice_id ) ) {
                return new \WP_REST_Response( array( 'message' => 'QPay invoice not found' ), 404 );
            }
    
            // Initialize API credentials.
            QPayAPI::set_credentials( $this->client_id, $this->client_secret, 'yes' === $this->testmode );
    
            // Verify payment via API.
            $payment_result = QPayAPI::check_payment( $qpay_invoice_id );
    
            if ( is_wp_error( $payment_result ) ) {
                error_log( "QPay Callback Error for Order {$order_id}: " . $payment_result->get_error_message() );
                return new \WP_REST_Response( array( 'message' => 'Payment check failed' ), 500 );
            }
    
            $count       = isset( $payment_result['count'] ) ? (int) $payment_result['count'] : 0;
            $paid_amount = isset( $payment_result['paid_amount'] ) ? (float) $payment_result['paid_amount'] : 0;
    
            if ( $count > 0 && $paid_amount > 0 ) {
                $payment_id = '';
                if ( ! empty( $payment_result['rows'] ) && isset( $payment_result['rows'][0]['payment_id'] ) ) {
                    $payment_id = $payment_result['rows'][0]['payment_id'];
                }
    
                $order->payment_complete( $payment_id );
    
                update_post_meta( $order_id, '_qpay_payment_id', $payment_id );
                update_post_meta( $order_id, '_transaction_id', $payment_id );
                update_post_meta( $order_id, '_qpay_paid_amount', $paid_amount );
    
                $order->add_order_note( sprintf(
                    __( 'QPay payment confirmed via callback. Payment ID: %1$s, Amount: %2$s MNT', 'creatorlms-qpay' ),
                    $payment_id,
                    number_format( $paid_amount, 2 )
                ) );
    
                do_action( 'creator_lms_checkout_after_create_order', $order, array() );
    
                return new \WP_REST_Response( array( 'message' => 'Payment confirmed' ), 200 );
            }
    
        return new \WP_REST_Response( array( 'message' => 'Payment not found' ), 200 );
    }

    /**
     * Check if gateway is available for use.
     *
     * @return bool
     */
    public function is_available() {
        return ( 'yes' === $this->enabled );
    }
}
