<?php


namespace Jet_FB_Paypal\ScenarioViews;


use Jet_FB_Paypal\SubscribeNowConnector;
use Jet_Form_Builder\Classes\Tools;
use Jet_Form_Builder\Gateways\Scenarios_Abstract\Scenario_View_Base;
use Jet_Form_Builder\Gateways\Paypal\Scenarios_Views;
use Jet_FB_Paypal\RestEndpoints;

class SubscriptionScenarioView extends Scenario_View_Base {

	use SubscribeNowConnector;

	public function get_title(): string {
		return _x( 'Create a subscription', 'Paypal gateway editor data', 'jet-form-builder' );
	}

	public function get_editor_labels(): array {
		return array_merge(
			$this->get_another( Scenarios_Views\Pay_Now::scenario_id() )->get_editor_labels(),
			array(
				'fetch_button_help'    => __(
					'Click on the button to further manage the subscription settings',
					'jet-form-builder-paypal-subscriptions'
				),
				'subscribe_plan_field' => __( 'Subscription Plan Field', 'jet-form-builder-paypal-subscriptions' ),
				'subscribe_plan'       => __( 'Subscription Plan', 'jet-form-builder-paypal-subscriptions' ),
				'copy_plan_button'     => __( 'Copy selected Plan ID', 'jet-form-builder-paypal-subscriptions' ),
				'quantity_field'       => __( 'Quantity field', 'jet-form-builder-paypal-subscriptions' ),
				'quantity_manual'      => __( 'Manual input of quantity', 'jet-form-builder-paypal-subscriptions' ),
			)
		);
	}

	public function get_editor_data(): array {
		return array(
			'fetch'             => array(
				'method' => RestEndpoints\FetchSubscribeNowEditor::get_methods(),
				'url'    => RestEndpoints\FetchSubscribeNowEditor::rest_url(),
			),
			'plan_from_options' => Tools::with_placeholder(
				array(
					array(
						'value' => 'field',
						'label' => __( 'From Field', 'jet-form-builder' ),
					),
				),
				__( 'Manual Input', 'jet-form-builder' )
			),
		);
	}

}