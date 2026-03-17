<?php


namespace Jet_FB_Paypal\QueryViews;


use Jet_FB_Paypal\DbModels\SubscriptionToPayerShipping;
use Jet_Form_Builder\Db_Queries\Views\View_Base;

class SubscriptionPayerShipping extends View_Base {

	public function table(): string {
		return SubscriptionToPayerShipping::table();
	}

	public function select_columns(): array {
		return SubscriptionToPayerShipping::schema_columns();
	}

	public function get_dependencies(): array {
		return array(
			new SubscriptionToPayerShipping(),
		);
	}
}