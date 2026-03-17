<?php


namespace Jet_FB_Paypal\RestEndpoints;


use Jet_FB_Paypal\TableViews\Payments;
use Jet_Form_Builder\Gateways\Rest_Api\Delete_Payments_Endpoint;

class DeletePayments extends Delete_Payments_Endpoint {

	public function get_table_view() {
		return new Payments();
	}

}