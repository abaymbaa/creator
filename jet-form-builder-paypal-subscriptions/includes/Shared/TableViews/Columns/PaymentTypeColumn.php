<?php


namespace Jet_FB_Paypal\TableViews\Columns;


use Jet_FB_Paypal\Utils\PaymentUtils;
use Jet_Form_Builder\Gateways\Table_Views\Columns\Payment_Type_Column;

class PaymentTypeColumn extends Payment_Type_Column {

	public function get_type_name( array $record ): string {
		if ( PaymentUtils::is_renewal( $record ) ) {
			return __( 'Renewal payment', 'jet-form-builder' );
		}

		return parent::get_type_name( $record );
	}

}