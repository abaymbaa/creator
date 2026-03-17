<?php


namespace Jet_FB_Paypal\EventsHandlers;

use Jet_FB_Paypal\FormEvents\SubscriptionReactivateEvent;

class BillingSubscriptionActivated extends Base\BillingSubscription {

	public function get_event_class(): string {
		return SubscriptionReactivateEvent::class;
	}

	public static function get_event_type() {
		return 'BILLING.SUBSCRIPTION.ACTIVATED';
	}
}
