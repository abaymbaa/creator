<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder;


use Jet_FB_Stripe_Gateway\Compatibility\Compatibility_Trait;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Rest_Endpoints\Rest_Controller;
use Jet_Form_Builder\Gateways\Gateway_Manager;

class Manager {

	use Compatibility_Trait;

	public static $instance = null;

	protected static function condition() {
		return (
			function_exists( 'jet_form_builder' )
			&& version_compare( jet_form_builder()->get_version(), '2.0.0', '>=' )
			&& isset( jet_form_builder()->allow_gateways )
			&& jet_form_builder()->allow_gateways
		);
	}

	public function __construct() {
		add_action(
			'jet-form-builder/gateways/register',
			array( $this, 'register_stripe_controller' )
		);

		( new Rest_Controller() )->rest_api_init();
	}

	public function register_stripe_controller( Gateway_Manager $jfb_manager ) {
		$jfb_manager->register_gateway( new Controller() );
	}


	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}