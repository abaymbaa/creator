<?php

namespace Jet_FB_Stripe_Gateway\RestEndpoints\WebhookEvents;

use Jet_FB_Paypal\FormEvents\RenewalPaymentEvent;
use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_FB_Paypal\DbModels\SubscriptionModel;
use Jet_FB_Paypal\DbModels\SubscriptionToPayerShipping;
use Jet_FB_Paypal\Resources\Subscription;

use Jet_FB_Paypal\Utils\SubscriptionUtils;
use Jet_Form_Builder\Db_Queries\Execution_Builder;
use Jet_Form_Builder\Exceptions\Handler_Exception;
use Jet_Form_Builder\Gateways\Db_Models\Payer_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payer_Shipping_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payment_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payment_To_Payer_Shipping_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payment_To_Record;


use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Logic\Subscription_Logic;

use WP_REST_Response;

class CheckoutSessionCompleted {

	public function handle( array $event ) {
		$session = $event['data']['object'] ?? [];

		$subscription_id = $session['metadata']['subscription_id'];

		$query = SubscriptionsView::find([
			'id' => $subscription_id,
		])->query();

		$rows = $query->db()->get_results( $query->sql(), ARRAY_A );

		$subscription_row = ! empty( $rows )
			? $query->view()->get_prepared_row( $rows[0] )
			: null;

		if ( ! $subscription_row ) {
			error_log('[StripeWebhook] Session not found, retrying...');
			return new WP_REST_Response( null, 202 );
		}


		try {
			Execution_Builder::instance()->transaction_start();

			( new SubscriptionModel() )->update(
				[
					'billing_id' => $session['subscription'],
				],
				[ 'id' => $subscription_id ]
			);

			$subscription = new Subscription( $subscription_row );
			$subscription->update_status_soft( Subscription_Logic::ACTIVE );

			$customer = $session['customer_details'] ?? [];
			$name     = explode( ' ', $customer['name'] ?? '' );


			$payer_id = Payer_Model::insert_or_update([
				'user_id'    => $subscription_row['user_id'],
				'payer_id'   => $session['customer'] ?? '',
				'first_name' => $name[0] ?? '',
				'last_name'  => $name[1] ?? '',
				'email'      => $customer['email'] ?? '',
			]);


			$address = $session['shipping']['address'] ?? [];

			$payer_ship_id = ( new Payer_Shipping_Model() )->insert([
				'payer_id'       => $payer_id,
				'full_name'      => $session['shipping']['name'] ?? '',
				'address_line_1' => $address['line1'] ?? '',
				'address_line_2' => $address['line2'] ?? '',
				'admin_area_2'   => $address['city'] ?? '',
				'admin_area_1'   => $address['state'] ?? '',
				'postal_code'    => $address['postal_code'] ?? '',
				'country_code'   => $address['country'] ?? '',
			]);


			( new SubscriptionToPayerShipping() )->insert([
				'subscription_id'   => $subscription_row['id'],
				'payer_shipping_id' => $payer_ship_id,
			]);

			Execution_Builder::instance()->transaction_commit();
		} catch ( Sql_Exception $e ) {
			Execution_Builder::instance()->transaction_rollback();
			throw new Gateway_Exception( $e->getMessage() );
		}

		return new WP_REST_Response( 'Handled', 200 );
	}

}
