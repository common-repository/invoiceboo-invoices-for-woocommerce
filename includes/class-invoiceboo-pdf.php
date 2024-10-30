<?php

/**
 * Invoice PDF specific functionality for handling PDF creation
 *
 *
 * @since      1.0
 * @package    InvoiceBoo
 * @subpackage InvoiceBoo/includes
 * @author     InvoiceBoo <hello@invoiceboo.com>
 */
class InvoiceBoo_PDF {

	/**
	 * Add Invoice download button to My Account > Orders page
	 *
	 * @since    1.0
	 */
	function validate_invoice_pdf() {

		$public = new InvoiceBoo_Public( INVOICEBOO_SLUG, INVOICEBOO_VERSION );
		$preview = false;
		$order = '';

		if( !class_exists( 'WooCommerce' ) ) return;

		if( empty( $_POST['invoiceboo-generate-pdf'] ) && empty( $_POST['invoiceboo-generate-pdf-preview'] ) ) return;

		if( isset( $_POST['invoiceboo-generate-pdf-preview'] ) ){
			$preview = true;
		}

		if( !$preview ){
			$order = $public->check_order();

			if( !$order['status'] ) return; //If order is not valid - exit

			$order = $order['data'];

		}

		echo $this->create_pdf( $order, $preview);

	}

	/**
	 * Add Invoice download button to My Account > Orders page
	 *
	 * @since    1.0
	 * @param    object     $order    		  	  WooCommerce Order object
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function create_pdf( $order, $preview = false ) {

		$admin = new InvoiceBoo_Admin( INVOICEBOO_SLUG, INVOICEBOO_VERSION );

		if( !$admin->invoiceboo_enabled() ) return; //Exit if Invoices have been disabled

		$args = $admin->get_invoice_data( $order, $preview, $inside_pdf = true );
		$pdf = new InvoiceBoo_TCPDF_Container();
		$pdf->setCreator( INVOICEBOO_NAME );
		$pdf->setAuthor( INVOICEBOO_NAME );
		$pdf->setTitle( get_bloginfo( 'title' ) );
		$pdf->setSubject( __( 'Invoice' ) );
		$pdf->setMargins( $margin_left = 18, $margin_top = 15, $margin_right = 18 );
		$pdf->setAutoPageBreak( TRUE, $margin_bottom = 15 );
		$pdf->setFont( $font_family = $args[ 'active-font' ], $font_style = '', $font_size = 10 );
		$pdf->SetPrintHeader( false );
		$pdf->SetPrintFooter( true );
		$pdf->setCellPadding( 0 );
		$pdf->AddPage();

		ob_start();

		echo $admin->get_template( 'invoiceboo-'. $admin->get_active_template_name( $preview ) .'.php', $args );
		$content = ob_get_contents();
		ob_end_clean();
		$pdf->writeHTML( $content );

		$filename = $this->get_pdf_filename( $order, $preview );
		ob_start();
		$pdf->Output( $filename, apply_filters( 'invoiceboo_pdf_output_destination', 'D' ) );
		header( 'Content-type: application/force-download' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		ob_end_flush();
		exit;

	}

	/**
	 * Get Invoice PDF file name
	 *
	 * @since    1.0
	 * @return   string
	 * @param    object     $order    		  	  WooCommerce Order object
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function get_pdf_filename( $order, $preview = false ) {

		$invoice = new InvoiceBoo_Invoice();
		$prefix = apply_filters( 'invoiceboo_pdf_filename_prefix', esc_html( 'invoice-' ) );
		$sufix = apply_filters( 'invoiceboo_pdf_filename_sufix', '-' . sanitize_title( get_bloginfo( 'title' ) ) );
		$invoice_number = $invoice->get_invoice_number( $order, $preview );
		return sanitize_file_name( strtolower( $prefix . $invoice_number . $sufix . '.pdf' ) );

	}

}

class InvoiceBoo_TCPDF_Container extends InvoiceBoo_TCPDF {

	/**
	 * Extending TCPFD Footer function
	 *
	 * @since    1.0
	 * @return   string
	 */
	public function Footer() {

		$this->SetY( -15 );
		$this->SetFont( 'raleway', 'R', 8 );
		$this->Cell( 0, 10, 'Powered by ' . INVOICEBOO_NAME, 0, false, 'C', 0, '', 0, false, 'T', 'M' );

	}

}