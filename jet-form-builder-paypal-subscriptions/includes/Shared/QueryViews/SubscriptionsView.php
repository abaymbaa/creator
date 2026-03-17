<?php


namespace Jet_FB_Paypal\QueryViews;

use Jet_FB_Paypal\DbModels\RecurringCyclesModel;
use Jet_FB_Paypal\DbModels\SubscriptionModel;
use Jet_FB_Paypal\DbModels\SubscriptionToPayerShipping;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Db_Queries\Query_Builder;
use Jet_Form_Builder\Db_Queries\Views\View_Base;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Gateways\Db_Models\Payer_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payer_Shipping_Model;

class SubscriptionsView extends View_Base {

	protected $order_by = array(
		array(
			'column' => 'id',
			'sort'   => self::FROM_HIGH_TO_LOW,
		),
	);

	/**
	 * @param Query_Builder $builder
	 *
	 */
	public function get_prepared_join( Query_Builder $builder ) {
		$subscription         = ( new SubscriptionModel() )->create()::table();
		$cycles               = ( new RecurringCyclesModel() )->create()::table();
		$subscription_to_ship = ( new SubscriptionToPayerShipping() )->create()::table();

		$payers      = Payer_Model::table();
		$payers_ship = Payer_Shipping_Model::table();

		$builder->join = "
LEFT JOIN `{$subscription_to_ship}` ON 1=1
    AND `{$subscription}`.`id` = `{$subscription_to_ship}`.`subscription_id`
    
LEFT JOIN `{$payers_ship}` ON 1=1
	AND `{$payers_ship}`.`id` = `{$subscription_to_ship}`.`payer_shipping_id`

LEFT JOIN `{$payers}` ON 1=1
	AND `{$payers}`.`id` = `{$payers_ship}`.`payer_id`
	
LEFT JOIN `{$cycles}` ON 1=1
	AND `{$subscription}`.`id` = `{$cycles}`.`subscription_id`
	AND `{$cycles}`.`tenure_type` = 'REGULAR'
";
	}

	public function table(): string {
		return SubscriptionModel::table();
	}

	public function select_columns(): array {
		return array_merge(
			SubscriptionModel::schema_columns(),
			Payer_Model::schema_columns( 'payer' ),
			RecurringCyclesModel::schema_columns( 'cycle' )
		);
	}
}
