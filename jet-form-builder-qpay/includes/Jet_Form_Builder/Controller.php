<?php

namespace Jet_FB_Qpay\Jet_Form_Builder;

use JFB_Modules\Gateways\Base_Gateway;
use Jet_Form_Builder\Gateways\Base_Scenario_Gateway;
use Jet_Form_Builder\Classes\Tools;
use Jet_FB_Qpay\Jet_Form_Builder\Logic\Pay_Now_Logic;

class Controller extends Base_Scenario_Gateway {

	public function get_id() {
		return 'qpay';
	}

	public function get_name() {
		return __( 'QPay', 'jet-form-builder-qpay' );
	}

	protected function options_list() {
		return array(
			'username' => array(
				'label'    => __( 'API Username', 'jet-form-builder-qpay' ),
				'type'     => 'text',
				'required' => true,
			),
			'password' => array(
				'label'    => __( 'API Password', 'jet-form-builder-qpay' ),
				'type'     => 'password',
				'required' => true,
			),
			'invoice_code' => array(
				'label'    => __( 'Invoice Code', 'jet-form-builder-qpay' ),
				'type'     => 'text',
				'required' => true,
			),
		);
	}

	public function get_scenario() {
		return new Pay_Now_Logic();
	}

	public function query_scenario() {
		return new Pay_Now_Logic();
	}

	public function additional_editor_data(): array {
		return array(
			'version' => 1,
		);
	}
	
	protected function retrieve_gateway_meta() {
		$tab = new \Jet_FB_Qpay\Jet_Form_Builder\Tabs\Qpay_Tab();
		return $tab->get_options();
	}
}
