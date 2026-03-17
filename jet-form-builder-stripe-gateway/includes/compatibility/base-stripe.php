<?php


namespace Jet_FB_Stripe_Gateway\Compatibility;

use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Logic\Pay_Now_Logic;
use Jet_FB_Stripe_Gateway\Compatibility\Jet_Form_Builder\Logic\Subscription_Logic;
use JFB_Modules\Gateways\Module;
use Jet_Form_Builder\Exceptions\Gateway_Exception;

trait Base_Stripe {

	public function get_id() {
		return 'stripe';
	}

	public function get_name() {
		return __( 'Stripe Checkout', 'jet-form-builder' );
	}

	protected function options_list() {
		return array(
			'public'       => array(
				'label' => __( 'Public Key', 'jet-form-builder' ),
			),
			'secret'       => array(
				'label' => __( 'Secret Key', 'jet-form-builder' ),
			),
			'currency'     => array(
				'label' => __( 'Currency Code', 'jet-form-builder' ),
			),
			'use_global'   => array(
				'label'    => __( 'Use Global Settings', 'jet-form-builder' ),
				'required' => false,
			),
			'gateway_type' => array(
				'label'   => _x( 'Gateway Action', 'Stripe gateways editor data', 'jet-form-builder' ),
				'options' => array(
					Pay_Now_Logic::scenario_id()      => __( 'Pay Now', 'jet-form-builder-stripe-gateway' ),
					Subscription_Logic::scenario_id() => __( 'Subscription', 'jet-form-builder-stripe-gateway' ),
				),
			),
		);
	}

	protected function failed_statuses() {
		return array( 'unpaid' );
	}

	protected function get_price( $price ) {
		return ( (float) $price ) * 100;
	}

	public function get_formated_amount( $amount ) {
		return number_format( $amount / 100.00, 2 );
	}

	protected function query_order_token( $order_id, $form_id ) {
		return $order_id . '-' . md5( $order_id . $form_id );
	}

	public function get_checkout_session( $params ) {
		return $this->request(
			array_merge(
				array(
					'mode'                 => 'payment',
					'payment_method_types' => $this->get_payment_methods(),
					'line_items'           => array(
						array(
							'quantity' => 1,
							'amount'   => $this->price,
							'currency' => $this->get_currency(),
							'name'     => $this->get_name_payment(),
						),
					),
				),
				$params
			)
		);
	}

	public function get_payment_methods() {
		return apply_filters( 'jet-form-builder/stripe/payment-methods', array( 'card' ), $this );
	}

	public function get_currency() {
		if ( isset( $this->gateways_meta['currency'] ) ) {
			return $this->gateways_meta['currency'];
		}
		if ( isset( $this->gateways_meta[ $this->get_id() ] ) && isset( $this->gateways_meta[ $this->get_id() ]['currency'] ) ) {
			return $this->gateways_meta[ $this->get_id() ]['currency'];
		}
	}

	public function get_name_payment() {
		return get_option( 'blogname' ) . ' ' . __( 'payment', 'jet-form-builder' );
	}

	protected static function gateway_id(): string {
		return 'stripe';
	}

	public static function get_credentials(): array {
		return Module::instance()->get_global_settings( self::gateway_id() ) ?: array();
	}

	public static function get_credentials_by_form( int $form_id ): array {
		if ( ! $form_id ) {
			return self::get_credentials();
		}

		$form_gateways = Module::instance()->get_form_gateways_by_id( $form_id );
		$credits       = $form_gateways[ self::gateway_id() ] ?? array();
		
		if ( ! empty( $credits['secret'] ) && empty( $credits['use_global'] ) ) {
			return $credits;
		}

		return self::get_credentials();
	}

	public static function get_token_by_form_id( int $form_id ) {
		if ( ! $form_id ) {
			return self::get_token_global();
		}

		$credits = self::get_credentials_by_form( $form_id );

		return self::get_token_with_credits(
			$credits['public'] ?? '',
			$credits['secret'] ?? ''
		);
	}

	public static function get_token_global() {
		$credits = self::get_credentials();

		return self::get_token_with_credits(
			$credits['public'] ?? '',
			$credits['secret'] ?? ''
		);
	}

	public static function get_token_with_credits( $public, $secret ) {
		if ( empty( $secret ) ) {
			throw new Gateway_Exception(
				'Empty `secret_key`.',
				(string) $public,
				(string) $secret
			);
		}
		return $secret;
	}

	public function get_current_token() {
		$secret = $this->current_gateway( 'secret' );
		return self::get_token_with_credits( $this->current_gateway( 'public' ), $secret );
	}


}
