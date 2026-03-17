<?php
namespace Jet_FB_Stripe_Gateway\RestEndpoints\Base;

use Jet_Form_Builder\Gateways\Db_Models\Payment_Model;
use WP_REST_Request;
use WP_REST_Response;

abstract class RestPayment {

    abstract public static function gateway_rest_base(): string;
    abstract public static function gateway_id(): string;
    abstract public function run_action( array $payment, WP_REST_Request $request ): WP_REST_Response;

    public static function register() {
        add_action( 'rest_api_init', function () {
            register_rest_route(
                'jet-fb-stripe/v1',
                static::gateway_rest_base(),
                array(
                    'methods'             => 'POST',
                    'callback'            => array( static::class, 'handle' ),
                    'permission_callback' => function () { return current_user_can( 'manage_options' ); },
                    'args'                => array(
                        'id' => array( 'validate_callback' => 'is_numeric' ),
                    ),
                )
            );
        } );
    }

    public static function handle( WP_REST_Request $request ) {
        $id = absint( $request->get_param( 'id' ) );
        if ( ! $id ) {
            return new WP_REST_Response( array( 'message' => 'Empty payment id' ), 400 );
        }
        $payment = ( new Payment_Model )->find_one( array( 'id' => $id ) );
        if ( empty( $payment ) ) {
            return new WP_REST_Response( array( 'message' => 'Payment not found' ), 404 );
        }
        if ( ( $payment['gateway'] ?? '' ) !== static::gateway_id() ) {
            return new WP_REST_Response( array( 'message' => 'Wrong gateway' ), 403 );
        }
        $self = new static();
        return $self->run_action( $payment, $request );
    }
}
