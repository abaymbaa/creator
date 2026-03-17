<?php

namespace Jet_FB_Paypal\Pages;

use Jet_Form_Builder\Gateways\Pages\Single_Payment_Page;

class SinglePaymentPage extends Single_Payment_Page {

	use PaypalPageTrait;

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