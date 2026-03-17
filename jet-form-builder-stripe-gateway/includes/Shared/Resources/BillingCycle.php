<?php


namespace Jet_FB_Paypal\Resources;

use Jet_FB_Paypal\Utils\Utils;
use Jet_Form_Builder\Gateways\Gateway_Manager;

class BillingCycle {

	private $subscription;
	private $pricing;
	private $quantity;
	private $interval_unit;
	private $interval_count;
	private $tenure_type;
	private $currency;
	private $amount;

	public function __construct( array $billing_cycle ) {
		$this->tenure_type = $billing_cycle['tenure_type'];

		$this->set_currency( $billing_cycle );
		$this->set_quantity( $billing_cycle );
		$this->set_interval_unit( $billing_cycle );
		$this->set_interval_count( $billing_cycle );
		$this->set_pricing( $billing_cycle );
		$this->set_amount( $billing_cycle );
	}

	public function get_encoded_symbol(): string {
		return html_entity_decode(
			Gateway_Manager::instance()->currency_symbol( $this->get_currency() )
		);
	}

	public function get_unit(): string {
		return Utils::format_recurring_unit( $this->get_interval_unit() );
	}

	/**
	 * @param Subscription $subscription
	 *
	 * @return BillingCycle
	 */
	public function set_subscription( Subscription $subscription ): BillingCycle {
		$this->subscription = $subscription;

		return $this;
	}

	public function subscription(): Subscription {
		return $this->subscription;
	}

	public function pricing(): PricingScheme {
		return $this->pricing;
	}

	/**
	 * @return mixed
	 */
	public function get_amount() {
		return Amount::escape( $this->amount );
	}

	/**
	 * @return mixed
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * @return mixed
	 */
	public function get_interval_count(): int {
		return $this->interval_count;
	}

	/**
	 * @return mixed
	 */
	public function get_interval_unit(): string {
		return $this->interval_unit;
	}

	/**
	 * @return mixed
	 */
	public function get_quantity(): int {
		return $this->quantity;
	}

	/**
	 * @return mixed
	 */
	public function get_tenure_type(): string {
		return $this->tenure_type;
	}

	/**
	 * @param array $billing_cycle
	 */
	public function set_quantity( array $billing_cycle ) {
		$quantity = $billing_cycle['total_cycles'] ?? $billing_cycle['quantity'] ?? 0;

		$this->set_quantity_raw( (int) $quantity );
	}

	/**
	 * @param int $quantity
	 */
	public function set_quantity_raw( int $quantity ) {
		$this->quantity = $quantity;
	}

	/**
	 * @param $billing_cycle
	 */
	public function set_interval_unit( array $billing_cycle ) {
		$unit = $billing_cycle['frequency']['interval_unit'] ?? $billing_cycle['interval_unit'] ?? '';

		$this->set_interval_unit_raw( $unit );
	}

	/**
	 * @param string $interval_unit
	 */
	public function set_interval_unit_raw( string $interval_unit ) {
		$this->interval_unit = $interval_unit;
	}

	/**
	 * @param $billing_cycle
	 */
	public function set_interval_count( array $billing_cycle ) {
		$count = $billing_cycle['frequency']['interval_count'] ?? $billing_cycle['interval_count'] ?? 0;

		$this->set_interval_count_raw( (int) $count );
	}

	/**
	 * @param int $interval_count
	 */
	public function set_interval_count_raw( int $interval_count ) {
		$this->interval_count = $interval_count;
	}

	/**
	 * @param array $billing_cycle
	 */
	public function set_pricing( array $billing_cycle ) {
		$pricing = $billing_cycle['pricing_scheme'] ?? array();

		if ( empty( $pricing ) ) {
			return;
		}
		$this->set_pricing_raw( new PricingScheme( $this, $pricing ) );
	}

	/**
	 * @param mixed $pricing
	 */
	public function set_pricing_raw( PricingScheme $pricing ) {
		$this->pricing = $pricing;
	}

	/**
	 * @param array $billing_cycle
	 */
	public function set_currency( array $billing_cycle ) {
		$this->set_currency_raw( $billing_cycle['currency'] ?? '' );
	}

	/**
	 * @param mixed $currency
	 */
	public function set_currency_raw( $currency ) {
		$this->currency = $currency;
	}

	/**
	 * @param array $billing_cycle
	 */
	public function set_amount( array $billing_cycle ) {
		$this->set_amount_raw( $billing_cycle['amount'] ?? 0 );
	}

	/**
	 * @param mixed $amount
	 */
	public function set_amount_raw( $amount ) {
		$this->amount = $amount;
	}


}
