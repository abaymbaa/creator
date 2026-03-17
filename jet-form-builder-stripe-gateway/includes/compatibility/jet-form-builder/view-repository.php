<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder;


use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Views\Pay_Now_View;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Views\Subscription_View;
use Jet_Form_Builder\Gateways\Scenarios_Abstract\Scenarios_View_Repository;

class View_Repository extends Scenarios_View_Repository {

	public function rep_instances(): array {
		return array(
			new Pay_Now_View(),
			new Subscription_View(),
		);
	}
}