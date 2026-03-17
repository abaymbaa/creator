<?php


namespace Jet_FB_Paypal\RestEndpoints;


use Jet_FB_Paypal\Pages\MetaBoxes\PaymentDetailsBox;
use Jet_Form_Builder\Gateways\Meta_Boxes\Payment_Details_Box;
use Jet_Form_Builder\Gateways\Rest_Api\Receive_Payment;

class ReceivePayment extends Receive_Payment {

	public function get_box(): Payment_Details_Box {
		return new PaymentDetailsBox();
	}

}