<?php


namespace Jet_FB_Paypal\Pages\Actions;


use Jet_FB_Paypal\Resources\Payment;
use Jet_FB_Paypal\RestEndpoints\PayPalRefundPayment;
use Jet_Form_Builder\Admin\Buttons\Base_Vui_Button;
use Jet_Form_Builder\Admin\Exceptions\Empty_Box_Exception;
use Jet_Form_Builder\Admin\Single_Pages\Actions\Base_Rest_Page_Action;
use Jet_Form_Builder\Classes\Arrayable\Array_Continue_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Gateways\Meta_Boxes\Payer_Box;
use Jet_Form_Builder\Gateways\Query_Views\Payment_View;

class RefundSinglePayment extends Base_Rest_Page_Action {

	public function get_slug(): string {
		return 'refund';
	}

	public function get_position(): string {
		return self::PRIMARY;
	}

	public function get_rest_methods(): string {
		return PayPalRefundPayment::get_methods();
	}

	/**
	 * @return string
	 */
	public function get_rest_url(): string {
		$args = new PaymentArgs();

		return PayPalRefundPayment::dynamic_rest_url( $args->get_args() );
	}

	public function get_payload(): array {
		try {
			$payment = Payment_View::findById( jet_fb_current_page()->get_id() );
		} catch ( Query_Builder_Exception $exception ) {
			return parent::get_payload();
		}

		return ( new Payment( $payment ) )->refund_payload();
	}

	/**
	 * @return Base_Vui_Button
	 * @throws Array_Continue_Exception
	 */
	public function get_button(): Base_Vui_Button {
		/** @var Payment $payment */
		$payment = ( new PaymentArgs() )->get_resource();

		$button = parent::get_button();
		$button->set_label( __( 'Refund', 'jet-form-builder-paypal-subscriptions' ) );
		$button->set_style( $button::STYLE_ACCENT_ERROR );
		$button->set_preset( $button::PRESET_PAGE_ACTION );
		$button->set_disabled( ! $payment->can_be_refunded() );

		return $button;
	}
}