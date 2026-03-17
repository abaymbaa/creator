<?php


namespace Jet_FB_Paypal\QueryViews;


use Jet_FB_Paypal\DbModels\SubscriptionModel;
use Jet_FB_Paypal\DbModels\SubscriptionToPaymentModel;
use Jet_Form_Builder\Db_Queries\Query_Builder;
use Jet_Form_Builder\Db_Queries\Views\View_Base;

class SubscriptionByPayment extends View_Base {

	public function table(): string {
		return SubscriptionToPaymentModel::table();
	}

	public function select_columns(): array {
		return array_merge(
			SubscriptionModel::schema_columns()
		);
	}

	public function get_dependencies(): array {
		return array(
			new SubscriptionToPaymentModel(),
		);
	}

	public function get_prepared_join( Query_Builder $builder ) {
		$relation      = SubscriptionToPaymentModel::table();
		$subscriptions = SubscriptionModel::table();

		$builder->join = "
LEFT JOIN `{$subscriptions}` ON 1=1
	AND `{$relation}`.`subscription_id` = `{$subscriptions}`.`id`
";
	}

}