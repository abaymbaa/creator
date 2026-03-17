<?php


namespace Jet_FB_Paypal\Pages\Actions;


use Jet_Form_Builder\Admin\Buttons\Base_Vui_Button;
use Jet_Form_Builder\Admin\Single_Pages\Actions\Base_Rest_Page_Action;

class UpdatePageAction extends Base_Rest_Page_Action {

	public function get_slug(): string {
		return 'update';
	}

	public function get_position(): string {
		return self::PRIMARY;
	}

	public function get_subscriptions(): array {
		return array( self::ON_UPDATE );
	}

	public function get_rest_url(): string {
		return '';
	}

	public function get_rest_methods(): string {
		return '';
	}

	/**
	 * @return Base_Vui_Button
	 */
	public function get_button(): Base_Vui_Button {
		$button = parent::get_button();
		$button->set_label( __( 'Update', 'jet-form-builder-paypal-subscriptions' ) );
		$button->set_size( $button::SIZE_MINI );
		$button->set_disabled( true );

		return $button;
	}
}