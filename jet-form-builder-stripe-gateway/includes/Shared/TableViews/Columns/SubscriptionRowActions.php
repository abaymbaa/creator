<?php


namespace Jet_FB_Paypal\TableViews\Columns;


use Jet_FB_Paypal\TableViews\Actions\CancelSubscription;
use Jet_FB_Paypal\TableViews\Actions\DeleteSubscription;
use Jet_FB_Paypal\TableViews\Actions\SuspendSubscription;
use Jet_FB_Paypal\TableViews\Actions\ViewSubscription;
use Jet_Form_Builder\Admin\Table_Views\Actions\View_Single_Action;
use Jet_Form_Builder\Admin\Table_Views\Columns\Base_Row_Actions_Column;

class SubscriptionRowActions extends Base_Row_Actions_Column {

	/**
	 * @return View_Single_Action[]
	 */
	protected function get_actions(): array {
		return array(
			new DeleteSubscription(),
			new ViewSubscription(),
			new SuspendSubscription(),
			new CancelSubscription(),
		);
	}
}