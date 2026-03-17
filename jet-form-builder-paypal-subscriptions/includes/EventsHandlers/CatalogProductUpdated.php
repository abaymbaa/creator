<?php


namespace Jet_FB_Paypal\EventsHandlers;

class CatalogProductUpdated extends Base\EventHandlerBase {

	public static function get_event_type() {
		return 'CATALOG.PRODUCT.UPDATED';
	}

}
