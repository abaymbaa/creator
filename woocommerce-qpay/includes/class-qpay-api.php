<?php
/**
 * QPay API Wrapper.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_QPay_API {

    private $username;
    private $password;
    private $merchant_id;
    private $base_url = 'https://merchant.qpay.mn/v2/';

    public function __construct( $username, $password, $merchant_id ) {
        $this->username    = $username;
        $this->password    = $password;
        $this->merchant_id = $merchant_id;
    }

    /**
     * Get access token.
     */
    public function get_access_token() {
        $token = get_transient( 'wc_qpay_access_token' );
        if ( $token ) {
            return $token;
        }

        $auth_string = base64_encode( $this->username . ':' . $this->password );

        $response = wp_remote_post( $this->base_url . 'auth/token', array(
            'headers' => array(
                'Authorization' => 'Basic ' . $auth_string,
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! isset( $body['access_token'] ) ) {
            return new WP_Error( 'qpay_auth_error', 'Failed to get QPay access token.' );
        }

        // Cache for 50 minutes (token usually expires in 1 hour)
        set_transient( 'wc_qpay_access_token', $body['access_token'], 50 * MINUTE_IN_SECONDS );

        return $body['access_token'];
    }

    /**
     * Create an invoice.
     */
    public function create_invoice( $order_id, $amount, $callback_url ) {
        $token = $this->get_access_token();
        if ( is_wp_error( $token ) ) {
            return $token;
        }

        $data = array(
            'template_id'         => 'NON_PROMOTION_INVOICE',
            'merchant_id'         => $this->merchant_id,
            'invoice_code'        => 'WC_ORDER_' . $order_id,
            'invoice_receiver_code' => 'WC_USER_' . get_current_user_id(),
            'invoice_description' => 'Payment for WooCommerce Order #' . $order_id,
            'amount'              => $amount,
            'callback_url'        => $callback_url,
        );

        $response = wp_remote_post( $this->base_url . 'invoice', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => json_encode( $data ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return json_decode( wp_remote_retrieve_body( $response ), true );
    }

    /**
     * Clear cached tokens.
     */
    public static function clear_tokens() {
        delete_transient( 'wc_qpay_access_token' );
    }
}
