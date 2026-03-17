<?php


namespace Jet_FB_Paypal\Pages\Columns;

use Jet_FB_Paypal\Resources\Payment;
use Jet_FB_Paypal\Utils\PaymentUtils;
use Jet_Form_Builder\Admin\Table_Views\Column_Base;

class RefundOptionsColumn extends Column_Base {

	protected $type = 'rawArray';

	public function get_value( array $record = array() ) {
		return array();
	}

}
