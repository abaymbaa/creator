<?php


namespace Jet_FB_Paypal\RestEndpoints;


use Jet_FB_Paypal\ApiActions;
use Jet_FB_Paypal\Logic\SubscribeNow;
use Jet_FB_Paypal\RestEndpoints\Base;
use Jet_Form_Builder\Gateways\Base_Gateway_Action;

class PayPalCancelSubscription extends Base\PayPalRestSubscriptionStatus
	implements Base\WithMessages {

	public static function gateway_rest_base(): string {
		return 'subscription/cancel/(?P<id>[\d]+)';
	}

	public function get_status(): string {
		return SubscribeNow::CANCELLED;
	}

	public function get_action(): Base_Gateway_Action {
		return new ApiActions\CancelSubscriptionAction();
	}

	public function get_message(): string {
		return __( 'Successfully cancelled subscription!', 'jet-form-builder' );
	}

	public static function get_messages(): array {
		return array(
			'ok_label'      => __( 'Cancel Subscription', 'jet-form-builder-paypal-subscriptions' ),
			'no_label'      => __( 'Do not cancel', 'jet-form-builder-paypal-subscriptions' ),
			'title'         => __( 'Please confirm cancellation', 'jet-form-builder-paypal-subscriptions' ),
			'control_label' => __( 'Reason', 'jet-form-builder-paypal-subscriptions' ),
			'control_desc'  => __( 'Excludes products with', 'jet-form-builder-paypal-subscriptions' ),
			'reason'        => __( 'Not satisfied with the service', 'jet-form-builder-paypal-subscriptions' ),
		);
	}


}
