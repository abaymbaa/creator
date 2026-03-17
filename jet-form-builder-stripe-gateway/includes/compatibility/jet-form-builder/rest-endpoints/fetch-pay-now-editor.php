<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Rest_Endpoints;

use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Actions\Retrieve_Balance;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Rest_Api\Rest_Api_Endpoint_Base;

class Fetch_Pay_Now_Editor extends Rest_Api_Endpoint_Base {

	public static function get_rest_base() {
		return 'stripe-base-fetch';
	}

	public static function get_methods() {
		return \WP_REST_Server::CREATABLE;
	}

	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function run_callback( \WP_REST_Request $request ) {
		$body   = $request->get_json_params();
		$secret = $body['secret'] ?? '';

		$request = ( new Retrieve_Balance() )
			->set_bearer_auth( $secret );

		try {
			$response = $request->send_request();

		} catch ( Gateway_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => $exception->getMessage(),
					'data'    => $exception->get_additional(),
				),
				400
			);
		}

		if ( isset( $response['error'] ) ) {
			return new \WP_REST_Response(
				array(
					'message' => $response['error']['message'] ?? __( 'Undefined error', 'jet-form-builder' ),
					'data'    => $response,
				),
				400
			);
		}

		return new \WP_REST_Response(
			array(
				'message' => __( 'Access key saved successfully!', 'jet-form-builder' ),
			)
		);
	}
}
