<?php


namespace Jet_FB_Paypal\EventsHandlers;


use Jet_FB_Paypal\FormEvents\SubscriptionCancelEvent;

class BillingSubscriptionCancelled extends Base\BillingSubscription {

	public function get_event_class(): string {
		return SubscriptionCancelEvent::class;
	}

	public static function get_event_type() {
		return 'BILLING.SUBSCRIPTION.CANCELLED';
	}

}
