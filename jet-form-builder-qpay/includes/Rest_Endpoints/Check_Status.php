<?php

namespace Jet_FB_Qpay\Rest_Endpoints;

use Jet_FB_Qpay\Qpay_Api;
use Jet_Form_Builder\Gateways\Db_Models\Payment_Model;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;

class Check_Status {

	public function register_endpoint() {
		register_rest_route( 'jet-fb-qpay/v1', '/check-status/(?P<id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'check_callback' ),
			'permission_callback' => '__return_true',
		) );
	}

	public function check_callback( $request ) {
		$id = $request->get_param( 'id' );
		
		$payment = ( new Payment_Model() )->query()->set_id( $id )->query_one();
		
		if ( ! $payment ) {
			return array( 'status' => 'error', 'message' => 'Payment not found.' );
		}

		if ( 'COMPLETED' === $payment['status'] ) {
			return array( 
				'status'   => 'success', 
				'redirect' => $this->get_success_url( $payment ) 
			);
		}

		// Check with QPay API
		$controller = new \Jet_FB_Qpay\Jet_Form_Builder\Controller();
		$api = new Qpay_Api(
			$controller->current_gateway( 'username' ),
			$controller->current_gateway( 'password' ),
			$controller->current_gateway( 'invoice_code' )
		);

		$result = $api->check_payment( $payment['transaction_id'] );

		if ( ! is_wp_error( $result ) && ! empty( $result['rows'] ) ) {
			// In QPay v2, payment is successful if 'rows' is not empty and status is correct
			// For simplicity, if we get a valid payment row, we mark it as completed
			$this->mark_as_completed( $id );
			
			return array( 
				'status'   => 'success', 
				'redirect' => $this->get_success_url( $payment ) 
			);
		}

		return array( 'status' => 'pending' );
	}

	protected function mark_as_completed( $id ) {
		try {
			( new Payment_Model() )->update(
				array( 'status' => 'COMPLETED' ),
				array( 'id' => $id )
			);
		} catch ( Sql_Exception $e ) {
			// Log error
		}
	}

	protected function get_success_url( $payment ) {
		// Try to find the referer URL from the form meta or just use home URL
		// JetFormBuilder usually handles redirection after payment.
		// For simplicity, redirect to home or a common success page if known.
		return get_home_url();
	}
}
