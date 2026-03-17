<?php
/**
 * Plugin Name: JetFormBuilder Stripe Gateway
 * Plugin URI:  https://jetformbuilder.com/addons/stripe-payments/
 * Description: A supplementary software to integrate your forms and Stripe payment system.
 * Version:     2.0.3
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * Text Domain: jet-form-builder-stripe-gateway
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'JET_FB_STRIPE_GATEWAY_VERSION', '2.0.3' );

define( 'JET_FB_STRIPE_GATEWAY__FILE__', __FILE__ );
define( 'JET_FB_STRIPE_GATEWAY_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'JET_FB_STRIPE_GATEWAY_PATH', plugin_dir_path( __FILE__ ) );
define( 'JET_FB_STRIPE_GATEWAY_URL', plugins_url( '/', __FILE__ ) );

require JET_FB_STRIPE_GATEWAY_PATH . 'vendor/autoload.php';

add_action( 'plugins_loaded', function () {

	if ( ! version_compare( PHP_VERSION, '7.0.0', '>=' ) ) {
		add_action( 'admin_notices', function () {
			$class   = 'notice notice-error';
			$message = __(
				'<b>Error:</b> <b>JetFormBuilder Stripe Gateway</b> plugin requires a PHP version ">= 7.0"',
				'jet-form-builder-stripe-gateway'
			);
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
		} );

		return;
	}
	require JET_FB_STRIPE_GATEWAY_PATH . 'includes/plugin.php';
}, 100 );

add_action( 'rest_api_init', function () {
	( new Jet_FB_Stripe_Gateway\RestEndpoints\StripeWebHookGlobal() )->register_endpoint();
} );
