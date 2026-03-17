<?php

namespace Jet_FB_Qpay;

class Qpay_Api {

	private $username;
	private $password;
	private $invoice_code;
	private $base_url = 'https://merchant.qpay.mn/v2/';

	public function __construct( $username, $password, $invoice_code ) {
		$this->username     = $username;
		$this->password     = $password;
		$this->invoice_code = $invoice_code;
	}

	public function get_access_token() {
		$token = get_transient( 'jet_fb_qpay_token' );
		if ( $token ) {
			return $token;
		}

		$username = $this->username;
		$password = $this->password;

		if ( empty( $username ) || empty( $password ) ) {
			return new \WP_Error( 
				'qpay_auth_missing_creds', 
				sprintf( 'QPay API credentials are missing. (Username: %s, Password: %s)', 
					$username ? 'SET' : 'EMPTY', 
					$password ? 'SET' : 'EMPTY' 
				)
			);
		}

		$auth_string = base64_encode( $username . ':' . $password );

		$response = wp_remote_post( $this->base_url . 'auth/token', array(
			'headers' => array(
				'Authorization' => 'Basic ' . $auth_string,
			),
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body_str = wp_remote_retrieve_body( $response );
		$body = json_decode( $body_str, true );

		if ( 200 !== $code ) {
			$error_msg = $body['message'] ?? $body['error_description'] ?? 'No error message provided';
			return new \WP_Error( 
				'qpay_api_error', 
				sprintf( 'QPay Auth Failed (HTTP %d): %s. Body: %s', $code, $error_msg, $body_str )
			);
		}

		if ( ! isset( $body['access_token'] ) ) {
			return new \WP_Error( 'qpay_auth_json_error', 'QPay API response missing access_token. Body: ' . $body_str );
		}

		set_transient( 'jet_fb_qpay_token', $body['access_token'], 50 * MINUTE_IN_SECONDS );

		return $body['access_token'];
	}

	public function create_invoice( $sender_invoice_no, $amount, $callback_url, $description = '' ) {
		$token = $this->get_access_token();
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$data = array(
			'invoice_code'          => $this->invoice_code,
			'sender_invoice_no'     => (string) $sender_invoice_no,
			'invoice_receiver_code' => 'terminal',
			'invoice_description'   => $description ?: 'Payment for Invoice #' . $sender_invoice_no,
			'amount'                => (float) $amount,
			'callback_url'          => $callback_url,
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

	public function check_payment( $invoice_id ) {
		$token = $this->get_access_token();
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$payload = array(
			'object_type' => 'INVOICE',
			'object_id'   => $invoice_id,
			'offset'      => array(
				'page_number' => 1,
				'page_limit'  => 100,
			),
		);

		$response = wp_remote_post( $this->base_url . 'payment/check', array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
			'body'    => json_encode( $payload ),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}
}
