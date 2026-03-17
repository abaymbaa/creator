<?php


namespace Jet_FB_Stripe_Gateway\FormEvents;


class SubscriptionExpiredEvent extends BaseSubscriptionEvent {

	public function get_id(): string {
		return 'STRIPE.SUBSCRIPTION.EXPIRED';
	}

	public function get_help(): string {
		return __(
			"is executed after the subscription expiry and 
			cannot be triggered \"manually.\"",
			'jet-form-builder-paypal-subscriptions'
		);
	}
}