<?php


namespace Jet_FB_Stripe_Gateway\Compatibility\Jet_Engine;


use Jet_Engine\Gateways\PayPal;
use Jet_FB_Stripe_Gateway\Api_Methods\Checkout_Session;
use Jet_FB_Stripe_Gateway\Compatibility\Base_Stripe;
use Jet_FB_Stripe_Gateway\Compatibility\Compatibility_Trait;

class Manager {

	use Compatibility_Trait;
	use Base_Stripe;

	const GATEWAY_META_KEY = '_jet_gateway_data';

	public $gateways_meta;
	public $redirect;
	public $price;

	public $payment_id;
	public $data;

	private $base;

	/**
	 * @return boolean
	 */
	protected static function condition() {
		return function_exists( 'jet_engine' ) && apply_filters( 'jet-engine/forms/allow-gateways', false );
	}

	private function __construct() {
		add_action( 'jet-engine/forms/handler/before-send', array( $this, 'prevent_notifications' ) );
		add_action( 'jet-engine/forms/gateways/fields', array( $this, 'editor_fields' ) );
		add_action( 'jet-engine/forms/handler/after-send', array( $this, 'process_payment' ), 10, 2 );
		add_action( 'jet-engine/forms/gateways/success/' . $this->get_id(), array( $this, 'process_payment_result' ) );
		add_filter( 'jet-engine/forms/gateways/register', array( $this, 'register_gateway' ) );
	}


	public function register_gateway( $gateways ) {
		$gateways[] = $this;

		return $gateways;
	}

	/**
	 * @return PayPal
	 */
	public function base() {
		if ( ! $this->base ) {
			$this->base = \Jet_Engine\Gateways\Manager::instance()->get_gateways()['paypal'];
		}

		return $this->base;
	}

	/**
	 * Prevent unnecessary notifications processings before form is send.
	 *
	 * @param  [type] $handler [description]
	 *
	 * @return [type]          [description]
	 */
	public function prevent_notifications( $handler ) {
		$gateways = \Jet_Engine\Gateways\Manager::instance()->get_form_gateways( $handler->form );

		if ( empty( $gateways ) || empty( $gateways['gateway'] ) ) {
			return;
		}

		if ( $this->get_id() !== $gateways['gateway'] ) {
			return;
		}

		$handler->notifcations->unregister_notification_type( 'redirect' );

		$order      = isset( $gateways['notifications_order'] ) ? absint( $gateways['notifications_order'] ) : false;
		$keep_these = ! empty( $gateways['notifications_before'] ) ? $gateways['notifications_before'] : array();
		$all        = $handler->notifcations->get_all();
		$keep_these = apply_filters( 'jet-engine/forms/gateways/notifications-before', $keep_these, $all );

		if ( empty( $all ) ) {
			return;
		}

		foreach ( $all as $index => $notification ) {

			if ( 'insert_post' === $notification['type'] ) {
				if ( false === $order || $index === $order ) {
					continue;
				}
			}

			if ( 'redirect' === $notification['type'] ) {
				$this->redirect = $notification;
			}

			if ( ! in_array( $index, $keep_these ) ) {
				$handler->notifcations->unregister_notification( $index );
			}

		}
	}

