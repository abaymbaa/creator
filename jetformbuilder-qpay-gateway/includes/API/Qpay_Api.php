<?php

namespace JFB_QPay\API;

class Qpay_Api {

	private $client_id;
	private $client_secret;
	private $is_sandbox;
	private $base_url = 'https://merchant.qpay.mn/v2';

	public function __construct( $client_id, $client_secret, $is_sandbox = false ) {
		$this->client_id     = $client_id;
		$this->client_secret = $client_secret;
		$this->is_sandbox     = $is_sandbox;
	}

	private function log( $message, $data = array() ) {
		if ( class_exists( '\Jet_Form_Builder\Dev_Log' ) ) {
			\Jet_Form_Builder\Dev_Log::instance()->info( $message, $data );
		}
	}

	public function get_access_token() {
		$transient_key = 'jfb_qpay_token_' . md5( $this->client_id );
		$token = get_transient( $transient_key );

		if ( $token ) {
			return $token;
		}

		$auth_url = $this->base_url . '/auth/token';
		$response = wp_remote_post( $auth_url, array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),
			),
		) );

		if ( is_wp_error( $response ) ) {
			$this->log( 'QPay Auth Error', array( 'error' => $response->get_error_message() ) );
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['access_token'] ) ) {
			$token = $body['access_token'];
			$expires_in = isset( $body['expires_in'] ) ? (int) $body['expires_in'] : ( 24 * 3600 );
			set_transient( $transient_key, $token, $expires_in - 60 ); // Cache slightly less than expiry
			return $token;
		}

		return false;
	}

	public function create_invoice( $args ) {
		$token = $this->get_access_token();
		if ( ! $token ) {
			return array( 'error' => 'Failed to get access token' );
		}

		$url = $this->base_url . '/invoice';
		$response = wp_remote_post( $url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
			'body' => json_encode( $args ),
		) );

		if ( is_wp_error( $response ) ) {
			$this->log( 'QPay Invoice Error', array( 'error' => $response->get_error_message(), 'args' => $args ) );
			return array( 'error' => $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			$this->log( 'QPay Invoice API Error', array( 'body' => $body, 'args' => $args ) );
		}

		return $body;
	}

	public function check_payment( $invoice_id ) {
		$token = $this->get_access_token();
		if ( ! $token ) {
			return array( 'error' => 'Failed to get access token' );
		}

		$url = $this->base_url . '/payment/check';
		$response = wp_remote_post( $url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
			'body' => json_encode( array(
				'object_type' => 'INVOICE',
				'object_id'   => $invoice_id,
			) ),
		) );

		if ( is_wp_error( $response ) ) {
			$this->log( 'QPay Check Error', array( 'error' => $response->get_error_message(), 'invoice_id' => $invoice_id ) );
			return array( 'error' => $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			$this->log( 'QPay Check API Error', array( 'body' => $body, 'invoice_id' => $invoice_id ) );
		}

		return $body;
	}
}
