<?php


namespace Jet_FB_Paypal\TableViews\Actions;


use Jet_FB_Paypal\Utils\PaymentUtils;

trait ShowIfSubscriptionIsset {

	public function show_in_row( array $record ): bool {
		return PaymentUtils::has_subscription( $record );
	}

}