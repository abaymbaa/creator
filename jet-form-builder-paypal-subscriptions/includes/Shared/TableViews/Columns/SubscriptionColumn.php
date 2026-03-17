<?php


namespace Jet_FB_Paypal\TableViews\Columns;


use Jet_Form_Builder\Admin\Table_Views\Column_Advanced_Base;

class SubscriptionColumn extends Column_Advanced_Base {

	public function get_label(): string {
		return __( 'Subscription', 'jet-form-builder' );
	}

	public function get_value( array $record = array() ) {
		return $record['subscription']['id'] ?? '';
	}

}