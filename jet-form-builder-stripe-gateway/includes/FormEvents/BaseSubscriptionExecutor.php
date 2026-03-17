<?php


namespace Jet_FB_Stripe_Gateway\FormEvents;


use Jet_Form_Builder\Actions\Events\Base_Executor;

class BaseSubscriptionExecutor extends Base_Executor {

	public function is_supported(): bool {
		return true;
	}

}