<?php


namespace Jet_FB_Paypal\ApiActions;

use Jet_Form_Builder\Gateways\Paypal\Api_Actions\Base_Action;

class ShowSubscriptionDetailsAction extends Base_Action {

	protected $method = 'GET';

	public function action_slug() {
		return 'SHOW_SUBSCRIPTION_DETAILS';
	}

	public function action_endpoint() {
		return "v1/billing/subscriptions/{billing_id}";
	}

	public function action_headers() {
		return array(
			'Content-Type' => 'application/json',
		);
	}
}
