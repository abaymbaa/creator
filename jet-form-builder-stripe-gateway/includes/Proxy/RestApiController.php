<?php


namespace Jet_FB_Stripe_Gateway\Proxy;

use Jet_FB_Paypal\RestEndpoints;
use JetStripeGatewayCore\JetFormBuilder\RestApiProxy;

class RestApiController extends RestApiProxy {

	public function plugin_version_compare(): string {
		return '2.0.3';
	}

	public function routes(): array {
		$endpoints = array(
			new RestEndpoints\PaypalWebHookFormId(),
			new RestEndpoints\PaypalWebHookGlobal(),
			new RestEndpoints\PayPalCancelSubscription(),
			new RestEndpoints\PayPalSuspendSubscription(),
			new RestEndpoints\FetchSubscribeNowEditor(),
			new RestEndpoints\PayPalRefundPayment(),
			new RestEndpoints\AddSubscriptionNote(),
			new RestEndpoints\ReceiveSubscriptions(),
			new RestEndpoints\ReceivePayments(),
			new RestEndpoints\ReceiveSubscription(),
			new RestEndpoints\FetchNotesBySubscription(),
			new RestEndpoints\FetchPaymentsBySubscription(),
			new RestEndpoints\ReceivePayment(),
			new RestEndpoints\DeleteSubscriptions(),
			new RestEndpoints\DeleteSubscription(),
		);

		if (
		class_exists( '\Jet_Form_Builder\Gateways\Rest_Api\Delete_Payments_Endpoint' )
		) {
			$endpoints[] = new RestEndpoints\DeletePayments();
		}

		return $endpoints;
	}

	public function on_base_need_install() {
	}

	public function on_base_need_update() {
	}

}