<?php


namespace Jet_FB_Paypal\RestEndpoints;


use Jet_FB_Paypal\DbModels\SubscriptionNoteModel;
use Jet_Form_Builder\Exceptions\Silence_Exception;
use Jet_Form_Builder\Rest_Api\Dynamic_Rest_Url_Trait;
use Jet_Form_Builder\Rest_Api\Rest_Api_Endpoint_Base;

class AddSubscriptionNote extends Rest_Api_Endpoint_Base {

	use Dynamic_Rest_Url_Trait;

	public static function get_rest_base() {
		return 'subscription-note/(?P<id>[\d]+)';
	}

	public static function get_methods() {
		return \WP_REST_Server::CREATABLE;
	}

	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function run_callback( \WP_REST_Request $request ) {
		$body   = $request->get_json_params();
		$sub_id = $request->get_param( 'id' );

		$text = $body['note'] ?? '';

		try {
			SubscriptionNoteModel::add( array(
				'subscription_id' => $sub_id,
				'message'         => $text
			) );

		} catch ( Silence_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => $exception->getMessage(),
				),
				503
			);
		}

		return new \WP_REST_Response( array() );
	}
}