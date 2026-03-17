<?php


namespace Jet_FB_Paypal\Proxy;



use Jet_FB_Paypal\ScenarioViews\SubscriptionScenarioView;
use JetPaypalCore\JetFormBuilder\Paypal\ScenariosViewProxy;

class ScenariosViews extends ScenariosViewProxy {

	public function plugin_version_compare(): string {
		return '2.0.3';
	}

	public function scenarios(): array {
		return array(
			new SubscriptionScenarioView(),
		);
	}

	public function on_base_need_update() {
	}

	public function on_base_need_install() {
	}
}