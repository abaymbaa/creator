<?php

namespace Jet_FB_Stripe_Gateway\RestEndpoints\WebhookEvents;

use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_FB_Paypal\Resources\Subscription;
use Jet_FB_Paypal\Utils\SubscriptionUtils;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Subscription_Connector;
use Jet_FB_Stripe_Gateway\FormEvents\SubscriptionReactivateEvent;
use Jet_FB_Stripe_Gateway\FormEvents\SubscriptionSuspendedEvent;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use WP_REST_Response;

class CustomerSubscriptionUpdated {

	use Subscription_Connector;

	public function handle( array $payload ): WP_REST_Response {

		try {
			$sub    = (array) ( $payload['data']['object'] ?? [] );
			$prev   = (array) ( $payload['data']['previous_attributes'] ?? [] );
			$stripe_sub_id = (string) ( $sub['id'] ?? '' );

			if ( '' === $stripe_sub_id ) {
				error_log( '[StripeWebhook][customer.subscription.updated] Missing subscription id in payload' );
				return new WP_REST_Response( array(
					'status' => 'ok',
					'action' => 'noop',
					'reason' => 'no_stripe_subscription_id',
				), 200 );
			}

			$now_paused = is_array( $sub['pause_collection'] ?? null );
			$was_paused = is_array( $prev['pause_collection'] ?? null );

			if ( $now_paused === $was_paused ) {
				return new WP_REST_Response( array(
					'status' => 'ok',
					'action' => 'noop',
				), 200 );
			}



			$q     = SubscriptionsView::find( array( 'billing_id' => $stripe_sub_id ) )->query();
			$rows  = $q->db()->get_results( $q->sql(), ARRAY_A );
			if ( ! empty( $rows ) ) {
				$subscription = $q->view()->get_prepared_row( $rows[0] );
			}

			try {
				$resource = new Subscription( $subscription );

				if ( $was_paused && ! $now_paused ) {
					// RESUME
					$resource->set_active();
					$action = 'resumed';
					SubscriptionUtils::execute_event_for_subscription( $subscription['id'], SubscriptionReactivateEvent::class );
				} else {
					// SUSPEND
					$resource->set_suspended();
					$action = 'suspended';
					SubscriptionUtils::execute_event_for_subscription( $subscription['id'], SubscriptionSuspendedEvent::class );
				}
			} catch ( Sql_Exception $e ) {
				error_log( '[StripeWebhook][customer.subscription.updated] Local update failed: ' . $e->getMessage() );

				return new WP_REST_Response( array(
					'status'               => 'ok',
					'action'               => 'error',
					'error'                => 'sql_exception',
					'message'              => $e->getMessage(),
					'stripe_subscription'  => $stripe_sub_id,
				), 200 );
			}

			return new WP_REST_Response( array(
				'status'              => 'ok',
				'action'              => $action, // suspended | resumed
				'stripe_subscription' => $stripe_sub_id,
			), 200 );

		} catch ( \Throwable $e ) {
			error_log( '[StripeWebhook][customer.subscription.updated] fatal: ' . $e->getMessage() );

			return new WP_REST_Response( array(
				'status'  => 'ok',
				'action'  => 'error',
				'error'   => 'fatal',
				'message' => $e->getMessage(),
			), 200 );
		}
	}

}
