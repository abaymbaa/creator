<?php

namespace Jet_FB_Stripe_Gateway\RestEndpoints\Base;

use WP_REST_Request;
use WP_REST_Response;

abstract class StripeWebHookBase {

	public static function get_methods() {
		return \WP_REST_Server::CREATABLE;
	}

	abstract public function get_route_namespace(): string;
	abstract public function run_event( array $event_data ): WP_REST_Response;

	public function register_endpoint() {
		register_rest_route(
			$this->get_route_namespace(),
			'/webhook',
			[
				'methods'             => self::get_methods(),
				'callback'            => [ $this, 'handle_webhook' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	public function handle_webhook( WP_REST_Request $request ) {
		$event_data = $request->get_json_params();

		if ( empty( $event_data['type'] ) ) {
			return new WP_REST_Response( [ 'message' => 'No event type' ], 400 );
		}

		return $this->run_event( $event_data );
	}
}
