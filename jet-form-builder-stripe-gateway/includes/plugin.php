<?php

namespace Jet_FB_Stripe_Gateway;

use Jet_FB_Stripe_Gateway\Compatibility\Jet_Engine;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder;
use JetStripeGatewayCore\LicenceProxy;
use Jet_FB_Stripe_Gateway\Proxy\AdminPages;
use Jet_FB_Stripe_Gateway\Proxy\AdminSinglePages;
use Jet_FB_Stripe_Gateway\Proxy\RestApiController;
use Jet_FB_Stripe_Gateway\FormEvents\EventsManager;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

class Plugin {
	/**
	 * Instance.
	 *
	 * Holds the plugin instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @var Plugin
	 */
	public static $instance = null;

	public $slug = 'jet-form-builder-stripe-gateway';

	public function __construct() {

		require_once JET_FB_STRIPE_GATEWAY_PATH . 'includes/Shared/Loader.php';

		$this->register_autoloader();

		Jet_Form_Builder\Manager::instance();

		add_action(
			'after_setup_theme',
			function () {
				Jet_Form_Builder\Tabs\Manager_Tabs::register();
				LicenceProxy::register();
				$this->init();
			},
			-100
		);
	}

	public function init() {
		$this->init_components();
	}

	public function init_components() {
		if ( is_admin() ) {
			new Editor();
		}

		if ( Jet_Engine\Manager::check() ) {
			Jet_Engine\Manager::instance();
		}

		AdminPages::register();
		AdminSinglePages::register();
		RestApiController::register();
		EventsManager::register();
	}

	/**
	 * Register autoloader.
	 */
	public function register_autoloader() {
		require JET_FB_STRIPE_GATEWAY_PATH . 'includes/autoloader.php';
		Autoloader::run();
	}

	public function get_version() {
		return JET_FB_STRIPE_GATEWAY_VERSION;
	}

	public function plugin_url( $path ) {
		return JET_FB_STRIPE_GATEWAY_URL . $path;
	}

	public function plugin_dir( $path = '' ) {
		return JET_FB_STRIPE_GATEWAY_PATH . $path;
	}

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @return Plugin An instance of the class.
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

Plugin::instance();
