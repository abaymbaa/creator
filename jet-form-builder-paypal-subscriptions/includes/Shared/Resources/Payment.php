<?php


namespace Jet_FB_Paypal\Resources;

use Jet_FB_Paypal\Utils\PaymentUtils;

class Payment implements GatewayResource {

	private $amount_value;
	private $status;
	private $subscription;
	private $payer;
	private $type;
	private $id;
	private $gateway_id;

	public function __construct( array $payment ) {
		$this->amount_value = $payment['amount_value'] ?? '0.00';
		$this->status       = $payment['status'] ?? '';
		$this->type         = $payment['type'] ?? 'initial';
		$this->gateway_id   = $payment['gateway_id'] ?? '';
		$this->id           = $payment['id'] ?? '';

		$this->set_payer( $payment );
		$this->set_subscription( $payment );
	}

	public function get_id(): string {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function get_gateway_id(): string {
		return $this->gateway_id;
	}

	/**
	 * @return Subscription
	 */
	public function subscription(): Subscription {
		return $this->subscription;
	}

	/**
	 * @return mixed
	 */
	public function payer(): Payer {
		return $this->payer;
	}

	/**
	 * @param array $payment
	 */
	public function set_subscription( array $payment ) {
		$subscription = $payment['subscription'] ?? array();

		if ( ! empty( $subscription ) ) {
			$this->set_subscription_raw(
				new Subscription( $subscription )
			);

			return;
		}

		$id = $payment['subscription_id'] ?? '';

		$this->set_subscription_raw(
			new Subscription(
				array( 'id' => $id )
			)
		);
	}

	/**
	 * @param array $payment
	 */
	public function set_payer( array $payment ) {
		$payer = $payment['payer'] ?? array();

		$this->set_payer_raw( new Payer( $payer ) );
	}

	public function is_refunded(): bool {
		return PaymentUtils::is_refunded( $this->get_status() );
	}

	public function is_renewal(): bool {
		return PaymentUtils::is_renewal( $this->get_type() );
	}

	public function can_be_refunded(): bool {
		return PaymentUtils::can_be_refunded(
			array(
				'status'          => $this->get_status(),
				'subscription_id' => $this->subscription()->get_id()
			)
		);
	}

	/**
	 * @return mixed|string
	 */
	public function get_status(): string {
		return $this->status;
	}

	/**
	 * @return mixed|string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * @return mixed|string
	 */
	public function get_amount_value(): string {
		return $this->amount_value;
	}


	/**
	 * @param Payer $payer
	 */
	public function set_payer_raw( Payer $payer ) {
		$this->payer = $payer;
	}

	/**
	 * @param mixed $subscription
	 */
	public function set_subscription_raw( Subscription $subscription ) {
		$this->subscription = $subscription;
	}

	public function refund_payload(): array {
		return array(
			'contact_email' => array(
				'label' => __( 'Subscriber Email', 'jet-form-builder-paypal-subscriptions' ),
				'value' => $this->payer()->get_email(),
			),
			'gross'         => array(
				'label' => __( 'Total Refund Amount', 'jet-form-builder' ),
				'value' => $this->get_amount_value(),
			),
			'invoice_id'    => array(
				'label' => __( 'Invoice Number (Optional)', 'jet-form-builder-paypal-subscriptions' ),
				'value' => '',
			),
			'note_to_payer' => array(
				'label' => __( 'Note To Buyer (Optional)', 'jet-form-builder-paypal-subscriptions' ),
				'value' => '',
			),
		);
	}

}
