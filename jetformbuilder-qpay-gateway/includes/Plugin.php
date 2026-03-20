<?php

namespace JFB_QPay;

use JFB_QPay\Gateway\Controller;
use JFB_QPay\Rest\Rest_Controller;
use JFB_QPay\Admin\Settings_Tab;
use Jet_Form_Builder\Admin\Tabs_Handlers\Tab_Handler_Manager;

class Plugin {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init_hooks();
	}

	private function init_hooks() {
		// Register Gateway
		add_action( 'jet-form-builder/gateways/register', array( $this, 'register_gateway' ) );

		// Register REST
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Enqueue Assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function register_gateway( $manager ) {
		$manager->register_gateway( new Controller() );
	}

	public function register_rest_routes() {
		( new Rest_Controller() )->register_routes();
	}

	public function enqueue_assets() {
		wp_enqueue_style(
			'jfb-qpay-modal',
			JFB_QPAY_URL . 'assets/css/qpay-modal.css',
			array(),
			JFB_QPAY_VERSION
		);

		wp_enqueue_script(
			'jfb-qpay-polling',
			JFB_QPAY_URL . 'assets/js/qpay-polling.js',
			array( 'jquery' ),
			JFB_QPAY_VERSION,
			true
		);

		wp_localize_script( 'jfb-qpay-polling', 'jfbQpay', array(
			'apiUrl'   => esc_url_raw( rest_url( 'jfb-qpay/v1' ) ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
		) );
	}
}
