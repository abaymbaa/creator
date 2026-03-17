<?php


namespace Jet_FB_Paypal\TableViews\Columns;


use Jet_FB_Paypal\Utils\PaymentUtils;
use Jet_Form_Builder\Gateways\Table_Views\Columns\Gross_Column;

class GrossColumn extends Gross_Column {

	public function get_gross_sign( $record ): string {
		if ( PaymentUtils::is_refunded( $record ) ) {
			return '-';
		}

		return parent::get_gross_sign( $record );
	}

}