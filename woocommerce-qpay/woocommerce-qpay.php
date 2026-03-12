<?php
/**
 * Plugin Name: WooCommerce QPay Gateway
 * Plugin URI: https://qpay.mn
 * Description: Adds QPay (qpay.mn) payment gateway to WooCommerce with support for Blocks checkout.
 * Version: 1.0.0
 * Author: Byambaa Avirmed
 * Author URI: https://fb.com/abaymbaa
 * Text Domain: woocommerce-qpay
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 8.5
 */

defined( 'ABSPATH' ) || exit;

define( 'WC_QPAY_VERSION', '1.0.0' );
define( 'WC_QPAY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_QPAY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Initialize the gateway.
 */
add_action( 'plugins_loaded', 'wc_qpay_init', 10 );

function wc_qpay_init() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    require_once WC_QPAY_PLUGIN_DIR . 'includes/class-wc-gateway-qpay.php';

    // Register the gateway
    add_filter( 'woocommerce_payment_gateways', 'wc_qpay_add_gateway' );

    // Ensure it shows up even if currency/other checks fail during testing
    add_filter( 'woocommerce_available_payment_gateways', 'wc_qpay_ensure_available', 100 );
}

function wc_qpay_ensure_available( $gateways ) {
    if ( is_admin() ) {
        return $gateways;
    }

    if ( ! isset( $gateways['qpay'] ) ) {
        $all_gateways = WC()->payment_gateways->payment_gateways();
        if ( isset( $all_gateways['qpay'] ) && 'yes' === $all_gateways['qpay']->enabled ) {
            $gateways['qpay'] = $all_gateways['qpay'];
        }
    }
    return $gateways;
}

function wc_qpay_add_gateway( $gateways ) {
    $gateways[] = 'WC_Gateway_QPay';
    return $gateways;
}

/**
 * Declare HPOS compatibility.
 */
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

/**
 * Register Blocks support.
 */
add_action( 'woocommerce_blocks_loaded', 'wc_qpay_gateway_block_support' );

function wc_qpay_gateway_block_support() {
    if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        return;
    }

    require_once WC_QPAY_PLUGIN_DIR . 'includes/class-wc-qpay-blocks-support.php';

    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( \Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
            $payment_method_registry->register( new WC_QPay_Blocks_Support() );
        }
    );
}
