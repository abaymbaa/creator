<?php


namespace Jet_FB_Paypal\TableViews\Columns;

use Jet_FB_Paypal\Pages\SingleSubscriptionPage;
use Jet_Form_Builder\Admin\Exceptions\Not_Found_Page_Exception;
use Jet_Form_Builder\Admin\Table_Views\Column_Advanced_Base;

class PrimarySubscriberColumn extends SubscriberColumn {

	public function get_type( array $record = array() ): string {
		if ( ! $this->is_attached( $record ) ) {
			return parent::get_type( $record );
		}

		return self::LINK;
	}

	/**
	 * @param array $record
	 *
	 * @return mixed
	 * @throws Not_Found_Page_Exception
	 */
	public function get_value( array $record = array() ) {
		$text = parent::get_value( $record );

		if ( ! $this->is_attached( $record ) ) {
			return $text;
		}

		$single = ( new SingleSubscriptionPage() )->set_id( $record['id'] ?? 0 );

		return array(
			'text'    => $text,
			'href'    => $single->get_url(),
			'primary' => true,
		);
	}
}
