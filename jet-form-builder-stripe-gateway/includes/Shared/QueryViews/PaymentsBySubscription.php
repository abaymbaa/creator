<?php


namespace Jet_FB_Paypal\QueryViews;


use Jet_FB_Paypal\DbModels\SubscriptionToPaymentModel;
use Jet_Form_Builder\Db_Queries\Query_Builder;
use Jet_Form_Builder\Db_Queries\Query_Conditions_Builder;
use Jet_Form_Builder\Db_Queries\Views\View_Base;
use Jet_Form_Builder\Gateways\Db_Models\Payment_Model;

class PaymentsBySubscription extends View_Base {

	protected $order_by = array(
		array(
			'column' => 'id',
			'sort'   => self::FROM_HIGH_TO_LOW,
		),
	);

	public function table(): string {
		return SubscriptionToPaymentModel::table();
	}

	public function select_columns(): array {
		return array_merge(
			Payment_Model::schema_columns()
		);
	}

	public function get_dependencies(): array {
		return array(
			new SubscriptionToPaymentModel(),
		);
	}

	public function set_filters( array $filters ) {
		parent::set_filters( $filters );

		$id = absint( $this->filters['subscription_id'] ?? 0 );

		if ( empty( $record_id ) ) {
			return $this;
		}

		$this->set_conditions(
			array(
				array(
					'type'   => Query_Conditions_Builder::TYPE_EQUAL,
					'values' => array( 'subscription_id', $id ),
				),
			)
		);

		return $this;
	}

	public function get_prepared_join( Query_Builder $builder ) {
		$relation = SubscriptionToPaymentModel::table();
		$payments = Payment_Model::table();

		$builder->join = "
LEFT JOIN `{$payments}` ON 1=1
	AND `{$relation}`.`payment_id` = `{$payments}`.`id`
";
	}


}