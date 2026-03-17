<?php


namespace Jet_FB_Paypal\QueryViews;

use Jet_FB_Paypal\DbModels\SubscriptionToRecordModel;
use Jet_Form_Builder\Actions\Methods\Form_Record\Models\Record_Model;
use Jet_Form_Builder\Db_Queries\Query_Builder;
use Jet_Form_Builder\Db_Queries\Views\View_Base;

class RecordBySubscription extends View_Base {

	public function table(): string {
		return SubscriptionToRecordModel::table();
	}

	public function select_columns(): array {
		return Record_Model::schema_columns();
	}

	public function get_dependencies(): array {
		return array(
			new SubscriptionToRecordModel(),
		);
	}

	/**
	 * @param Query_Builder $builder
	 */
	public function get_prepared_join( Query_Builder $builder ) {
		$subscription_to_record = SubscriptionToRecordModel::table();
		$records                = Record_Model::table();

		$builder->join = "
LEFT JOIN `{$records}` ON 1=1
    AND `{$records}`.`id` = `{$subscription_to_record}`.`record_id`
		";
	}
}
