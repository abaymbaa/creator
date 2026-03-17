<?php


namespace Jet_FB_Paypal\Utils;


use Jet_FB_Paypal\QueryViews\PaymentsWithSales;

class PaymentUtils {

	public static function is_refunded( $record ): bool {
		return PaymentsWithSales::REFUNDED_STATUS === ( $record['status'] ?? $record );
	}

	public static function is_renewal( $record ): bool {
		return PaymentsWithSales::RENEW_TYPE === ( $record['type'] ?? $record );
	}

	public static function has_subscription( array $record ): bool {
		return ! empty( $record['subscription']['id'] ?? $record['subscription_id'] ?? '' );
	}

	public static function can_be_refunded( array $record ): bool {
		return PaymentUtils::has_subscription( $record ) && ! PaymentUtils::is_refunded( $record );
	}

}