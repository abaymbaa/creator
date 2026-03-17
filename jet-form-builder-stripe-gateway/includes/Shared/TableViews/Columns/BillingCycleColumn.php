<?php


namespace Jet_FB_Paypal\TableViews\Columns;

use Jet_FB_Paypal\Resources\BillingCycle;
use Jet_Form_Builder\Admin\Table_Views\Column_Advanced_Base;

class BillingCycleColumn extends Column_Advanced_Base {

	public function get_label(): string {
		return __( 'Billing Cycle', 'jet-form-builder-paypal-subscriptions' );
	}

	public function get_value( array $record = array() ) {
		$cycle    = new BillingCycle( $record['cycle'] ?? array() );
		$text     = sprintf(
			'%s %s / %s',
			$cycle->get_encoded_symbol(),
			$cycle->get_amount(),
			$cycle->get_unit()
		);
		$quantity = $cycle->get_quantity();
		$quantity = $quantity ? "($quantity)" : '';

		return trim( $text . ' ' . $quantity );
	}

}
