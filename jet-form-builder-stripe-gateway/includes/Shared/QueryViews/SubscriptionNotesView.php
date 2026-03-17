<?php


namespace Jet_FB_Paypal\QueryViews;


use Jet_FB_Paypal\DbModels\SubscriptionNoteModel;
use Jet_Form_Builder\Db_Queries\Views\View_Base;

class SubscriptionNotesView extends View_Base {

	protected $order_by = array(
		array(
			'column' => 'id',
			'sort'   => self::FROM_HIGH_TO_LOW,
		),
	);

	public function table(): string {
		return SubscriptionNoteModel::table();
	}

	public function select_columns(): array {
		return SubscriptionNoteModel::schema_columns();
	}

}