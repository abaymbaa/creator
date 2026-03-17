<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Logic;

use Jet_FB_Paypal\DbModels\RecurringCyclesModel;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Actions\Create_Checkout_Session;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Actions\Retrieve_Price;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Subscription_Connector;

use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Webhook_Manager;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Exceptions\Handler_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Gateways\Paypal\Scenarios_Logic\With_Resource_It;
use Jet_Form_Builder\Gateways\Scenarios_Abstract\Scenario_Logic_Base;
use Jet_Form_Builder\Gateways\Base_Gateway;
use Jet_Form_Builder\Actions\Types\Save_Record;

use Jet_FB_Paypal\DbModels\SubscriptionModel;
use Jet_FB_Paypal\QueryViews\SubscriptionWithRecord;
use Jet_FB_Paypal\DbModels\SubscriptionToRecordModel;




class Subscription_Logic extends Scenario_Logic_Base implements With_Resource_It {

	use Subscription_Connector;

	const QUERY_VAR = 'session_id';
	const SUBSCRIPTION_QUERY_VAR = 'subscription_id';

	const APPROVAL_PENDING = 'APPROVAL_PENDING';
	const APPROVED         = 'APPROVED';
	const ACTIVE           = 'ACTIVE';
	const SUSPENDED        = 'SUSPENDED';
	const CANCELLED        = 'CANCELLED';
	const EXPIRED          = 'EXPIRED';
	const REFUNDED         = 'REFUNDED';

	protected $subscription_id;

	/**
	 * @return mixed
	 */
	protected function query_token() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return sanitize_text_field( wp_unslash( $_GET[ self::SUBSCRIPTION_QUERY_VAR ] ?? '' ) );
	}

	public function get_failed_statuses() {
		return array( 'open', 'expired' );
	}

	public function after_actions() {
		( new Webhook_Manager() )->maybe_create_webhook();
		$this->set_gateway_data();

		$this->subscription_id = $this->create_subscription();

		$session = $this->create_resource();

		$this->add_context(
			array(
				'session_id' => $this->subscription_id,
			)
		);

		jet_fb_action_handler()->add_response(
			array( 'redirect' => $session['url'] )
		);

		Save_Record::add_hidden();

		add_action(
			'jet-form-builder/form-handler/after-send',
			array( $this, 'attach_record_id' )
		);
	}

	public function process_after() {

	}

	public function process_status( $type = 'success' ) {

	}


	public function create_resource() {
		$controller = jet_fb_gateway_current();

		$this->set_gateway_data();

		$request = ( new Create_Checkout_Session() )
			->set_bearer_auth( $controller->current_gateway( 'secret' ) )
			->set_currency( $controller->current_gateway( 'currency' ) )
			->set_price_id( $this->get_from_field_or_manual( 'plan_field', 'plan_manual' ) )
			->set_mode( 'subscription' )
			->set_urls(
				$this->get_referrer_url( Base_Gateway::SUCCESS_TYPE ),
				$this->get_referrer_url( Base_Gateway::FAILED_TYPE )
			)
			->add_body_param( 'metadata', array(
				'subscription_id'   => $this->subscription_id,
			))
			->add_body_param( 'subscription_data', array(
				'metadata' => array(
					'subscription_id'   => $this->subscription_id,
				),
			));

		do_action( 'jet-form-builder/gateways/before-create', $request );

		$session = $request->send_request();

		if ( isset( $session['error'] ) ) {
			throw new Gateway_Exception( $session['error']['message'], $session );
		}

		return $session;
	}

	public function create_subscription() {
		try {
			$primary_id = ( new SubscriptionModel() )->insert(
				array(
					'billing_id' => '',
					'gateway_id' => jet_fb_gateway_current()->get_id(),
					'scenario'   => self::scenario_id(),
					'form_id'    => jet_fb_handler()->form_id,
					'user_id'    => get_current_user_id(),
					'status'     => self::APPROVAL_PENDING,
				)
			);
			$price_id = $this->get_from_field_or_manual( 'plan_field', 'plan_manual' );

			$price = ( new Retrieve_Price() )
				->set_bearer_auth( jet_fb_gateway_current()->current_gateway( 'secret' ) )
				->set_path( [ 'id' => $price_id ] )
				->send_request();

			( new RecurringCyclesModel() )->insert(
				array(
					'subscription_id' => $primary_id,
					'quantity'        => 1,
					'interval_unit'   => $price['recurring']['interval'],
					'interval_count'  => $price['recurring']['interval_count'],
					'currency'        => $price['currency'],
					'amount'          => number_format( $price['unit_amount'] / 100, 2, '.', '' ),
					'tenure_type'     => 'regular',
				)
			);

		} catch ( Sql_Exception $exception ) {
			throw new Gateway_Exception( $exception->getMessage() );
		}

		return $primary_id;
	}

	public function save_resource( $session ) {

	}

	public function get_referrer_url( string $type ) {
		$url = parent::get_referrer_url( $type );

		return add_query_arg(
			array(
				self::QUERY_VAR => '{CHECKOUT_SESSION_ID}',
				self::SUBSCRIPTION_QUERY_VAR => $this->subscription_id
			),
			$url
		);
	}

	public function set_gateway_data() {
		jet_fb_gateway_current()->set_plan_field();
		jet_fb_gateway_current()->set_plan_from_field();
	}

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

	public function attach_record_id() {
		$record_id       = jet_fb_action_handler()->get_context( Save_Record::ID, 'id' );
		$session_id = $this->get_context( 'session_id' );

		if ( ! $record_id || ! $session_id ) {
			return;
		}

		( new SubscriptionToRecordModel() )->insert(
			array(
				'subscription_id' => $session_id,
				'record_id'       => $record_id,
			)
		);
	}

	protected function query_scenario_row(): array {
		try {
			return SubscriptionWithRecord::findOne(
				array(
					'id' => $this->query_token(),
				)
			)->query()->query_one();

		} catch ( Query_Builder_Exception $exception ) {
			throw new Gateway_Exception( $exception->getMessage() );
		}
	}

}