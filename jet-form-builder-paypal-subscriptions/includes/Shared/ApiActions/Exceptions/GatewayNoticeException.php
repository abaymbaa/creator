<?php


namespace Jet_FB_Paypal\ApiActions\Exceptions;

use Jet_Form_Builder\Exceptions\Gateway_Exception;

class GatewayNoticeException extends Gateway_Exception {

	protected $actions = array();
	protected $icon    = 'warning';

	public function add_action( array $options ): GatewayNoticeException {
		$options = array_merge(
			array(
				'label' => '',
				'url'   => '',
			),
			$options
		);

		$this->actions[] = $options;

		return $this;
	}

	/**
	 * @return string
	 */
	public function get_icon(): string {
		return $this->icon;
	}

	/**
	 * @param string $icon
	 */
	public function set_icon( string $icon ): GatewayNoticeException {
		$this->icon = $icon;

		return $this;
	}


	/**
	 * @return array
	 */
	public function get_actions(): array {
		return $this->actions;
	}

}
