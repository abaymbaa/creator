<?php
/**
 * QPay API Client
 *
 * Handles all communication with the QPay v2 REST API.
 * Modeled after the existing RazorpayAPI pattern.
 *
 * @package CreatorLMS_QPay
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

class QPayAPI {

    /**
     * QPay Client ID.
     * @var string
     */
    private static $client_id = '';

    /**
     * QPay Client Secret.
     * @var string
     */
    private static $client_secret = '';

    /**
     * Whether to use sandbox environment.
     * @var bool
     */
    private static $sandbox = true;

    /**
     * Sandbox base URL.
     */
    const SANDBOX_URL = 'https://merchant-sandbox.qpay.mn';

    /**
     * Production base URL.
     */
    const PRODUCTION_URL = 'https://merchant.qpay.mn';

    /**
     * Transient key for caching the access token.
     */
    const TOKEN_TRANSIENT_KEY = 'crlms_qpay_access_token';

    /**
     * Transient key for caching the refresh token.
     */
    const REFRESH_TOKEN_TRANSIENT_KEY = 'crlms_qpay_refresh_token';

    /**
     * Use environment-specific transient keys so sandbox/live tokens don't collide.
     */
    private static function token_transient_key() {
        return self::TOKEN_TRANSIENT_KEY . ( self::$sandbox ? '_sandbox' : '_live' );
    }

    private static function refresh_transient_key() {
        return self::REFRESH_TOKEN_TRANSIENT_KEY . ( self::$sandbox ? '_sandbox' : '_live' );
    }

    /**
     * Set API credentials.
     *
     * @param string $client_id QPay Client ID.
     * @param string $client_secret QPay Client Secret.
     * @param bool $sandbox Whether to use sandbox.
     */
    public static function set_credentials( $client_id, $client_secret, $sandbox = true ) {
        self::$client_id     = $client_id;
        self::$client_secret = $client_secret;
        self::$sandbox       = $sandbox;
    }

    /**
     * Get the base URL based on environment.
     *
     * @return string
     */
    private static function get_base_url() {
        return self::$sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL;
    }

    /**
     * Get an access token from QPay.
     * Caches the token as a WordPress transient.
     *
     * @param bool $force Force a new token even if cached.
     * @return string|WP_Error Access token or error.
     */
    public static function get_token( $force = false ) {
        if ( ! $force ) {
            $cached_token = get_transient( self::token_transient_key() );
            if ( $cached_token ) {
                return $cached_token;
            }
        }

        if ( empty( self::$client_id ) || empty( self::$client_secret ) ) {
            return new \WP_Error( 'qpay_credentials_missing', __( 'QPay API credentials are not configured.', 'creatorlms-qpay' ) );
        }

        $url  = self::get_base_url() . '/v2/auth/token';
        $args = array(
            'method'  => 'POST',
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( self::$client_id . ':' . self::$client_secret ),
                'Content-Type'  => 'application/json',
            ),
            'timeout' => 30,
            'body'    => '{}',
        );

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            error_log( 'QPay Auth Error: ' . $response->get_error_message() );
            return $response;
        }

        $http_code = wp_remote_retrieve_response_code( $response );
        $body      = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $http_code < 200 || $http_code >= 300 ) {
            $error_msg = isset( $body['message'] ) ? $body['message'] : __( 'Failed to authenticate with QPay.', 'creatorlms-qpay' );
            error_log( "QPay Auth Error (HTTP {$http_code}): " . print_r( $body, true ) );
            return new \WP_Error( 'qpay_auth_error', $error_msg );
        }

        if ( empty( $body['access_token'] ) ) {
            return new \WP_Error( 'qpay_auth_error', __( 'No access token returned by QPay.', 'creatorlms-qpay' ) );
        }

        // Cache access token for 1 hour (QPay tokens typically last ~2 hours).
        set_transient( self::token_transient_key(), $body['access_token'], HOUR_IN_SECONDS );

        // Cache refresh token for 24 hours.
        if ( ! empty( $body['refresh_token'] ) ) {
            set_transient( self::refresh_transient_key(), $body['refresh_token'], DAY_IN_SECONDS );
        }

        return $body['access_token'];
    }

    /**
     * Refresh an expired access token.
     *
     * @return string|WP_Error New access token or error.
     */
    public static function refresh_token() {
        $refresh_token = get_transient( self::refresh_transient_key() );

        if ( empty( $refresh_token ) ) {
            // No refresh token, get a brand new token.
            return self::get_token( true );
        }

        $url  = self::get_base_url() . '/v2/auth/refresh';
        $args = array(
            'method'  => 'POST',
            'headers' => array(
                'Authorization' => 'Bearer ' . $refresh_token,
                'Content-Type'  => 'application/json',
            ),
            'timeout' => 30,
            'body'    => '{}',
        );

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            error_log( 'QPay Token Refresh Error: ' . $response->get_error_message() );
            // Fallback to fresh token.
            return self::get_token( true );
        }

        $http_code = wp_remote_retrieve_response_code( $response );
        $body      = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $http_code < 200 || $http_code >= 300 || empty( $body['access_token'] ) ) {
            // Refresh failed, try fresh auth.
            return self::get_token( true );
        }

        set_transient( self::token_transient_key(), $body['access_token'], HOUR_IN_SECONDS );

        if ( ! empty( $body['refresh_token'] ) ) {
            set_transient( self::refresh_transient_key(), $body['refresh_token'], DAY_IN_SECONDS );
        }

        return $body['access_token'];
    }

    /**
     * Make an authenticated request to the QPay API.
     *
     * @param string $endpoint API endpoint path (e.g., '/v2/invoice').
     * @param array  $payload  Request body data.
     * @param string $method   HTTP method.
     * @param bool   $retry    Whether this is a retry after token refresh.
     * @return array|WP_Error  Decoded response or error.
     */
    private static function request( $endpoint, $payload = array(), $method = 'POST', $retry = false ) {
        $token = self::get_token();

        if ( is_wp_error( $token ) ) {
            return $token;
        }

        $url  = self::get_base_url() . $endpoint;
        $args = array(
            'method'  => strtoupper( $method ),
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ),
            'timeout' => 60,
        );

        if ( ! empty( $payload ) && in_array( $method, array( 'POST', 'PUT' ), true ) ) {
            $args['body'] = wp_json_encode( $payload );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            error_log( 'QPay API Request Error: ' . $response->get_error_message() );
            return $response;
        }

        $http_code = wp_remote_retrieve_response_code( $response );
        $body      = json_decode( wp_remote_retrieve_body( $response ), true );

        // Token expired â€” refresh and retry once.
        if ( 401 === $http_code && ! $retry ) {
            $new_token = self::refresh_token();
            if ( ! is_wp_error( $new_token ) ) {
                return self::request( $endpoint, $payload, $method, true );
            }
            return $new_token;
        }

        if ( $http_code >= 200 && $http_code < 300 ) {
            return $body;
        }

        $error_message = __( 'QPay API Error', 'creatorlms-qpay' );
        if ( isset( $body['message'] ) ) {
            $error_message = $body['message'];
        }

        error_log( "QPay API Error ({$endpoint}) - HTTP {$http_code}: " . print_r( $body, true ) );
        return new \WP_Error( 'qpay_api_error', $error_message, array( 'status' => $http_code, 'response_body' => $body ) );
    }

    /**
     * Create a QPay invoice.
     *
     * @param array $data Invoice data.
     * @return array|WP_Error Invoice response (invoice_id, qr_text, qr_image, urls[]).
     */
    public static function create_invoice( $data ) {
        return self::request( '/v2/invoice', $data, 'POST' );
    }

    /**
     * Check payment status for an invoice.
     *
     * @param string $invoice_id QPay invoice ID.
     * @return array|WP_Error Payment check response (count, paid_amount, rows[]).
     */
    public static function check_payment( $invoice_id ) {
        $payload = array(
            'object_type' => 'INVOICE',
            'object_id'   => $invoice_id,
            'offset'      => array(
                'page_number' => 1,
                'page_limit'  => 100,
            ),
        );
        return self::request( '/v2/payment/check', $payload, 'POST' );
    }

    /**
     * Get invoice details.
     *
     * @param string $invoice_id QPay invoice ID.
     * @return array|WP_Error Invoice data.
     */
    public static function get_invoice( $invoice_id ) {
        return self::request( '/v2/invoice/' . $invoice_id, array(), 'GET' );
    }

    /**
     * Cancel an invoice.
     *
     * @param string $invoice_id QPay invoice ID.
     * @return array|WP_Error Response.
     */
    public static function cancel_invoice( $invoice_id ) {
        return self::request( '/v2/invoice/' . $invoice_id, array(), 'DELETE' );
    }

    /**
     * Clear cached tokens.
     */
    public static function clear_tokens() {
        delete_transient( self::TOKEN_TRANSIENT_KEY . '_sandbox' );
        delete_transient( self::TOKEN_TRANSIENT_KEY . '_live' );
        delete_transient( self::REFRESH_TOKEN_TRANSIENT_KEY . '_sandbox' );
        delete_transient( self::REFRESH_TOKEN_TRANSIENT_KEY . '_live' );
    }
}



