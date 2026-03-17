<?php
namespace Jet_FB_Paypal\Pages;

use Jet_Form_Builder\Admin\Pages\Base_Page;

trait PaypalPageTrait {

	public function base_script_url(): string {
		return JET_FB_SUBSCRIPTIONS_SHARED_URL . "assets/js/pages/{$this->slug()}.js";
	}

}