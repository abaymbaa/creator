<?php


namespace Jet_FB_Paypal\Resources;

use Jet_FB_Paypal\ApiActions;
use Jet_FB_Paypal\DbModels\SubscriptionModel;
use Jet_FB_Paypal\DbModels\SubscriptionNoteModel;
use Jet_FB_Paypal\Logic\SubscribeNow;
use Jet_FB_Paypal\Utils\SubscriptionUtils;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Exceptions\Handler_Exception;
use Jet_Form_Builder\Exceptions\Silence_Exception;

class Subscription implements WithPayer, GatewayResource {

	private $plan;
	private $quantity;
	private $gateway_id;

	private $id;
	private $status;
	private $message;
	private $payer_id;
	private $first_name;
	private $last_name;
	private $email;
	private $shipping;
	private $links;
	private $form_id;
	private $user_id;

	/** @var \WP_User|bool */
	private $user;

	public function __construct( array $args ) {
		$this->quantity   = $args['quantity'] ?? 1;
		$this->id         = (string) $args['id'] ?? '';
		$this->status     = $args['status'] ?? '';
		$this->message    = $args['message'] ?? '';
		$this->links      = $args['links'] ?? array();
		$this->gateway_id = $args['gateway_id'] ?? '';
		$this->form_id    = $args['form_id'] ?? 0;
		$this->user_id    = $args['user_id'] ?? 0;

		$subscriber = $args['subscriber'] ?? array();

		if ( empty( $subscriber ) ) {
			return;
		}

		$this->set_subscriber( $subscriber );
	}

	/**
	 * @param Plan $plan
	 * @param array $args
	 *
	 * @return Subscription
	 * @throws Gateway_Exception
	 */
	public static function create( Plan $plan, array $args ): Subscription {
		$action = ( new ApiActions\SubscribeNowAction() )
			->set_bearer_auth( jet_fb_gateway_current()->get_current_token() )
			->set_app_context(
				array(
					'return_url' => $args['return_url'] ?? '',
					'cancel_url' => $args['cancel_url'] ?? '',
				)
			);

		$action->set_body(
			array(
				'plan_id'             => $plan->get_plan_id(),
				'quantity'            => $args['quantity'] ?? 1,
				'application_context' => $action->get_app_context(),
			)
		);

		do_action( 'jet-form-builder/gateways/before-create', $action );

		$subscription = $action->send_request();

		$self = new static( $subscription );
		$self->set_plan( $plan );

		if ( ! $self->get_id() ) {
			throw new Gateway_Exception( $self->message, $subscription );
		}

		return $self;
	}

	/**
	 * @param string $billing_id
	 *
	 * @return Subscription
	 * @throws Gateway_Exception
	 */
	public static function details( string $billing_id ): Subscription {
		$subscription = ( new ApiActions\ShowSubscriptionDetailsAction() )
			->set_bearer_auth( jet_fb_gateway_current()->get_current_token() )
			->set_path(
				array(
					'billing_id' => $billing_id,
				)
			)
			->send_request();

		return new static( $subscription );
	}

	public function set_subscriber( array $subscriber ) {
		$this->payer_id   = $subscriber['payer_id'] ?? '';
		$this->first_name = $subscriber['name']['given_name'] ?? '';
		$this->last_name  = $subscriber['name']['surname'] ?? '';
		$this->email      = $subscriber['email_address'] ?? '';

		$this->shipping = new Shipping( $subscriber['shipping_address'] ?? array() );
	}

	public function set_refunded(): Subscription {
		if ( $this->is_active() ) {
			$this->update_status_soft( SubscribeNow::REFUNDED );
		}

		return $this;
	}

	public function set_suspended(): Subscription {
		if ( $this->is_active() ) {
			$this->update_status_soft( SubscribeNow::SUSPENDED );
		}

		return $this;
	}

	public function set_active(): Subscription {
		if ( !$this->is_active() ) {
			$this->update_status_soft( SubscribeNow::ACTIVE );
		}

		return $this;
	}

	/**
	 * @param mixed $plan
	 */
	public function set_plan( Plan $plan ) {
		$this->plan = $plan;
	}

	/**
	 * @return Shipping
	 */
	public function shipping(): Shipping {
		return $this->shipping;
	}

	public function is_cancelled(): bool {
		return SubscriptionUtils::is_cancelled( $this->get_status() );
	}

	public function is_suspended(): bool {
		return SubscriptionUtils::is_suspended( $this->get_status() );
	}

	public function is_expired(): bool {
		return SubscriptionUtils::is_expired( $this->get_status() );
	}

	public function is_active(): bool {
		return SubscriptionUtils::is_active( $this->get_status() );
	}

	public function is_custom_status(): bool {
		return SubscriptionUtils::is_custom_status( $this->get_status() );
	}

	public function is_broken(): bool {
		return SubscriptionUtils::is_broken( $this->get_status() );
	}

	public function can_be_suspended(): bool {
		return SubscriptionUtils::can_be_suspended( $this->get_status() );
	}

	public function can_be_cancelled(): bool {
		return SubscriptionUtils::can_be_cancelled( $this->get_status() );
	}

	/**
	 * @return Plan
	 */
	public function plan(): Plan {
		return $this->plan;
	}

	public function get_quantity(): int {
		return $this->quantity;
	}

	/**
	 * @return int|mixed
	 */
	public function get_form_id(): int {
		return $this->form_id;
	}

	/**
	 * @return string
	 */
	public function get_status(): string {
		return $this->status;
	}

	/**
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * @return mixed|string
	 */
	public function get_gateway_id(): string {
		return $this->gateway_id;
	}

	/**
	 * @return string
	 */
	public function get_email(): string {
		return $this->email;
	}

	/**
	 * @return string
	 */
	public function get_first_name(): string {
		return $this->first_name;
	}

	/**
	 * @return string
	 */
	public function get_last_name(): string {
		return $this->last_name;
	}

	/**
	 * @return string
	 */
	public function get_payer_id(): string {
		return $this->payer_id;
	}

	/**
	 * @return array|mixed
	 */
	public function get_links(): array {
		return $this->links;
	}

	public function get_user() {
		if ( ! is_null( $this->user ) ) {
			return $this->user;
		}
		$this->user = get_user_by( 'ID', $this->user_id );

		return $this->user;
	}

	/**
	 * Return true if status was updated
	 *
	 * @param string $new_status
	 *
	 * @return bool
	 */
	public function update_status_soft( string $new_status ): bool {
		try {
			$this->update_status( $new_status );
		} catch ( Handler_Exception $exception ) {
			return false;
		}

		return true;
	}

	/**
	 * @throws Sql_Exception
	 * @throws Silence_Exception
	 */
	public function update_status( string $new_status ) {
		( new SubscriptionModel() )->update(
			array(
				'status' => $new_status,
			),
			array(
				'id' => $this->get_id(),
			)
		);

		SubscriptionNoteModel::add(
			array(
				'subscription_id' => $this->get_id(),
				'message'         => SubscriptionUtils::status_note(
					$this->get_status(),
					$new_status
				),
			)
		);

		$this->status = $new_status;
	}

}
