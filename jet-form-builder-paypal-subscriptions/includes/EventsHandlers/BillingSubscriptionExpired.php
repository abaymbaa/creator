<?php


namespace Jet_FB_Paypal\EventsHandlers;


use Jet_FB_Paypal\FormEvents\SubscriptionExpiredEvent;

class BillingSubscriptionExpired extends Base\BillingSubscription {

	public function get_event_class(): string {
		return SubscriptionExpiredEvent::class;
	}

	public static function get_event_type() {
		return 'BILLING.SUBSCRIPTION.EXPIRED';
	}

}



