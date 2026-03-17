<?php


namespace Jet_FB_Paypal\RestEndpoints;


use Jet_FB_Paypal\TableViews;
use Jet_Form_Builder\Gateways\Rest_Api\Receive_Payments;

class ReceivePayments extends Receive_Payments {

	public function get_table_view() {
		return new TableViews\Payments();
	}

}