<?php


namespace Jet_FB_Paypal\DbModels\Constraints;


use Jet_FB_Paypal\DbModels\SubscriptionModel;
use Jet_Form_Builder\Db_Queries\Base_Db_Constraint;

class SubscriptionModelConstraint extends Base_Db_Constraint {

	public function __construct() {
		$this->set_model( new SubscriptionModel() );
		$this->set_foreign_keys( array( 'subscription_id' ) );
		$this->on_delete( self::ACTION_CASCADE );
	}

}