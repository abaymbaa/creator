<?php


namespace Jet_FB_Paypal\Resources;

use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_FB_Paypal\ApiActions;

class Plan {

	private $plan_id;
	private $billing_cycles = array();

	public function __construct( $plan_id = '' ) {
		$this->plan_id = $plan_id;
	}

	public function get_plan_id(): string {
		return $this->plan_id;
	}

	/**
	 * @return Plan
	 * @throws Gateway_Exception
	 */
	public function details(): Plan {
		if ( ! $this->plan_id ) {
			throw new Gateway_Exception( __( 'Plan is not defined', 'jet-form-builder' ) );
		}

		$response = ( new ApiActions\ShowPlanDetailsAction() )
			->set_bearer_auth( jet_fb_gateway_current()->get_current_token() )
			->set_path(
				array(
					'plan_id' => $this->plan_id,
				)
			)
			->send_request();

		$this->billing_cycles = $response['billing_cycles'] ?? array();

		return $this;
	}

	public function get_cycles(): array {
		return $this->billing_cycles;
	}


}
