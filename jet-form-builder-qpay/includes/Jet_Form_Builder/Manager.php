<?php

namespace Jet_FB_Qpay\Jet_Form_Builder;

use Jet_Form_Builder\Gateways\Gateway_Manager;

class Manager {

	public static $instance = null;

	public function __construct() {
		add_action(
			'jet-form-builder/gateways/register',
			array( $this, 'register_gateway' )
		);
	}

	public function register_gateway( Gateway_Manager $manager ) {
		$manager->register_gateway( new Controller() );
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
