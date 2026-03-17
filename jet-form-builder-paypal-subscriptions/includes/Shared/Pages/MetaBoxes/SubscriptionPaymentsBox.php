<?php


namespace Jet_FB_Paypal\Pages\MetaBoxes;

use Jet_FB_Paypal\Pages\Columns\RefundOptionsColumn;
use Jet_FB_Paypal\Pages\Columns\SubscriptionPaymentsActions;
use Jet_FB_Paypal\QueryViews\PaymentsBySubscription;
use Jet_FB_Paypal\QueryViews\PaymentsBySubscriptionCount;
use Jet_FB_Paypal\RestEndpoints\FetchPaymentsBySubscription;
use Jet_FB_Paypal\TableViews\Columns\GrossColumn;
use Jet_FB_Paypal\TableViews\Columns\PaymentStatusColumn;
use Jet_FB_Paypal\TableViews\Columns\PaymentTypeColumn;
use Jet_Form_Builder\Admin\Exceptions\Empty_Box_Exception;
use Jet_Form_Builder\Admin\Single_Pages\Meta_Boxes\Base_Table_Box;
use Jet_Form_Builder\Admin\Table_Views\Column_Base;
use Jet_Form_Builder\Admin\Table_Views\Columns\Created_At_Column;
use Jet_Form_Builder\Admin\Table_Views\Columns\Record_Id_Column_Advanced;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

class SubscriptionPaymentsBox extends Base_Table_Box {

	public function get_title(): string {
		return __( 'Related Payments', 'jet-form-builder' );
	}

	public function get_total(): int {
		return PaymentsBySubscriptionCount::findOne(
			array( 'subscription_id' => $this->get_id() )
		)->get_count();
	}

	public function get_rest_url(): string {
		return FetchPaymentsBySubscription::dynamic_rest_url(
			array( 'id' => $this->get_id() )
		);
	}

	public function get_rest_methods(): string {
		return FetchPaymentsBySubscription::get_methods();
	}

	public function get_columns(): array {
		return array(
			'type'               => new PaymentTypeColumn(),
			'date'               => new Created_At_Column(),
			'status'             => new PaymentStatusColumn(),
			'gross'              => new GrossColumn(),
			'id'                 => new Record_Id_Column_Advanced(),
			'refund'             => new RefundOptionsColumn(),
			Column_Base::ACTIONS => new SubscriptionPaymentsActions(),
		);
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 * @throws Empty_Box_Exception
	 */
	public function get_raw_list( array $args ): array {
		try {
			return PaymentsBySubscription::find(
				array( 'subscription_id' => $this->get_id() )
			)->set_table_args( $args )->query()->query_all();

		} catch ( Query_Builder_Exception $exception ) {
			throw new Empty_Box_Exception( $exception->getMessage(), ...$exception->get_additional() );
		}
	}
}
