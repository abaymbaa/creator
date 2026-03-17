<?php

namespace Jet_FB_Qpay\Jet_Form_Builder\Tabs;

class Manager {

	public static $instance = null;

	public function __construct() {
		add_filter(
			'jet-form-builder/register-tabs-handlers',
			array( $this, 'register_tab' )
		);
	}

	public function register_tab( $handlers ) {
		$handlers[] = new Qpay_Tab();
		return $handlers;
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
