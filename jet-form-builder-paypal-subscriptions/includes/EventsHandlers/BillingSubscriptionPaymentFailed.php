<?php


namespace Jet_FB_Paypal\EventsHandlers;

class BillingSubscriptionPaymentFailed extends Base\BillingSubscription {

	public static function get_event_type() {
		return 'BILLING.SUBSCRIPTION.PAYMENT.FAILED';
	}

}
