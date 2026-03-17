<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Logic;


use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Actions\Create_Checkout_Session;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Actions\Expire_Checkout_Session;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Actions\Retrieve_Checkout_Session;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Pay_Now_Connector;
use Jet_Form_Builder\Actions\Types\Save_Record;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Db_Queries\Execution_Builder;
use Jet_Form_Builder\Exceptions\Action_Exception;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Exceptions\Repository_Exception;
use Jet_Form_Builder\Gateways\Db_Models\Payer_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payer_Shipping_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payment_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payment_To_Payer_Shipping_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payment_To_Record;
use Jet_Form_Builder\Gateways\Paypal\Scenarios_Logic\With_Resource_It;
use Jet_Form_Builder\Gateways\Query_Views\Payment_With_Record_View;
use Jet_Form_Builder\Gateways\Scenarios_Abstract\Scenario_Logic_Base;
use Jet_Form_Builder\Gateways\Base_Gateway;

class Pay_Now_Logic extends Scenario_Logic_Base implements With_Resource_It {

	use Pay_Now_Connector;

	const QUERY_VAR = 'session_id';

	/**
	 * @return mixed
	 */
	protected function query_token() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return sanitize_text_field( wp_unslash( $_GET[ self::QUERY_VAR ] ?? '' ) );
	}

	public function get_failed_statuses() {
		return array( 'open', 'expired' );
	}


	/**
	 * @throws Gateway_Exception
	 * @throws Repository_Exception
	 */
	public function after_actions() {
		$this->set_gateway_data();

		/**
		 * Create payment by /v1/checkout/sessions
		 */
		$payment = $this->create_resource();

		/**
		 * By default save payment id & form data to inserted post meta
		 */
		$this->add_context(
			array(
				'payment_id' => $this->save_resource( $payment ),
			)
		);

		/**
		 * Redirect to Stripe checkout for approve payment
		 */
		jet_fb_action_handler()->add_response(
			array( 'redirect' => $payment['url'] )
		);

		Save_Record::add_hidden();

		add_action(
			'jet-form-builder/form-handler/after-send',
			array( $this, 'attach_record_id' )
		);
	}

	/**
	 * @return array
	 * @throws Gateway_Exception
	 * @throws Repository_Exception
	 */
	public function create_resource() {
		$controller = jet_fb_gateway_current();

		$request = ( new Create_Checkout_Session() )
			->set_bearer_auth( $controller->current_gateway( 'secret' ) )
			->set_currency( $controller->current_gateway( 'currency' ) )
			->set_price( $controller->get_price_var() )
			->set_urls(
				$this->get_referrer_url( Base_Gateway::SUCCESS_TYPE ),
				$this->get_referrer_url( Base_Gateway::FAILED_TYPE )
			);

		do_action( 'jet-form-builder/gateways/before-create', $request );

		$session = $request->send_request();

		if ( isset( $session['error'] ) ) {
			throw new Gateway_Exception( $session['error']['message'], $session );
		}

		return $session;
	}


	/**
	 * @throws Sql_Exception
	 * @throws Repository_Exception
	 */
	public function attach_record_id() {
		$record_id  = jet_fb_action_handler()->get_context( Save_Record::ID, 'id' );
		$payment_id = $this->get_context( 'payment_id' );

		if ( ! $record_id || ! $payment_id ) {
			return;
		}

		( new Payment_To_Record() )->insert(
			array(
				'record_id'  => $record_id,
				'payment_id' => $payment_id,
			)
		);
	}

	/**
	 * @throws Gateway_Exception
	 */
	public function process_after() {
		if ( 'CREATED' !== $this->get_scenario_row( 'status' ) ) {
			throw new Gateway_Exception( 'Payment was already captured' );
		}

		$session = ( new Retrieve_Checkout_Session() )
			->set_bearer_auth( jet_fb_gateway_current()->current_gateway( 'secret' ) )
			->set_path( array( 'id' => $this->get_scenario_row( 'transaction_id' ) ) )
			->send_request();

		// throw an exception
		if ( isset( $session['error'] ) ) {
			$this->on_error( $session );
		}

		// throw an exception
		if ( 'complete' !== ( $session['status'] ?? '' ) ) {
			Expire_Checkout_Session::expire( $this, $session );

			$this->on_error( $session );
		}

		try {
			Execution_Builder::instance()->transaction_start();

			$this->save_payment( $session );

			Execution_Builder::instance()->transaction_commit();

		} catch ( Sql_Exception $exception ) {
			Execution_Builder::instance()->transaction_rollback();

			throw new Gateway_Exception( $exception->getMessage() );
		}
	}

	/**
	 * @param array $payment
	 * @param string $log_message
	 *
	 * @throws Gateway_Exception
	 */
	private function on_error( array $payment, string $log_message = '' ) {
		if ( ! $log_message ) {
			$log_message = __( 'Payment was voided', 'jet-form-builder-stripe-gateway' );
		}

		$this->scenario_row(
			array(
				'status' => 'VOIDED',
			)
		);
		try {
			( new Payment_Model() )->update(
				array(
					'status' => 'VOIDED',
				),
				array(
					'id' => $this->get_scenario_row( 'id' ),
				)
			);
		} catch ( Sql_Exception $exception ) {
			return;
		} finally {
			throw new Gateway_Exception( $log_message, $payment );
		}
	}


	/**
	 * @param array $payment
	 *
	 * @throws Sql_Exception
	 */
	private function save_payment( array $payment ) {
		( new Payment_Model() )->update(
			array(
				'status' => 'COMPLETED',
			),
			array(
				'id' => $this->get_scenario_row( 'id' ),
			)
		);

		/**
		 * We save the current status of the payment,
		 * so that we can then determine which actions
		 * to execute and which message to show to the user
		 */
		$this->scenario_row(
			array(
				'status' => $payment['status'] ?? 'expired',
			)
		);

		$customer = $payment['customer_details'] ?? array();
		$name     = explode( ' ', $customer['name'] ?? '' );

		$payer_id = Payer_Model::insert_or_update(
			array(
				'user_id'    => $this->get_scenario_row( 'user_id' ),
				'payer_id'   => $payment['customer'] ?? '',
				'first_name' => $name[0] ?? null,
				'last_name'  => $name[1] ?? null,
				'email'      => $customer['email'],
			)
		);

		$address = $payment['shipping']['address'] ?? array();

		$payer_ship_id = ( new Payer_Shipping_Model() )->insert(
			array(
				'payer_id'       => $payer_id,
				'full_name'      => $payment['shipping']['name'] ?? '',
				'address_line_1' => $address['line1'] ?? '',
				'address_line_2' => $address['line2'] ?? '',
				'admin_area_2'   => $address['city'] ?? '',
				'admin_area_1'   => $address['state'] ?? '',
				'postal_code'    => $address['postal_code'] ?? '',
				'country_code'   => $address['country'] ?? '',
			)
		);

		( new Payment_To_Payer_Shipping_Model() )->insert(
			array(
				'payment_id'        => $this->get_scenario_row( 'id' ),
				'payer_shipping_id' => $payer_ship_id,
			)
		);
	}

	/**
	 * @return array
	 * @throws Gateway_Exception
	 */
	protected function query_scenario_row() {
		try {
			return Payment_With_Record_View::findOne(
				array(
					'transaction_id' => $this->get_queried_token(),
				)
			)->query()->query_one();

		} catch ( Query_Builder_Exception $exception ) {
			throw new Gateway_Exception( $exception->getMessage() );
		}
	}

	/**
	 * @throws Gateway_Exception
	 */
	public function set_gateway_data() {
		jet_fb_gateway_current()->set_price_field();
		jet_fb_gateway_current()->set_price_from_filed();
	}

	/**
	 * @param $resource
	 *
	 * @return int
	 * @throws Gateway_Exception
	 */
	public function save_resource( $resource ) {
		$payment_row = array(
			'transaction_id'         => $resource['id'],
			'initial_transaction_id' => $resource['id'],
			'form_id'                => jet_fb_handler()->form_id,
			'user_id'                => get_current_user_id(),
			'gateway_id'             => jet_fb_gateway_current()->get_id(),
			'scenario'               => self::scenario_id(),
			'amount_value'           => jet_fb_gateway_current()->get_price_var() / 100,
			'amount_code'            => jet_fb_gateway_current()->current_gateway( 'currency' ),
			'type'                   => Base_Gateway::PAYMENT_TYPE_INITIAL,
			'status'                 => 'CREATED',
		);

		try {
			return ( new Payment_Model() )->insert( $payment_row );
		} catch ( Sql_Exception $exception ) {
			throw new Gateway_Exception( $exception->getMessage() );
		}
	}

	public function get_referrer_url( string $type ) {
		$url = parent::get_referrer_url( $type );

		return add_query_arg(
			array(
				self::QUERY_VAR => '{CHECKOUT_SESSION_ID}'
			),
			$url
		);
	}
}