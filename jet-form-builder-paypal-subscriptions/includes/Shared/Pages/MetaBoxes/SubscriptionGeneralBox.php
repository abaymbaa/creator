<?php


namespace Jet_FB_Paypal\Pages\MetaBoxes;


use Jet_FB_Paypal\Pages\Actions\CancelSubscriptionPageAction;
use Jet_FB_Paypal\Pages\Actions\DeletePageAction;
use Jet_FB_Paypal\Pages\Actions\SuspendSubscriptionPageAction;
use Jet_FB_Paypal\Pages\Actions\UpdatePageAction;
use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_FB_Paypal\Resources\Subscription;
use Jet_FB_Paypal\RestEndpoints\ReceiveSubscription;
use Jet_FB_Paypal\TableViews\Columns\BillingCycleColumn;
use Jet_FB_Paypal\TableViews\Columns\SubscriberColumn;
use Jet_FB_Paypal\TableViews\Columns\SubscriptionStatusColumn;
use Jet_FB_Paypal\Utils\SubscriptionUtils;
use Jet_Form_Builder\Admin\Exceptions\Not_Found_Page_Exception;
use Jet_Form_Builder\Admin\Single_Pages\Actions\Base_Rest_Page_Action;
use Jet_Form_Builder\Admin\Single_Pages\Meta_Boxes\Base_List_Box;
use Jet_Form_Builder\Admin\Table_Views\Columns\Created_At_Column;
use Jet_Form_Builder\Admin\Table_Views\Columns\Updated_At_Column;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

class SubscriptionGeneralBox extends Base_List_Box {

	const SLUG = 'subscription-details';

	public function get_title(): string {
		return __( 'Subscription Details', 'jet-form-builder-paypal-subscriptions' );
	}

	public function get_slug(): string {
		return self::SLUG;
	}

	public function get_columns(): array {
		return array(
			'subscriber'    => new SubscriberColumn(),
			'billing_cycle' => new BillingCycleColumn(),
			'status'        => new SubscriptionStatusColumn(),
			'created_at'    => new Created_At_Column(),
			'updated_at'    => new Updated_At_Column(),
		);
	}

	public function get_rest_url(): string {
		return ReceiveSubscription::dynamic_rest_url(
			array( 'id' => $this->get_id() )
		);
	}

	public function get_rest_methods(): string {
		return ReceiveSubscription::get_methods();
	}

	/**
	 * @return Base_Rest_Page_Action[]
	 * @throws Not_Found_Page_Exception
	 */
	public function get_actions(): array {
		if ( ! SubscriptionUtils::is_single() ) {
			return array();
		}

		$subscription = new Subscription( $this->get_list() );
		$actions      = array();

		if ( $subscription->is_broken() ) {
			$actions[] = new DeletePageAction();
		} else {
			array_push( $actions,
				new SuspendSubscriptionPageAction(),
				new CancelSubscriptionPageAction()
			);
		}

		return $actions;
	}

	/**
	 * @return array
	 * @throws Not_Found_Page_Exception
	 */
	public function get_list(): array {
		try {
			return SubscriptionsView::findById( $this->get_id() );
		} catch ( Query_Builder_Exception $exception ) {
			throw new Not_Found_Page_Exception( $exception->getMessage(), ...$exception->get_additional() );
		}
	}
}