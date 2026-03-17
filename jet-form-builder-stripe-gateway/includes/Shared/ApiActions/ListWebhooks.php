<?php


namespace Jet_FB_Paypal\ApiActions;

use Jet_Form_Builder\Gateways\Paypal\Api_Actions\Base_Action;

class ListWebhooks extends Base_Action {

	protected $method = 'GET';

	public function action_slug() {
		return 'LIST_WEBHOOKS';
	}

	public function action_endpoint() {
		return 'v1/notifications/webhooks';
	}

	public function action_headers() {
		return array(
			'Content-Type' => 'application/json',
		);
	}


}
