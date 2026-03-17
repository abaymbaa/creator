<?php

namespace Jet_FB_Paypal\RestEndpoints;

use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Gateways\Paypal;

class PaypalWebHookGlobal extends Base\PayPalWebHookBase {

	public static function gateway_rest_base(): string {
		return 'event-subscription';
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed|string
	 * @throws Gateway_Exception
	 */
	public function get_token( \WP_REST_Request $request ) {
		return Paypal\Controller::get_token_global();
	}
}
