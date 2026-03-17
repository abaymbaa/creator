<?php


namespace Jet_FB_Paypal\Pages\MetaBoxes;


use Jet_FB_Paypal\Pages\Actions\RefundSinglePayment;
use Jet_FB_Paypal\TableViews\Columns\PaymentStatusColumn;
use Jet_Form_Builder\Admin\Table_Views\Columns\Created_At_Column;
use Jet_Form_Builder\Admin\Table_Views\Columns\Updated_At_Column;
use Jet_Form_Builder\Gateways\Meta_Boxes\Columns\Gateway_Type_Column;
use Jet_Form_Builder\Gateways\Meta_Boxes\Columns\Payment_Amount_Column;
use Jet_Form_Builder\Gateways\Meta_Boxes\Columns\Payment_Currency_Column;
use Jet_Form_Builder\Gateways\Meta_Boxes\Payment_Details_Box;

class PaymentDetailsBox extends Payment_Details_Box {

	public static function rep_item_id() {
		return Payment_Details_Box::class;
	}

	public function get_columns(): array {
		return array(
			'amount'     => new Payment_Amount_Column(),
			'code'       => new Payment_Currency_Column(),
			'gateway'    => new Gateway_Type_Column(),
			'status'     => new PaymentStatusColumn(),
			'created_at' => new Created_At_Column(),
			'updated_at' => new Updated_At_Column(),
		);
	}

	public function get_actions(): array {
		return array_merge(
			array(
				new RefundSinglePayment()
			),
			parent::get_actions()
		);
	}

}