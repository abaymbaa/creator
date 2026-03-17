<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Actions;

class Retrieve_Balance extends Base_Action {

	protected $method = 'GET';

	/**
	 * @return mixed
	 */
	public function action_endpoint() {
		return 'v1/balance';
	}
}
