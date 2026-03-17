<?php


namespace Jet_FB_Paypal\FormEvents;


class SubscriptionSuspendedEvent extends BaseSubscriptionEvent {

	public function get_id(): string {
		return 'GATEWAY.SUBSCRIPTION.SUSPENDED';
	}

	public function get_help(): string {
		return __(
			"is executed when a subscription is stopped. 
			Suspension can be carried out similarly 
			to the routine described in the 
			<code>GATEWAY.SUBSCRIPTION.CANCEL</code> event.",
			'jet-form-builder-paypal-subscriptions'
		);
	}

}