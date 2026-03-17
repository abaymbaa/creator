<?php


namespace Jet_FB_Paypal\Pages\Actions;


use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_FB_Paypal\Resources\GatewayResource;
use Jet_FB_Paypal\Resources\Subscription;
use Jet_Form_Builder\Classes\Arrayable\Array_Continue_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

class SubscriptionArgs extends BaseResourceArgs {

	/**
	 * @return GatewayResource
	 * @throws Array_Continue_Exception
	 */
	public function get_resource(): GatewayResource {
		try {
			return new Subscription(
				SubscriptionsView::findById( $this->get_id() )
			);
		} catch ( Query_Builder_Exception $exception ) {
			throw new Array_Continue_Exception( $exception->getMessage(), ...$exception->get_additional() );
		}
	}

}