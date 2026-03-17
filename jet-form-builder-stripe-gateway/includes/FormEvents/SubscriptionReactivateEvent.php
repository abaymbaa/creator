<?php


namespace Jet_FB_Stripe_Gateway\FormEvents;


class SubscriptionReactivateEvent extends BaseSubscriptionEvent {

	public function get_id(): string {
		return 'STRIPE.SUBSCRIPTION.REACTIVATE';
	}

	public function get_help(): string {
		return __(
			"is executed when a subscription is being activated. 
			Reactivation solely applies to the existing subscription and can be 
			carried out through a personal STRIPE business account, 
			namely, its <b><i>Pay & Get Paid > Subscriptions</i></b> section.",
			'jet-form-builder-paypal-subscriptions'
		);
	}

}