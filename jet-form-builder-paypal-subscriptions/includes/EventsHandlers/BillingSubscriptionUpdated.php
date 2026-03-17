<?php


namespace Jet_FB_Paypal\EventsHandlers;

class BillingSubscriptionUpdated extends Base\EventHandlerBase {

	public static function get_event_type() {
		return 'BILLING.SUBSCRIPTION.UPDATED';
	}

}
