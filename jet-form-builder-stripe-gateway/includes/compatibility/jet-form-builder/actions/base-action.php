<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Actions;

use Jet_Form_Builder\Gateways\Base_Gateway_Action;

abstract class Base_Action extends Base_Gateway_Action {

	/**
	 * @return mixed
	 */
	public function action_slug() {
		return '';
	}

	/**
	 * @return string
	 */
	public function base_url(): string {
		return 'https://api.stripe.com/';
	}

	protected function to_json( $body ) {
		return $body;
	}

	protected function is_body_ready(): bool {
		return is_array( $this->body ) && ! empty( $this->body );
	}
}
