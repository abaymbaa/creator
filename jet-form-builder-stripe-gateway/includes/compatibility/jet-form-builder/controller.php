<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder;

use Jet_FB_Stripe_Gateway\Compatibility\Base_Stripe;
use Jet_Form_Builder\Gateways\Base_Scenario_Gateway;
use Jet_Form_Builder\Classes\Tools;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Exceptions\Repository_Exception;
use Jet_Form_Builder\Gateways\Scenarios_Abstract\Scenario_Logic_Base;

class Controller extends Base_Scenario_Gateway {

	use Base_Stripe;

	protected $token_query_name = 'session_id';

	protected $plan_field;
	protected $plan_var;

	/**
	 * @return Scenario_Logic_Base
	 * @throws Repository_Exception
	 */
	public function get_scenario() {
		return Scenarios_Manager::instance()->get_logic( $this );
	}

	/**
	 * @return Scenario_Logic_Base
	 * @throws Gateway_Exception
	 */
	public function query_scenario() {
		return Scenarios_Manager::instance()->query_logic();
	}

	public function custom_labels(): array {
		return array(
			'scenario' => Scenarios_Manager::instance()->view()->get_editor_labels(),
		);
	}

	public function additional_editor_data(): array {
		return array_merge(
			array(
				'version'   => 1,
				'scenarios' => Tools::with_placeholder(
					Scenarios_Manager::instance()->view()->get_items_list(),
					__( 'Choose scenario...', 'jet-form-builder' )
				),
			),
			Scenarios_Manager::instance()->view()->get_editor_data()
		);
	}

	protected function get_price( $price ) {
		return ( (float) $price ) * 100;
	}

	public function set_plan_field() {
		$scenario = $this->current_gateway();

		if ( empty( $scenario['plan_field'] ) ) {
			return;
		}

		$this->plan_field = $scenario['plan_field'];
	}

	public function set_plan_from_field() {
		if ( empty( $this->plan_field ) ) {
			return;
		}

		$value = jet_fb_action_handler()->request_data( $this->plan_field );

		if ( ! empty( $value ) ) {
			$this->plan_var = $value;
		}
	}

	public function get_plan_var() {
		return $this->plan_var;
	}

	public function get_current_token() {
		$client_id = $this->current_gateway( 'client_id' );
		$secret    = $this->current_gateway( 'secret' );

		return self::get_token_with_credits( $client_id, $secret );
	}

}
