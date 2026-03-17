<?php
/**
 * Plugin Name: JetFormBuilder QPay Gateway
 * Description: Simple QPay integration for JetFormBuilder.
 * Version:     1.0.0
 * Author:      Antigravity
 * Text Domain: jet-form-builder-qpay
 * License:     GPL-3.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'JET_FB_QPAY_VERSION', '1.0.0' );
define( 'JET_FB_QPAY__FILE__', __FILE__ );
define( 'JET_FB_QPAY_PATH', plugin_dir_path( __FILE__ ) );
define( 'JET_FB_QPAY_URL', plugins_url( '/', __FILE__ ) );

add_action( 'plugins_loaded', function () {

	if ( ! function_exists( 'jet_form_builder' ) ) {
		return;
	}

	require_once JET_FB_QPAY_PATH . 'includes/Autoloader.php';
	
	// Initialize autoloader
	$loader = new \Jet_FB_Qpay\Autoloader();
	$loader->add_namespace( 'Jet_FB_Qpay', JET_FB_QPAY_PATH . 'includes' );
	$loader->register();

	// Initialize the plugin
	\Jet_FB_Qpay\Plugin::instance();

}, 100 );
