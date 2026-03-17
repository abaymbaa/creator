<?php


namespace Jet_FB_Paypal\Resources;


class Shipping {

	private $full_name;
	private $address_line_1;
	private $address_line_2;
	private $admin_area_2;
	private $admin_area_1;
	private $postal_code;
	private $country_code;

	public function __construct( array $shipping ) {
		$this->full_name      = $shipping['name']['full_name'] ?? '';
		$this->address_line_1 = $shipping['address']['address_line_1'] ?? '';
		$this->address_line_2 = $shipping['address']['address_line_2'] ?? '';
		$this->admin_area_2   = $shipping['address']['admin_area_2'] ?? '';
		$this->admin_area_1   = $shipping['address']['admin_area_1'] ?? '';
		$this->postal_code    = $shipping['address']['postal_code'] ?? '';
		$this->country_code   = $shipping['address']['country_code'] ?? '';
	}

	/**
	 * @return mixed|string
	 */
	public function get_address_line_1(): string {
		return $this->address_line_1;
	}

	/**
	 * @return mixed|string
	 */
	public function get_address_line_2(): string {
		return $this->address_line_2;
	}

	/**
	 * @return mixed|string
	 */
	public function get_admin_area_1(): string {
		return $this->admin_area_1;
	}

	/**
	 * @return mixed|string
	 */
	public function get_admin_area_2(): string {
		return $this->admin_area_2;
	}

	/**
	 * @return mixed|string
	 */
	public function get_country_code(): string {
		return $this->country_code;
	}

	/**
	 * @return mixed|string
	 */
	public function get_full_name(): string {
		return $this->full_name;
	}

	/**
	 * @return mixed|string
	 */
	public function get_postal_code(): string {
		return $this->postal_code;
	}

}