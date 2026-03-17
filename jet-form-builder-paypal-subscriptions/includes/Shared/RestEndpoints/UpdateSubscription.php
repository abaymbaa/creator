<?php


namespace Jet_FB_Paypal\RestEndpoints;


use Jet_FB_Paypal\Pages\SubscriptionsPage;
use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_Form_Builder\Admin\Single_Pages\Base_Page_Updater;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

class UpdateSubscription extends Base_Page_Updater {

	/**
	 * @return string
	 */
	public static function get_page_slug(): string {
		return SubscriptionsPage::SLUG;
	}

	public function get_resource( \WP_REST_Request $request ) {
		$subscription_id = $request->get_param( 'id' );

		try {
			/**
			 * Execute this action if there is an entry
			 * with this $subscription_id in the database
			 */
			return SubscriptionsView::findById( $subscription_id );

		} catch ( Query_Builder_Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'message' => $exception->getMessage(),
					'data'    => $exception->get_additional(),
				),
				404
			);
		}
	}

}