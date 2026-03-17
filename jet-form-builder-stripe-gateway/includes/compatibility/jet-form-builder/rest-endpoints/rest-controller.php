<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Rest_Endpoints;


use Jet_Form_Builder\Rest_Api\Rest_Api_Controller_Base;
use Jet_Form_Builder\Rest_Api\Rest_Api_Endpoint_Base;

class Rest_Controller extends Rest_Api_Controller_Base {

	/**
	 * @return Rest_Api_Endpoint_Base[]
	 */
	public function routes(): array {
		return array(
			new Fetch_Pay_Now_Editor(),
			new Fetch_Stripe_Plans(),
			new Refund_Payment(),
			new Cancel_Subscription(),
			new Subscription_Suspend(),
		);
	}
}