<?php


namespace Jet_FB_Paypal\Logic;

use Jet_FB_Paypal\ApiActions;
use Jet_FB_Paypal\DbModels\RecurringCyclesModel;
use Jet_FB_Paypal\DbModels\SubscriptionModel;
use Jet_FB_Paypal\DbModels\SubscriptionToPayerShipping;
use Jet_FB_Paypal\DbModels\SubscriptionToRecordModel;
use Jet_FB_Paypal\EventsListenersManager;
use Jet_FB_Paypal\PreparedViews;
use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_FB_Paypal\QueryViews\SubscriptionWithRecord;
use Jet_FB_Paypal\Resources\BillingCycle;
use Jet_FB_Paypal\Resources\Plan;
use Jet_FB_Paypal\Resources\Subscription;
use Jet_FB_Paypal\RestEndpoints;
use Jet_FB_Paypal\SubscribeNowConnector;
use Jet_Form_Builder\Actions\Types\Save_Record;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Exceptions\Repository_Exception;
use Jet_Form_Builder\Exceptions\Silence_Exception;
use Jet_Form_Builder\Gateways\Base_Gateway;
use Jet_Form_Builder\Gateways\Db_Models\Payer_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payer_Shipping_Model;
use Jet_Form_Builder\Gateways\Paypal;
use Jet_Form_Builder\Gateways\Paypal\Scenarios_Logic;
use Jet_Form_Builder\Gateways\Scenarios_Abstract\Scenario_Logic_Base;
use Jet_FB_Paypal\Utils\Utils;

class SubscribeNow extends Scenario_Logic_Base implements Scenarios_Logic\With_Resource_It {

	const APPROVAL_PENDING = 'APPROVAL_PENDING';
	const APPROVED         = 'APPROVED';
	const ACTIVE           = 'ACTIVE';
	const SUSPENDED        = 'SUSPENDED';
	const CANCELLED        = 'CANCELLED';
	const EXPIRED          = 'EXPIRED';
	const REFUNDED         = 'REFUNDED';

	use ApiActions\Traits\ListWebhookTrait;
	use SubscribeNowConnector;

