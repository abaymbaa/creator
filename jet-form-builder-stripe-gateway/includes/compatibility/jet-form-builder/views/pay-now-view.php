<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Views;


use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Pay_Now_Connector;
use Jet_Form_Builder\Gateways\Scenarios_Abstract\Scenario_View_Base;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Rest_Endpoints\Fetch_Pay_Now_Editor;

class Pay_Now_View extends Scenario_View_Base {

	use Pay_Now_Connector;

	public function get_title(): string {
		return _x( 'Pay Now', 'Stripe gateway editor data', 'jet-form-builder' );
	}

	public function get_editor_labels(): array {
		return array(
			'currency'           => __( 'Currency Code', 'jet-form-builder' ),
			'fetch_button'       => __( 'Sync Access Token', 'jet-form-builder' ),
			'fetch_button_retry' => __( 'Access Token updated', 'jet-form-builder' ),
			'fetch_button_label' => __( 'Request Button', 'jet-form-builder' ),
			'fetch_button_help'  => __( 'Click on the button to further manage the payment settings', 'jet-form-builder' ),
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