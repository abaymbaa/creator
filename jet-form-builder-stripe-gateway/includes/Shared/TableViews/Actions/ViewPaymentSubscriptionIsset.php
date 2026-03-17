<?php


namespace Jet_FB_Paypal\TableViews\Actions;

use Jet_FB_Paypal\TableViews\Columns\SubscriptionColumn;
use Jet_Form_Builder\Admin\Exceptions\Not_Found_Page_Exception;
use Jet_Form_Builder\Admin\Table_Views\Actions\Link_Single_Action;
use Jet_FB_Paypal\Pages;

class ViewPaymentSubscriptionIsset extends Link_Single_Action {

	use ShowIfSubscriptionIsset;

	public function get_slug(): string {
		return 'view_subscription';
	}

	public function get_label(): string {
		return __( 'View Subscription' );
	}

	public function show_in_header(): bool {
		return false;
	}

	/**
	 * @param array $record
	 *
	 * @return string
	 * @throws Not_Found_Page_Exception
	 */
	public function get_href( array $record ): string {
		$subscription_id = ( new SubscriptionColumn() )->get_value( $record );
		$single          = ( new Pages\SingleSubscriptionPage() )->set_id( $subscription_id );

		return $single->get_url();
	}
}
