<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder;


use Jet_Form_Builder\Classes\Instance_Trait;
use Jet_Form_Builder\Gateways\Scenarios_Abstract\Scenarios_Manager_Abstract;

/**
 * @method static Scenarios_Manager instance()
 *
 * Class Scenarios_Manager
 * @package Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder
 */
class Scenarios_Manager extends Scenarios_Manager_Abstract {

	use Instance_Trait;

	public function __construct() {
		$this->set_logic_manager( new Logic_Repository() );
		$this->set_view_manager( new View_Repository() );

		parent::__construct();
	}

}