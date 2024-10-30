<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @since      1.0
 * @package    InvoiceBoo
 * @subpackage InvoiceBoo/public
 * @author     InvoiceBoo <hello@invoiceboo.com>
 */
class InvoiceBoo_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in InvoiceBoo_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The InvoiceBoo_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if( !class_exists( 'WooCommerce' ) ) return;

		if( $this->is_invoiceboo_endpoint() || is_wc_endpoint_url( 'orders' ) ){
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/invoiceboo-public.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in InvoiceBoo_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The InvoiceBoo_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if( !$this->is_invoiceboo_endpoint() ) return;

		wp_enqueue_script( $this->plugin_name . '-micromodal', plugin_dir_url( __FILE__ ) . 'js/micromodal.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/invoiceboo-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Add InvoiceBoo endpoint
	 *
	 * @since    1.0
	 */
	function invoiceboo_endpoint() {

		$admin = new InvoiceBoo_Admin( INVOICEBOO_SLUG, INVOICEBOO_VERSION );
		add_rewrite_endpoint( 'invoice-id', EP_ROOT | EP_PAGES );

		if( !$admin->get_data( 'rewrite-rules-flushed' ) ){
			flush_rewrite_rules(); //Necessary to prevent from manually saving Permalinks
			$admin->update_data( 'rewrite-rules-flushed', true );
		}

	}

	/**
	 * Redirect to InvoiceBoo page if this is a valid InvoiceBoo page request
	 *
	 * @since    1.0
	 */
	function invoiceboo_template_redirect() {

		if( !class_exists( 'WooCommerce' ) ) return;

		if( !$this->is_invoiceboo_endpoint() ) return;

		status_header( 200 ); //Setting page status to 200 instead of 404
		echo $this->display_invoiceboo_page();
		exit;

	}

	/**
	 * Retrieve InvoiceBoo endpoint URL
	 *
	 * @since    1.0
	 * @return   string
	 */
	function get_invoiceboo_endpoint_url() {

		return get_site_url() . '/'. INVOICEBOO_SLUG .'/';

	}

	/**
	 * Validate if this is InvoiceBoo endpoint
	 *
	 * @since    1.0
	 * @return   boolean
	 */
	function is_invoiceboo_endpoint() {

		global $wp;
		$result = false;

		if( $wp ){

			if( isset( $wp->request ) ){
				$result = isset( $wp->query_vars['invoice-id'] ) && ( $wp->request == INVOICEBOO_SLUG );
			}
		}

		return $result;

	}

	/**
	 * Change InvoiceBoo page title
	 *
	 * @since    1.0
	 * @return   string
	 * @param    string     $title    		  	  Page title
	 */
	function set_invoiceboo_page_title( $title ) {

		if( $this->is_invoiceboo_endpoint() ){
			$invoiceboo_name = INVOICEBOO_NAME;
			$blog_name = get_bloginfo( 'name' );
			$title = apply_filters( 'invoiceboo_page_title', $invoiceboo_name . ' | ' . $blog_name );
		}

		return $title;

	}

	/**
	 * Display invoice page contents
	 *
	 * @since    1.0
	 * @return   HTML
	 */
	function display_invoiceboo_page() {

		$admin = new InvoiceBoo_Admin( INVOICEBOO_SLUG, INVOICEBOO_VERSION );
		$order = '';
		$order_id = '';
		$order_result = $this->check_order();
		$content = $this->invoice_inaccessible();

		if( $order_result['status'] ){ //If Order found and Invoice can be displayed
			$order = $order_result['data'];
			$order_id = $order->get_id();
			$content = $this->get_invoice_content( $order );
			add_action( 'invoiceboo_page_header', array( $this, 'output_invoiceboo_form' ) );
		}

		$args = array(
			'order-id' 			=> $order_id,
			'invoice-content' 	=> $content
		);

		return $admin->get_template( 'invoiceboo-page.php', $args );
		
	}

	/**
	 * Build content for Invoice page
	 *
	 * @since    1.0
	 * @return   HTML
	 * @param    object     $order    		  	  WooCommerce Order object
	 */
	function get_invoice_content( $order ) {
		
		if( !$order ) return;

		$admin = new InvoiceBoo_Admin( INVOICEBOO_SLUG, INVOICEBOO_VERSION );
		$invoice = new InvoiceBoo_Invoice();
		$invoice_nr = $invoice->get_invoice_number( $order, $preview = false );
		$order_date = $admin->get_order_date_created( $order, $preview = false );
		$args = $admin->get_invoice_data( $order, $preview = false );

		ob_start(); 
		echo $admin->get_template( 'invoiceboo-'. $admin->get_active_template_name( $preview = false ) .'.php', $args ); 
		$output = ob_get_contents();
		ob_end_clean();

		return $output;

	}

	/**
	 * Create message for when Invoice is not accessible
	 *
	 * @since    1.0
	 * @return   HTML
	 */
	function invoice_inaccessible() {

		ob_start(); ?>
		<h1><?php esc_html_e( 'Invoice', 'invoiceboo-invoices-for-woocommerce' ); ?></h1>
		<p><?php echo esc_html__( 'It seems that the Order does not exist, or you do not have permission to access.', 'invoiceboo-invoices-for-woocommerce' );
			if( apply_filters( 'invoiceboo_authorization_required', true ) ){ //If user authorization enabled
				echo ' ' . sprintf(
					/* translators: %s - URL link */
					esc_attr__( 'Please open %sWooCommerce Orders%s page to download your Invoice.', 'invoiceboo-invoices-for-woocommerce' ), '<a href="' . esc_url( wc_get_account_endpoint_url( 'orders' ) )  . '">', '</a>'
				);
			}?></p>
		<?php
		$output = ob_get_contents();
		ob_end_clean();

		return $output;

	}

	/**
	 * Build Invoice form for adding Invoice data
	 *
	 * @since    1.0
	 */
	function output_invoiceboo_form() {

		ob_start(); ?>
		<form method="post">
			<button type="submit" id="invoiceboo-generate-pdf" class="invoiceboo-button button"><?php esc_html_e( 'Download', 'invoiceboo-invoices-for-woocommerce' ); ?></button>
			<div class="invoiceboo-modal" id="invoiceboo-modal" aria-hidden="true">
				<div class="invoiceboo-modal-overlay" tabindex="-1" data-micromodal-close>
					<div class="invoiceboo-modal-content-container" role="dialog" aria-modal="true">
						<button type="button" class="invoiceboo-close-modal" aria-label="<?php echo esc_html__( 'Close', 'invoiceboo-invoices-for-woocommerce'); ?>" data-micromodal-close></button>
						<div class="invoiceboo-modal-content" id="invoiceboo-modal-content">
							<div id="invoiceboo-form" class="invoiceboo-wrapper">
								<h2><?php esc_html_e( 'Billed to', 'invoiceboo-invoices-for-woocommerce' ); ?></h2>
								<label for="customer-company-details"><?php esc_html_e( 'Please enter your business details and save your changes.', 'invoiceboo-invoices-for-woocommerce' ); ?></label>
								<textarea id="customer-company-details" class="input-text" name="customer-company-details" rows="4" cols="40"></textarea>
								<input type="hidden" name="invoiceboo-generate-pdf" value="true" />
								<button type="button" class="invoiceboo-button button" data-micromodal-close><?php esc_html_e( 'Save', 'invoiceboo-invoices-for-woocommerce' ); ?></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php ob_end_flush();

	}

	/**
	 * Output Invoiceboo branding
	 *
	 * @since    1.0
	 */
	function add_invoiceboo_branding() {

		ob_start(); ?>
		<div id="invoiceboo-branding">Powered by <a href="<?php echo esc_url( INVOICEBOO_PAGE ); ?>" target="_blank"><?php echo esc_html( INVOICEBOO_NAME ); ?></a></div>
		<?php ob_end_flush();

	}

	/**
	 * Add Invoice download button to My Account > Orders page
	 *
	 * @since    1.0
	 * @return   array
	 * @param    array      $actions    		  Available order actions
	 * @param    object     $order    		  	  WooCommerce Order object
	 */
	function add_download_button_my_account( $actions = NULL, $order = NULL ) {

		if( !class_exists( 'WooCommerce' ) ) return;

		$admin = new InvoiceBoo_Admin( INVOICEBOO_SLUG, INVOICEBOO_VERSION );
		$invoice = new InvoiceBoo_Invoice();

		if( $admin->invoiceboo_enabled() ){

			if( $order ){
				$order_id = $order->get_id();

				if( $invoice->invoice_exists( $order_id ) ){

					if( $admin->can_download_invoice( $order_id ) ){
						$actions['invoice-id'] = array(
							'url' 		=> $this->get_invoice_url( $order_id ),
							'name' 		=> __( 'Invoice', 'invoiceboo-invoices-for-woocommerce' )
						);
					}
				}
			}
		}

		return $actions;

	}

	/**
	 * Add Invoice download button to WooCommerce Thank you page
	 *
	 * @since    1.0
	 * @param    integer     $order_id    		WooCommerce Order ID
	 */
	function add_download_button_thank_you( $order_id ) {

		$admin = new InvoiceBoo_Admin( INVOICEBOO_SLUG, INVOICEBOO_VERSION );
		$invoice = new InvoiceBoo_Invoice();

		if( !$invoice->invoice_exists( $order_id ) ) return; //Exit if Invoice does not exist

		if( $admin->invoiceboo_enabled() ){

			if( $admin->can_download_invoice( $order_id ) ){
				$download_url = $this->get_invoice_url( $order_id );
				$button_url = '<a href="' . $download_url . '" class="button invoiceboo-download-invoice" target="_blank">' . __( 'Download Invoice', 'invoiceboo-invoices-for-woocommerce' ) . '</a>';
				$invoice_link_thanks  = sprintf( '<p class="invoiceboo-download-block">%s</p>', $button_url );
				echo wp_kses_post( $invoice_link_thanks );
			}
		}

	}

	/**
	 * Add Invoice download information to customer order email
	 *
	 * @since    1.0
	 * @return   array
	 * @param    object     $order				WooCommerce Order object
	 * @param    boolean    $sent_to_admin		If this email is sent to admin or customer
	 * @param    boolean    $plain_text			If this is plain text email or HTML
	 */
	function add_download_link_to_email( $order, $sent_to_admin, $plain_text ){

		if( !class_exists( 'WooCommerce' ) ) return;

		if( $sent_to_admin ) return; //If email is sent to admin - do not include Invoice download link

		$admin = new InvoiceBoo_Admin( INVOICEBOO_SLUG, INVOICEBOO_VERSION );
		$invoice = new InvoiceBoo_Invoice();

		if( $admin->invoiceboo_enabled( $ignore_admin = true ) ){ //Not displaying download link in case Invoices have been disabled

			$order_id = $order->get_id();
			$invoice->new_invoice( $order_id );

			if( !$invoice->invoice_exists( $order_id ) ) return; //Exit if Invoice does not exist

			if( $admin->can_download_invoice( $order_id ) ){
				$invoice_order_url = $this->get_invoice_url( $order_id );
				
				if ( $plain_text === false ) {
					echo '<h2>' . __( 'Download Invoice', 'invoiceboo-invoices-for-woocommerce' ) . '</h2>';
					echo '<p>' . sprintf(
						/* translators: %s - URL link */
						esc_attr__( 'Please add your business details and %sDownload Invoice%s here.', 'invoiceboo-invoices-for-woocommerce' ), '<a href="' . esc_url( $invoice_order_url )  . '">', '</a>'
					) . '</p>';

				} else { //In case plain text email is set to be sent
					echo esc_html__( 'DOWNLOAD INVOICE', 'invoiceboo-invoices-for-woocommerce' ) . "\n\n";
					echo esc_html__( 'Please add your business details and Download Invoice here: ', 'invoiceboo-invoices-for-woocommerce' ) . esc_url( $invoice_order_url ) . "\n\n";
				}
			}
		}

	}

	/**
	 * Get a link of a given order Invoice
	 *
	 * @since    1.0
	 * @return   array
	 * @param    integer     $order_id    		  	  WooCommerce Order number
	 */
	function get_invoice_url( $order_id = NULL ) {

		$invoice = new InvoiceBoo_Invoice();

		return add_query_arg(
			array(
				'invoice-id' 	=> $order_id,
				'hash' 			=> $invoice->get_invoice_hash( $order_id )
			),
			$this->get_invoiceboo_endpoint_url()
		);

	}

	/**
	 * Check if Order is valid, exists and belongs to user who is trying to view it (in case when authorization enabled).
	 * If order is valid, Order object is also returned.
	 *
	 * @since    1.0
	 * @return   array
	 */
	function check_order(){

		$result['status'] = false;
		$result['data'] = '';
		$invoice = new InvoiceBoo_Invoice();

		if( isset( $_GET[ 'invoice-id' ] ) && isset( $_GET[ 'hash' ] ) ){
			$order_id = trim( sanitize_text_field( $_GET['invoice-id'] ), '.,;:!?' ); //Remove special characters from the beginning and end of the string
			$hash = $_GET['hash'];
			$order = wc_get_order( $order_id );

			if( $invoice->get_invoice_hash( $order_id ) == $hash ){ //If Invoice hashes match - continue

				if( !empty( $order ) ){ //Continue if order found

					if( apply_filters( 'invoiceboo_authorization_required', false ) ){ //If user authorization enabled
						
						$current_user = wp_get_current_user();
						$order_user_id = $order->get_user_id();

						if ( $order_user_id == $current_user->ID ){ //If both user ID's match - Invoice can be generated
							$result['status'] = true;
							$result['data'] = $order;
						}

					}else{
						$result['status'] = true;
						$result['data'] = $order;
					}
				}
			}
		}

		return $result;
	}

}