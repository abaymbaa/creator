<?php


namespace Jet_FB_Paypal\Pages;

use Jet_FB_Paypal\TableViews;
use Jet_Form_Builder\Gateways\Pages\Payments_Page;

class PaymentsPage extends Payments_Page {
	use PaypalPageTrait;

	public function page_config(): array {
		return ( new TableViews\Payments )->load_view();
	}

	public function register_scripts() {
		wp_register_script(
			$this->slug(),
			$this->base_script_url(),
			array( 'wp-api', 'wp-api-fetch' ),
			\Jet_Form_Builder\Plugin::instance()->get_version(),
			true
		);
	}

}
