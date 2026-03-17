<?php


namespace Jet_FB_Paypal\Pages\Actions;

use Jet_FB_Paypal\Pages\MetaBoxes\SubscriptionPayerBox;
use Jet_FB_Paypal\TableViews\Actions\RefundPaymentIsset;
use Jet_FB_Paypal\Utils\PaymentUtils;
use Jet_Form_Builder\Admin\Exceptions\Empty_Box_Exception;

class RefundSubscriptionPayment extends RefundPaymentIsset {

	public function show_in_header(): bool {
		return false;
	}

	public function show_in_row( array $record ): bool {
		return ! PaymentUtils::is_refunded( $record );
	}

	public function get_payload( array $record ): array {
		try {
			$list = ( new SubscriptionPayerBox )->set_single_id()->get_list();
		} catch ( Empty_Box_Exception $e ) {
			return parent::get_payload( $record );
		}

		if ( empty( $list ) ) {
			return parent::get_payload( $record );
		}

		$record['payer'] = $list;

		return parent::get_payload( $record );
	}

}
