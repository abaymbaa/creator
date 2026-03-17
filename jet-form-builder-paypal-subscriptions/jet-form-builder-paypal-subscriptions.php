<?php
/**
 * Plugin Name: JetFormBuilder PayPal Recurring payments
 * Plugin URI:  https://jetformbuilder.com/addons/paypal-recurring-payments
 * Description: A tweak that allows you to create subscriptions and accept recurring payments via PayPal-integrated forms.
 * Version:     1.1.4
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-form-builder-paypal-subscriptions
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'JET_FB_PAYPAL_VERSION', '1.1.4' );

define( 'JET_FB_PAYPAL__FILE__', __FILE__ );
define( 'JET_FB_PAYPAL_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'JET_FB_PAYPAL_PATH', plugin_dir_path( __FILE__ ) );
define( 'JET_FB_PAYPAL_URL', plugins_url( '/', __FILE__ ) );

require JET_FB_PAYPAL_PATH . 'vendor/autoload.php';

if ( version_compare( PHP_VERSION, '7.0.0', '>=' ) ) {
	add_action( 'plugins_loaded', function () {
		require JET_FB_PAYPAL_PATH . 'includes/plugin.php';
	}, 100 );
} else {
	add_action( 'admin_notices', function () {
		$class   = 'notice notice-error';
		$message = __(
			'<b>Error:</b> <b>JetFormBuilder PayPal Recurring payments</b> plugin requires a PHP version ">= 7.0.0" to work properly!',
			'jet-form-builder-paypal-subscriptions'
		);
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
	} );
}

