<?php


namespace Jet_FB_Paypal\EventsHandlers;

class PaymentSaleReversed extends Base\EventHandlerBase {

	public static function get_event_type() {
		return 'PAYMENT.SALE.REVERSED';
	}

}
