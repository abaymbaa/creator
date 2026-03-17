<?php


namespace Jet_FB_Paypal\EventsHandlers;

use Jet_FB_Paypal\FormEvents\SubscriptionSuspendedEvent;

class BillingSubscriptionSuspended extends Base\BillingSubscription {

	public function get_event_class(): string {
		return SubscriptionSuspendedEvent::class;
	}

	public static function get_event_type() {
		return 'BILLING.SUBSCRIPTION.SUSPENDED';
	}

}
