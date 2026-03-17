<?php


namespace Jet_FB_Paypal\TableViews\Columns;

use Jet_FB_Paypal\Utils\SubscriptionUtils;
use Jet_Form_Builder\Admin\Table_Views\Column_Advanced_Base;
use Jet_FB_Paypal\Logic\SubscribeNow;

class SubscriptionStatusColumn extends Column_Advanced_Base {

	protected $type = self::STATUS;

	public function get_label(): string {
		return __( 'Status', 'jet-form-builder-paypal-subscriptions' );
	}

	public function get_replace_map(): array {
		return array(
			SubscribeNow::ACTIVE           => array(
				'type' => self::STATUS_SUCCESS,
				'text' => __( 'Active', 'jet-form-builder' ),
			),
			SubscribeNow::CANCELLED        => array(
				'type' => self::STATUS_FAILED,
				'text' => __( 'Cancelled', 'jet-form-builder' ),
			),
			SubscribeNow::EXPIRED          => array(
				'type' => self::STATUS_FAILED,
				'text' => __( 'Expired', 'jet-form-builder' ),
			),
			SubscribeNow::APPROVED         => array(
				'type' => self::STATUS_INFO,
				'text' => __( 'Approved', 'jet-form-builder' ),
			),
			SubscribeNow::APPROVAL_PENDING => array(
				'type' => self::STATUS_INFO,
				'text' => __( 'Pending approve', 'jet-form-builder' ),
			),
			SubscribeNow::SUSPENDED        => array(
				'type' => self::STATUS_WARNING,
				'text' => __( 'Suspended', 'jet-form-builder' ),
			),
			SubscribeNow::REFUNDED         => array(
				'type' => self::STATUS_WARNING,
				'text' => __( 'Refunded', 'jet-form-builder' ),
			),
		);
	}

	public function get_value( array $record = array() ) {
		$status = $record['status'] ?? '';
		$map    = $this->get_replace_map();
		$item   = $map[ $status ] ?? array();

		$item['value'] = $status;

		return $item;
	}
}
