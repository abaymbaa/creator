<?php


namespace Jet_FB_Paypal\Resources;


class Payer {

	private $email;

	public function __construct( array $payer ) {
		$this->email = $payer['email'] ?? '';
	}

	/**
	 * @return mixed|string
	 */
	public function get_email(): string {
		return $this->email;
	}

}