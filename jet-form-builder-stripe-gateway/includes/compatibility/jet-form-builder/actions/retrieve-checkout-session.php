<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Actions;


class Retrieve_Checkout_Session extends Base_Action {

	protected $method = \WP_REST_Server::READABLE;

	public function action_endpoint() {
		return 'v1/checkout/sessions/{id}';
	}

}