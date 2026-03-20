<?php

namespace JFB_QPay\Rest;

use JFB_QPay\API\Qpay_Api;
use JFB_QPay\DB\Table_Manager;
use JFB_Modules\Gateways\Module as GM;

class Callback_Handler {

	public function handle( $request ) {
		$params = $request->get_params();
		$invoice_id = $params['invoice_id'] ?? '';

		if ( ! $invoice_id ) {
			return new \WP_REST_Response( array( 'message' => 'Missing invoice_id' ), 400 );
		}

		$transaction = Table_Manager::get_by_invoice( $invoice_id );
		if ( ! $transaction ) {
			return new \WP_REST_Response( array( 'message' => 'Transaction not found' ), 404 );
		}

		// Verify with QPay API
		$settings = GM::instance()->get_global_settings( 'qpay' );
		$client_id     = $settings['client_id'] ?? '';
		$client_secret = $settings['client_secret'] ?? '';
		$is_sandbox    = ! empty( $settings['is_sandbox'] );

		$api = new Qpay_Api( $client_id, $client_secret, $is_sandbox );
		$check = $api->check_payment( $invoice_id );

		if ( isset( $check['rows'] ) && ! empty( $check['rows'] ) ) {
			$status = 'paid'; // simplify
			Table_Manager::update_status( $invoice_id, $status );
			return new \WP_REST_Response( array( 'message' => 'Success' ), 200 );
		}

		return new \WP_REST_Response( array( 'message' => 'Payment not verified' ), 200 );
	}
}
