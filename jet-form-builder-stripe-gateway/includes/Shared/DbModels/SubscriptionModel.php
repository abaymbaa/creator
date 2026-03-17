<?php


namespace Jet_FB_Paypal\DbModels;


use Jet_Form_Builder\Db_Queries\Base_Db_Model;
use Jet_Form_Builder\Db_Queries\Constraints\Form_Constraint;

class SubscriptionModel extends Base_Db_Model {

	/**
	 * @inheritDoc
	 */
	public static function table_name(): string {
		return 'subscriptions';
	}

	/**
	 * @inheritDoc
	 */
	public static function schema(): array {
		return array(
			'id'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'form_id'    => 'bigint(20) UNSIGNED NOT NULL',
			'user_id'    => 'bigint(20)',
			'billing_id' => 'varchar(255)',
			'gateway_id' => 'varchar(100)',
			'scenario'   => 'varchar(100)',
			'status'     => 'varchar(100)',
			'created_at' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
			'updated_at' => 'TIMESTAMP NOT NULL'
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function schema_keys(): array {
		return array(
			'id'      => 'primary key',
			'form_id' => 'index',
			'user_id' => 'index',
		);
	}

	public function foreign_relations(): array {
		return array(
			new Form_Constraint(),
		);
	}

}