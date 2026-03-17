<?php

namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder;

use Jet_Form_Builder\Exceptions\Gateway_Exception;

class Webhook_Manager {

	const ENDPOINT_PATH = '/wp-json/jfb-stripe/v1/webhook';

	/**
	 * @throws Gateway_Exception
	 */
	public function maybe_create_webhook() {
		$token      = jet_fb_gateway_current()->current_gateway( 'secret' );
		$target_url = rtrim( get_site_url(), '/' ) . self::ENDPOINT_PATH;

		$webhook_id = $this->get_webhook_id_by_endpoint( $target_url, $token );

		if ( $webhook_id ) {
			return;
		}

		$response = wp_remote_post(
			'https://api.stripe.com/v1/webhook_endpoints',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body' => array(
					'url'            => $target_url,
					'enabled_events' => array(
						'checkout.session.completed',
						'invoice.paid',
						'invoice.payment_failed',
						'customer.subscription.updated',
						'customer.subscription.deleted'
					),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Gateway_Exception( $response->get_error_message() );
		}

		$res_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $res_body['id'] ) ) {
			throw new Gateway_Exception( 'Could not create Stripe webhook.', $res_body );
		}
	}

	public function get_webhook_id_by_endpoint( $compared_url, $token ) {
		$response = wp_remote_get(
			'https://api.stripe.com/v1/webhook_endpoints',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Gateway_Exception( $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		$webhooks = $body['data'] ?? array();

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
