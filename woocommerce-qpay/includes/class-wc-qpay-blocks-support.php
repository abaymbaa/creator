<?php
/**
 * QPay Blocks Support class.
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_QPay_Blocks_Support extends AbstractPaymentMethodType {

    /**
     * The gateway name.
     *
     * @var string
     */
    protected $name = 'qpay';

    /**
     * Initializes the payment method type.
     */
    public function initialize() {
        $this->settings = get_option( 'woocommerce_qpay_settings', array() );
        error_log( 'QPay Blocks Support: initialized. Settings: ' . print_r( $this->settings, true ) );
    }

    /**
     * Returns if this payment method should be active.
     *
     * @return bool
     */
    public function is_active() {
        $active = ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'];
        error_log( 'QPay Blocks Support: is_active returning ' . ( $active ? 'TRUE' : 'FALSE' ) );
        return $active;
    }

    /**
     * Returns an array of script handles to register for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles() {
        $script_path = WC_QPAY_PLUGIN_URL . 'assets/js/qpay-blocks.js';
        
        $dependencies = array(
            'wc-blocks-registry',
            'wc-settings',
            'wp-element',
            'wp-html-entities',
            'wp-i18n',
        );
        $version = WC_QPAY_VERSION;

        wp_register_script(
            'wc-qpay-blocks-integration',
            $script_path,
            $dependencies,
            $version,
            true
        );

        return array( 'wc-qpay-blocks-integration' );
    }

    /**
     * Returns an array of key/value pairs to represent the settings in the JS.
     *
     * @return array
     */
    public function get_payment_method_data() {
        return array(
            'title'       => $this->get_setting( 'title', 'QPay' ),
            'description' => $this->get_setting( 'description', 'Pay via QPay QR code.' ),
            'icon'        => 'https://qpay.mn/logo.png',
            'supports'    => array_filter( $this->get_supported_features() ),
        );
    }

    /**
     * Returns supported features.
     *
     * @return array
     */
    public function get_supported_features() {
        $gateways = WC()->payment_gateways()->payment_gateways();
        if ( isset( $gateways['qpay'] ) ) {
            return $gateways['qpay']->supports;
        }
        return array( 'products' );
    }
}
