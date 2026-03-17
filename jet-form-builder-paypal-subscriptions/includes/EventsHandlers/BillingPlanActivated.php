<?php


namespace Jet_FB_Paypal\EventsHandlers;

class BillingPlanActivated extends Base\EventHandlerBase {

	public static function get_event_type() {
		return 'BILLING.PLAN.ACTIVATED';
	}

}
