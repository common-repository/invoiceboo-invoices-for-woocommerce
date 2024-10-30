<?php

/**
 * Invoice specific functionality (creation, update)
 *
 *
 * @since      1.0
 * @package    InvoiceBoo
 * @subpackage InvoiceBoo/includes
 * @author     InvoiceBoo <hello@invoiceboo.com>
 */
class InvoiceBoo_Invoice {

	/**
	 * Create new Invoice
	 *
	 * @since    1.0
	 * @param    integer     $order_id    		WooCommerce Order ID
	 */
	function new_invoice( $order_id ) {

		global $wpdb;
		$admin = new InvoiceBoo_Admin( INVOICEBOO_SLUG, INVOICEBOO_VERSION );
		$table_name = $wpdb->prefix . INVOICEBOO_SLUG;

		if( !class_exists( 'WooCommerce' ) ) return;
		
		if( !$admin->invoiceboo_enabled() ) return; //Exit if InvoiceBoo disabled

		if( !$admin->can_download_invoice( $order_id ) ) return; //Exit if order status is incorrect

		if( $this->invoice_exists( $order_id ) ) return; //Exit if invoice for this order already has been created to prevent multiple invoices for the same order

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO $table_name
				( order_id, hash, created )
				VALUES ( %d, %s, %s )",
				array(
					'order_id'			=> (int) $order_id,
					'hash'				=> $this->create_invoice_hash(),
					'created'			=> current_time( 'mysql', false )
				)
			)
		);

	}

	/**
	 * Check if Invoice has already been created
	 * Return Invoice ID if it is found or 0 if not found
	 *
	 * @since    1.0
	 * @return   boolean / integer
	 * @param    integer     $order_id    		WooCommerce Order ID
	 */
	function invoice_exists( $order_id ) {

		global $wpdb;
		$table_name = $wpdb->prefix . INVOICEBOO_SLUG;
		$invoice_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id
				FROM $table_name
				WHERE order_id = %d",
				$order_id
			)
		);

		return $invoice_id;

	}

	/**
	 * Get Invoice number
	 *
	 * @since    1.0
	 * @return   string
	 * @param    object     $order    		  	  WooCommerce Order object
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function get_invoice_number( $order, $preview = false ) {

		if( !class_exists( 'WooCommerce' ) ) return;

		global $wpdb;
		$admin = new InvoiceBoo_Admin( INVOICEBOO_SLUG, INVOICEBOO_VERSION );
		$table_name = $wpdb->prefix . INVOICEBOO_SLUG;
		$invoice_number = $admin->get_setting_defaults( 'order-id' );

		if( !$preview ){
			$order_id = $order->get_ID();
			$invoice_number = $order_id;
		}

		return $invoice_number;

	}

	/**
	 * Create Invoice hash
	 *
	 * @since    1.0
	 * @return   string
	 */
	function create_invoice_hash() {

		$hash = wp_generate_password( $length = 128, $special_chars = false );
		return $hash;

	}

	/**
	 * Get Invoice hash from order ID
	 *
	 * @since    1.0
	 * @return   string
	 * @param    integer    $order_id    		  WooCommerce Order ID
	 */
	function get_invoice_hash( $order_id ) {

		global $wpdb;
		$table_name = $wpdb->prefix . INVOICEBOO_SLUG;

		$hash = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT hash
				FROM $table_name
				WHERE order_id = %d",
				$order_id
			)
		);

		return $hash;

	}

}
