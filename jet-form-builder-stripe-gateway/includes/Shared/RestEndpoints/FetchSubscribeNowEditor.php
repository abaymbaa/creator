<?php


namespace Jet_FB_Paypal\RestEndpoints;

use Jet_FB_Paypal\ApiActions\Exceptions\GatewayNoticeException;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_FB_Paypal\ApiActions\ListSubscriptionPlans;
use Jet_Form_Builder\Gateways\Paypal\Rest_Endpoints\Fetch_Pay_Now_Editor;

class FetchSubscribeNowEditor extends Fetch_Pay_Now_Editor {

	public static function get_rest_base() {
		return 'paypal/subscribe-now-fetch';
	}

	public function run_callback( \WP_REST_Request $request ) {
		try {
			$token = $this->get_token( $request );

			$action = ( new ListSubscriptionPlans() )
				->set_bearer_auth( $token );

			$response = $action->get_plans_as_list();

		} catch ( GatewayNoticeException $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => $exception->getMessage(),
					'actions' => $exception->get_actions(),
					'data'    => $exception->get_additional(),
				),
				500
			);
		} catch ( Gateway_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => $exception->getMessage(),
					'data'    => $exception->get_additional(),
				),
				500
			);
		}

		return new \WP_REST_Response(
			array(
				'message' => __( 'Access key saved successfully!', 'jet-form-builder' ),
				'data'    => $response,
			)
		);
	}

}
