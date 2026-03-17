<?php


namespace Jet_FB_Paypal\Pages\MetaBoxes;


use Jet_FB_Paypal\QueryViews\RecordBySubscription;
use Jet_Form_Builder\Actions\Methods\Form_Record\Admin\Meta_Boxes\Form_Record_Values_Box;
use Jet_Form_Builder\Actions\Methods\Form_Record\Admin\Pages\Single_Form_Record_Page;
use Jet_Form_Builder\Admin\Exceptions\Empty_Box_Exception;
use Jet_Form_Builder\Admin\Exceptions\Not_Found_Page_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

class RecordToSubscriptionBox extends Form_Record_Values_Box {

	public function get_title(): string {
		return __( 'Related Form Record', 'jet-form-builder' );
	}

	/**
	 * @return array
	 * @throws Empty_Box_Exception
	 * @throws Not_Found_Page_Exception
	 */
	public function get_single(): array {
		$single = ( new Single_Form_Record_Page() )->set_id( $this->get_id() );

		return array(
			'href'  => $single->get_url(),
			'title' => __( 'View related form record', 'jet-form-builder' ),
		);
	}

	/**
	 * @return int
	 * @throws Empty_Box_Exception
	 */
	public function get_id(): int {
		$subscription_id = parent::get_id();

		try {
			list ( $record_id ) = RecordBySubscription::findOne(
				array(
					'subscription_id' => $subscription_id,
				)
			)->query()->query_col();

			if ($record_id === null) {
				throw new Empty_Box_Exception('Record ID is null for subscription ID: ' . $subscription_id);
			}

		} catch ( Query_Builder_Exception $exception ) {
			throw new Empty_Box_Exception( $exception->getMessage(), ...$exception->get_additional() );
		}

		return $record_id;

	}

}