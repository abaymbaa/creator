<?php


namespace Jet_FB_Paypal\Pages;

use Jet_FB_Paypal\Pages\Actions\CancelSubscriptionPageAction;
use Jet_FB_Paypal\Pages\Actions\DeletePageAction;
use Jet_FB_Paypal\Pages\Actions\UpdatePageAction;
use Jet_FB_Paypal\Pages\Actions\SuspendSubscriptionPageAction;
use Jet_FB_Paypal\Pages\MetaBoxes\RecordToSubscriptionBox;
use Jet_FB_Paypal\Pages\MetaBoxes\SubscriptionGeneralBox;
use Jet_FB_Paypal\Pages\MetaBoxes\SubscriptionNotesBox;
use Jet_FB_Paypal\Pages\MetaBoxes\SubscriptionPayerBox;
use Jet_FB_Paypal\Pages\MetaBoxes\SubscriptionPayerShippingBox;
use Jet_FB_Paypal\Pages\MetaBoxes\SubscriptionPaymentsBox;
use Jet_Form_Builder\Admin\Pages\Pages_Manager;
use Jet_Form_Builder\Admin\Single_Pages\Base_Single_Page;
use Jet_Form_Builder\Admin\Single_Pages\Meta_Containers\Normal_Meta_Container;
use Jet_Form_Builder\Admin\Single_Pages\Meta_Containers\Side_Meta_Container;

class SingleSubscriptionPage extends Base_Single_Page {

	use PaypalPageTrait;

	/**
	 * Page title
	 */
	public function title(): string {
		return __( 'JetFormBuilder Subscription', 'jet-form-builder-paypal-subscriptions' );
	}

	public function parent_slug(): string {
		return SubscriptionsPage::SLUG;
	}

	public function meta_containers(): array {
		return array(
			new Normal_Meta_Container(
				new SubscriptionPayerBox(),
				new SubscriptionPayerShippingBox(),
				new SubscriptionPaymentsBox(),
				new SubscriptionNotesBox()
			),
			new Side_Meta_Container(
				new SubscriptionGeneralBox(),
				new RecordToSubscriptionBox()
			),
		);
	}

	public function assets() {
		wp_enqueue_style( Pages_Manager::STYLE_ADMIN );
		wp_enqueue_script( Pages_Manager::SCRIPT_VUEX_PACKAGE );

		parent::assets();
	}
}
