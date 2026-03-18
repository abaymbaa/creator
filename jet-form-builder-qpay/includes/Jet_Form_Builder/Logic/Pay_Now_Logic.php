<?php

namespace Jet_FB_Qpay\Jet_Form_Builder\Logic;

use Jet_FB_Qpay\Qpay_Api;
use JFB_Modules\Gateways\Db_Models\Payment_Model;
use JFB_Modules\Gateways\Db_Models\Payment_Meta_Model;
use Jet_Form_Builder\Gateways\Scenarios_Abstract\Scenario_Logic_Base;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;

use JFB_Modules\Gateways\Query_Views\Payment_With_Record_View;

class Pay_Now_Logic extends Scenario_Logic_Base {

	public static function scenario_id() {
		return 'pay_now';
	}

	protected function query_token() {
		return sanitize_text_field( $_GET['payment_token'] ?? '' );
	}

	protected function query_scenario_row() {
		return Payment_With_Record_View::findOne( array( 'transaction_id' => $this->get_queried_token() ) )->query()->query_one();
	}

	public function get_failed_statuses() {
		return array( 'FAILED', 'CANCELLED' );
	}

	public function after_actions() {
		$controller = jet_fb_gateway_current();

		try {
			// This will set price_field, price, order_id, etc. from form settings
			$controller->set_gateway_data();
		} catch ( \Exception $e ) {
			// If price field is missing or other error, we might want to handle it
		}

		$price = $controller->get_price_var();
		$record_id = $controller->get_order_id(); // This is the ID of the related action (e.g. Save Record)

		if ( ! $price ) {
			// Fallback or error if price is not resolved
			$price = 100; 
		}

		$api = new Qpay_Api(
			$controller->current_gateway( 'username' ),
			$controller->current_gateway( 'password' ),
			$controller->current_gateway( 'invoice_code' )
		);

		// Use the $record_id as the sender_invoice_no for QPay invoice description,
		// but use the ACTUAL payment ID for the transaction management.
		$qpay_payment_id = $this->save_preliminary_payment( $price, $record_id );

		$receipt_url = get_rest_url( null, 'jet-fb-qpay/v1/receipt/' . $qpay_payment_id );

		$result = $api->create_invoice( $qpay_payment_id, $price, $receipt_url );

		if ( is_wp_error( $result ) ) {
			wp_die( $result->get_error_message() );
		}

		// Update payment with invoice ID and QR info
		$this->update_payment_with_invoice( $qpay_payment_id, $result );

		jet_fb_action_handler()->add_response(
			array( 'redirect' => $receipt_url )
		);
	}

	protected function save_preliminary_payment( $price, $related_id = 0 ) {
		$payment_row = array(
			'transaction_id' => 'temp_' . ( $related_id ?: time() ),
			'form_id'        => jet_fb_handler()->form_id,
			'user_id'        => get_current_user_id(),
			'gateway_id'     => 'qpay',
			'scenario'       => 'pay_now',
			'amount_value'   => $price,
			'amount_code'    => 'MNT',
			'status'         => 'CREATED',
		);

		try {
			return ( new Payment_Model() )->insert( $payment_row );
		} catch ( Sql_Exception $exception ) {
			wp_die( $exception->getMessage() );
		}
	}

	protected function update_payment_with_invoice( $id, $invoice_data ) {
		try {
			( new Payment_Model() )->update(
				array(
					'transaction_id' => $invoice_data['invoice_id'],
				),
				array( 'id' => $id )
			);
			
			// Store QR data using Payment_Meta_Model
			$meta_model = new Payment_Meta_Model();
			
			$meta_model->insert( array(
				'payment_id' => $id,
				'meta_key'   => '_qpay_qr_text',
				'meta_value' => $invoice_data['qr_text'],
			) );

			$meta_model->insert( array(
				'payment_id' => $id,
				'meta_key'   => '_qpay_qr_image',
				'meta_value' => $invoice_data['qr_image'] ?? '',
			) );

			$meta_model->insert( array(
				'payment_id' => $id,
				'meta_key'   => '_qpay_urls',
				'meta_value' => wp_json_encode( $invoice_data['urls'] ?? array() ),
			) );
			
		} catch ( Sql_Exception $exception ) {
			// Fail silently or log
		}
	}

	public function process_after() {
		// This is called when returning from the gateway. 
		// Since we use a custom receipt page and polling, we might not need this 
		// unless there's a direct return from bank apps.
	}
}
