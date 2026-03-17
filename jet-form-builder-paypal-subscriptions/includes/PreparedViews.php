<?php


namespace Jet_FB_Paypal;

use Jet_FB_Paypal\ApiActions;
use Jet_Form_Builder\Exceptions\Gateway_Exception;

class PreparedViews {


	/**
	 * @param string $token
	 * @param string $plan_id
	 *
	 * @throws Gateway_Exception
	 */
	public static function get_plan_by_id( string $token, string $plan_id ) {

	}

	/**
	 * @param string $token
	 * @param string $product_id
	 *
	 * @return mixed
	 * @throws Gateway_Exception
	 */
	public static function get_product_by_id( string $token, string $product_id ) {
		return ( new ApiActions\ShowProductDetails() )
			->set_bearer_auth( $token )
			->set_path(
				array(
					'product_id' => $product_id,
				)
			)
			->send_request();
	}


}
