<?php


namespace Jet_FB_Paypal\TableViews\Actions;

use Jet_FB_Paypal\Resources\Payment;
use Jet_FB_Paypal\RestEndpoints\PayPalRefundPayment as RefundAction;
use Jet_FB_Paypal\Utils\PaymentUtils;
use Jet_Form_Builder\Admin\Table_Views\Actions\Api_Single_Action;

class RefundPaymentIsset extends Api_Single_Action {

	use BaseSubscriptionArgs;

	public function get_method(): string {
		return RefundAction::get_methods();
	}

	public function get_rest_url( array $record ): string {
		return RefundAction::dynamic_rest_url( $this->get_args( $record ) );
	}

	public function get_type(): string {
		return 'danger';
	}

	public function get_slug(): string {
		return 'refund';
	}

	public function get_label(): string {
		return __( 'Refund', 'jet-form-builder' );
	}

	public function show_in_header(): bool {
		return false;
	}

	public function show_in_row( array $record ): bool {
		return PaymentUtils::can_be_refunded( $record );
	}

	public function get_payload( array $record ): array {
		$payment = new Payment( $record );

		return $payment->refund_payload();
	}
}
