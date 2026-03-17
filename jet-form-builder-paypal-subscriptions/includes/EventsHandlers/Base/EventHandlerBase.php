<?php


namespace Jet_FB_Paypal\EventsHandlers\Base;

use Jet_FB_Paypal\EventsListenersManager;
use Jet_Form_Builder\Classes\Repository\Repository_Static_Item_It;

abstract class EventHandlerBase implements Repository_Static_Item_It {

	public static function rep_item_id() {
		return static::get_event_type();
	}

	abstract public static function get_event_type();

	public function on_catch_event( $webhook_event ) {
		$this->manager()->response()->set_headers_custom(
			array(
				'Webhook-Response' => 'Successfully catch!',
			)
		);
	}

	public function manager(): EventsListenersManager {
		return EventsListenersManager::instance();
	}

}
