<?php

namespace Jet_FB_Stripe_Gateway\RestEndpoints\WebhookEvents;

use Jet_FB_Paypal\Utils\SubscriptionUtils;
use Jet_Form_Builder\Actions\Events\Gateway_Failed\Gateway_Failed_Event;
use WP_REST_Response;


class InvoicePaymentFailed {

	public function handle( array $payload ): WP_REST_Response
	{
		try {
			$invoice = $payload['data']['object'] ?? array();
			$lines   = $invoice['lines']['data'] ?? array();
			$line    = $lines[0] ?? array();
			
			$jfb_subscription_id = $line['metadata']['subscription_id']
				?? ($invoice['parent']['subscription_details']['metadata']['subscription_id'] ?? null);

			if ( empty( $jfb_subscription_id ) ) {
				return new WP_REST_Response( [ 'error' => 'Missing JFB subscription_id' ], 500 );
			}

			SubscriptionUtils::execute_event_for_subscription( $jfb_subscription_id, Gateway_Failed_Event::class );

		} catch ( \Throwable $e ) {
			error_log( '[StripeWebhook][invoice.payment_failed] Exception: ' . $e->getMessage() );
		}
		return new WP_REST_Response([
			'status' => 'ok'
		], 200);
	}
}
