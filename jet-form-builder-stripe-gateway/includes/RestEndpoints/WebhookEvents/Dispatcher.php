<?php

namespace Jet_FB_Stripe_Gateway\RestEndpoints\WebhookEvents;

use WP_REST_Response;

class Dispatcher {

	public function dispatch(string $event_type, array $payload ): WP_REST_Response {
		switch ( $event_type ) {

			case 'checkout.session.completed':
				return ( new CheckoutSessionCompleted() )->handle( $payload );

			case 'invoice.paid':
				return ( new InvoicePaid() )->handle( $payload );

			case 'invoice.payment_failed':
				return ( new InvoicePaymentFailed() )->handle( $payload );


			case 'customer.subscription.updated':
				return ( new CustomerSubscriptionUpdated() )->handle( $payload );

			case 'customer.subscription.deleted':
				return ( new CustomerSubscriptionCancelled() )->handle( $payload );

			default:
				return new WP_REST_Response( [ 'message' => 'Unhandled event' ], 200 );
		}
	}
}
