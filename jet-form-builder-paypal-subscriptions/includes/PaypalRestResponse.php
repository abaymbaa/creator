<?php


namespace Jet_FB_Paypal;


use Jet_Form_Builder\Rest_Api\Rest_Response;

class PaypalRestResponse extends Rest_Response {

	public function get_custom_header_prefix(): string {
		return ( parent::get_custom_header_prefix() . 'PAYPAL-' );
	}

}