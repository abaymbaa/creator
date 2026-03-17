<?php


namespace Jet_FB_Paypal\EventsHandlers\Base;

use Jet_FB_Paypal\DbModels\SubscriptionNoteModel;
use Jet_FB_Paypal\EventsHandlers\FormEvent;
use Jet_FB_Paypal\QueryViews\RecordBySubscription;
use Jet_FB_Paypal\QueryViews\SubscriptionsView;
use Jet_FB_Paypal\Resources\Subscription;
use Jet_FB_Paypal\Utils\RecordTools;
use Jet_FB_Paypal\Utils\SubscriptionUtils;
use Jet_Form_Builder\Actions\Methods\Form_Record\Admin\Meta_Boxes\Form_Record_Errors_Box;
use Jet_Form_Builder\Actions\Methods\Form_Record\Controller;
use Jet_Form_Builder\Actions\Methods\Form_Record\Models\Record_Error_Model;
use Jet_Form_Builder\Actions\Methods\Form_Record\Query_Views\Record_Fields_View;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Dev_Mode\Logger;
use Jet_Form_Builder\Dev_Mode\Manager;
use Jet_Form_Builder\Exceptions\Action_Exception;
use Jet_Form_Builder\Exceptions\Gateway_Exception;
use Jet_Form_Builder\Exceptions\Handler_Exception;
use Jet_Form_Builder\Exceptions\Query_Builder_Exception;

abstract class BillingSubscription extends EventHandlerBase implements FormEvent {

	public function get_event_class(): string {
		return '';
	}

	/**
	 * @param $webhook_event
	 *
	 * @return array
	 * @throws Gateway_Exception
	 */
	public function on_catch_event( $webhook_event ) {
		$subscription_id = $webhook_event['resource']['id'] ?? false;

		try {
			$subscription = SubscriptionsView::findOne(
				array(
					'billing_id' => $subscription_id,
				)
			)->query()->query_one();

		} catch ( Query_Builder_Exception $exception ) {
			throw new Gateway_Exception( "Undefined subscription: {$subscription_id}" );
		}

		$resource   = new Subscription( $subscription );
		$is_updated = $resource->update_status_soft( $webhook_event['resource']['status'] );

		$this->trigger_event( $resource );

		return array( $subscription, $resource );
	}

	/**
	 * Returns record_id
	 *
	 * @param Subscription $resource
	 */
	protected function trigger_event( Subscription $resource ) {
		$event = $this->get_event_class();

		if ( ! $event ) {
			return;
		}

		try {
			SubscriptionUtils::trigger_event( $resource, $event );

		} catch ( Query_Builder_Exception $exception ) {
			$this->manager()->response()->set_headers_custom(
				array(
					'query-exception' => $exception->getMessage(),
				)
			);

			return;
		} catch ( Action_Exception $exception ) {
			$this->manager()->response()->set_headers_custom(
				array(
					'action-exception' => $exception->getMessage(),
				)
			);
		} catch ( Sql_Exception $exception ) {
			$this->manager()->response()->set_headers_custom(
				array(
					'sql-exception' => $exception->getMessage(),
				)
			);
		} finally {
			$this->manager()->response()->set_headers_custom(
				array(
					'is-executed' => true,
				)
			);
		}
	}


}
