<?php


namespace Jet_FB_Paypal\RestEndpoints;


use Jet_FB_Paypal\QueryViews\SubscriptionsCount;
use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_FB_Paypal\TableViews\SubscribeNow;
use Jet_Form_Builder\Db_Queries\Views\View_Base;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Rest_Api\Dynamic_Rest_Url_Trait;
use Jet_Form_Builder\Rest_Api\Rest_Api_Endpoint_Base;
use Jet_Form_Builder\Rest_Api\Traits\Paginated_Args;

class DeleteSubscription extends Rest_Api_Endpoint_Base {

	use Dynamic_Rest_Url_Trait;

	/**
	 * @return mixed
	 */
	public static function get_rest_base() {
		return 'subscription/delete/(?P<id>[\d]+)';
	}

	/**
	 * @return mixed
	 */
	public static function get_methods() {
		return \WP_REST_Server::DELETABLE;
	}

	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed
	 */
	public function run_callback( \WP_REST_Request $request ) {
		$subscription_id = $request->get_param( 'id' );

		try {
			SubscriptionsView::delete( $subscription_id );
		} catch ( Query_Builder_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => __( 'Something went wrong on delete.', 'jet-form-builder' ),
				),
				503
			);
		}

		return new \WP_REST_Response(
			array(
				'message' => __( 'Successfully removed', 'jet-form-builder' ),
			)
		);
	}

}