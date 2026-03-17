<?php


namespace Jet_FB_Paypal\QueryViews;

use Jet_FB_Paypal\DbModels\SubscriptionToPayerShipping;
use Jet_Form_Builder\Db_Queries\Query_Builder;
use Jet_Form_Builder\Db_Queries\Views\View_Base;
use Jet_Form_Builder\Gateways\Db_Models\Payer_Model;
use Jet_Form_Builder\Gateways\Db_Models\Payer_Shipping_Model;

class PayerShippingBySubscription extends View_Base {

	public function table(): string {
		return SubscriptionToPayerShipping::table();
	}

	public function select_columns(): array {
		return array_merge(
			Payer_Model::schema_columns(),
			Payer_Shipping_Model::schema_columns( 'ship' )
		);
	}

	public function get_dependencies(): array {
		return array(
			new SubscriptionToPayerShipping(),
		);
	}

	public function get_prepared_join( Query_Builder $builder ) {
		$relation = SubscriptionToPayerShipping::table();
		$ship     = Payer_Shipping_Model::table();
		$payer    = Payer_Model::table();

		$builder->join = "
INNER JOIN `{$ship}` ON 1=1
	AND `{$ship}`.`id` = `{$relation}`.`payer_shipping_id`
	
INNER JOIN `{$payer}` ON 1=1
	AND `{$payer}`.`id` = `{$ship}`.`payer_id`
";
	}

}
