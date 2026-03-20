<?php

namespace JFB_QPay\DB;

class Table_Manager {

	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'jfb_qpay_transactions';
	}

	public static function create_table() {
		global $wpdb;
		$table_name = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			invoice_id varchar(100) NOT NULL,
			invoice_code varchar(100) DEFAULT '' NOT NULL,
			status varchar(50) DEFAULT 'waiting' NOT NULL,
			amount decimal(10,2) NOT NULL,
			form_id bigint(20) NOT NULL,
			post_id bigint(20) DEFAULT 0 NOT NULL,
			qr_text text DEFAULT '' NOT NULL,
			urls text DEFAULT '' NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY invoice_id (invoice_id),
			KEY status (status)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public static function insert( $data ) {
		global $wpdb;
		return $wpdb->insert( self::get_table_name(), $data );
	}

	public static function update_status( $invoice_id, $status ) {
		global $wpdb;
		return $wpdb->update(
			self::get_table_name(),
			array( 'status' => $status ),
			array( 'invoice_id' => $invoice_id )
		);
	}

	public static function get_by_invoice( $invoice_id ) {
		global $wpdb;
		$table = self::get_table_name();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE invoice_id = %s", $invoice_id ), ARRAY_A );
	}
}
