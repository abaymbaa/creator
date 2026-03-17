<?php


namespace Jet_FB_Paypal\Utils;


use Jet_Form_Builder\Actions\Methods\Form_Record\Controller;
use Jet_Form_Builder\Actions\Methods\Form_Record\Tools;
use Jet_Form_Builder\Dev_Mode\Manager;

class RecordTools {

	/**
	 * @param int $record_id
	 *
	 * @throws \Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception
	 */
	public static function update_record( int $record_id ) {
		if ( class_exists( '\Jet_Form_Builder\Actions\Methods\Form_Record\Tools' ) ) {
			Tools::update_record( $record_id );

			return;
		}
		if ( ! $record_id ) {
			return;
		}
		$controller = ( new Controller() )->set_record_id( $record_id );
		$controller->set_setting( 'save_errors', Manager::instance()->active() );

		$controller->save_actions();
		$controller->save_errors();
	}

}