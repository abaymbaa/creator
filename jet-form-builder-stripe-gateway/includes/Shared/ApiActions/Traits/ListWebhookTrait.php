<?php


namespace Jet_FB_Paypal\ApiActions\Traits;

use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_FB_Paypal\ApiActions;

trait ListWebhookTrait {

	/**
	 * @param $compared_url
	 *
	 * @param bool $token
	 *
	 * @return false|mixed
	 * @throws Gateway_Exception
	 */
	public function get_webhook_id_by_endpoint( $compared_url, $token ) {
		$response = ( new ApiActions\ListWebhooks() )
			->set_bearer_auth( $token )
			->send_request();

		if ( ! isset( $response['webhooks'] ) ) {
			throw new Gateway_Exception( 'Can\'t get webhooks list', $response );
		}
		$webhooks = $response['webhooks'] ?? array();

		return $this->search_webhook_by_url( $webhooks, $compared_url );
	}

	public function search_webhook_by_url( $webhooks, $endpoint ) {
		$rest_url = get_rest_url();
		foreach ( $webhooks as $webhook ) {
			$url = $webhook['url'] ?? '';
			if (
				1 === preg_match( "#$endpoint#", $url ) &&
				1 === preg_match( "#$rest_url#", $url )
			) {
				return $webhook['id'];
			}
		}
		return false;
	}

}
