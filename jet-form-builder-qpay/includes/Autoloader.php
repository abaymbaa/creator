<?php

namespace Jet_FB_Qpay;

class Autoloader {

	protected $namespaces = array();

	public function add_namespace( $namespace, $base_dir ) {
		$namespace = trim( $namespace, '\\' ) . '\\';
		$base_dir  = rtrim( $base_dir, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

		$this->namespaces[ $namespace ] = $base_dir;
	}

	public function register() {
		spl_autoload_register( array( $this, 'load_class' ) );
	}

	public function load_class( $class ) {
		foreach ( $this->namespaces as $namespace => $base_dir ) {
			if ( 0 !== strpos( $class, $namespace ) ) {
				continue;
			}

			$relative_class = substr( $class, strlen( $namespace ) );
			$file = $base_dir . str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class ) . '.php';

			if ( file_exists( $file ) ) {
				require $file;
				return true;
			}
		}

		return false;
	}
}
