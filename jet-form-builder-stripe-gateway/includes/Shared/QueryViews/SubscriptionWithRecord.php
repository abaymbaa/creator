<?php


namespace Jet_FB_Paypal\QueryViews;

use Jet_FB_Paypal\DbModels\SubscriptionModel;
use Jet_FB_Paypal\DbModels\SubscriptionToRecordModel;
use Jet_Form_Builder\Actions\Methods\Form_Record\Models\Record_Model;
use Jet_Form_Builder\Db_Queries\Query_Builder;

class SubscriptionWithRecord extends SubscriptionsView {

	public function get_prepared_join( Query_Builder $builder ) {
		parent::get_prepared_join( $builder );

		$sub_to_record = ( new SubscriptionToRecordModel() )->create()::table();
		$subscription  = SubscriptionModel::table();
		$record        = Record_Model::table();

		$builder->join .= "
LEFT JOIN `{$sub_to_record}` ON 1=1
    AND `{$subscription}`.`id` = `{$sub_to_record}`.`subscription_id`
    
LEFT JOIN `{$record}` ON 1=1
    AND `{$record}`.`id` = `{$sub_to_record}`.`record_id`
		";
	}

	public function select_columns(): array {
		return array_merge(
			parent::select_columns(),
			Record_Model::schema_columns( 'record' )
		);
	}

}