	/**
	 * Process gateway payment
	 *
	 * @return [type] [description]
	 */
	public function process_payment( $handler, $success ) {

		if ( ! $success ) {
			return;
		}

		$gateways = \Jet_Engine\Gateways\Manager::instance()->get_form_gateways( $handler->form );

		if ( empty( $gateways )
		     || empty( $gateways['gateway'] )
		     || $this->get_id() !== $gateways['gateway']
		) {
			return;
		}


		$public   = ! empty( $gateways['stripe_public'] ) ? esc_attr( $gateways['stripe_public'] ) : false;
		$secret   = ! empty( $gateways['stripe_secret'] ) ? esc_attr( $gateways['stripe_secret'] ) : false;
		$currency = ! empty( $gateways['stripe_currency'] ) ? esc_attr( $gateways['stripe_currency'] ) : false;

		if ( ! $currency || ! $public || ! $secret ) {
			return;
		}

		$this->gateways_meta = array(
			'public'   => $public,
			'secret'   => $secret,
			'currency' => $currency
		);

		$price_field = ! empty( $gateways['price_field'] ) ? esc_attr( $gateways['price_field'] ) : false;
		$price_field = apply_filters( 'jet-engine/forms/gateways/price-field', $price_field, $handler );

		if ( ! $price_field
		     || empty( $handler->form_data['inserted_post_id'] )
		     || empty( $handler->form_data[ $price_field ] )
		) {
			return;
		}

		$order_id    = $handler->form_data['inserted_post_id'];
		$this->price = $this->get_price( $handler->form_data[ $price_field ] );

		$remove_refer_args = array(
			'jet_gateway',
			'payment',
			'token',
			'PayerID',
		);

		$success_refer    = $handler->refer;
		$cancel_refer     = $handler->refer;
		$success_redirect = ! empty( $gateways['use_success_redirect'] ) ? $gateways['use_success_redirect'] : false;
		$success_redirect = filter_var( $success_redirect, FILTER_VALIDATE_BOOLEAN );

		if ( ! $success_refer ) {
			$success_refer = home_url( '/' );
		}

		if ( ! $cancel_refer ) {
			$cancel_refer = home_url( '/' );
		}

		if ( $success_redirect && $this->redirect ) {
			$type = ! empty( $this->redirect['redirect_type'] ) ? $this->redirect['redirect_type'] : 'static_page';

			if ( 'static_page' === $type ) {
				$to_page       = ! empty( $this->redirect['redirect_page'] ) ? $this->redirect['redirect_page'] : false;
				$success_refer = ! empty( $to_page ) ? get_permalink( $to_page ) : false;
			} else {
				$success_refer = ! empty( $this->redirect['redirect_url'] ) ? $this->redirect['redirect_url'] : false;
			}
		}
		$order_token = $this->query_order_token( $order_id, $handler->form );

		$additional_args = array(
			'token'       => $order_token,
			'jet_gateway' => $this->get_id(),
		);

		$success_refer = add_query_arg(
			$additional_args,
			trailingslashit( remove_query_arg( $remove_refer_args, $success_refer ) )
		);

		$cancel_refer = add_query_arg(
			$additional_args,
			trailingslashit( remove_query_arg( $remove_refer_args, $cancel_refer ) )
		);

		$payment = $this->get_checkout_session( array(
			'success_url' => add_query_arg( array( 'payment' => 'success' ), $success_refer ),
			'cancel_url'  => add_query_arg( array( 'payment' => 'canceled' ), $cancel_refer ),
		) );

		if ( ! $payment || isset( $payment['error'] ) ) {
			return;
		}

		update_post_meta(
			$order_id,
			'_jet_gateway_data',
			json_encode( array(
				'payment_id' => $payment['id'],
				'token'      => $order_token,
				'form_id'    => $handler->form,
				'form_data'  => $handler->form_data,
			), JSON_UNESCAPED_UNICODE )
		);

		$stripe_args = array(
			'stripe_session_id' => $payment['id'],
			'stripe_public_key' => $this->gateways_meta['public']
		);

		if ( $handler->is_ajax() ) {
			wp_send_json( array_merge( array( 'status' => 'success' ), $stripe_args ) );
		} else {
			wp_redirect( add_query_arg(
				$stripe_args,
				trailingslashit( remove_query_arg( $remove_refer_args, $cancel_refer ) )
			) );
			die();
		}

	}

	public function decode_unserializable( $value ) {
		$data = json_decode( $value, true );

		return $data ? $data : maybe_unserialize( $value );
	}

	public function init_data() {
		$row_data = $this->get_form_by_payment( esc_attr( $_GET['token'] ) );

		$this->payment_id    = $row_data['post_id'];
		$this->data          = $this->decode_unserializable( $row_data['meta_value'] );
		$this->gateways_meta = \Jet_Engine\Gateways\Manager::instance()->get_form_gateways( $this->data['form_id'] );

		$this->gateways_meta['success_message'] = $this->gateways_meta['success_message']
			? $this->gateways_meta['success_message']
			: 'Payment success';

		$this->gateways_meta['failed_message'] = $this->gateways_meta['failed_message']
			? $this->gateways_meta['failed_message']
			: 'Payment failed';

		return $this;
	}

