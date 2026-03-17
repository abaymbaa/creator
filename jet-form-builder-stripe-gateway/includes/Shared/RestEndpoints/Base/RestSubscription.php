<?php


namespace Jet_FB_Paypal\RestEndpoints\Base;

use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Gateways\Rest_Api\Gateway_Endpoint;

abstract class RestSubscription extends Gateway_Endpoint {

	abstract public function run_action( array $subscription, \WP_REST_Request $request ): \WP_REST_Response;

	public static function get_methods() {
		return \WP_REST_Server::CREATABLE;
	}

	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function run_callback( \WP_REST_Request $request ) {
		$subscription_id = $request->get_param( 'id' );

		try {
			/**
			 * Execute this action if there is an entry
			 * with this $subscription_id in the database
			 */
			$subscription = SubscriptionsView::findById( $subscription_id );

		} catch ( Query_Builder_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => $exception->getMessage(),
					'data'    => $exception->get_additional(),
				),
				404
			);
		}

		return $this->run_action( $subscription, $request );
	}

}
