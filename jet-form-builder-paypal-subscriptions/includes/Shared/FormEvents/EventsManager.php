<?php


namespace Jet_FB_Paypal\FormEvents;


use Jet_Form_Builder\Actions\Events\Base_Event;

class EventsManager {

	public static function register() {
		add_filter(
			'jet-form-builder/event-types',
			array( static::class, 'add_events' )
		);
	}

	/**
	 * @param Base_Event[] $events
	 *
	 * @return array
	 */
	public static function add_events( array $events ): array {
		array_push(
			$events,
			new SubscriptionCancelEvent(),
			new SubscriptionExpiredEvent(),
			new SubscriptionSuspendedEvent(),
			new SubscriptionReactivateEvent(),
			new RenewalPaymentEvent()
		);

		return $events;
	}

}