<?php


namespace Jet_FB_Paypal\TableViews;

use Jet_FB_Paypal\QueryViews\SubscriptionsCount;
use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_FB_Paypal\RestEndpoints;
use Jet_FB_Paypal\TableViews\Columns\BillingCycleColumn;
use Jet_FB_Paypal\TableViews\Columns\PrimarySubscriberColumn;
use Jet_FB_Paypal\TableViews\Columns\SubscriberColumn;
use Jet_FB_Paypal\TableViews\Columns\SubscriptionRowActions;
use Jet_FB_Paypal\TableViews\Columns\SubscriptionStatusColumn;
use Jet_Form_Builder\Admin\Table_Views\Column_Base;
use Jet_Form_Builder\Admin\Table_Views\Columns\Created_At_Column;
use Jet_Form_Builder\Admin\Table_Views\Columns\Record_Id_Column_Advanced;
use Jet_Form_Builder\Admin\Table_Views\View_Advanced_Base;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

class SubscribeNow extends View_Advanced_Base {

	public function get_raw_list( array $args ): array {
		try {
			return ( new SubscriptionsView() )
				->set_table_args( $args )
				->query()
				->query_all();

		} catch ( Query_Builder_Exception $exception ) {
			return array();
		}
	}

	public function get_rest_url(): string {
		return RestEndpoints\ReceiveSubscriptions::rest_url();
	}

	public function get_rest_methods(): string {
		return RestEndpoints\ReceiveSubscriptions::get_methods();
	}

	public function get_total(): int {
		return SubscriptionsCount::count();
	}

	public function get_columns(): array {
		return array(
			'subscriber'         => new PrimarySubscriberColumn(),
			'billing_cycle'      => new BillingCycleColumn(),
			'status'             => new SubscriptionStatusColumn(),
			'create_time'        => new Created_At_Column(),
			'id'                 => new Record_Id_Column_Advanced(),
			Column_Base::ACTIONS => new SubscriptionRowActions(),
		);
	}

}
