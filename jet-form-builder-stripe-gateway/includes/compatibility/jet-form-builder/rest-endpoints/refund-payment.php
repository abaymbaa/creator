<?php

namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Rest_Endpoints;

use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Rest_Api\Rest_Api_Endpoint_Base;
use Jet_Form_Builder\Gateways\Db_Models\Payment_Model;


use Jet_FB_Paypal\QueryViews\PaymentsWithSales;
use Jet_FB_Paypal\Resources\Subscription;
use JFB_Modules\Gateways\Module;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Controller;
use WP_REST_Response;

class Refund_Payment extends Rest_Api_Endpoint_Base {

	public static function get_rest_base() {
		return 'stripe/payment/refund/(?P<id>\d+)';
	}

	public static function get_methods() {
		return \WP_REST_Server::CREATABLE;
	}

	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function run_callback( \WP_REST_Request $request ) {
		$id   = (int) $request->get_param( 'id' );

        $query = PaymentsWithSales::find(
            [ 'id' => $id ]
        )->query();
        $rows = $query->db()->get_results( $query->sql(), ARRAY_A );

        if ( ! empty( $rows ) ) {
            $payment = $query->view()->get_prepared_row( $rows[0] );
        } else {
            error_log('payment not found');
            return new WP_REST_Response( [ 'error' => 'payment not found' ], 500 );
        }

        $form_id = (int) ( $payment['form_id'] ?? 0 );
		$invoice_id = $payment['transaction_id'];
        $secret = Controller::get_token_by_form_id( $form_id );

		$resp = wp_remote_get(
			'https://api.stripe.com/v1/invoice_payments?invoice=' . rawurlencode( $invoice_id ) . '&status=paid&limit=1',
			array(
				'headers' => array('Authorization' => 'Bearer ' . $secret),
				'timeout' => 30,
			)
		);
		if (is_wp_error($resp)) { /* handle 503 */ }

		$charges = json_decode(wp_remote_retrieve_body($resp), true);
		$pi_id = $charges['data'][0]['payment']['payment_intent'] ?? '';

		if ( empty( $pi_id ) ) {
			return new WP_REST_Response( [ 'error' => 'payment_intent not found' ], 400 );
		}


		$in          = $request->get_json_params() ?: [];
		$invoice_opt = isset( $in['invoice_id'] ) ? sanitize_text_field( (string) $in['invoice_id'] ) : '';
		$note_opt    = isset( $in['note_to_payer'] ) ? sanitize_textarea_field( (string) $in['note_to_payer'] ) : '';


		$args = array(
			'headers' => array(
				'Authorization'   => 'Bearer ' . $secret,
			),
			'timeout' => 30,
			'body'    => array(
				'payment_intent' => $pi_id,
			),
		);

		if ( $invoice_opt !== '' ) {
			$args['body']['metadata[invoice_id]'] = $invoice_opt;
		}
		if ( $note_opt !== '' ) {
			$args['body']['metadata[note_to_buyer]'] = $note_opt;
		}

		$refund_resp = wp_remote_post( 'https://api.stripe.com/v1/refunds', $args );

		$status_code = wp_remote_retrieve_response_code( $refund_resp );
		$refund_body = json_decode( wp_remote_retrieve_body( $refund_resp ), true );

		if ( $status_code < 200 || $status_code >= 300 ) {
			return new WP_REST_Response( [
				'error'   => 'stripe_refund_api_error',
				'code'    => $status_code,
				'details' => $refund_body,
			], 500 );
		}

		try {
			( new Payment_Model )->update(
				array(
					'status' => PaymentsWithSales::REFUNDED_STATUS,
				),
				array(
					'id' => $payment['id'],
				)
			);
		} catch ( Sql_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => $exception->getMessage(),
					'data'    => $exception->get_additional(),
				),
				505
			);
		}

		$resource = new Subscription( $payment['subscription'] );
		$resource->set_refunded();

		return new \WP_REST_Response(
			array(
				'message' => 'Success',
			)
		);


	}
}
