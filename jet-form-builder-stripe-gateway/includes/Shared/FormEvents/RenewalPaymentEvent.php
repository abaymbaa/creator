<?php


namespace Jet_FB_Paypal\FormEvents;


class RenewalPaymentEvent extends BaseSubscriptionEvent {

	public function get_id(): string {
		return 'RENEWAL.PAYMENT';
	}

	public function get_help(): string {
		return __(
			'executed each time a renewal payment is received.',
			'jet-form-builder-paypal-subscriptions'
		);
	}
}