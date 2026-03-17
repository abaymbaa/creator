<?php

namespace Jet_FB_Qpay\Rest_Endpoints;

class Manager {

	public static $instance = null;

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	}

	public function register_endpoints() {
		( new Receipt_Page() )->register_endpoint();
		( new Check_Status() )->register_endpoint();
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
