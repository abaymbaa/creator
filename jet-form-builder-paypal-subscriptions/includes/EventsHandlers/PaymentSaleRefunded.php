<?php


namespace Jet_FB_Paypal\EventsHandlers;

use Jet_FB_Paypal\QueryViews\PaymentsWithSales;
use Jet_FB_Paypal\Resources\Subscription;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Gateways\Db_Models\Payment_Model;

class PaymentSaleRefunded extends Base\EventHandlerBase {

	public static function get_event_type() {
		return 'PAYMENT.SALE.REFUNDED';
	}

	/**
	 * @param $webhook_event
	 *
	 * @return array
	 * @throws Gateway_Exception
	 */
	public function on_catch_event( $webhook_event ) {
		$sale_id = $webhook_event['resource']['sale_id'] ?? '';

		try {
			$payment = PaymentsWithSales::findOne( array(
				'transaction_id' => $sale_id
			) )->query()->query_one();

		} catch ( Query_Builder_Exception $exception ) {
			throw new Gateway_Exception( $exception->getMessage(), ...$exception->get_additional() );
		}

		( new Payment_Model )->update_soft(
			array(
				'status' => PaymentsWithSales::REFUNDED_STATUS
			),
			array(
				'id' => $payment['id']
			)
		);

		$resource = new Subscription( $payment['subscription'] );
		$resource->set_refunded();

		return array( $payment, $resource );
	}


}
