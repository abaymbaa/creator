<?php


namespace Jet_FB_Paypal\TableViews\Actions;


use Jet_FB_Paypal\RestEndpoints\DeleteSubscriptions;
use Jet_FB_Paypal\Utils\SubscriptionUtils;
use Jet_Form_Builder\Admin\Table_Views\Actions\Api_Single_Action;

class DeleteSubscription extends Api_Single_Action {

	public function get_slug(): string {
		return 'delete';
	}

	public function get_type(): string {
		return 'danger';
	}

	public function get_label(): string {
		return __( 'Delete', 'jet-form-builder-paypal-subscriptions' );
	}

	public function get_method(): string {
		return DeleteSubscriptions::get_methods();
	}

	public function get_rest_url( array $record ): string {
		return DeleteSubscriptions::rest_url();
	}

	public function show_in_header(): bool {
		return false;
	}

	public function show_in_row( array $record ): bool {
		return SubscriptionUtils::is_broken( $record );
	}

}