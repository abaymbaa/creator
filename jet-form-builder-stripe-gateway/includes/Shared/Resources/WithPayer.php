<?php


namespace Jet_FB_Paypal\Resources;


interface WithPayer {

	/**
	 * @return string
	 */
	public function get_email(): string;

	/**
	 * @return string
	 */
	public function get_first_name(): string;

	/**
	 * @return string
	 */
	public function get_last_name(): string;

	/**
	 * @return string
	 */
	public function get_payer_id(): string;

}