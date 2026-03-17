<?php


namespace Jet_FB_Paypal\Pages\MetaBoxes;


use Jet_FB_Paypal\Pages\SingleSubscriptionPage;
use Jet_Form_Builder\Admin\Exceptions\Empty_Box_Exception;
use Jet_Form_Builder\Admin\Exceptions\Not_Found_Page_Exception;

trait RelatedSubscriptionBoxTrait {

	public function get_title(): string {
		return __( 'Related Subscription', 'jet-form-builder' );
	}

	/**
	 * @return array
	 * @throws Empty_Box_Exception
	 * @throws Not_Found_Page_Exception
	 */
	public function get_single(): array {
		$single = ( new SingleSubscriptionPage() )->set_id( $this->get_id() );

		return array(
			'href'  => $single->get_url(),
			'title' => __( 'View related subscription', 'jet-form-builder' ),
		);
	}

}