<?php


namespace Jet_FB_Paypal\RestEndpoints;


use Jet_FB_Paypal\QueryViews\SubscriptionsCount;
use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_FB_Paypal\TableViews\SubscribeNow;
use Jet_Form_Builder\Db_Queries\Views\View_Base;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Rest_Api\Rest_Api_Endpoint_Base;
use Jet_Form_Builder\Rest_Api\Traits\Paginated_Args;

class DeleteSubscriptions extends Rest_Api_Endpoint_Base {

	use Paginated_Args;

	/**
	 * @return mixed
	 */
	public static function get_rest_base() {
		return 'subscriptions/delete';
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
		$body = $request->get_json_params();
		$view = new SubscribeNow();

		$args = View_Base::get_paginated_args( $this->get_paginate_args( $request ) );

		try {
			SubscriptionsView::delete(
				array(
					array(
						'type'   => 'in',
						'values' => array( 'id', $body['checked'] ?? array() ),
					),
				)
			);
		} catch ( Query_Builder_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => __( 'Something went wrong on delete.', 'jet-form-builder' ),
				),
				503
			);
		}
		$list = $view->get_raw_list( $args );

		return new \WP_REST_Response(
			array(
				'message' => __( 'Successfully removed', 'jet-form-builder' ),
				'list'    => $view->prepare_list( $list ),
				'total'   =>SubscriptionsCount::count( $args ),
			)
		);
	}

}