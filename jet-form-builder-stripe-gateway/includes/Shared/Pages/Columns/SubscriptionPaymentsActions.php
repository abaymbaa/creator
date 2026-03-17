<?php


namespace Jet_FB_Paypal\Pages\Columns;


use Jet_FB_Paypal\Pages\Actions\RefundSubscriptionPayment;
use Jet_FB_Paypal\Pages\Actions\ViewSubscriptionPayment;
use Jet_Form_Builder\Admin\Table_Views\Actions\View_Single_Action;
use Jet_Form_Builder\Admin\Table_Views\Columns\Base_Row_Actions_Column;

class SubscriptionPaymentsActions extends Base_Row_Actions_Column {

	/**
	 * @return View_Single_Action[]
	 */
	protected function get_actions(): array {
		return array(
			new ViewSubscriptionPayment(),
			new RefundSubscriptionPayment(),
		);
	}
}