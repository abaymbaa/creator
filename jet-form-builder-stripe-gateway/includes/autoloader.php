<?php

namespace Jet_FB_Stripe_Gateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Autoloader handler class is responsible for loading the different
 * classes needed to run the plugin.
 */
class Autoloader {

	/**
	 * Run autoloader.
	 *
	 * Register a function as `__autoload()` implementation.
	 *
	 * @since 1.6.0
	 * @access public
	 * @static
	 */

	public static function run() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}


	/**
	 * Load class.
	 *
	 * For a given class name, require the class file.
	 *
	 * @since 1.6.0
	 * @access private
	 * @static
	 *
	 * @param string $relative_class_name Class name.
	 */
	private static function load_class( $class_name ) {

		$relative = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name );
		$psr4     = JET_FB_STRIPE_GATEWAY_PATH . 'includes/' . $relative . '.php';

		$legacy_rel = strtolower( str_replace( '_', '-', $relative ) );
		$legacy     = JET_FB_STRIPE_GATEWAY_PATH . 'includes/' . $legacy_rel . '.php';

		if ( is_readable( $psr4 ) ) {
			require $psr4;
			return;
		}

		if ( is_readable( $legacy ) ) { 
			require $legacy;
			return;
		}
	}

	/**
	 * Autoload.
	 *
	 * For a given class, check if it exist and load it.
	 *
	 * @since 1.6.0
	 * @access private
	 * @static
	 *
	 * @param string $class Class name.
	 */
	private static function autoload( $class ) {

		if ( 0 !== strpos( $class, __NAMESPACE__ . '\\' ) ) {
			return;
		}

		$relative_class_name = preg_replace( '/^' . __NAMESPACE__ . '\\\/', '', $class );
		$final_class_name    = __NAMESPACE__ . '\\' . $relative_class_name;

		if ( ! class_exists( $final_class_name ) ) {
			self::load_class( $relative_class_name );
		}

	}
}
