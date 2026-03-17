<?php


namespace Jet_FB_Paypal\RestEndpoints;

use Jet_FB_Paypal\QueryViews\PaymentsWithSales;
use Jet_FB_Paypal\Resources\Subscription;
use Jet_FB_Paypal\RestEndpoints\Base\RestPayment;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Gateways\Db_Models\Payment_Model;
use Jet_Form_Builder\Gateways\Paypal;
use Jet_FB_Paypal\ApiActions;

class PayPalRefundPayment extends RestPayment {

	public static function gateway_rest_base(): string {
		return 'payment/refund/(?P<id>[\d]+)';
	}

	public static function gateway_id(): string {
		return Paypal\Controller::ID;
	}

	public function run_action( array $payment, \WP_REST_Request $request ): \WP_REST_Response {
		$form_id = $payment['form_id'] ?? false;
		$body    = $request->get_json_params();

		try {
			$request = ( new ApiActions\RefundPaymentSale() )
				->set_path(
					array( 'sale_id' => $payment['transaction_id'] )
				)
				->set_bearer_auth(
					Paypal\Controller::get_token_by_form_id( $form_id )
				)
				->set_invoice_id( $body['invoice_id'] ?? '' )
				->set_note_to_payer( $body['note_to_payer'] ?? '' );

			$request->refund();

		} catch ( Gateway_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => $exception->getMessage(),
					'data'    => $exception->get_additional(),
					'file'    => $exception->getFile(),
					'line'    => $exception->getLine(),
				),
				503
			);
		}

		try {
			( new Payment_Model )->update(
				array(
					'status' => PaymentsWithSales::REFUNDED_STATUS
				),
				array(
					'id' => $payment['id']
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
