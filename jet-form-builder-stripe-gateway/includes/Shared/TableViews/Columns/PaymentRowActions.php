<?php


namespace Jet_FB_Paypal\TableViews\Columns;


use Jet_FB_Paypal\TableViews\Actions\RefundPaymentIsset;
use Jet_FB_Paypal\TableViews\Actions\ViewPaymentSubscriptionIsset;
use Jet_Form_Builder\Admin\Table_Views\Actions\View_Single_Action;
use Jet_Form_Builder\Gateways\Table_Views\Columns\Row_Actions_Column;

class PaymentRowActions extends Row_Actions_Column {

	/**
	 * @return View_Single_Action[]
	 */
	protected function get_actions(): array {
		$actions = array_merge(
			parent::get_actions(),
			array(
				new ViewPaymentSubscriptionIsset(),
				new RefundPaymentIsset(),
			)
		);

		usort( $actions, function ( $prev, $next ) {
			/** @var $prev View_Single_Action */
			/** @var $next View_Single_Action */

			return 'danger' === $prev->get_type() ? 1 : - 1;
		} );


		return $actions;
	}
}