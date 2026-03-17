<?php


namespace Jet_FB_Paypal\ApiActions;


use Jet_Form_Builder\Gateways\Paypal\Api_Actions\Base_Action;

class ShowProductDetails extends Base_Action {

	protected $method = 'GET';

	public function action_slug() {
		return 'SHOW_PRODUCT_DETAILS';
	}

	public function action_endpoint() {
		return 'v1/catalogs/products/{product_id}';
	}

	public function action_headers() {
		return array(
			'Content-Type' => 'application/json',
		);
	}
}