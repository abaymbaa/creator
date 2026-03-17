<?php


namespace Jet_FB_Paypal\QueryViews;


use Jet_Form_Builder\Db_Queries\Views\View_Base_Count_Trait;

/**
 * @method static PaymentsBySubscriptionCount findOne( $columns )
 *
 * Class PaymentsBySubscriptionCount
 * @package Jet_FB_Paypal\QueryViews
 */
class PaymentsBySubscriptionCount extends PaymentsBySubscription {

	use View_Base_Count_Trait;

}