	protected function query_token() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return sanitize_text_field( wp_unslash( $_GET['subscription_id'] ?? '' ) );
	}

	public function get_failed_statuses() {
		return array( self::CANCELLED, self::EXPIRED );
	}

	/**
	 * @return void
	 * @throws Gateway_Exception|Repository_Exception
	 */
	public function after_actions() {
		/**
		 * Create subscription by /v1/billing/subscriptions
		 */
		$subscription = $this->create_resource();

		/**
		 * By default save subscription id & form data to inserted post meta
		 */
		$this->add_context(
			array(
				'subscription_id' => $this->save_resource( $subscription ),
			)
		);

		/**
		 * We create webhooks at the very beginning, since we must "catch" all events.
		 *
		 * Previously, this was done in that method,
		 *
		 * @see \Jet_FB_Paypal\Logic\SubscribeNow::process_after
		 * and practice has shown that when creating the first subscription,
		 * only its activation is "caught", but not completing a payment.
		 */
		$this->maybe_create_webhooks( jet_fb_handler()->form_id );

		/**
		 * Redirect to Paypal for agree and subscribe
		 */
		$this->add_redirect( $subscription->get_links() );

		add_action(
			'jet-form-builder/form-handler/after-send',
			array( $this, 'attach_record_id' )
		);
	}

	/**
	 * @throws Sql_Exception
	 * @throws Repository_Exception
	 */
	public function attach_record_id() {
		$record_id       = jet_fb_action_handler()->get_context( Save_Record::ID, 'id' );
		$subscription_id = $this->get_context( 'subscription_id' );

		if ( ! $record_id || ! $subscription_id ) {
			return;
		}

		( new SubscriptionToRecordModel() )->insert(
			array(
				'subscription_id' => $subscription_id,
				'record_id'       => $record_id,
			)
		);
	}


	/**
	 * @return Subscription
	 * @throws Gateway_Exception
	 * @throws Repository_Exception
	 */
	public function create_resource() {
		$plan = new Plan( $this->get_plan_id() );

		return Subscription::create(
			$plan,
			array(
				'quantity'   => $this->get_quantity(),
				'return_url' => $this->get_referrer_url( Base_Gateway::SUCCESS_TYPE ),
				'cancel_url' => $this->get_referrer_url( Base_Gateway::FAILED_TYPE ),
			)
		);
	}

	/**
	 * @param Subscription $subscription
	 *
	 * @return int
	 * @throws Gateway_Exception
	 */
	public function save_resource( $subscription ) {
		$subscription->plan()->details();

		try {
			$primary_id = ( new SubscriptionModel() )->insert(
				array(
					'billing_id' => $subscription->get_id(),
					'gateway_id' => jet_fb_gateway_current()->get_id(),
					'scenario'   => self::scenario_id(),
					'form_id'    => jet_fb_handler()->form_id,
					'user_id'    => get_current_user_id(),
					'status'     => $subscription->get_status(),
				)
			);

			foreach ( $subscription->plan()->get_cycles() as $billing_cycle ) {
				$cycle = ( new BillingCycle( $billing_cycle ) )->set_subscription( $subscription );

				( new RecurringCyclesModel() )->insert(
					array(
						'subscription_id' => $primary_id,
						'quantity'        => $cycle->get_quantity(),
						'interval_unit'   => $cycle->get_interval_unit(),
						'interval_count'  => $cycle->get_interval_count(),
						'currency'        => $cycle->pricing()->amount()->get_currency(),
						'amount'          => $cycle->pricing()->amount()->sanitize_value(),
						'tenure_type'     => $cycle->get_tenure_type(),
					)
				);
			}
		} catch ( Sql_Exception $exception ) {
			throw new Gateway_Exception( $exception->getMessage() );
		}

		do_action(
			'jet-form-builder/gateways/after-save',
			$primary_id,
			$subscription
		);

		return $primary_id;
	}

	/**
	 * @return array
	 * @throws Gateway_Exception
	 */
	protected function query_scenario_row() {
		try {
			return SubscriptionWithRecord::findOne(
				array(
					'billing_id' => $this->get_queried_token(),
				)
			)->query()->query_one();

		} catch ( Query_Builder_Exception $exception ) {
			throw new Gateway_Exception( $exception->getMessage() );
		}
	}


	/**
	 * @throws Gateway_Exception
	 */
	public function process_after() {
		$subscription = Subscription::details( $this->get_queried_token() );

		if ( ! $subscription->is_active() ) {
			throw new Gateway_Exception( 'Subscription not approved', $subscription->get_status() );
		}

		$payer_ship = new Payer_Shipping_Model();

		/**
		 * And at the same time we receive the initial payment
		 *
		 * @see \Jet_FB_Paypal\EventsHandlers\PaymentSaleCompleted::on_catch_event
		 */

		$resource = new Subscription( $this->get_scenario_row() );
		$resource->update_status_soft( $subscription->get_status() );

		$this->scenario_row(
			array(
				'status' => $subscription->get_status(),
			)
		);

		try {
			$payer_id = Payer_Model::insert_or_update(
				array(
					'user_id'    => $this->get_scenario_row( 'user_id' ),
					'payer_id'   => $subscription->get_payer_id(),
					'first_name' => $subscription->get_first_name(),
					'last_name'  => $subscription->get_last_name(),
					'email'      => $subscription->get_email(),
				)
			);

			$payer_ship_id = $payer_ship->insert(
				array(
					'payer_id'       => $payer_id,
					'full_name'      => $subscription->shipping()->get_full_name(),
					'address_line_1' => $subscription->shipping()->get_address_line_1(),
					'address_line_2' => $subscription->shipping()->get_address_line_2(),
					'admin_area_2'   => $subscription->shipping()->get_admin_area_2(),
					'admin_area_1'   => $subscription->shipping()->get_admin_area_1(),
					'postal_code'    => $subscription->shipping()->get_postal_code(),
					'country_code'   => $subscription->shipping()->get_country_code(),
				)
			);

			( new SubscriptionToPayerShipping() )->insert(
				array(
					'subscription_id'   => $this->get_scenario_row( 'id' ),
					'payer_shipping_id' => $payer_ship_id,
				)
			);

		} catch ( Sql_Exception $exception ) {
			throw new Gateway_Exception( $exception->getMessage() );
		}
	}

	/**
	 * @param int $form_id
	 *
	 * @throws Gateway_Exception
	 */
	private function maybe_create_webhooks( $form_id = 0 ) {
		list( $endpoint, $url ) = $this->get_rest_api_endpoint( $form_id );
		$token      = jet_fb_gateway_current()->get_current_token();
		$webhook_id = $this->get_webhook_id_by_endpoint( $endpoint, $token );

		if ( $webhook_id ) {
			return;
		}

		$response = ( new ApiActions\CreateWebhook() )
			->set_bearer_auth( jet_fb_gateway_current()->get_current_token() )
			->set_param_url( $url )
			->set_param_event_types( EventsListenersManager::instance()->get_events_types_list() )
			->send_request();

		if ( empty( $response['id'] ) ) {
			throw new Gateway_Exception(
				'Can\'t create webhook. ' . $response['message'] ?? '',
				$response
			);
		}

	}

	/**
	 * @param int $form_id
	 *
	 * @return array
	 */
	private function get_rest_api_endpoint( $form_id = 0 ) {
		$gateway = array(
			'gateway' => Paypal\Controller::ID,
		);

		$with_form = array_merge(
			$gateway,
			array(
				'id' => $form_id,
			)
		);

		if ( jet_fb_gateway_current()->current_gateway( 'use_global' ) ) {
			return array(
				RestEndpoints\PaypalWebHookGlobal::get_dynamic_base( $gateway ),
				RestEndpoints\PaypalWebHookGlobal::dynamic_rest_url( $gateway ),
			);
		}

		return array(
			RestEndpoints\PaypalWebHookFormId::get_dynamic_base( $with_form ),
			RestEndpoints\PaypalWebHookFormId::dynamic_rest_url( $with_form ),
		);
	}

	/**
	 * @throws Gateway_Exception|Repository_Exception
	 */
	public function get_plan_id() {
		$plan_id = $this->get_from_field_or_manual( 'plan_field', 'plan_manual' );

		if ( ! $plan_id ) {
			throw new Gateway_Exception(
				'empty_field',
				jet_fb_gateway_current()->get_current_scenario()
			);
		}

		return $plan_id;
	}

	/**
	 * @return integer
	 * @throws Gateway_Exception|Repository_Exception
	 */
	public function get_quantity() {
		$quantity = $this->get_from_field_or_manual( 'quantity_field', 'quantity_manual' );

		return absint( $quantity ? $quantity : 1 );
	}

	/**
	 * @param $option_field
	 * @param $option_manual
	 *
	 * @return mixed
	 * @throws Gateway_Exception
	 */
	protected function get_from_field_or_manual( $option_field, $option_manual ) {
		$scenario   = jet_fb_gateway_current()->get_current_scenario();
		$field_name = $scenario[ $option_field ] ?? false;

		if ( ! $field_name ) {
			return $scenario[ $option_manual ] ?? false;
		}

		$request = jet_fb_action_handler()->request_data;

		if ( empty( $request[ $field_name ] ) ) {
			throw new Gateway_Exception(
				'Empty value for ' . $option_field . ' in field ' . $field_name,
				$field_name,
				$request
			);
		}

		return $request[ $field_name ];
	}
}
