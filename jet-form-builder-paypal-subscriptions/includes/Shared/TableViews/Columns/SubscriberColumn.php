<?php


namespace Jet_FB_Paypal\TableViews\Columns;

use Jet_Form_Builder\Admin\Table_Views\Column_Advanced_Base;

class SubscriberColumn extends Column_Advanced_Base {

	public function get_label(): string {
		return __( 'Subscriber', 'jet-form-builder-paypal-subscriptions' );
	}

	protected function is_attached( array $record ): bool {
		return ! empty( $record['payer']['email'] ?? '' );
	}

	/**
	 * @param array $record
	 *
	 * @return string
	 */
	public function get_value( array $record = array() ) {
		return $record['payer']['email'] ?? __( 'Not attached', 'jet-form-builder-paypal-subscriptions' );
	}
}
