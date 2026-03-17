<?php


namespace Jet_FB_Stripe_Gateway\Proxy;


use Jet_FB_Paypal\Plugin;
use JetStripeGatewayCore\JetFormBuilder\WithInit;
use JetStripeGatewayCore\JetFormBuilder\EditorAssetsManager;

class EditorAssets {

	use WithInit;
	use EditorAssetsManager;

	public function plugin_version_compare(): string {
		return '2.0.3';
	}

	public function on_plugin_init() {
		$this->assets_init();
	}

	public function before_init_editor_assets() {
		wp_enqueue_script(
			Plugin::instance()->slug,
			Plugin::instance()->plugin_url( 'assets/js/editor.js' ),
			array(),
			Plugin::instance()->get_version(),
			true
		);
	}

	public function on_base_need_install() {
	}

	public function on_base_need_update() {
	}
}