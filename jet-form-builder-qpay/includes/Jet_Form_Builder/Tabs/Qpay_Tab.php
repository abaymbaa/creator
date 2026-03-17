<?php

namespace Jet_FB_Qpay\Jet_Form_Builder\Tabs;

use Jet_Form_Builder\Admin\Tabs_Handlers\Base_Handler;
use Jet_FB_Qpay\Plugin;

class Qpay_Tab extends Base_Handler {

	public function slug() {
		return 'qpay';
	}

	public function on_get_request() {
		$username     = sanitize_text_field( $_POST['username'] ?? '' );
		$password     = sanitize_text_field( $_POST['password'] ?? '' );
		$invoice_code = sanitize_text_field( $_POST['invoice_code'] ?? '' );

		$result = $this->update_options( array(
			'username'     => $username,
			'password'     => $password,
			'invoice_code' => $invoice_code,
		) );

		$this->send_response( $result );
	}

	public function on_load() {
		return $this->get_options( array(
			'username'     => '',
			'password'     => '',
			'invoice_code' => '',
		) );
	}

	public function before_assets() {
		wp_enqueue_script(
			'jet-fb-qpay-admin',
			JET_FB_QPAY_URL . 'assets/js/admin.js',
			array( 'jet-form-builder-admin-package' ),
			JET_FB_QPAY_VERSION,
			true
		);
	}
}
