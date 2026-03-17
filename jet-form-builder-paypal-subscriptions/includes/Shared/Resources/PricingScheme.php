<?php


namespace Jet_FB_Paypal\Resources;

class PricingScheme {

	private $cycle;
	private $fixed_price;
	private $pricing_model;
	private $tiers;
	private $find_tier;
	private $amount;

	public function __construct( BillingCycle $cycle, array $scheme ) {
		$this->set_cycle( $cycle );
		$this->fixed_price   = $scheme['fixed_price'] ?? array();
		$this->pricing_model = $scheme['pricing_model'] ?? '';
		$this->tiers         = $scheme['tiers'] ?? array();
	}

	/**
	 * @param BillingCycle $cycle
	 *
	 * @return PricingScheme
	 */
	public function set_cycle( BillingCycle $cycle ): PricingScheme {
		$this->cycle = $cycle;

		return $this;
	}

	public function is_fixed(): bool {
		return ! empty( $this->fixed_price );
	}

	public function cycle(): BillingCycle {
		return $this->cycle;
	}

	public function amount(): Amount {
		if ( is_null( $this->amount ) ) {
			$this->amount = $this->query_amount();
		}

		return $this->amount;
	}

	private function query_amount(): Amount {
		if ( $this->is_fixed() ) {
			return new Amount( $this, $this->fixed_price );
		}

		switch ( $this->pricing_model ) {
			case 'TIERED':
			case 'VOLUME':
			default:
				$tier = $this->get_tier();

				return new Amount( $this, $tier['amount'] );
		}
	}

	public function get_tier(): array {
		if ( ! is_null( $this->find_tier ) ) {
			return $this->find_tier;
		}
		$quantity = $this->cycle()->subscription()->get_quantity();

		foreach ( $this->tiers as $tier ) {
			$start = (int) ( $tier['starting_quantity'] ?? 0 );
			$end   = (int) ( $tier['ending_quantity'] ?? 0 ); // 0 -> from start and more

			if ( $quantity >= $start && ( 0 === $end || $quantity <= $end ) ) {
				$this->find_tier = $tier;
				break;
			}
		}

		return $this->find_tier;
	}


}
