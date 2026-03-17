<?php


namespace Jet_FB_Paypal;

use Jet_FB_Paypal\EventsHandlers;
use Jet_Form_Builder\Classes\Instance_Trait;
use Jet_Form_Builder\Classes\Repository\Repository_Pattern_Trait;
use Jet_Form_Builder\Exceptions\Repository_Exception;

/**
 * @method static EventsListenersManager instance()
 *
 * Class EventsListenersManager
 * @package Jet_Form_Builder\Gateways\Paypal
 */
class EventsListenersManager {

	protected $response;

	use Instance_Trait;
	use Repository_Pattern_Trait;

	public function __construct() {
		$this->rep_install();
	}

	public function response(): PaypalRestResponse {
		if ( ! $this->response ) {
			$this->response = new PaypalRestResponse( 'Success' );
		}

		return $this->response;
	}

	public function rep_instances(): array {
		return apply_filters(
			'jet-form-builder/gateways/paypal/events',
			array(
				new EventsHandlers\BillingPlanActivated(),
				new EventsHandlers\BillingPlanCreated(),
				new EventsHandlers\BillingPlanDeactivated(),
				new EventsHandlers\BillingPlanPricingChangeActivated(),
				new EventsHandlers\BillingPlanUpdated(),
				new EventsHandlers\BillingSubscriptionActivated(),
				new EventsHandlers\BillingSubscriptionCancelled(),
				new EventsHandlers\BillingSubscriptionCreated(),
				new EventsHandlers\BillingSubscriptionExpired(),
				new EventsHandlers\BillingSubscriptionPaymentFailed(),
				new EventsHandlers\BillingSubscriptionSuspended(),
				new EventsHandlers\BillingSubscriptionUpdated(),
				new EventsHandlers\CatalogProductUpdated(),
				new EventsHandlers\PaymentSaleCompleted(),
				new EventsHandlers\PaymentSaleRefunded(),
				new EventsHandlers\PaymentSaleReversed(),
			)
		);
	}

	/**
	 * @param $event_type
	 *
	 * @return EventsHandlers\Base\EventHandlerBase
	 * @throws Repository_Exception
	 */
	public function get_event( $event_type ): EventsHandlers\Base\EventHandlerBase {
		return $this->rep_get_item( $event_type );
	}

	public function get_events_types_list() {
		return $this->rep_get_keys();
	}
}
