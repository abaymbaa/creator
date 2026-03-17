<?php


namespace Jet_FB_Paypal\Pages\MetaBoxes;


use Jet_FB_Paypal\QueryViews\PayerShippingBySubscription;
use Jet_Form_Builder\Admin\Exceptions\Empty_Box_Exception;
use Jet_Form_Builder\Classes\Arrayable\Arrayable_Once;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;
use Jet_Form_Builder\Gateways\Meta_Boxes\Payer_Box;

class SubscriptionPayerBox extends Payer_Box implements Arrayable_Once {

	public function get_list(): array {
		try {
			return PayerShippingBySubscription::findOne(
				array(
					'subscription_id' => $this->get_id(),
				)
			)->query()->query_one();
		} catch ( Query_Builder_Exception $exception ) {
			throw new Empty_Box_Exception( $exception->getMessage(), ...$exception->get_additional() );
		}
	}

}