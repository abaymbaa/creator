<?php
/**
 * Plugin Name: CreatorLMS QPay Gateway
 * Plugin URI: https://qpay.mn
 * Description: Adds QPay (qpay.mn) payment gateway to CreatorLMS. Enables QR code payments via Mongolian bank apps.
 * Version: 1.0.0
 * Author: CreatorLMS
 * Author URI: https://creatorlms.com
 * Text Domain: creatorlms-qpay
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

define( 'CRLMS_QPAY_VERSION', '1.0.0' );
define( 'CRLMS_QPAY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CRLMS_QPAY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Bootstrap the QPay gateway after CreatorLMS loads its e-commerce package.
 */
add_action( 'plugins_loaded', 'crlms_qpay_bootstrap', 30 );

function crlms_qpay_bootstrap() {
    static $done = false;
    if ( $done ) {
        return;
    }

    // Check if CreatorLMS is active.
    if ( ! class_exists( 'CreatorLms', false ) && ! function_exists( 'CRLMS' ) ) {
        add_action( 'admin_notices', 'crlms_qpay_missing_creatorlms_notice' );
        return;
    }

    // Check if the e-commerce gateway base exists (loaded by CreatorLMS packages).
    if ( ! class_exists( 'CodeRex\\Ecommerce\\Abstracts\\PaymentGateway' ) ) {
        // If CreatorLMS loads late for some reason, retry on init.
        add_action( 'init', 'crlms_qpay_bootstrap', 1 );
        return;
    }

    $done = true;

    // Load gateway files.
    require_once CRLMS_QPAY_PLUGIN_DIR . 'includes/QPayAPI.php';
    require_once CRLMS_QPAY_PLUGIN_DIR . 'includes/GatewayQPay.php';

    // CreatorLMS uses the same filter name for different payloads in different places.
    // We handle both:
    // - numeric array: list of gateway class names
    // - associative array: gateway settings map
    add_filter( 'creatorlms_payment_gateways', 'crlms_qpay_filter_creatorlms_payment_gateways', 10, 1 );
    add_filter( 'creatorlms_gateway_settings', 'crlms_qpay_register_gateway_settings', 10, 1 );

    // If credentials/mode change, flush cached tokens so API calls use fresh auth.
    add_action( 'update_option_creatorlms_qpay_settings', 'crlms_qpay_clear_tokens_on_settings_update', 10, 2 );
}

/**
 * Filter `creatorlms_payment_gateways`.
 *
 * @param array $value Existing gateway class list OR settings map (CreatorLMS inconsistency).
 * @return array
 */
function crlms_qpay_filter_creatorlms_payment_gateways( $value ) {
    if ( ! is_array( $value ) ) {
        return $value;
    }

    // Numeric array: gateway class names.
    if ( array_values( $value ) === $value ) {
        if ( ! in_array( 'GatewayQPay', $value, true ) ) {
            $value[] = 'GatewayQPay';
        }
        return $value;
    }

    // Associative array: settings map.
    return crlms_qpay_register_gateway_settings( $value );
}

/**
 * Register QPay settings in the gateway settings map.
 *
 * @param array $settings Existing settings map.
 * @return array
 */
function crlms_qpay_register_gateway_settings( $settings ) {
    if ( ! is_array( $settings ) ) {
        return $settings;
    }
    if ( ! class_exists( 'GatewayQPay' ) ) {
        return $settings;
    }

    $gateway                 = new GatewayQPay();
    $settings[ $gateway->id ] = $gateway->get_settings();

    return $settings;
}

/**
 * Clear QPay cached tokens after settings updates.
 *
 * @param mixed $old_value
 * @param mixed $value
 * @return void
 */
function crlms_qpay_clear_tokens_on_settings_update( $old_value, $value ) {
    if ( class_exists( 'QPayAPI' ) ) {
        QPayAPI::clear_tokens();
    }
}

/**
 * Admin notice when CreatorLMS is not active.
 */
function crlms_qpay_missing_creatorlms_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <strong><?php esc_html_e( 'CreatorLMS QPay Gateway', 'creatorlms-qpay' ); ?></strong>
            <?php esc_html_e( 'requires CreatorLMS to be installed and activated.', 'creatorlms-qpay' ); ?>
        </p>
    </div>
    <?php
}