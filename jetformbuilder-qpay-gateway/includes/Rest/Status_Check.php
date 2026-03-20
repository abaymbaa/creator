<?php

namespace JFB_QPay\Rest;

use JFB_QPay\DB\Table_Manager;

class Status_Check {

	public function handle( $request ) {
		$invoice_id = $request->get_param( 'invoice_id' );

		if ( ! $invoice_id ) {
			return new \WP_REST_Response( array( 'error' => 'Missing invoice_id' ), 400 );
		}

		$transaction = Table_Manager::get_by_invoice( $invoice_id );

		if ( ! $transaction ) {
			return new \WP_REST_Response( array( 'error' => 'Transaction not found' ), 404 );
		}

		return new \WP_REST_Response( array(
			'status' => $transaction['status'],
		), 200 );
	}
}
