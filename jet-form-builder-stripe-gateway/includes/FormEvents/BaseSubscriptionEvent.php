<?php


namespace Jet_FB_Stripe_Gateway\FormEvents;

use Jet_Form_Builder\Actions\Events\Base_Gateway_Event;

abstract class BaseSubscriptionEvent extends Base_Gateway_Event {

	public function executors(): array {
		return array(
			new BaseSubscriptionExecutor()
		);
	}

	public function get_gateway(): string {
		return 'stripe';
	}

	public function get_scenario(): string {
		return 'SUBSCRIPTION';
	}

}