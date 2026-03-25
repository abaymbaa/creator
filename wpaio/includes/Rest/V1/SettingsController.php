<?php
namespace Wpaio\Rest\V1;

use Wpaio\Abstracts\RestController;
use Wpaio\Admin\Settings\AdminSettings;

/**
 * SettingsController class.
 *
 * Handles REST API endpoints for settings.
 *
 * @since 1.0.0
 */
class SettingsController extends RestController {

	/**
	 * The base route for settings endpoints.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $base = 'settings/(?P<group_id>[\w-]+)';

	protected $search = 'page/search';


	/**
	 * Register the routes for the settings endpoints.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->base,
			array(
				'args'   => array(
					'group_id' => array(
						'description' => __( 'Settings group ID.', 'wpaio' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_items' ),
					'permission_callback' => array( $this, 'update_items_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->base . '/(?P<id>[\w-]+)',
			array(
				'args'   => array(
					'group_id' => array(
						'description' => __( 'Settings group ID.', 'wpaio' ),
						'type'        => 'string',
					),
					'id'       => array(
						'description' => __( 'Unique identifier for the resource.', 'wpaio' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_items_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::EDITABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->search,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_page' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/integration-status/(?P<name>[\w,-]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_integration_status' ),
					'permission_callback' => array( $this, 'get_integration_status_permission_check' ),
					'args'                => array(
						'name' => array(
							'description' => __( 'Integration name(s), comma-separated.', 'wpaio' ),
							'type'        => 'string',
							'required'    => true,
						),
					),
				),
			)
		);
	}

	/**
	 * Get integration status
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function get_integration_status( $request ) {
		$integration_names_str = $request->get_param( 'name' );
		$integration_names     = array_map( 'trim', explode( ',', $integration_names_str ) );
		$integrations_option   = get_option( 'wpaio_integrations', array() );
		$statuses              = array();
		$is_pro_active         = apply_filters( 'wpaio_is_pro', false );

		foreach ( $integration_names as $name ) {
			if ( empty( $name ) ) {
				continue;
			}
			$is_enabled = ! empty( $integrations_option[ $name ]['is_enable'] ) ? 1 == $integrations_option[ $name ]['is_enable'] : false;
			$statuses[ $name ] = $is_enabled && $is_pro_active;
		}

		if ( 1 === count( $integration_names ) ) {
			return rest_ensure_response( array( 'is_enabled' => $statuses[ $integration_names[0] ] ) );
		}

		return rest_ensure_response( $statuses );
	}

	/**
	 * Permission check for getting integration status
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function get_integration_status_permission_check( $request ) {
		// Restrict endpoint to only users who have the capability to manage options.
		return current_user_can( 'manage_options' );
	}


	/**
	 * Get items (settings) for a specific group.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response|\WP_Error The response or error object.
	 *
	 * @since 1.0.0
	 */
	public function get_items( $request ) {
		$settings = $this->get_group_settings( $request['group_id'] );
		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		$data = array();

		foreach ( $settings as $setting_obj ) {
			$setting = $this->prepare_item_for_response( $setting_obj, $request );
			$setting = $this->prepare_response_for_collection( $setting );
			$data[]  = $setting;
		}

		return rest_ensure_response( $data );
	}


