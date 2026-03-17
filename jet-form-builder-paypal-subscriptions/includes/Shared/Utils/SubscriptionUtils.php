<?php
namespace Jet_FB_Paypal\Utils;

use Jet_FB_Paypal\Logic\SubscribeNow;
use Jet_FB_Paypal\Pages\SingleSubscriptionPage;
use Jet_FB_Paypal\QueryViews\RecordBySubscription;
use Jet_FB_Paypal\Resources\Subscription;
use Jet_Form_Builder\Actions\Methods\Form_Record\Query_Views\Record_Fields_View;
use Jet_Form_Builder\Actions\Methods\Form_Record\Tools;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Exceptions\Action_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

class SubscriptionUtils {

	public static function default_statuses(): array {
		return array(
			SubscribeNow::APPROVAL_PENDING,
			SubscribeNow::APPROVED,
			SubscribeNow::ACTIVE,
			SubscribeNow::SUSPENDED,
			SubscribeNow::CANCELLED,
			SubscribeNow::EXPIRED,
		);
	}

	public static function broken_statuses(): array {
		return array(
			SubscribeNow::APPROVAL_PENDING,
			SubscribeNow::APPROVED,
			SubscribeNow::EXPIRED,
			SubscribeNow::CANCELLED,
		);
	}

	public static function is_custom_status( $record ): bool {
		$status = self::prepare_status( $record );

		return ( ! in_array( $status, self::default_statuses(), true ) );
	}

	private static function prepare_status( $record ): string {
		if ( is_array( $record ) ) {
			return $record['status'] ?? '';
		}

		return $record;
	}

	public static function is_broken( $record ): bool {
		$status = self::prepare_status( $record );

		return in_array( $status, self::broken_statuses(), true );
	}

	public static function is_active( $record ): bool {
		$status = self::prepare_status( $record );

		return SubscribeNow::ACTIVE === $status;
	}

	public static function is_expired( $record ): bool {
		$status = self::prepare_status( $record );

		return SubscribeNow::EXPIRED === $status;
	}

	public static function is_cancelled( $record ): bool {
		$status = self::prepare_status( $record );

		return SubscribeNow::CANCELLED === $status;
	}

	public static function is_suspended( $record ): bool {
		$status = self::prepare_status( $record );

		return SubscribeNow::SUSPENDED === $status;
	}

	public static function can_be_suspended( $record ): bool {
		$status = self::prepare_status( $record );

		return (
			SubscribeNow::ACTIVE === $status
			|| self::is_custom_status( $status )
		);
	}

	public static function can_be_cancelled( $record ): bool {
		$status = self::prepare_status( $record );

		return (
			SubscribeNow::SUSPENDED === $status
			|| SubscribeNow::ACTIVE === $status
			|| self::is_custom_status( $status )
		);
	}

	public static function status_note( string $old_status, string $new_status ): string {
		if ( $old_status === $new_status ) {
			return '';
		}

		return sprintf(
			__( 'Subscription status changed from %1$s to %2$s', 'jet-form-builder' ),
			$old_status,
			$new_status
		);
	}

	public static function is_single(): bool {
		return is_a( jet_fb_current_page(), SingleSubscriptionPage::class );
	}

	/**
	 * Returns record_id
	 *
	 * @param Subscription $resource
	 * @param string $event
	 *
	 * @return int
	 * @throws Action_Exception
	 * @throws Query_Builder_Exception
	 * @throws Sql_Exception
	 */
	public static function trigger_event( Subscription $resource, string $event ): int {
		$record    = self::apply_record_by_subscription( $resource->get_id() );
		$record_id = $record['id'] ?? 0;

		try {
			jet_fb_events()->execute( $event );
		} catch ( Action_Exception $exception ) {
			throw $exception;
		} finally {
			RecordTools::update_record( $record_id );
		}

		return $record_id;
	}

	/**
	 * @param $subscription_id
	 *
	 * @return array
	 * @throws Query_Builder_Exception
	 */
	public static function apply_record_by_subscription( $subscription_id ): array {
		$record = RecordBySubscription::findOne(
			array(
				'subscription_id' => $subscription_id,
			)
		)->query()->query_one();


		if ( class_exists( '\Jet_Form_Builder\Actions\Methods\Form_Record\Tools' ) ) {
			Tools::apply_context( $record );

			return $record;
		}

		$request = Record_Fields_View::get_request_list( $record['id'] ?? 0 );

		jet_fb_action_handler()->add_request( $request );
		jet_fb_action_handler()->set_form_id( $record['form_id'] ?? 0 );

		return $record;
	}


}