<?php


namespace Jet_FB_Paypal\Pages\Columns;


use Jet_Form_Builder\Admin\Table_Views\Column_Advanced_Base;

class CreatedByNoteColumn extends Column_Advanced_Base {

	protected $column = 'created_by';

	public function get_label(): string {
		return __( 'Created By', 'jet-form-builder' );
	}

}