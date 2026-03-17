<?php


namespace Jet_FB_Paypal\DbModels;


use Jet_Form_Builder\Db_Queries\Base_Db_Model;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use Jet_Form_Builder\Exceptions\Silence_Exception;
use Jet_FB_Paypal\DbModels\Constraints\SubscriptionModelConstraint;

class SubscriptionNoteModel extends Base_Db_Model {

	/**
	 * @inheritDoc
	 */
	public static function table_name(): string {
		return 'subscriptions_notes';
	}

	/**
	 * @inheritDoc
	 */
	public static function schema(): array {
		return array(
			'id'              => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'subscription_id' => 'bigint(20) NOT NULL',
			'created_by'      => 'varchar(100)',
			'message'         => 'text',
			'created_at'      => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
			'updated_at'      => 'TIMESTAMP NOT NULL',
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function schema_keys(): array {
		return array(
			'id'              => 'primary key',
			'subscription_id' => 'index'
		);
	}

	public function foreign_relations(): array {
		return array(
			new SubscriptionModelConstraint()
		);
	}

	/**
	 * @param $columns
	 *
	 * @return int
	 * @throws Silence_Exception
	 */
	public static function add( $columns ): int {
		if ( ! is_array( $columns ) ) {
			$columns = array( 'message' => $columns );
		}

		$login = wp_get_current_user()->user_login ?? 'WEBHOOK';

		$columns = array_merge( array(
			'created_by' => $login,
			'message'    => ''
		), $columns );

		if ( ! $columns['message'] ) {
			throw new Silence_Exception( 'Your note is empty.' );
		}

		return ( new static )->insert_soft( $columns );
	}

	public static function add_soft( $columns ): int {
		try {
			return static::add( $columns );
		} catch ( Silence_Exception $exception ) {
			return 0;
		}
	}


}