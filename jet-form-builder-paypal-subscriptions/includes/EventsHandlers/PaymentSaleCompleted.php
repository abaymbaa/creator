<?php


namespace Jet_FB_Paypal\EventsHandlers;

use Jet_FB_Paypal\DbModels\SubscriptionToPaymentModel;
use Jet_FB_Paypal\FormEvents\RenewalPaymentEvent;
use Jet_FB_Paypal\Logic\SubscribeNow;
use Jet_FB_Paypal\QueryViews\RecordBySubscription;
use Jet_FB_Paypal\QueryViews\SubscriptionPayerShipping;
use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_FB_Paypal\QueryViews\PaymentsBySubscription;
use Jet_FB_Paypal\Resources\Subscription;
use Jet_FB_Paypal\Utils;
use Jet_Form_Builder\Actions\Methods\Form_Record\Query_Views\Record_Fields_View;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Exceptions\Action_Exception;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Exceptions\Handler_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Gateways\Db_Models\Payment_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payment_To_Payer_Shipping_Model;
use Jet_Form_Builder\Gateways\Paypal;

class PaymentSaleCompleted extends Base\EventHandlerBase {

	public static function get_event_type() {
		return 'PAYMENT.SALE.COMPLETED';
	}

	public function get_transaction_args( $subscription, $webhook_event ): array {
		try {
			$initial_payment = PaymentsBySubscription::findOne(
				array(
					'subscription_id' => $subscription['id'],
				)
			)->query()->query_one();

			return array(
				$initial_payment['transaction_id'],
				Utils\Utils::format_type( $webhook_event['resource_type'] ),
			);

		} catch ( Query_Builder_Exception $exception ) {
			$this->manager()->response()->set_headers_custom(
				array(
					'Message' => $exception->getMessage(),
					'Args'    => wp_json_encode(
						array(
							$exception->get_additional(),
							$subscription,
						)
					),
				)
			);

			return array(
				$webhook_event['resource']['id'],
				Paypal\Controller::PAYMENT_TYPE_INITIAL,
			);
		}
	}

	/**
	 * @param $webhook_event
	 *
	 * @return array
	 * @throws Gateway_Exception
	 */
	public function on_catch_event( $webhook_event ) {
		$subscription_id = $webhook_event['resource']['billing_agreement_id'] ?? '';

		if ( ! $subscription_id ) {
			throw new Gateway_Exception( 'Empty `billing_agreement_id`', $webhook_event );
		}

		try {
			$subscription = SubscriptionsView::findOne( array( 'billing_id' => $subscription_id ) )
											->query()
			                                ->query_one();

		} catch ( Query_Builder_Exception $exception ) {
			throw new Gateway_Exception( $exception->getMessage(), ...$exception->get_additional() );
		}

		list ( $initial_transaction_id, $transaction_type ) = $this->get_transaction_args(
			$subscription,
			$webhook_event
		);

		try {
			$payment_id = ( new Payment_Model() )->insert(
				array(
					'transaction_id'         => $webhook_event['resource']['id'],
					'initial_transaction_id' => $initial_transaction_id,
					'form_id'                => $subscription['form_id'],
					'user_id'                => $subscription['user_id'],
					'gateway_id'             => Paypal\Controller::ID,
					'scenario'               => SubscribeNow::scenario_id(),
					'amount_value'           => $webhook_event['resource']['amount']['total'],
					'amount_code'            => $webhook_event['resource']['amount']['currency'],
					'type'                   => $transaction_type,
					'status'                 => strtoupper( $webhook_event['resource']['state'] ),
				)
			);

			( new SubscriptionToPaymentModel() )->insert(
				array(
					'subscription_id' => $subscription['id'],
					'payment_id'      => $payment_id,
				)
			);
		} catch ( Sql_Exception $exception ) {
			throw new Gateway_Exception( $exception->getMessage(), ...$exception->get_additional() );
		}

		try {
			$pair = SubscriptionPayerShipping::findOne(
				array(
					'subscription_id' => $subscription['id'],
				)
			)->query()->query_one();

			( new Payment_To_Payer_Shipping_Model )->insert(
				array(
					'payment_id'        => $payment_id,
					'payer_shipping_id' => $pair['payer_shipping_id'],
				)
			);

		} catch ( Query_Builder_Exception $exception ) {
			// do nothing
		} catch ( Sql_Exception $exception ) {
			// do nothing
		}

		$subscription = new Subscription( $subscription );

		try {
			Utils\SubscriptionUtils::trigger_event(
				$subscription,
				RenewalPaymentEvent::class
			);
		} catch ( Handler_Exception $exception ) {
			// silence
		}

		if ( ! $subscription->is_custom_status() ) {
			return array( $payment_id, $subscription );
		}

		$subscription->update_status_soft( SubscribeNow::ACTIVE );

		return array( $payment_id, $subscription );
	}

}
