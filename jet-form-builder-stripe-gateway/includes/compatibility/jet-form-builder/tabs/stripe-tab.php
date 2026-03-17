<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Tabs;


use Jet_FB_Stripe_Gateway\Plugin;
use Jet_Form_Builder\Admin\Tabs_Handlers\Base_Handler;

class Stripe_Tab extends Base_Handler {

	public function slug() {
		return 'stripe';
	}

	public function on_get_request() {
		$public = sanitize_text_field( $_POST['public'] );
		$secret = sanitize_text_field( $_POST['secret'] );

		$result = $this->update_options( array(
			'public' => $public,
			'secret' => $secret,
		) );

		$result ? wp_send_json_success( array(
			'message' => __( 'Saved successfully!', 'jet-form-builder' )
		) ) : wp_send_json_error( array(
			'message' => __( 'Unsuccessful save.', 'jet-form-builder' )
		) );
	}

	public function on_load() {
		return $this->get_options( array(
			'public' => '',
			'secret' => ''
		) );
	}

	public function before_assets() {
		wp_enqueue_script(
			Plugin::instance()->slug . "-{$this->slug()}",
			Plugin::instance()->plugin_url( 'assets/js/builder.admin.js' ),
			array(),
			Plugin::instance()->get_version(),
			true
		);
	}
}