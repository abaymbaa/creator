<?php

namespace JFB_QPay;

class Autoloader {

	public static function run() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	public static function autoload( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ . '\\' ) ) {
			return;
		}

		$relative_class = substr( $class, strlen( __NAMESPACE__ . '\\' ) );
		$file = JFB_QPAY_PATH . 'includes/' . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
