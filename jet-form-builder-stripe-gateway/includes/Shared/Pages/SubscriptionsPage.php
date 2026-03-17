<?php


namespace Jet_FB_Paypal\Pages;

use Jet_FB_Paypal\TableViews;
use Jet_Form_Builder\Admin\Editor;
use Jet_Form_Builder\Admin\Pages\Base_Page;
use Jet_Form_Builder\Admin\Pages\Pages_Manager;
use Jet_Form_Builder\Dev_Mode;

class SubscriptionsPage extends Base_Page {

	use PaypalPageTrait;

	const SLUG = 'jfb-subscriptions';

	public function slug(): string {
		return self::SLUG;
	}

	public function title(): string {
		return __( 'Subscriptions', 'jet-form-builder' );
	}

	public function page_config(): array {
		return ( new TableViews\SubscribeNow() )->load_view();
	}

	public function assets() {
		wp_enqueue_script( Pages_Manager::SCRIPT_VUEX_PACKAGE );

		parent::assets();
	}

}
