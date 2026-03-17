<?php


namespace Jet_FB_Paypal\RestEndpoints;

use Jet_FB_Paypal\Pages\MetaBoxes\SubscriptionGeneralBox;
use Jet_Form_Builder\Admin\Exceptions\Not_Found_Page_Exception;
use Jet_Form_Builder\Rest_Api\Dynamic_Rest_Url_Trait;
use Jet_Form_Builder\Rest_Api\Rest_Api_Endpoint_Base;

class ReceiveSubscription extends Rest_Api_Endpoint_Base {

	use Dynamic_Rest_Url_Trait;

	public static function get_rest_base() {
		return 'subscription/(?P<id>[\d]+)';
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
		$box = ( new SubscriptionGeneralBox() )->set_id( $request->get_param( 'id' ) );

		try {
			$record = $box->get_list();
		} catch ( Not_Found_Page_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => __( 'Subscription not found', 'jet-form-builder' ),
				),
				404
			);
		}

		return new \WP_REST_Response(
			array(
				'list' => $box->prepare_record( $record ),
			)
		);
	}
}
