<?php


namespace Jet_FB_Paypal\Pages\Actions;


use Jet_FB_Paypal\RestEndpoints\DeleteSubscription;
use Jet_Form_Builder\Admin\Buttons\Base_Vui_Button;
use Jet_Form_Builder\Admin\Single_Pages\Actions\Base_Rest_Page_Action;

class DeletePageAction extends Base_Rest_Page_Action {

	public function get_slug(): string {
		return 'delete';
	}

	public function get_position(): string {
		return self::PRIMARY;
	}

	public function get_rest_url(): string {
		return DeleteSubscription::dynamic_rest_url(
			array( 'id' => jet_fb_current_page()->get_id() )
		);
	}

	public function get_rest_methods(): string {
		return DeleteSubscription::get_methods();
	}

	public function get_payload(): array {
		return array(
			'redirect' => jet_fb_current_page()->get_parent()->get_url(),
		);
	}

	/**
	 * @return Base_Vui_Button
	 */
	public function get_button(): Base_Vui_Button {
		$button = parent::get_button();
		$button->set_label( __( 'Delete', 'jet-form-builder-paypal-subscriptions' ) );
		$button->set_size( $button::SIZE_MINI_X2 );
		$button->set_style( $button::STYLE_ACCENT_ERROR );

		return $button;
	}
}