<?php

namespace Jet_FB_Stripe_Gateway\Compatibility;

trait Compatibility_Trait {

	private static $check = null;

	protected static function condition() {
		return false;
	}

	/**
	 * @return boolean
	 */
	public static function check() {
		if ( is_null( self::$check ) ) {
			self::$check = self::condition();
		}

		return self::$check;
	}

}