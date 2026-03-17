<?php


namespace Jet_FB_Paypal\ApiActions;

use Jet_FB_Paypal\ApiActions\Exceptions\GatewayNoticeException;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use JFB_Modules\Gateways\Paypal\Api_Actions\Base_Action;

class ListSubscriptionPlans extends Base_Action {

	protected $method = 'GET';

	protected $page = 1;

	public function action_slug() {
		return 'LIST_PLANS';
	}

	public function action_endpoint() {
		return 'v1/billing/plans';
	}

	public function action_headers() {
		return array(
			'Content-Type' => 'application/json',
		);
	}

	public function action_query_args(): array {
		return array(
			'page_size' => 20,
			'page'      => $this->page
		);
	}

	protected function get_main_url(): string {
		return str_replace( 'api-m.', '', $this->base_url() );
	}

	/**
	 * @throws Gateway_Exception|GatewayNoticeException
	 */
	public function get_plans(): array {
		$response = $this->send_request();

		if ( isset( $response['message'] ) ) {
			throw new Gateway_Exception( $response['message'], $response );
		}

		$plans = $response['plans'] ?? array();

		if ( empty( $plans ) ) {
			$exception = new GatewayNoticeException( 'No plans were found.', $response );

			throw $exception->add_action(
				array(
					'label' => __( 'Add Subscription Plan', 'jet-form-builder-paypal-subscriptions' ),
					'url'   => $this->get_main_url() . '/billing/plans',
				)
			);
		}

		if ( isset( $response['links'] ) ) {
			$is_next_page = array_filter( $response['links'], function ( $item ) {
				return isset( $item['rel'] ) && $item['rel'] === 'next';
			} );

			if ( $is_next_page ) {
				$this->page += 1;

				$next_page_response = $this->get_plans();
				$next_page_plans    = $next_page_response ?? array();

				if ( ! empty( $next_page_plans ) ) {
					$plans = array_merge( $plans, $next_page_plans );
				}
			}
		}

		return $plans;
	}


	/**
	 * @return array[]
	 * @throws GatewayNoticeException
	 * @throws Gateway_Exception
	 */
	public function get_plans_as_list(): array {
		$plans    = $this->get_plans();
		$response = array(
			array(
				'key'  => '',
				'name' => __( 'Choose your Subscription Plan', 'jet-form-builder' ),
			),
		);

		foreach ( $plans as $plan ) {
			$is_disabled = 'ACTIVE' !== $plan['status'];

			$response[] = array(
				'key'         => $plan['id'],
				'name'        => $plan['name'],
				'description' => $plan['description'],
				'disabled'    => $is_disabled,
				'className'   => $is_disabled ? 'is-disabled' : '',
			);
		}

		return $response;
	}
}
