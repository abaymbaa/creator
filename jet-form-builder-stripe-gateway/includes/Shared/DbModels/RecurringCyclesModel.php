<?php


namespace Jet_FB_Paypal\DbModels;


use Jet_Form_Builder\Db_Queries\Base_Db_Model;
use Jet_FB_Paypal\DbModels\Constraints\SubscriptionModelConstraint;

class RecurringCyclesModel extends Base_Db_Model {

	/**
	 * @inheritDoc
	 */
	public static function table_name(): string {
		return 'recurring_cycles';
	}

	/**
	 * @inheritDoc
	 */
	public static function schema(): array {
		return array(
			'id'              => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'subscription_id' => 'bigint(20)',
			'quantity'        => 'int(11)',
			'interval_unit'   => 'varchar(20)',
			'interval_count'  => 'int(11)',
			'currency'        => 'varchar(20)',
			'amount'          => 'decimal(10,2)',
			'tenure_type'     => 'varchar(20)',
			'created_at'      => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
			'updated_at'      => 'TIMESTAMP NOT NULL'
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function schema_keys(): array {
		return array(
			'id'              => 'primary key',
			'subscription_id' => 'index'
		);
	}

	public function foreign_relations(): array {
		return array(
			new SubscriptionModelConstraint()
		);
	}
}