<?php

namespace Jet_FB_Stripe_Gateway\RestEndpoints\WebhookEvents;

use Jet_FB_Paypal\DbModels\SubscriptionToPaymentModel;
use Jet_FB_Paypal\QueryViews\PaymentsBySubscription;
use Jet_FB_Paypal\QueryViews\SubscriptionPayerShipping;
use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Subscription_Connector;
use Jet_FB_Stripe_Gateway\FormEvents\RenewalPaymentEvent;
use Jet_Form_Builder\Actions\Events\Gateway_Success\Gateway_Success_Event;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Gateways\Db_Models\Payment_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payment_To_Payer_Shipping_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payer_Model;
use Jet_FB_Paypal\Utils\SubscriptionUtils;

use WP_REST_Response;

class InvoicePaid {

	use Subscription_Connector;

	public function handle( array $payload ): WP_REST_Response {

		$invoice = $payload['data']['object'] ?? [];
		$subscription_id = $invoice['parent']['subscription_details']['metadata']['subscription_id'] ?? null;

		if ( ! $subscription_id ) {
			throw new Gateway_Exception( 'Empty `subscription` id', $invoice );
		}
		
		$query = SubscriptionsView::find(
			[ 'id' => $subscription_id ]
		)->query();
		$rows = $query->db()->get_results( $query->sql(), ARRAY_A );

		if ( ! empty( $rows ) ) {
			$subscription = $query->view()->get_prepared_row( $rows[0] );
		} else {
			error_log('subscription not ready');
			return new WP_REST_Response( [ 'error' => 'subscription not ready' ], 500 );
		}


		$initial_payment = null;
		try {
			$query = PaymentsBySubscription::find(
				[ 'subscription_id' => $subscription['id'] ]
			)->query();
			$rows = $query->db()->get_results( $query->sql(), ARRAY_A );

			if ( ! empty( $rows ) ) {
				$initial_payment = $query->view()->get_prepared_row( $rows[0] );
			}
		} catch ( Query_Builder_Exception $exception ) {
			// not critical
		}
		$initial_transaction_id = $initial_payment['transaction_id'] ?? null;

		if ($initial_payment) {
			$type = 'RENEWAL';
			$amount = isset($subscription['cycle']) && isset($subscription['cycle']['amount']) ? $subscription['cycle']['amount'] : 0;
			SubscriptionUtils::execute_event_for_subscription( $subscription['id'], RenewalPaymentEvent::class );
		} else {
			$type = 'INITIAL';
			$amount = isset($invoice['amount_paid']) ? $invoice['amount_paid'] / 100 : 0;
			SubscriptionUtils::execute_event_for_subscription( $subscription['id'], Gateway_Success_Event::class );
		}


		$currency       = $invoice['currency'] ?? '';
		$transaction_id = $invoice['id'];

		try {
			$payment_model = new Payment_Model();
			$payment_row_id = $payment_model->insert([
				'transaction_id'         => $transaction_id,
				'initial_transaction_id' => $initial_transaction_id,
				'form_id'                => $subscription['form_id'],
				'user_id'                => $subscription['user_id'],
				'gateway_id'             => 'stripe',
				'scenario'               => $subscription['scenario'],
				'amount_value'           => $amount,
				'amount_code'            => $currency,
				'type'                   => $type,
				'status'                 => 'COMPLETED',
			]);
		} catch ( Sql_Exception $exception ) {
			throw new Gateway_Exception( $exception->getMessage(), ...$exception->get_additional() );
		}

		( new SubscriptionToPaymentModel() )->insert([
			'subscription_id' => $subscription['id'],
			'payment_id'      => $payment_row_id,
		]);

		try {
			$pair = SubscriptionPayerShipping::findOne(
				[ 'subscription_id' => $subscription['id'] ]
			)->query()->query_one();

			( new Payment_To_Payer_Shipping_Model )->insert([
				'payment_id'        => $payment_row_id,
				'payer_shipping_id' => $pair['payer_shipping_id'],
			]);
		} catch ( Query_Builder_Exception | Sql_Exception $exception ) {
			// ignore
		}


		return new WP_REST_Response([
			'status' => 'ok',
			'payment_id' => $payment_row_id,
			'subscription_id' => $subscription['id'],
		], 200);
	}

}
