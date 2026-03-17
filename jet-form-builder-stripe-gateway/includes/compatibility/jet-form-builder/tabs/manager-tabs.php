<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Tabs;

use JetStripeGatewayCore\JetFormBuilder\RegisterFormTabs;

class Manager_Tabs {

	use RegisterFormTabs;

	public function plugin_version_compare() {
		return '1.2.2';
	}

	public function tabs(): array {
		return array(
			new Stripe_Tab()
		);
	}

	public function on_base_need_update() {
		$this->add_admin_notice( 'warning', __(
			'<b>Warning</b>: <b>JetFormBuilder Stripe Gateway</b> needs <b>JetFormBuilder</b> update.',
			'jet-form-builder-stripe-gateway'
		) );
	}

	public function on_base_need_install() {
	}
}