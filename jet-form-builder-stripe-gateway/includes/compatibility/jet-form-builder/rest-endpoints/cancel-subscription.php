<?php

namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Rest_Endpoints;

use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_Form_Builder\Rest_Api\Rest_Api_Endpoint_Base;
use Jet_FB_Paypal\Resources\Subscription;
use Jet_FB_Paypal\Logic\SubscribeNow;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Controller;

class Cancel_Subscription extends Rest_Api_Endpoint_Base {

	public static function get_rest_base() {
		return '/stripe/subscription/cancel/(?P<id>[\d]+)';
	}

	public static function get_methods() {
		return \WP_REST_Server::CREATABLE;
	}

	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function run_callback( \WP_REST_Request $request ) {
		$id   = (int) $request->get_param( 'id' );

		$query = SubscriptionsView::find(
			[ 'id' => $id ]
		)->query();
		$rows = $query->db()->get_results( $query->sql(), ARRAY_A );

		if ( ! empty( $rows ) ) {
			$subscription = $query->view()->get_prepared_row( $rows[0] );
		} else {
			error_log('subscription not ready');
			return new WP_REST_Response( [ 'error' => 'subscription not ready' ], 500 );
		}

		$form_id    = (int) ( $subscription['form_id'] ?? 0 );
		$billing_id = (string) ( $subscription['billing_id'] ?? '' );

		if ( ! $form_id || ! $billing_id ) {
			return new \WP_REST_Response(
				array( 'message' => 'Required data not found (form_id/billing_id).' ),
				400
			);
		}

		try {
			$secret = Controller::get_token_by_form_id( $form_id );
			$reason = sanitize_textarea_field( (string) $request->get_param('reason') );
			$body = [];
			if ( $reason !== '' ) {
				$body['cancellation_details[comment]'] = $reason;
			}
			$resp = wp_remote_request(
				'https://api.stripe.com/v1/subscriptions/' . rawurlencode( $billing_id ),
				array(
					'method'  => 'DELETE',
					'headers' => array( 'Authorization' => 'Bearer ' . $secret ),
					'timeout' => 30,
					'body'    => $body,
				)
			);

			if ( is_wp_error( $resp ) ) {
				throw new Gateway_Exception( 'Stripe cancel request failed', array(
					'error' => $resp->get_error_message(),
				) );
			}

			$code = wp_remote_retrieve_response_code( $resp );
			$body = json_decode( wp_remote_retrieve_body( $resp ), true );

			if ( $code < 200 || $code >= 300 ) {
				throw new Gateway_Exception( 'Stripe cancel api error', array(
					'code'    => $code,
					'details' => $body,
				) );
			}
		} catch ( Gateway_Exception $e ) {
			return new \WP_REST_Response(
				array(
					'message' => $e->getMessage(),
					'data'    => $e->get_additional(),
					'file'    => $e->getFile(),
					'line'    => $e->getLine(),
				),
				503
			);
		}

		$resource = new Subscription( $subscription );
		$resource->update_status_soft( SubscribeNow::CANCELLED );

		do_action( 'jet-form-builder/subscription/change-status-manual', $resource );

		return new \WP_REST_Response(
			array( 'message' => __( 'Successfully cancelled subscription!', 'jet-form-builder-stripe-gateway' ) )
		);
	}
}
