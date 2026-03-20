<?php

namespace JFB_QPay\Rest;

class Rest_Controller {

	public function register_routes() {
		register_rest_route( 'jfb-qpay/v1', '/callback', array(
			'methods'             => 'POST',
			'callback'            => array( new Callback_Handler(), 'handle' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( 'jfb-qpay/v1', '/check-status', array(
			'methods'             => 'GET',
			'callback'            => array( new Status_Check(), 'handle' ),
			'permission_callback' => '__return_true',
		) );
	}
}
