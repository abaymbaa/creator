<?php


namespace JetStripeGatewayCore\JetFormBuilder;

/**
 * Use this if you set action data from user side (.js)
 *
 * Trait ActionCompatibility
 * @package JetStripeGatewayCore
 */
trait ActionCompatibility {

	public function visible_attributes_for_gateway_editor() {
		return array();
	}

	public function self_script_name() {
		return false;
	}

	public function editor_labels() {
		return array();
	}
}