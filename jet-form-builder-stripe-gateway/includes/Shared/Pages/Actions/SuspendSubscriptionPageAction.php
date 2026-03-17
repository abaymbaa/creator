<?php


namespace Jet_FB_Paypal\Pages\Actions;


use Jet_FB_Paypal\Resources\Subscription;
use Jet_FB_Paypal\RestEndpoints\PayPalSuspendSubscription;
use Jet_Form_Builder\Admin\Buttons\Base_Vui_Button;
use Jet_Form_Builder\Admin\Single_Pages\Actions\Base_Rest_Page_Action;
use Jet_Form_Builder\Classes\Arrayable\Array_Continue_Exception;

class SuspendSubscriptionPageAction extends Base_Rest_Page_Action {

	public function get_slug(): string {
		return 'suspend';
	}

	public function get_position(): string {
		return self::PRIMARY;
	}

	public function get_rest_methods(): string {
		return PayPalSuspendSubscription::get_methods();
	}

	/**
	 * @return string
	 */
	public function get_rest_url(): string {
		$args = new SubscriptionArgs();

		return PayPalSuspendSubscription::dynamic_rest_url( $args->get_args() );
	}

	public function get_payload(): array {
		return PayPalSuspendSubscription::get_messages();
	}

	/**
	 * @return Base_Vui_Button
	 * @throws Array_Continue_Exception
	 */
	public function get_button(): Base_Vui_Button {
		/** @var Subscription $subscription */
		$subscription = ( new SubscriptionArgs() )->get_resource();

		$button = parent::get_button();
		$button->set_label( __( 'Suspend', 'jet-form-builder-paypal-subscriptions' ) );
		$button->set_size( $button::SIZE_MINI_X2 );
		$button->set_style( $button::STYLE_ACCENT_BORDER );
		$button->set_disabled( ! $subscription->can_be_suspended() );

		return $button;
	}

}