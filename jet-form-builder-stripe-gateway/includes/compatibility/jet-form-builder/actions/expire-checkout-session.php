<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Actions;


use Jet_Form_Builder\Gateways\Scenarios_Abstract\Scenario_Logic_Base;

class Expire_Checkout_Session extends Base_Action {

	public function action_endpoint() {
		return 'v1/checkout/sessions/{id}/expire';
	}

	public static function expire( Scenario_Logic_Base $logic, array $session ) {
		if ( 'open' !== ( $session['status'] ?? '' ) ) {
			return;
		}
		( new static() )
			->set_bearer_auth( jet_fb_gateway_current()->current_gateway( 'secret' ) )
			->set_path( array( 'id' => $logic->get_scenario_row( 'transaction_id' ) ) )
			->send_request();
	}

}