<?php


namespace Jet_FB_Paypal\FormEvents;


class SubscriptionCancelEvent extends BaseSubscriptionEvent {

	public function get_id(): string {
		return 'GATEWAY.SUBSCRIPTION.CANCEL';
	}

	public function get_help(): string {
		return __(
			"is executed when a subscription is canceled.
			Cancellation can be made on the website through the <b><i>JetFormBuilder > Subscriptions</i></b> page, 
			a single subscription page, and through a personal PayPal business account, namely, 
			its <b><i>Pay & Get Paid > Subscriptions</i></b> section.",
			'jet-form-builder-paypal-subscriptions'
		);
	}

}