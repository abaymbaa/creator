<?php

namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Rest_Endpoints;

use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_Form_Builder\Rest_Api\Rest_Api_Endpoint_Base;
use Jet_Form_Builder\Gateways\Db_Models\Payment_Model;


use Jet_FB_Paypal\Resources\Subscription;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Controller;
use WP_REST_Response;

class Subscription_Suspend extends Rest_Api_Endpoint_Base {

	public static function get_rest_base() {
		return 'stripe/subscription/suspend/(?P<id>\d+)';
	}

	public static function get_methods() {
		return \WP_REST_Server::CREATABLE;
	}

	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function run_callback( \WP_REST_Request $request ) {
		try {

			$subscription_id = (int) $request->get_param( 'id' );
			if ( ! $subscription_id ) {
				return new WP_REST_Response( [ 'error' => 'empty_subscription_id' ], 400 );
			}

			$sub_q    = SubscriptionsView::find( [ 'id' => $subscription_id ] )->query();
			$sub_rows = $sub_q->db()->get_results( $sub_q->sql(), ARRAY_A );
			if ( empty( $sub_rows ) ) {
				return new WP_REST_Response( [ 'error' => 'subscription_not_found' ], 404 );
			}

			$subscription  = $sub_q->view()->get_prepared_row( $sub_rows[0] );

			$gateway = strtolower( (string) ( $subscription['gateway_id'] ?? $subscription['gateway'] ?? '' ) );
			if ( 'stripe' !== $gateway ) {
				return new WP_REST_Response( [ 'error' => 'not_a_stripe_subscription' ], 400 );
			}

			$stripe_sub_id = (string) ( $subscription['billing_id'] ?? '' );
			if ( '' === $stripe_sub_id ) {
				return new WP_REST_Response( [ 'error' => 'stripe_subscription_id_empty' ], 400 );
			}

			$form_id = (int) ( $subscription['form_id'] ?? 0 );
			if ( ! $form_id ) {
				return new WP_REST_Response( [ 'error' => 'form_id_empty' ], 400 );
			}

			$secret = Controller::get_token_by_form_id( $form_id );
			if ( ! is_string( $secret ) || '' === $secret ) {
				return new WP_REST_Response( [ 'error' => 'secret_not_found_for_form' ], 500 );
			}

			$secret = Controller::get_token_by_form_id( $form_id );
			$reason = sanitize_textarea_field( (string) $request->get_param('reason') );
			$body = [
				'pause_collection[behavior]' => 'mark_uncollectible',
			];
			if ( $reason !== '' ) {
				$body['metadata[suspend_reason]'] = $reason;
			}

			$resp = wp_remote_post(
				'https://api.stripe.com/v1/subscriptions/' . rawurlencode( $stripe_sub_id ),
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $secret,
					),
					'timeout' => 30,
					'body'    => $body,
				)
			);

			if ( is_wp_error( $resp ) ) {
				error_log( '[StripeSuspend] http_error: ' . $resp->get_error_message() );
				return new WP_REST_Response(
					[ 'error' => 'stripe_http_error', 'details' => $resp->get_error_message() ],
					502
				);
			}

			$code = wp_remote_retrieve_response_code( $resp );
			$body = json_decode( wp_remote_retrieve_body( $resp ), true );
			if ( $code < 200 || $code >= 300 ) {
				error_log( '[StripeSuspend] api_error ' . $code . ' ' . ( $body['error']['message'] ?? 'unknown' ) );
				return new WP_REST_Response(
					[
						'error'   => 'stripe_api_error',
						'code'    => $code,
						'details' => $body,
					],
					500
				);
			}

			$resource = new Subscription( $subscription );
			$resource->set_suspended();

			return new WP_REST_Response(
				array(
					'message'         => 'Subscription paused on Stripe',
					'subscription_id' => $subscription_id,
					'stripe_response' => $body,
				),
				200
			);

		} catch ( \Throwable $e ) {
			error_log( '[StripeSuspend] fatal: ' . $e->getMessage() );
			return new WP_REST_Response(
				array(
					'error'   => 'internal_error',
					'message' => $e->getMessage(),
				),
				500
			);
		}
	}


}
