<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0
 * @package    InvoiceBoo
 * @subpackage InvoiceBoo/includes
 * @author     InvoiceBoo <hello@invoiceboo.com>
 */
class InvoiceBoo_Activator {

	/**
	 * Create main table for storing Invoice data
	 *
	 * @since    1.0
	 */
	public static function activate() {

		//Deactivating InvoiceBoo Pro
		if ( class_exists( 'InvoiceBoo' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			deactivate_plugins( 'invoiceboo-invoices-for-woocommerce-pro/invoiceboo.php' );
		}

		global $wpdb;
		$admin = new InvoiceBoo_Admin( INVOICEBOO_SLUG, INVOICEBOO_VERSION );
		$table_name = $wpdb->prefix . INVOICEBOO_SLUG;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			order_id BIGINT UNSIGNED NOT NULL,
			hash varchar(256) NOT NULL,
			created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		$admin->update_data( 'database-version', INVOICEBOO_VERSION );

	}

}