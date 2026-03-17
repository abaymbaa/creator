<?php


namespace Jet_FB_Paypal\RestEndpoints;


use Jet_FB_Paypal\Pages\MetaBoxes\SubscriptionPaymentsBox;
use Jet_Form_Builder\Admin\Exceptions\Empty_Box_Exception;
use Jet_Form_Builder\Db_Queries\Views\View_Base;
use Jet_Form_Builder\Rest_Api\Rest_Api_Endpoint_Base;
use Jet_Form_Builder\Rest_Api\Traits;
use Jet_Form_Builder\Rest_Api\Dynamic_Rest_Url_Trait;

class FetchPaymentsBySubscription extends Rest_Api_Endpoint_Base {

	use Traits\Paginated_Args;
	use Dynamic_Rest_Url_Trait;

	public static function get_rest_base() {
		return 'subscription/payments-box/(?P<id>[\d]+)';
	}

	public function get_common_args(): array {
		return array(
			'id' => array(
				'type'     => 'integer',
				'required' => true,
			),
		);
	}

	public static function get_methods() {
		return \WP_REST_Server::READABLE;
	}

	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function run_callback( \WP_REST_Request $request ) {
		$box  = ( new SubscriptionPaymentsBox() )->set_id( $request->get_param( 'id' ) );
		$args = View_Base::get_paginated_args( $this->get_paginate_args( $request ) );

		try {
			$records = $box->get_raw_list( $args );
		} catch ( Empty_Box_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => __( 'Payments not found', 'jet-form-builder' ),
				),
				404
			);
		}

		return new \WP_REST_Response(
			array(
				'list'  => $box->prepare_list( $records ),
				'total' => $box->get_total(),
			)
		);
	}

}