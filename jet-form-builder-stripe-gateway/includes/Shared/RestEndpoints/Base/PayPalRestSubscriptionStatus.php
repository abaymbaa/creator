<?php


namespace Jet_FB_Paypal\RestEndpoints\Base;

use Jet_FB_Paypal\Resources\Subscription;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Gateways\Base_Gateway_Action;
use Jet_Form_Builder\Gateways\Paypal;

abstract class PayPalRestSubscriptionStatus extends RestSubscription {

	abstract public function get_action(): Base_Gateway_Action;

	abstract public function get_status(): string;

	abstract public function get_message(): string;

	public static function gateway_id(): string {
		return Paypal\Controller::ID;
	}

	public function run_action( array $subscription, \WP_REST_Request $request ): \WP_REST_Response {
		$body = $request->get_json_params();

		try {
			$token = Paypal\Controller::get_token_by_form_id( $subscription['form_id'] ?? false );

			$action = $this->get_action()
			               ->set_bearer_auth( $token )
			               ->set_body( array( 'reason' => $body['reason'] ) )
			               ->set_path( $subscription )
			               ->request()
			               ->check_response_code();

		} catch ( Gateway_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => $exception->getMessage(),
					'data'    => $exception->get_additional(),
				),
				500
			);
		}

		$resource = new Subscription( $subscription );
		$resource->update_status_soft( $this->get_status() );

		do_action(
			'jet-form-builder/subscription/change-status-manual',
			$resource
		);

		return new \WP_REST_Response(
			array(
				'message' => $action->response_message(
					$this->get_message()
				),
			)
		);
	}

}