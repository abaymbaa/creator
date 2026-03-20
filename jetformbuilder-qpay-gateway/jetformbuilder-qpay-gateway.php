<?php
/**
 * Plugin Name: JetFormBuilder QPay Gateway
 * Description: Integrate QPay.mn (v2 API) into JetFormBuilder.
 * Version:     1.0.0
 * Author:      Byambaa
 * Text Domain: jetformbuilder-qpay-gateway
 * License:     GPL-3.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'JFB_QPAY_VERSION', '1.0.0' );
define( 'JFB_QPAY__FILE__', __FILE__ );
define( 'JFB_QPAY_PATH', plugin_dir_path( __FILE__ ) );
define( 'JFB_QPAY_URL', plugins_url( '/', __FILE__ ) );

require_once JFB_QPAY_PATH . 'includes/Autoloader.php';

// Register Autoloader
\JFB_QPay\Autoloader::run();

// Initialize Plugin
add_action( 'plugins_loaded', function () {
	\JFB_QPay\Plugin::instance();
} );

// Activation Hook
register_activation_hook( __FILE__, function() {
	\JFB_QPay\DB\Table_Manager::create_table();
} );
