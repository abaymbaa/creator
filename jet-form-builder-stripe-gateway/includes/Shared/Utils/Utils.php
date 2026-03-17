<?php


namespace Jet_FB_Paypal\Utils;


class Utils {

	public static function format_type( $resource_type ) {
		switch ( $resource_type ) {
			case 'sale':
				return 'renew';
			default:
				return $resource_type;
		}
	}

	/**
	 * @param $unit
	 *
	 * @return false|string
	 */
	public static function format_recurring_unit( $unit ) {
		$unit = strtolower( $unit );

		switch ( $unit ) {
			case 'd':
			case 'day':
				return __( 'Daily', 'jet-form-builder' );
			case 'm':
			case 'month':
				return __( 'Monthly', 'jet-form-builder' );
			case 'w':
			case 'week':
				return __( 'Weekly', 'jet-form-builder' );
			case 'y':
			case 'year':
				return __( 'Yearly', 'jet-form-builder' );
			default:
				return false;
		}
	}

}