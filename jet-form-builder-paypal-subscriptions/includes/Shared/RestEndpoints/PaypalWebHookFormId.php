<?php

namespace Jet_FB_Paypal\RestEndpoints;

use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Gateways\Paypal;

class PaypalWebHookFormId extends Base\PayPalWebHookBase {

	public static function gateway_rest_base(): string {
		return 'event-subscription/(?P<id>[\d]+)';
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed|string
	 * @throws Gateway_Exception
	 */
	public function get_token( \WP_REST_Request $request ) {
		$params = $request->get_url_params();

		return Paypal\Controller::get_token_by_form_id( (int) ( $params['id'] ?? 0 ) );
	}


}
