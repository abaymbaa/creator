<?php


namespace Jet_FB_Paypal\DbModels;


use Jet_FB_Paypal\DbModels\Constraints\SubscriptionModelConstraint;
use Jet_Form_Builder\Db_Queries\Base_Db_Model;
use Jet_Form_Builder\Gateways\Db_Models\Constraints\Payer_Shipping_Model_Constraint;

class SubscriptionToPayerShipping extends Base_Db_Model {

	/**
	 * @inheritDoc
	 */
	public static function table_name(): string {
		return 'subscription_to_payer_shipping';
	}

	/**
	 * @inheritDoc
	 */
	public static function schema(): array {
		return array(
			'id'                => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'subscription_id'   => 'bigint(20) NOT NULL',
			'payer_shipping_id' => 'bigint(20) NOT NULL',
		);
	}

	public static function schema_keys(): array {
		return array(
			'id'                => 'primary key',
			'subscription_id'   => 'index',
			'payer_shipping_id' => 'index',
		);
	}

	public function foreign_relations(): array {
		return array(
			new Payer_Shipping_Model_Constraint(),
			new SubscriptionModelConstraint(),
		);
	}

}