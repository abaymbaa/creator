<?php


namespace Jet_FB_Paypal\QueryViews;

use Jet_FB_Paypal\DbModels\SubscriptionModel;
use Jet_FB_Paypal\DbModels\SubscriptionToPaymentModel;
use Jet_Form_Builder\Actions\Methods\Form_Record\Models\Record_Model;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Db_Queries\Query_Builder;
use Jet_Form_Builder\Db_Queries\Query_Conditions_Builder;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Gateways\Db_Models\Payer_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payer_Shipping_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payment_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payment_To_Payer_Shipping_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payment_To_Record;
use Jet_Form_Builder\Gateways\Query_Views\Payment_View;

class PaymentsWithSales extends Payment_View {

	const REFUNDED_STATUS = 'REFUNDED';
	const RENEW_TYPE      = 'renew';

	public function get_dependencies(): array {
		return array_merge(
			parent::get_dependencies(),
			array(
				new SubscriptionToPaymentModel(),
				new Payment_To_Payer_Shipping_Model(),
				new Payment_To_Record(),
			)
		);
	}

	/**
	 * @param Query_Builder $builder
	 */
	public function get_prepared_join( Query_Builder $builder ) {
		$subscription_to_payment = SubscriptionToPaymentModel::table();
		$payments_to_p_ships     = Payment_To_Payer_Shipping_Model::table();
		$payments_to_records     = Payment_To_Record::table();
		$subscription            = SubscriptionModel::table();
		$records                 = Record_Model::table();
		$payments                = Payment_Model::table();
		$payers                  = Payer_Model::table();
		$payers_ship             = Payer_Shipping_Model::table();

		$builder->join = "
--
-- Join payers & shipping details
--
LEFT JOIN `{$payments_to_p_ships}` ON 1=1
    AND `{$payments}`.`id` = `{$payments_to_p_ships}`.`payment_id`

LEFT JOIN `{$payers_ship}` ON 1=1 
	AND {$payers_ship}.`id` = `{$payments_to_p_ships}`.`payer_shipping_id`
	
LEFT JOIN `{$payers}` ON 1=1
	AND `{$payers}`.`id` = `{$payers_ship}`.`payer_id`

--
-- Join records
--
LEFT JOIN `{$payments_to_records}` ON 1=1
	AND `{$payments_to_records}`.`payment_id` = `{$payments}`.`id`
	
LEFT JOIN `{$records}` ON 1=1
	AND `{$records}`.`id` = `{$payments_to_records}`.`record_id`
	
--
-- Join subscriptions
--
LEFT JOIN `{$subscription_to_payment}` ON 1=1
	AND `{$subscription_to_payment}`.`payment_id` = `{$payments}`.`id`
	
LEFT JOIN `{$subscription}` ON 1=1
	AND `{$subscription}`.`id` = `{$subscription_to_payment}`.`subscription_id`
";
	}

	public function select_columns(): array {
		return array_merge(
			parent::select_columns(),
			SubscriptionModel::schema_columns( 'subscription' ),
			Payer_Model::schema_columns( 'payer' ),
			Record_Model::schema_columns( 'record' )
		);
	}

}
