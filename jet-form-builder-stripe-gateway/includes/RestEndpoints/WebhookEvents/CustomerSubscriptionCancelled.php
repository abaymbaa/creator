<?php

namespace Jet_FB_Stripe_Gateway\RestEndpoints\WebhookEvents;

use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_FB_Paypal\Resources\Subscription;
use Jet_FB_Paypal\Utils\SubscriptionUtils;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Logic\Subscription_Logic;
use Jet_FB_Stripe_Gateway\FormEvents\SubscriptionCancelEvent;
use Jet_FB_Stripe_Gateway\FormEvents\SubscriptionExpiredEvent;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use WP_REST_Response;

class CustomerSubscriptionCancelled {

	public function handle( array $payload ): WP_REST_Response {
		try {
			$subscription_id = $payload['data']['object']['id'] ?? '';
			if ( ! $subscription_id ) {
				throw new Gateway_Exception( 'Empty `subscription` id', $payload );
			}

			$subscription_row = SubscriptionsView::findOne( [
				'billing_id' => $subscription_id,
			] )->query()->query_one();

			if ( ! $subscription_row ) {
				throw new Gateway_Exception( 'Empty subscription row', $payload );
			}

			$subObj               = (array) ( $payload['data']['object'] ?? [] );
			$cancel_at_period_end = (bool)  ( $subObj['cancel_at_period_end'] ?? false );
			$ended_at             = (int)   ( $subObj['ended_at'] ?? 0 );
			$current_period_end   = (int)   (
				$subObj['current_period_end']
				?? ( $subObj['items']['data'][0]['current_period_end'] ?? 0 )
			);
			$cancel_at            = (int)   ( $subObj['cancel_at'] ?? 0 );

			$req                  = (array) ( $payload['request'] ?? [] );
			$was_system_initiated = empty( $req['id'] ) && empty( $req['idempotency_key'] );

			$is_expired_end_of_term = $cancel_at_period_end && $was_system_initiated;

			if ( $ended_at && $current_period_end && $ended_at === $current_period_end ) {
				$is_expired_end_of_term = true;
			}

			if ( $ended_at && $cancel_at && $ended_at === $cancel_at ) {
				$is_expired_end_of_term = true;
			}

			$subscription = new Subscription( $subscription_row );
			$subscription->update_status_soft( Subscription_Logic::CANCELLED );

			if ( $is_expired_end_of_term ) {
				SubscriptionUtils::execute_event_for_subscription( $subscription->get_id(), SubscriptionExpiredEvent::class );
			} else {
				SubscriptionUtils::execute_event_for_subscription( $subscription->get_id(), SubscriptionCancelEvent::class );
			}

			return new WP_REST_Response( [ 'success' => 1 ] );

		} catch ( \Throwable $e ) {
			error_log( '[StripeWebhook] SubscriptionCancelled ERROR: ' . $e->getMessage() );
			throw new Gateway_Exception( $e->getMessage(), $payload );
		}
	}
}
