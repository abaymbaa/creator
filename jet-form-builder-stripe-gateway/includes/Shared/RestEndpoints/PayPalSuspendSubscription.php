<?php


namespace Jet_FB_Paypal\RestEndpoints;


use Jet_FB_Paypal\ApiActions;
use Jet_FB_Paypal\Logic\SubscribeNow;
use Jet_FB_Paypal\RestEndpoints\Base;
use Jet_Form_Builder\Gateways\Base_Gateway_Action;


class PayPalSuspendSubscription extends Base\PayPalRestSubscriptionStatus
	implements Base\WithMessages {

	public static function gateway_rest_base(): string {
		return 'subscription/suspend/(?P<id>[\d]+)';
	}

	public function get_action(): Base_Gateway_Action {
		return new ApiActions\SuspendSubscriptionAction();
	}

	public function get_status(): string {
		return SubscribeNow::SUSPENDED;
	}

	public function get_message(): string {
		return __( 'Successfully suspended subscription!', 'jet-form-builder' );
	}

	public static function get_messages(): array {
		return array(
			'ok_label'      => __( 'Suspend Subscription', 'jet-form-builder-paypal-subscriptions' ),
			'no_label'      => __( 'Do not suspend', 'jet-form-builder-paypal-subscriptions' ),
			'title'         => __( 'Please confirm', 'jet-form-builder-paypal-subscriptions' ),
			'control_label' => __( 'Reason', 'jet-form-builder-paypal-subscriptions' ),
			'control_desc'  => __( 'Excludes products with', 'jet-form-builder-paypal-subscriptions' ),
			'reason'        => __( 'Not satisfied with the service', 'jet-form-builder-paypal-subscriptions' ),
		);
	}
}