<?php


namespace Jet_FB_Paypal\TableViews\Actions;

trait BaseSubscriptionArgs {

	public function get_args( array $record ): array {
		return array(
			'gateway' => $record['gateway_id'],
			'id'      => $record['id'],
		);
	}

}
