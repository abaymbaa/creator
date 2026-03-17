<?php

namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Views;

use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Subscription_Connector;
use Jet_Form_Builder\Gateways\Scenarios_Abstract\Scenario_View_Base;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Rest_Endpoints\Fetch_Pay_Now_Editor;

class Subscription_View extends Scenario_View_Base {

	use Subscription_Connector;

	public function get_title(): string {
		return _x( 'Subscription', 'Stripe gateway editor data', 'jet-form-builder-stripe-gateway' );
	}

	public function get_editor_labels(): array {
		return array(
			'subscribe_plan_field' 		 => __( 'Subscription Plan Field', 'jet-form-builder-stripe-gateway' ),
			'subscribe_plan'       		 => __( 'Subscription Plan', 'jet-form-builder-stripe-gateway' ),
			'refresh_plans_button'     	 => __( 'Refresh Plans From Stripe', 'jet-form-builder-stripe-gateway' ),
			'plans_fetched_successfully' => __( 'Successfully Updated Plans', 'jet-form-builder-stripe-gateway' ),
		);
	}

	public function get_editor_data(): array {
		return array(
			'fetch' => array(
				'method' => Fetch_Pay_Now_Editor::get_methods(),
				'url'    => Fetch_Pay_Now_Editor::rest_url(),
			),
		);
	}
}
