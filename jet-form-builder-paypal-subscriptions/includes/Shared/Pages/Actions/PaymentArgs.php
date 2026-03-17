<?php


namespace Jet_FB_Paypal\Pages\Actions;


use Jet_FB_Paypal\QueryViews\PaymentsWithSales;
use Jet_FB_Paypal\QueryViews\SubscriptionByPayment;
use Jet_FB_Paypal\Resources\GatewayResource;
use Jet_FB_Paypal\Resources\Payment;
use Jet_Form_Builder\Classes\Arrayable\Array_Continue_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

class PaymentArgs extends BaseResourceArgs {

	/**
	 * @return GatewayResource
	 * @throws Array_Continue_Exception
	 */
	public function get_resource(): GatewayResource {
		try {
			$payment = new Payment(
				PaymentsWithSales::findById( $this->get_id() )
			);

			if ( ! $payment->subscription()->get_id() ) {
				throw new Array_Continue_Exception( 'Payment is not related to subscription' );
			}

			return $payment;

		} catch ( Query_Builder_Exception $exception ) {
			throw new Array_Continue_Exception( $exception->getMessage(), ...$exception->get_additional() );
		}
	}

}