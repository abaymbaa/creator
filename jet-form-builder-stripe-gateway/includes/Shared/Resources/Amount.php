<?php


namespace Jet_FB_Paypal\Resources;


class Amount {

	private $value;
	private $currency_code;

	private $pricing;

	public function __construct( PricingScheme $pricing, array $amount ) {
		$this->pricing       = $pricing;
		$this->value         = $amount['value'] ?? 0;
		$this->currency_code = $amount['currency_code'] ?? '';
	}

	private function get_quantity(): int {
		return $this->pricing->cycle()->subscription()->get_quantity();
	}

	public function sanitize_value(): string {
		return static::sanitize( $this->value, $this->get_quantity() );
	}

	public function esc_value(): string {
		return static::sanitize( $this->value, $this->get_quantity() );
	}

	public function get_currency(): string {
		return $this->currency_code;
	}

	public static function sanitize( $value, $quantity = 1 ): string {
		return number_format( $quantity * $value, 2, '.', '' );
	}

	public static function escape( $value, $quantity = 1 ): string {
		return number_format( $quantity * $value, 2, '.', ',' );
	}

}