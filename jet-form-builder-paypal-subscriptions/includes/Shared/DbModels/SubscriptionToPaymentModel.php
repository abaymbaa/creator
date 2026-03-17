<?php


namespace Jet_FB_Paypal\DbModels;


use Jet_FB_Paypal\DbModels\Constraints\SubscriptionModelConstraint;
use Jet_Form_Builder\Db_Queries\Base_Db_Model;
use Jet_Form_Builder\Gateways\Db_Models\Constraints\Payment_Model_Constraint;

class SubscriptionToPaymentModel extends Base_Db_Model {

	public static function table_name(): string {
		return 'subscriptions_to_payments';
	}

	/**
	 * @inheritDoc
	 */
	public static function schema(): array {
		return array(
			'id'              => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'payment_id'      => 'bigint(20) NOT NULL',
			'subscription_id' => 'bigint(20) NOT NULL',
		);
	}

	public static function schema_keys(): array {
		return array(
			'id'              => 'primary key',
			'payment_id'      => 'index',
			'subscription_id' => 'index'
		);
	}

	public function foreign_relations(): array {
		return array(
			new Payment_Model_Constraint(),
			new SubscriptionModelConstraint(),
		);
	}

}