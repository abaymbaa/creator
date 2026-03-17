<?php


namespace Jet_FB_Paypal\Pages\Actions;

use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_FB_Paypal\Resources\GatewayResource;
use Jet_FB_Paypal\Resources\Subscription;
use Jet_Form_Builder\Classes\Arrayable\Array_Continue_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

abstract class BaseResourceArgs {

	abstract public function get_resource(): GatewayResource;

	public function get_id(): int {
		return jet_fb_current_page()->get_id();
	}

	/**
	 * @return array
	 */
	public function get_args(): array {
		$resource = $this->get_resource();

		return array(
			'gateway' => $resource->get_gateway_id(),
			'id'      => $resource->get_id(),
		);
	}

}
