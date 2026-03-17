<?php


namespace Jet_FB_Paypal\TableViews\Actions;


use Jet_FB_Paypal\Pages\SingleSubscriptionPage;
use Jet_FB_Paypal\Utils\SubscriptionUtils;
use Jet_Form_Builder\Admin\Exceptions\Not_Found_Page_Exception;
use Jet_Form_Builder\Admin\Table_Views\Actions\Link_Single_Action;

class ViewSubscription extends Link_Single_Action {

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
		return SubscriptionUtils::can_be_cancelled( $record )
				|| SubscriptionUtils::can_be_suspended( $record );
	}

	/**
	 * @param array $record
	 *
	 * @return string
	 * @throws Not_Found_Page_Exception
	 */
	public function get_href( array $record ): string {
		$single = ( new SingleSubscriptionPage() )->set_id( $record['id'] ?? 0 );

		return $single->get_url();
	}


}