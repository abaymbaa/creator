<?php


namespace Jet_FB_Paypal\ApiActions;

use Jet_Form_Builder\Gateways\Paypal\Api_Actions;

class SubscribeNowAction extends Api_Actions\Base_Action {

	use Api_Actions\Traits\App_Context_Trait;

	private $plan_id;

	const SLUG = 'SUBSCRIBE_NOW';

	public function action_slug() {
		return self::SLUG;
	}

	public function action_endpoint() {
		return 'v1/billing/subscriptions';
	}

	public function action_headers() {
		return array(
			'Content-Type' => 'application/json',
			'Prefer'       => 'return=representation'
		);
	}
}