	/**
	 * Store payment status into order and show success/failed message
	 * @return [type] [description]
	 */
	public function process_payment_result() {
		$this->init_data();

		if ( empty( $this->gateways_meta['stripe_public'] )
		     || empty( $this->gateways_meta['stripe_secret'] )
		) {
			return;
		}

		$payment = $this->request( array(), '/' . $this->data['payment_id'], false );

		if ( ! $payment || isset( $payment['error'] ) || empty( $payment['payment_status'] ) ) {
			return;
		}

		$this->data = array_merge( $this->data, array(
			'status'  => $payment['payment_status'],
			'amount'  => array(
				'value'         => $this->get_formated_amount( $payment['amount_total'] ),
				'currency_code' => $payment['currency']
			),
			'date'    => date_i18n( 'F j, Y, H:i' ),
			'gateway' => $this->get_name(),
		) );

		if ( isset( $payment['customer_details'] ) && isset( $payment['customer_details']['email'] ) ) {
			$this->data['payer'] = array(
				'email' => $payment['customer_details']['email']
			);
		}

		update_post_meta( $this->payment_id, self::GATEWAY_META_KEY, json_encode( $this->data, JSON_UNESCAPED_UNICODE ) );

		\Jet_Engine\Gateways\Manager::instance()->add_data( $this->data );

		if ( in_array( $this->data['status'], $this->failed_statuses() ) ) {
			$this->base()->process_status( 'failed', $this->data['form_id'], $this->gateways_meta, $this->data['form_data'] );
		} else {
			$this->base()->process_status( 'success', $this->data['form_id'], $this->gateways_meta, $this->data['form_data'] );
		}

	}

	public function request( $params, $endpoint = '', $post = true ) {
		if ( empty( $this->gateways_meta['secret'] ) && empty( $this->gateways_meta['stripe_secret'] ) ) {
			return false;
		}
		$secret = isset( $this->gateways_meta['stripe_secret'] ) ? $this->gateways_meta['stripe_secret'] : $this->gateways_meta['secret'];

		$checkout = new Checkout_Session( esc_attr( $secret ) );

		$checkout->create( $params, $endpoint, $post );

		return $checkout->get_response( 'create' );
	}

	/**
	 * Returns form data by payment ID
	 *
	 * @param  [type] $payment [description]
	 *
	 * @return [type]          [description]
	 */
	public function get_form_by_payment( $payment = null ) {

		if ( ! $payment ) {
			return;
		}

		global $wpdb;
		$sql = "SELECT * FROM $wpdb->postmeta WHERE meta_key = '" . self::GATEWAY_META_KEY . "' AND meta_value LIKE '%$payment%';";

		return $wpdb->get_row( $sql, ARRAY_A );
	}


	/**
	 * Gateway-specific editor fields
	 *
	 * @return [description]
	 */
	public function editor_fields() {
		?>
        <div class="jet-engine-gateways-section" v-if="'stripe' === gateways.gateway">
            <div class="jet-engine-gateways-section__title"><?php
				_e( 'Stripe settings:', 'jet-engine' );
				?></div>
            <div class="jet-engine-gateways-row">
                <label for="gateways_stripe_public" class="jet-engine-gateways-row__label"><?php
					_e( 'Public Key', 'jet-engine' );
					?></label>
                <input type="text" v-model="gateways.stripe_public" id="gateways_stripe_public"
                       name="_gateways[stripe_public]" placeholder="<?php _e( 'Public Key', 'jet-engine' ); ?>">
            </div>
            <div class="jet-engine-gateways-row">
                <label for="gateways_stripe_secret" class="jet-engine-gateways-row__label"><?php
					_e( 'Secret Key', 'jet-engine' );
					?></label>
                <input type="text" v-model="gateways.stripe_secret" id="gateways_stripe_secret"
                       name="_gateways[stripe_secret]" placeholder="<?php _e( 'Secret', 'jet-engine' ); ?>">
            </div>
            <div class="jet-engine-gateways-row">
                <label for="gateways_stripe_currency" class="jet-engine-gateways-row__label"><?php
					_e( 'Currency Code', 'jet-engine' );
					?></label>
                <input type="text" v-model="gateways.stripe_currency" id="gateways_stripe_currency"
                       name="_gateways[stripe_currency]" placeholder="<?php _e( 'Currency code', 'jet-engine' ); ?>">
            </div>
        </div>
		<?php
	}

	public static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}