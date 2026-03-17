<?php


namespace Jet_FB_Paypal\EventsHandlers;


class BillingSubscriptionCreated extends Base\EventHandlerBase {

	public static function get_event_type() {
		return 'BILLING.SUBSCRIPTION.CREATED';
	}

}
