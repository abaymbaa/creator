<?php


namespace JetStripeGatewayCore;


trait SmartBaseFormField {

	public $custom_field;

	abstract public function get_template();

	abstract public function render_instance();
}