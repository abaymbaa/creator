<?php

namespace Jet_FB_Stripe_Gateway\RestEndpoints;

use WP_REST_Response;
use Jet_FB_Stripe_Gateway\RestEndpoints\Base\StripeWebHookBase;
use Jet_FB_Stripe_Gateway\RestEndpoints\WebhookEvents\Dispatcher;

class StripeWebHookGlobal extends StripeWebHookBase {

	public function get_route_namespace(): string {
		return 'jfb-stripe/v1';
	}

	public function run_event( array $event_data ): WP_REST_Response {
		$event_type = $event_data['type'] ?? 'unknown';

		$dispatcher = new Dispatcher();

		return $dispatcher->dispatch( $event_type, $event_data );
	}
}
