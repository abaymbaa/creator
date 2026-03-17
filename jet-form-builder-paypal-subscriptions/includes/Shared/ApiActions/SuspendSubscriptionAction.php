<?php


namespace Jet_FB_Paypal\ApiActions;

use Jet_Form_Builder\Gateways\Paypal\Api_Actions\Base_Action;

class SuspendSubscriptionAction extends Base_Action {

	protected $method = \WP_REST_Server::CREATABLE;

	public function action_slug() {
		return 'SUSPEND_SUBSCRIPTION';
	}

	public function action_endpoint() {
		return "v1/billing/subscriptions/{billing_id}/suspend";
	}

	public function action_headers() {
		return array(
			'Content-Type' => 'application/json',
		);
	}

	public function accept_code(): int {
		return self::CODE_NO_CONTENT;
	}

}