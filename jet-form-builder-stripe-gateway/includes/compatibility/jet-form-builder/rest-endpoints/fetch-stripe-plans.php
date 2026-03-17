<?php
namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Rest_Endpoints;

use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Rest_Api\Rest_Api_Endpoint_Base;

class Fetch_Stripe_Plans extends Rest_Api_Endpoint_Base {

	public static function get_rest_base() {
		return 'fetch-stripe-plans';
	}

	public static function get_methods() {
		return \WP_REST_Server::CREATABLE;
	}

	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	public function run_callback( \WP_REST_Request $request ) {
		$secret        = $request->get_param( 'secret' );
		$force_refresh = filter_var( $request->get_param( 'force_refresh' ), FILTER_VALIDATE_BOOLEAN );

		if ( empty( $secret ) ) {
			throw new Gateway_Exception( 'Stripe secret key is required', $secret );
		}

		$data = $this->get_cached_stripe_plans( $secret, $force_refresh );

		return rest_ensure_response( [
			'success' => true,
			'data'    => $data,
		] );
	}

	private function get_cached_stripe_plans( string $secret, bool $force_refresh = false ): array {
		$cache_key = 'jet_fb_stripe_plans_' . md5( $secret );

		if ( ! $force_refresh ) {
			$cached = get_transient( $cache_key );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$prices   = $this->fetch_prices( $secret );
		$products = $this->fetch_products( $secret );

		$data = $this->map_prices_with_products( $prices, $products );

		set_transient( $cache_key, $data, WEEK_IN_SECONDS );

		return $data;
	}

	private function fetch_prices( string $secret ): array {
		$response = wp_remote_get(
			'https://api.stripe.com/v1/prices/search?query=' . urlencode( "type:'recurring'" ),
			[
				'headers' => [ 'Authorization' => 'Bearer ' . $secret ],
				'timeout' => 15,
			]
		);

		if ( is_wp_error( $response ) ) {
			throw new Gateway_Exception( $response->get_error_message(), $response );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( empty( $body->data ) ) {
			throw new Gateway_Exception( 'Stripe returned no prices', $body );
		}

		return $body->data;
	}

	private function fetch_products( string $secret ): array {
		$response = wp_remote_get(
			'https://api.stripe.com/v1/products?limit=100',
			[
				'headers' => [ 'Authorization' => 'Bearer ' . $secret ],
				'timeout' => 15,
			]
		);

		if ( is_wp_error( $response ) ) {
			throw new Gateway_Exception( $response->get_error_message(), $response );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );

		return $body->data ?? [];
	}

	private function map_prices_with_products( array $prices, array $products ): array {
		$product_meta = [];
		$product_names = [];

		foreach ( $products as $product ) {
			$product_meta[ $product->id ] = [
				'name'   => $product->name ?? '',
				'active' => (bool) ( $product->active ?? false ),
			];
		}

		$data = [];


		foreach ( $prices as $price ) {
			$meta      = $product_meta[ $price->product ] ?? null;
			$name      = $meta['name'] ?? null;
			$is_active = $meta ? (bool) $meta['active'] : true; // если продукт не нашли — не ломаем UX

			$interval   = $price->recurring->interval ?? '';
			$amount_raw = isset( $price->unit_amount ) ? (float) $price->unit_amount : 0.0;
			$amount     = $amount_raw / 100;
			$currency   = isset( $price->currency ) ? strtoupper( (string) $price->currency ) : '';

			$label_base = $name
				? sprintf( '%s (%.2f %s/%s)', $name, $amount, $currency, $interval )
				: ( $price->nickname ?? $price->id );

			$label = $is_active ? $label_base : ( '⛔ ' . $label_base );

			$data[] = [
				'id'         => $price->id,
				'key'        => $price->id,
				'label'      => $label,
				'amount'     => $amount,
				'currency'   => $currency,
				'interval'   => $interval,
				'product_id' => $price->product,
				'is_active'  => $is_active,
				'disabled'   => ! $is_active,
			];
		}

		return $data;
	}
}
