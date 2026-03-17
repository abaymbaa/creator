<?php


namespace Jet_FB_Paypal\DbModels;


use Jet_FB_Paypal\DbModels\Constraints\SubscriptionModelConstraint;
use Jet_Form_Builder\Actions\Methods\Form_Record\Constraints\Record_Model_Constraint;
use Jet_Form_Builder\Db_Queries\Base_Db_Model;

class SubscriptionToRecordModel extends Base_Db_Model {

	public static function table_name(): string {
		return 'subscriptions_to_records';
	}

	/**
	 * @inheritDoc
	 */
	public static function schema(): array {
		return array(
			'id'              => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'record_id'       => 'bigint(20) NOT NULL',
			'subscription_id' => 'bigint(20) NOT NULL',
		);
	}

	public static function schema_keys(): array {
		return array(
			'id'              => 'primary key',
			'subscription_id' => 'index',
			'record_id'       => 'index'
		);
	}

	public function foreign_relations(): array {
		return array(
			new Record_Model_Constraint(),
			new SubscriptionModelConstraint(),
		);
	}

}