	/**
	 * Get items (settings) for a specific group.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response|\WP_Error The response or error object.
	 *
	 * @since 1.0.0
	 */
	public function update_items( $request ) {
		$get_data = $request->get_json_params();
		$group_id = $request['group_id'];

		if ( ! is_array( $get_data ) ) {
			return new \WP_Error( 'rest_setting_setting_invalid', __( 'Invalid setting.', 'wpaio' ), array( 'status' => 404 ) );
		}

		$settings = $this->get_group_settings( $group_id );

		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		foreach ( $get_data as $key => $value ) {
			if ( ! is_array( $value ) ) {
				$value = $group_id === 'permalink' ? $value : wp_kses_post( trim( stripslashes( is_null( $value ) ? '' : $value ) ) );
			}

			if ( 'payment-gateway' === $group_id && is_array( $value ) && isset( $value['value'] ) ) {
				$value = $value['value'];
			}
			
			if ( ! $this->is_valid_option_key( $key ) ) {
				continue;
			}

			update_option( $key, $value );
			flush_rewrite_rules(true);
			do_action("wpaio_{$group_id}_settings_updated", $key, $value, $group_id );
			
			// Trigger payment gateway settings event for tracking
			if ( 'payment-gateway' === $group_id ) {
				do_action( 'wpaio_payment_gateway_settings_saved', $key, $value );
			}
		}
		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Updated successfully.', 'wpaio' ),
			)
		);
	}

	/**
	 * Validate if the option key is valid.
	 *
	 * @param string $key The option key.
	 * @return bool True if valid, false otherwise.
	 *
	 * @since 1.0.0
	 */
	private function is_valid_option_key( $key ) {
		$keys = array(
			'wpaio_course_page_id',
			'wpaio_profile_page_id',
			'wpaio_checkout_page_id',
			'wpaio_thank_you_page_id',
			'wpaio_privacy_policy_page_id',
			'wpaio_terms_page_id',
			'wpaio_registration_page_id',
			'wpaio_courses_per_page',
			'wpaio_archive_page_layout',
			'wpaio_archive_page_layout_style',
			'wpaio_archive_page_filter_is_enabled',
			'wpaio_archive_page_filters',
			'wpaio_archive_page_sorting_is_enabled',
			'wpaio_archive_page_search_is_enabled',
			'wpaio_archive_page_category_is_enabled',
			'wpaio_archive_page_row',
			'wpaio_single_course_page_features',
			'wpaio_single_course_page_layout',
			'wpaio_columns_per_row',
			'wpaio_container_width',
			'wpaio_debug_mode',
			'wpaio_color_preset',
			'wpaio_primary_color_scheme',
			'wpaio_primary_hover_color_scheme',
			'wpaio_heading_color_scheme',
			'wpaio_body_text_color_scheme',
			'wpaio_body_progress_color_scheme',
			'wpaio_checkout_page_layout_type',
			'wpaio_leaderboard_settings',
			'wpaio_privacy_policy_message',
			'wpaio_guest_checkout',
			'wpaio_allow_purchase_without_login',
			'wpaio_permalink',
			'wpaio_offline_settings',
			'wpaio_stripe_settings',
			'wpaio_paypal_settings',
			'wpaio_mollie_settings',
			'wpaio_razorpay_settings',
			'wpaio_authorize_net_settings',
			'wpaio_qpay_settings',
			'wpaio_currency',
			'wpaio_currency_pos',
			'wpaio_price_thousand_sep',
			'wpaio_price_decimal_sep',
			'wpaio_price_num_decimals',
			'wpaio_tax_enabled',
			'wpaio_tax_label',
			'wpaio_prices_include_tax',
			'wpaio_eu_vat_enabled',
			'wpaio_disable_vat_validation',
			'wpaio_vat_number_label',
			'wpaio_fallback_tax_rate',
			'wpaio_existing_tax_rates',
			'wpaio_new_tax_rates',
			'wpaio_tax_rates',
			'wpaio_countries',
			'wpaio_states',
			'wpaio_use_custom_video_player',
			'wpaio_email_branding_image',
			'wpaio_email_base_color',
			'wpaio_email_background_color',
			'wpaio_email_body_background_color',
			'wpaio_email_body_text_color',
			'wpaio_email_button_possition',
			'wpaio_email_sender_email_address',
			'wpaio_email_sender_name',
			'wpaio_email_footer_text',
		);
		return in_array( $key, $keys, true );
	}


	/**
	 * Get a single item (setting) for a specific group.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response|\WP_Error The response or error object.
	 *
	 * @since 1.0.0
	 */
	public function get_item( $request ) {
		$setting = $this->get_setting( $request['group_id'], $request['id'] );

		if ( is_wp_error( $setting ) ) {
			return $setting;
		}

		$response = $this->prepare_item_for_response( $setting, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Update a single item (setting) for a specific group.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response|\WP_Error The response or error object.
	 *
	 * @since 1.0.0
	 */
	public function update_item( $request ) {
		$setting = $this->get_setting( $request['group_id'], $request['id'] );

		if ( is_wp_error( $setting ) ) {
			return $setting;
		}
		$value = is_null( $request['value'] ) ? '' : $request['value'];
		$value = wp_kses_post( trim( stripslashes( $value ) ) );

		update_option( $request['id'], $value );

		$response = $this->prepare_item_for_response( $setting, $request );
		return rest_ensure_response( $response );
	}


	/**
	 * Get a single setting for a specific group.
	 *
	 * @param string $group_id The group ID.
	 * @param string $setting_id The setting ID.
	 * @return array|\WP_Error The setting array or error object.
	 *
	 * @since 1.0.0
	 */
	public function get_setting( $group_id, $setting_id ) {

		if ( empty( $setting_id ) ) {
			return new \WP_Error( 'rest_setting_setting_invalid', __( 'Invalid setting.', 'wpaio' ), array( 'status' => 404 ) );
		}

		$settings = $this->get_group_settings( $group_id );

		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		$setting = null;
		foreach ( $settings as $s ) {
			if ( $s['id'] === $setting_id ) {
				$setting = $s;
				break;
			}
		}

		if ( is_null( $setting ) ) {
			return new \WP_Error( 'rest_setting_setting_invalid', __( 'Invalid setting.', 'wpaio' ), array( 'status' => 404 ) );
		}

		return $setting;
	}


	/**
	 * Get settings for a specific group.
	 *
	 * @param string $group_id The group ID.
	 * @return array|\WP_Error The settings array or error object.
	 *
	 * @since 1.0.0
	 */
	public function get_group_settings( $group_id ) {
		if ( empty( $group_id ) ) {
			return new \WP_Error( 'rest_setting_setting_group_invalid', __( 'Invalid setting group.', 'wpaio' ), array( 'status' => 404 ) );
		}
		$settings          = apply_filters( 'wpaio_settings-' . $group_id, array() );
		$filtered_settings = array();
		
		foreach ( $settings as $setting ) {
			$option_key = $setting['id'];
			if ( 0 === strpos( $option_key, 'wpaio_' ) && false !== strpos( $option_key, '_settings' ) ) {
				$current_settings = AdminSettings::get_option( $option_key, array() );	
				$merged_settings = array_merge( $setting['default'], $current_settings );
				$setting['value'] = $merged_settings;
			} else {
				$setting['value'] = AdminSettings::get_option( $option_key, $setting['default'] );
			}
			
			$filtered_settings[] = $setting;
		}

		return $filtered_settings;
	}

	public function get_page( $request ) {
		$search_text = $request->get_param( 'value' );
		$limit       = $request->get_param( 'limit' ) ? absint( $request->get_param( 'limit' ) ) : -1;
		$exclude_ids = $request->get_param( 'exclude' ) ? array_map( 'absint', (array) $request->get_param( 'exclude' ) ) : array();

		$args                 = array(
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'posts_per_page'         => $limit,
			'post_type'              => 'page',
			'post_status'            => array( 'publish', 'private', 'draft' ),
			's'                      => $search_text,
			'post__not_in'           => $exclude_ids,
		);
		$search_results_query = new \WP_Query( $args );

		$pages_results = array();
		foreach ( $search_results_query->get_posts() as $post ) {
			$pages_results[ $post->ID ] = sprintf(
			/* translators: 1: page name 2: page ID */
				__( '%1$s (ID: %2$s)', 'wpaio' ),
				$this->get_page_title( $post->ID ),
				$post->ID
			);
		}
		$response = $this->prepare_item_for_response( $pages_results, $request );
		return rest_ensure_response( $response );
	}


	/**
	 * Get page title safely.
	 *
	 * @param int $page_id The page type.
	 * @return string The page title or empty string.
	 */
	private function get_page_title( $page_id ) {
		global $wpdb;

		if ( ! $page_id ) {
			return '';
		}

		// Direct database query to get the post title
		$title = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_title FROM {$wpdb->posts} WHERE ID = %d AND post_type = 'page' AND post_status = 'publish'",
				$page_id
			)
		);

		return $title ? $title : '';
	}


	/**
	 * Prepare a single item for response.
	 *
	 * @param array            $item The item array.
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response The response object.
	 *
	 * @since 1.0.0
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data     = $this->add_additional_fields_to_object( $item, $request );
		$response = rest_ensure_response( $data );
		return $response;
	}


	/**
	 * Check permissions for getting items.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return bool True if the current user has permission, false otherwise.
	 *
	 * @since 1.0.0
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Check permissions for updating items.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return bool True if the current user has permission, false otherwise.
	 *
	 * @since 1.0.0
	 */
	public function update_items_permissions_check( $request ) {
		return current_user_can( 'manage_options' );
	}
}
