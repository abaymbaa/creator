<?php

namespace Jet_FB_Qpay;

class Plugin {

	public static $instance = null;

	public function __construct() {
		$this->init_components();
	}

	public function init_components() {
		\Jet_FB_Qpay\Jet_Form_Builder\Manager::instance();
		\Jet_FB_Qpay\Jet_Form_Builder\Tabs\Manager::instance();
		\Jet_FB_Qpay\Rest_Endpoints\Manager::instance();
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
