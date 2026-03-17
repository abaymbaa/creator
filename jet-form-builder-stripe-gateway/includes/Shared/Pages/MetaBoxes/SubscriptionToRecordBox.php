<?php


namespace Jet_FB_Paypal\Pages\MetaBoxes;


use Jet_FB_Paypal\Pages\SingleSubscriptionPage;
use Jet_FB_Paypal\QueryViews\SubscriptionByRecord;
use Jet_Form_Builder\Admin\Exceptions\Empty_Box_Exception;
use Jet_Form_Builder\Admin\Exceptions\Not_Found_Page_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

class SubscriptionToRecordBox extends SubscriptionGeneralBox {

	use RelatedSubscriptionBoxTrait;

	/**
	 * @return int
	 * @throws Empty_Box_Exception
	 */
	public function get_id(): int {
		$record_id = parent::get_id();

		try {
			list ( $subscription_id ) = SubscriptionByRecord::findOne(
				array(
					'record_id' => $record_id,
				)
			)->query()->query_col();

		} catch ( Query_Builder_Exception $exception ) {
			throw new Empty_Box_Exception( $exception->getMessage(), ...$exception->get_additional() );
		}

		return $subscription_id;
	}

}