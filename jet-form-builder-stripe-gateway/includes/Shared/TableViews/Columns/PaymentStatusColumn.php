<?php


namespace Jet_FB_Paypal\TableViews\Columns;


use Jet_FB_Paypal\QueryViews\PaymentsWithSales;
use Jet_Form_Builder\Gateways\Table_Views\Columns\Payment_Status_Column;

class PaymentStatusColumn extends Payment_Status_Column {

	public function get_replace_map(): array {
		return parent::get_replace_map() + array(
			PaymentsWithSales::REFUNDED_STATUS => array(
				'type' => self::STATUS_FAILED,
				'text' => __( 'Refunded', 'jet-form-builder' ),
			)
		);
	}

}