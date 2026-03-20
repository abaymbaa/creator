<?php

namespace JFB_QPay\Admin;

use Jet_Form_Builder\Admin\Tabs_Handlers\Base_Handler;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Settings_Tab extends Base_Handler {

	public function slug() {
		return 'qpay';
	}

	public function on_get_request() {
		$client_id             = sanitize_text_field( $_POST['client_id'] ?? '' );
		$client_secret         = sanitize_text_field( $_POST['client_secret'] ?? '' );
		$invoice_code          = sanitize_text_field( $_POST['invoice_code'] ?? '' );
		$invoice_receiver_code = sanitize_text_field( $_POST['invoice_receiver_code'] ?? '' );
		$is_sandbox            = 'true' === sanitize_key( $_POST['is_sandbox'] ?? '' );

		$result = $this->update_options( array(
			'client_id'             => $client_id,
			'client_secret'         => $client_secret,
			'invoice_code'          => $invoice_code,
			'invoice_receiver_code' => $invoice_receiver_code,
			'is_sandbox'            => $is_sandbox,
		) );

		$this->send_response( $result );
	}

	public function on_load() {
		return $this->get_options( array(
			'client_id'             => '',
			'client_secret'         => '',
			'invoice_code'          => '',
			'invoice_receiver_code' => '',
			'is_sandbox'            => false,
		) );
	}

	public function before_assets() {
		wp_enqueue_script(
			'jfb-qpay-admin',
			JFB_QPAY_URL . 'assets/js/qpay-admin.js',
			array( 'wp-hooks', 'wp-i18n' ),
			JFB_QPAY_VERSION,
			true
		);

		wp_localize_script( 'jfb-qpay-admin', 'jfbQpayAdmin', array(
			'labels' => array(
				'title'                 => __( 'QPay Gateway API', 'jetformbuilder-qpay-gateway' ),
				'client_id'             => __( 'Client ID', 'jetformbuilder-qpay-gateway' ),
				'client_secret'         => __( 'Client Secret', 'jetformbuilder-qpay-gateway' ),
				'invoice_code'          => __( 'Invoice Code', 'jetformbuilder-qpay-gateway' ),
				'invoice_receiver_code' => __( 'Terminal ID (Receiver Code)', 'jetformbuilder-qpay-gateway' ),
				'is_sandbox'            => __( 'Sandbox Mode', 'jetformbuilder-qpay-gateway' ),
			)
		) );
	}
}
