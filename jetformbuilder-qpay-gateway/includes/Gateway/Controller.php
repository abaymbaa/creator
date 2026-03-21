<?php

namespace JFB_QPay\Gateway;

use JFB_Modules\Gateways\Base_Gateway;
use JFB_QPay\API\Qpay_Api;
use JFB_QPay\DB\Table_Manager;
use Jet_Form_Builder\Exceptions\Gateway_Exception;

class Controller extends Base_Gateway {

	public function __construct() {
		if ( class_exists( 'Jet_Form_Builder\Admin\Tabs_Handlers\Tab_Handler_Manager' ) ) {
			\Jet_Form_Builder\Admin\Tabs_Handlers\Tab_Handler_Manager::instance()->install( new \JFB_QPay\Admin\Settings_Tab() );
		}
	}

	public function get_id() {
		return 'qpay';
	}

	public function get_name() {
		return __( 'QPay.mn', 'jetformbuilder-qpay-gateway' );
	}

	protected function options_list() {
		return array(
			'client_id' => array(
				'label' => __( 'Client ID', 'jetformbuilder-qpay-gateway' ),
				'type'  => 'text',
			),
			'client_secret' => array(
				'label' => __( 'Client Secret', 'jetformbuilder-qpay-gateway' ),
				'type'  => 'password',
			),
			'invoice_code' => array(
				'label' => __( 'Invoice Code', 'jetformbuilder-qpay-gateway' ),
				'type'  => 'text',
			),
			'invoice_receiver_code' => array(
				'label' => __( 'Terminal ID (Receiver Code)', 'jetformbuilder-qpay-gateway' ),
				'type'  => 'text',
			),
			'is_sandbox' => array(
				'label' => __( 'Sandbox Mode', 'jetformbuilder-qpay-gateway' ),
				'type'  => 'checkbox',
			),
			'price_field' => array(
				'label' => __( 'Price Field Name', 'jetformbuilder-qpay-gateway' ),
				'type'  => 'text',
				'help'  => __( 'Enter the name of the field containing the payment amount (e.g., calculated_field_name).', 'jetformbuilder-qpay-gateway' ),
			),
			'use_global' => array(
				'label'    => __( 'Use Global Settings', 'jetformbuilder-qpay-gateway' ),
				'type'     => 'checkbox',
				'required' => false,
			),
		);
	}

	public function additional_editor_data(): array {
		return array(
			'version' => 1,
		);
	}

	protected function retrieve_gateway_meta() {
		return $this->gateways_meta;
	}

	public function action( $action_handler ) {
		$settings = $this->get_settings();

		$client_id             = $settings['client_id'] ?? '';
		$client_secret         = $settings['client_secret'] ?? '';
		$invoice_code          = $settings['invoice_code'] ?? '';
		$invoice_receiver_code = $settings['invoice_receiver_code'] ?? '';
		$is_sandbox            = ! empty( $settings['is_sandbox'] );
		$price_field           = $settings['price_field'] ?? '';

		if ( ! $client_id || ! $client_secret ) {
			if ( class_exists( '\Jet_Form_Builder\Dev_Log' ) ) {
				\Jet_Form_Builder\Dev_Log::instance()->info( 'QPay: Missing credentials', array(
					'form_id' => jet_fb_action_handler()->get_form_id(),
				) );
			}
			throw new Gateway_Exception( __( 'QPay credentials are not configured.', 'jetformbuilder-qpay-gateway' ) );
		}

		$amount = jet_fb_context()->get_value( $price_field );
		
		if ( ! $amount ) {
			// Try to log what happened
			if ( class_exists( '\Jet_Form_Builder\Dev_Log' ) ) {
				\Jet_Form_Builder\Dev_Log::instance()->info( 'QPay: Invalid amount', array(
					'price_field' => $price_field,
					'form_id'     => jet_fb_action_handler()->get_form_id(),
				) );
			}
			throw new Gateway_Exception( sprintf( __( 'Invalid payment amount in field "%s".', 'jetformbuilder-qpay-gateway' ), $price_field ) );
		}

		$api = new Qpay_Api( $client_id, $client_secret, $is_sandbox );
		
		$invoice_args = array(
			'invoice_code'         => $invoice_code,
			'sender_invoice_no'    => (string) time(),
			'invoice_receiver_code'=> $invoice_receiver_code ?: 'terminal', 
			'invoice_description'  => sprintf( __( 'Payment for Form #%d', 'jetformbuilder-qpay-gateway' ), jet_fb_action_handler()->get_form_id() ),
			'amount'               => (float) $amount,
			'callback_url'         => esc_url_raw( rest_url( 'jfb-qpay/v1/callback' ) ),
		);

		$response = $api->create_invoice( $invoice_args );

		if ( isset( $response['error'] ) || ! isset( $response['invoice_id'] ) ) {
			throw new Gateway_Exception( sprintf( __( 'QPay Error: %s', 'jetformbuilder-qpay-gateway' ), $response['error'] ?? 'Unknown error' ) );
		}

		// Store in DB
		Table_Manager::insert( array(
			'invoice_id'   => $response['invoice_id'],
			'invoice_code' => $invoice_code,
			'status'       => 'waiting',
			'amount'       => (float) $amount,
			'form_id'      => jet_fb_action_handler()->get_form_id(),
			'qr_text'      => $response['qr_text'] ?? '',
			'urls'         => wp_json_encode( $response['urls'] ?? array() ),
		) );

		// Pass data to frontend
		jet_fb_action_handler()->response_data['qpay_checkout'] = array(
			'invoice_id' => $response['invoice_id'],
			'qr_text'    => $response['qr_text'] ?? '',
			'urls'       => $response['urls'] ?? array(),
			'amount'     => $amount,
		);
	}

	protected function get_settings() {
		$form_id = jet_fb_action_handler()->get_form_id();
		$module  = \JFB_Modules\Gateways\Module::instance();
		$form_gateways = $module->get_form_gateways_by_id( $form_id );
		$qpay_settings = $form_gateways['qpay'] ?? array();

		if ( empty( $qpay_settings['use_global'] ) && ! empty( $qpay_settings['client_id'] ) ) {
			return $qpay_settings;
		}

		return array_merge( $qpay_settings, $module->get_global_settings( 'qpay' ) );
	}
}
