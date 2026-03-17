<?php


namespace Jet_FB_Paypal\FormEvents;


use Jet_FB_Paypal\Logic\SubscribeNow;
use Jet_Form_Builder\Actions\Events\Base_Gateway_Event;
use Jet_Form_Builder\Gateways\Paypal\Controller;

abstract class BaseSubscriptionEvent extends Base_Gateway_Event {

	public function executors(): array {
		return array(
			new BaseSubscriptionExecutor()
		);
	}

	public function get_gateway(): string {
		return Controller::ID;
	}

	public function get_scenario(): string {
		return SubscribeNow::scenario_id();
	}

}