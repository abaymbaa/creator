<?php


namespace Jet_FB_Paypal\ApiActions;

use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Gateways\Paypal\Api_Actions\Base_Action;

class ShowPlanDetailsAction extends Base_Action {

	protected $method = 'GET';

	public function action_slug() {
		return 'SHOW_PLAN_DETAILS';
	}

	public function action_endpoint() {
		return 'v1/billing/plans/{plan_id}';
	}

	public function action_headers() {
		return array(
			'Content-Type' => 'application/json',
		);
	}

	public function send_request() {
		$plan = parent::send_request();

		if ( ! isset( $plan['id'] ) ) {
			throw new Gateway_Exception( 'Plan is not found.', $plan );
		}

		return $plan;
	}
}
