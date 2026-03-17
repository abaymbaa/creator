<?php


namespace Jet_FB_Paypal\Proxy;


use Jet_FB_Paypal\Logic\SubscribeNow;
use JetPaypalCore\JetFormBuilder\Paypal\ScenariosLogicProxy;

class ScenariosLogic extends ScenariosLogicProxy {

	public function plugin_version_compare(): string {
		return '2.0.3';
	}

	public function scenarios(): array {
		return array(
			new SubscribeNow()
		);
	}

	public function on_base_need_update() {
	}

	public function on_base_need_install() {
	}
}