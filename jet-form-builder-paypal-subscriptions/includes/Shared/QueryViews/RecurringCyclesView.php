<?php


namespace Jet_FB_Paypal\QueryViews;


use Jet_FB_Paypal\DbModels\RecurringCyclesModel;
use Jet_Form_Builder\Db_Queries\Views\View_Base;

class RecurringCyclesView extends View_Base {

	public function table(): string {
		return RecurringCyclesModel::table();
	}

	public function select_columns(): array {
		return RecurringCyclesModel::schema_keys();
	}
}