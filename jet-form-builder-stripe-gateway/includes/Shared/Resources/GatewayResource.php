<?php


namespace Jet_FB_Paypal\Resources;


interface GatewayResource {

	public function get_gateway_id(): string;

	public function get_id(): string;

}