<?php


namespace Jet_FB_Paypal\Pages\Actions;


use Jet_Form_Builder\Admin\Exceptions\Not_Found_Page_Exception;
use Jet_Form_Builder\Admin\Table_Views\Actions\Link_Single_Action;
use Jet_Form_Builder\Gateways\Pages\Single_Payment_Page;

class ViewSubscriptionPayment extends Link_Single_Action {

	public function get_slug(): string {
		return 'view';
	}

	public function get_label(): string {
		return __( 'View', 'jet-form-builder' );
	}

	public function show_in_header(): bool {
		return false;
	}

	public function show_in_row( array $record ): bool {
		return true;
	}

	/**
	 * @param array $record
	 *
	 * @return string
	 * @throws Not_Found_Page_Exception
	 */
	public function get_href( array $record ): string {
		$single = ( new Single_Payment_Page() )->set_id( $record['id'] ?? 0 );

		return $single->get_url();
	}

}