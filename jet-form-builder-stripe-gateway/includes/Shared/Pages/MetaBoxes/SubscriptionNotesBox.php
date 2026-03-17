<?php


namespace Jet_FB_Paypal\Pages\MetaBoxes;

use Jet_FB_Paypal\Pages\Columns\CreatedByNoteColumn;
use Jet_FB_Paypal\QueryViews\SubscriptionNotesView;
use Jet_FB_Paypal\QueryViews\SubscriptionNotesViewCount;
use Jet_FB_Paypal\RestEndpoints\AddSubscriptionNote;
use Jet_FB_Paypal\RestEndpoints\FetchNotesBySubscription;
use Jet_Form_Builder\Actions\Methods\Form_Record\Admin\View_Columns\Error_Message_Column;
use Jet_Form_Builder\Admin\Exceptions\Empty_Box_Exception;
use Jet_Form_Builder\Admin\Single_Pages\Meta_Boxes\Base_Table_Box;
use Jet_Form_Builder\Admin\Table_Views\Columns\Created_At_Column;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

class SubscriptionNotesBox extends Base_Table_Box {

	protected $limit          = 8;
	protected $show_overflow  = true;
	protected $footer_heading = false;

	public function get_title(): string {
		return __( 'Notes', 'jet-form-builder-paypal-subscriptions' );
	}

	public function get_columns(): array {
		return array(
			'by'         => new CreatedByNoteColumn(),
			'text'       => new Error_Message_Column(),
			'created_at' => new Created_At_Column(),
		);
	}

	public function get_rest_url(): string {
		return FetchNotesBySubscription::dynamic_rest_url(
			array( 'id' => $this->get_id() )
		);
	}

	public function get_rest_methods(): string {
		return FetchNotesBySubscription::get_methods();
	}

	public function get_stable_limit(): int {
		return $this->limit;
	}

	public function get_total(): int {
		return SubscriptionNotesViewCount::findOne(
			array( 'subscription_id' => $this->get_id() )
		)->get_count();
	}

	private function get_add_not_endpoint(): array {
		return array(
			'url'    => AddSubscriptionNote::dynamic_rest_url( array( 'id' => $this->get_id() ) ),
			'method' => AddSubscriptionNote::get_methods(),
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
			return array_reverse(
				SubscriptionNotesView::find(
					array( 'subscription_id' => $this->get_id() )
				)->set_table_args( $args )
				->query()
				->query_all()
			);

		} catch ( Query_Builder_Exception $exception ) {
			throw new Empty_Box_Exception( $exception, ...$exception->get_additional() );
		}
	}

	public function to_array(): array {
		return parent::to_array() + array(
			'add_note' => $this->get_add_not_endpoint(),
		);
	}
}
