<?php


namespace Jet_FB_Paypal\RestEndpoints\Base;

use Jet_Form_Builder\Actions\Methods\Form_Record\Controller;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Dev_Mode\Manager;
use Jet_Form_Builder\Exceptions\Action_Exception;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Exceptions\Repository_Exception;
use Jet_FB_Paypal\EventsListenersManager;
use Jet_FB_Paypal\ApiActions\VerifyWebhookSignatureAction;
use Jet_FB_Paypal\ApiActions\Traits;
use Jet_Form_Builder\Gateways\Paypal;
use Jet_Form_Builder\Gateways\Rest_Api\Gateway_Endpoint;

abstract class PayPalWebHookBase extends Gateway_Endpoint {

	use Traits\ListWebhookTrait;

	abstract public function get_token( \WP_REST_Request $request );

	public static function get_methods() {
		return \WP_REST_Server::CREATABLE;
	}

	public static function gateway_id(): string {
		return Paypal\Controller::ID;
	}

	public function run_callback( \WP_REST_Request $request ) {
		try {
			$token      = $this->get_token( $request );
			$webhook_id = $this->get_webhook_id_by_endpoint(
				static::get_dynamic_base(
					array( 'gateway' => Paypal\Controller::ID )
				),
				$token
			);

			( new VerifyWebhookSignatureAction() )
				->set_bearer_auth( $token )
				->set_params_from_request( $request )
				->set_webhook_id( $webhook_id )
				->set_webhook_event( $request->get_body() )
				->is_success();

			return $this->run_event( $request->get_json_params() );

		} catch ( Gateway_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => $exception->getMessage()
				),
				503,
				array(
					'X-JFB-Paypal-Webhook-Message' => $exception->getMessage(),
					'X-JFB-Paypal-Webhook-Args'    => wp_json_encode( $exception->get_additional() ),
				)
			);
		}
	}

	/**
	 * @param $webhook_event
	 *
	 * @return \WP_REST_Response
	 * @throws Gateway_Exception
	 */
	private function run_event( $webhook_event ): \WP_REST_Response {
		$event_type = $webhook_event['event_type'] ?? false;
		define( 'JET_FB_REST_WEBHOOK', true );

		try {
			$event = EventsListenersManager::instance()->get_event( $event_type );
			$event->on_catch_event( $webhook_event );

			return EventsListenersManager::instance()->response();

		} catch ( Repository_Exception $exception ) {
			throw new Gateway_Exception(
				"Undefined event type: $event_type",
				EventsListenersManager::instance()->get_events_types_list()
			);
		}
	}

}
