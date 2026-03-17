<?php


namespace Jet_FB_Paypal\TableViews;

use Jet_FB_Paypal\Pages\Columns\RefundOptionsColumn;
use Jet_FB_Paypal\QueryViews\PaymentsWithSales;
use Jet_FB_Paypal\QueryViews\PaymentsWithSalesCount;
use Jet_FB_Paypal\RestEndpoints;
use Jet_FB_Paypal\TableViews\Columns\GrossColumn;
use Jet_FB_Paypal\TableViews\Columns\PaymentRowActions;
use Jet_FB_Paypal\TableViews\Columns\PaymentRowActionsLegacy;
use Jet_FB_Paypal\TableViews\Columns\PaymentStatusColumn;
use Jet_FB_Paypal\TableViews\Columns\PaymentTypeColumn;
use Jet_FB_Paypal\TableViews\Columns\SubscriptionColumn;
use Jet_Form_Builder\Admin\Table_Views\Column_Base;
use Jet_Form_Builder\Admin\Table_Views\Columns\Created_At_Column;
use Jet_Form_Builder\Admin\Table_Views\Columns\Record_Id_Column_Advanced;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Gateways\Table_Views\Actions\Delete_Action;
use Jet_Form_Builder\Gateways\Table_Views\Columns\Payer_Column;

class Payments extends \Jet_Form_Builder\Gateways\Table_Views\Payments {

	public function get_raw_list( array $args ): array {
		try {
			return ( new PaymentsWithSales() )
				->set_table_args( $args )
				->query()
				->query_all();

		} catch ( Query_Builder_Exception $exception ) {
			return array();
		}
	}

	public function get_total(): int {
		return PaymentsWithSalesCount::count();
	}

	public function get_columns(): array {
		return array(
			Column_Base::CHOOSE  => new Record_Id_Column_Advanced(),
			Column_Base::ACTIONS => new PaymentRowActions(),
			'type'               => new PaymentTypeColumn(),
			'date'               => new Created_At_Column(),
			'status'             => new PaymentStatusColumn(),
			'gross'              => new GrossColumn(),
			'payer'              => new Payer_Column(),
			'related_id'         => new SubscriptionColumn(),
			'id'                 => new Record_Id_Column_Advanced(),
			'refund'             => new RefundOptionsColumn(),
		);
	}

}

