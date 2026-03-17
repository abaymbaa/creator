<?php


namespace Jet_FB_Paypal\QueryViews;


use Jet_FB_Paypal\DbModels\SubscriptionNoteModel;
use Jet_Form_Builder\Db_Queries\Views\View_Base;
use Jet_Form_Builder\Db_Queries\Views\View_Base_Count_Trait;

/**
 * @method static SubscriptionNotesViewCount findOne( $columns )
 *
 * Class SubscriptionNotesViewCount
 * @package Jet_FB_Paypal\QueryViews
 */
class SubscriptionNotesViewCount extends SubscriptionNotesView {

	use View_Base_Count_Trait;

}