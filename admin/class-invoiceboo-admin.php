<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      1.0
 * @package    InvoiceBoo
 * @subpackage InvoiceBoo/admin
 * @author     InvoiceBoo <hello@invoiceboo.com>
 */
class InvoiceBoo_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		if( !$this->is_invoiceboo_page() ) return;

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/invoiceboo-admin.css', array( 'wp-color-picker' ), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		if( !$this->is_invoiceboo_page() ) return;

		$data = array(
			'ajaxurl'				=> admin_url( 'admin-ajax.php' ),
			'logo_upload_button'	=> array(
				'label'			=> $this->get_setting_defaults( 'logo-upload-button-label' ),
				'insert_label'	=> $this->get_setting_defaults( 'logo-upload-button-insert-label' )
			)
		);

		wp_enqueue_script( $this->plugin_name . '-selectize', plugin_dir_url( __FILE__ ) . 'js/selectize.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/invoiceboo-admin.js', array( 'wp-color-picker', 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'invoiceboo_admin_data', $data ); //Sending data over to JS file

	}

	/**
	 * Check if database update is required
	 *
	 * @since    1.0
	 * @return   boolean
	 */
	function update_database() {

		if ( $this->get_data( 'database-version' ) != INVOICEBOO_VERSION ) {
			activate_invoiceboo();
		}
	}

	/**
	 * Check if current page is InvoiceBoo plugin page inside WordPress admin section
	 *
	 * @since    1.0
	 * @return   boolean
	 */
	function is_invoiceboo_page(){

		global $invoiceboo_admin_page;
		$result = false;
		$screen = get_current_screen();

		if( is_object( $screen ) && $screen->id == $invoiceboo_admin_page ){ //If we are on InvoiceBoo page
			$result = true;
		}

		return $result;

	}

	/**
	 * Check if InvoiceBoo has been enabled
	 *
	 * @since    1.0
	 * @return   boolean
	 * @param    boolean     $ignore_admin 		 	 Whether admin rights should be ignored
	 */
	function invoiceboo_enabled( $ignore_admin = false ){

		$settings = get_option( 'invoiceboo_settings' );
		$display = isset( $settings[ 'enabled' ] ) ? $settings[ 'enabled' ] : false;
		$current_user_is_admin = current_user_can( 'manage_options' );

		if( !$ignore_admin ){ //Whether admin rights should be ignored

			if( $current_user_is_admin ){ //If current user has admin rights, display Invoice form
				$display = true;
			}

		}

		return $display;

	}

	/**
	 * Check if Invoice can be downloaded according to the status of the Order
	 * By default Invoices can be downloaded as soon as the order has status "Processing" or "Completed"
	 *
	 * @since    1.0
	 * @return   boolean
	 * @param    integer     $order_id    		WooCommerce Order ID
	 */
	function can_download_invoice( $order_id ){

		if( !class_exists( 'WooCommerce' ) ) return;

		$allowed = false;
		$approved_statuses = $this->approved_order_statuses();
		$order = wc_get_order( $order_id );

		if ( $order ) {

			if ( in_array( $order->get_status(), $approved_statuses ) ) { //If order has approved status
				$allowed = true;
			}
		}

		return $allowed;

	}

	/**
	 * Approved order statuses
	 *
	 * @since    1.0
	 * @return   array
	 */
	function approved_order_statuses(){

		$statuses = $this->get_settings( 'approved-order-statuses' );
		return apply_filters( 'invoiceboo_approved_order_statuses', $statuses );

	}

	/**
	* Display available and selected WooCommerce order statuses
	*
	* @since    1.0
	* @return   HTML
	*/
	public function display_order_statuses(){
		
		if( !class_exists( 'WooCommerce' ) ) return;

		if( !function_exists( 'wc_get_order_statuses' ) ) return; //In case this WooCommerce function is not defined

		$woocommerce_statuses = wc_get_order_statuses();
		$status_slugs = str_replace( 'wc-', '', array_keys( $woocommerce_statuses ) ); //Remove "wc-" from beginning of the status
		$statuses = array_combine( $status_slugs, $woocommerce_statuses );
		unset( $statuses[ 'refunded' ], $statuses[ 'checkout-draft' ] ); //Remove unnecessary status from the list of available statuses
		$option = 'approved-order-statuses';
		$selected_statuses = $this->get_settings( $option );

		echo '<select id="invoiceboo-order-statuses" class="invoiceboo-select invoiceboo-multiple invoiceboo-select-multiple-items" multiple name="invoiceboo_settings['. $option .'][]" placeholder="' . esc_attr__( 'Select order status', 'invoiceboo-invoices-for-woocommerce' ) . '" autocomplete="off" style="display: none;">';
		foreach( $statuses as $key => $status ){
			$selected = '';
			
			if( in_array( $key, $selected_statuses ) ){
				$selected = 'selected';
			}

			echo "<option value='". esc_attr( $key ) ."' $selected>". esc_html( $status ) ."</option>";
		}
		echo '</select>';
	}

	/**
	 * Retrieve default InvoiceBoo settings
	 *
	 * @since    1.0
	 * @return   mixed
	 * @param    string     $field 		 	 Specific field that is reuqested
	 */
	function get_setting_defaults( $field = false ) {

		$data = array(
			'order-id' 							=> '12345',
			'date' 								=> new DateTime( 'now' ),
			'company-details' 					=> '',
			'customer-company-details' 			=> '',
			'active-template' 					=> 'sunrise',
			'main-color' 						=> '#20bb91',
			'secondary-color' 					=> '#000000',
			'invoice-logo-url' 					=> '',
			'logo-upload-button-label' 			=> __( 'Add Company Logo', 'invoiceboo-invoices-for-woocommerce' ),
			'logo-upload-button-insert-label' 	=> __( 'Use image', 'invoiceboo-invoices-for-woocommerce' ),
			'active-font' 						=> 'dejavusans',
			'approved-order-statuses' 			=> array( 'processing', 'completed' )
		);

		if( $field ){ //If a specific field value is requested
			if( isset( $data[$field] ) ){ //Checking if value exists
				$data = $data[$field];
			}
		}

		return $data;

	}

	/**
	 * Retrieve InvoiceBoo settings
	 *
	 * @since    1.0
	 * @return   mixed
	 * @param    string     $field 		 	 Specific field that is reuqested
	 */
	function get_settings( $field = false ) {

		$settings = get_option( 'invoiceboo_settings' );
		$data = array(
			'invoiceboo-enabled' 		=> isset( $settings[ 'enabled' ] ) ? $settings[ 'enabled' ] : false,
			'approved-order-statuses' 	=> isset( $settings[ 'approved-order-statuses' ] ) ? $settings[ 'approved-order-statuses' ] : $this->get_setting_defaults( 'approved-order-statuses' ),
			'company-details' 			=> isset( $settings[ 'company-details' ] ) ? $settings[ 'company-details' ] : $this->get_setting_defaults( 'company-details' ),
			'active-template' 			=> isset( $settings[ 'active-template' ] ) ? $settings[ 'active-template' ] : $this->get_setting_defaults( 'active-template' ),
			'main-color' 				=> isset( $settings[ 'main-color' ] ) ? $settings[ 'main-color' ] : $this->get_setting_defaults( 'main-color' ),
			'secondary-color' 			=> isset( $settings[ 'secondary-color' ] ) ? $settings[ 'secondary-color' ] : $this->get_setting_defaults( 'secondary-color' ),
			'invoice-logo-id' 			=> isset( $settings[ 'invoice-logo-id' ] ) ? $settings[ 'invoice-logo-id' ] : '',
			'active-font' 				=> isset( $settings[ 'active-font' ] ) ? $settings[ 'active-font' ] : $this->get_setting_defaults( 'active-font' )
		);

		if( $field ){ //If a specific field value is requested

			if( isset( $data[$field] ) ){ //Checking if value exists
				$data = $data[$field];
			}

		}

		return $data;

	}

	/**
	 * Filter InvoiceBoo settings and update them accordingly
	 * Order options are filtered to make sure default value is added just once if the option was never saved before
	 *
	 * @since    1.0
	 * @param    mixed     $old_value 		 	Previous option value
	 * @param    mixed     $value 		 		New option value
	 */
	function filter_settings( $old_value, $value, $option ){

		if( !isset( $value['approved-order-statuses'] ) ){ //If order status is not set in the new value - make sure we save empty array
			$value['approved-order-statuses'] = array();
			update_option( $option, $value );
		}
		
	}

	/**
	 * Retrieve InvoiceBoo saved options
	 *
	 * @since    1.0
	 * @return   mixed
	 * @param    string     $option_name 		 Specific field that is reuqested
	 */
	function get_data( $option_name = false ) {

		$options = get_option( 'invoiceboo_data' );
		$data = array(
			'database-version' 			=> isset( $options[ 'database-version' ] ) ? $options[ 'database-version' ] : '',
			'rewrite-rules-flushed' 	=> isset( $options[ 'rewrite-rules-flushed' ] ) ? $options[ 'rewrite-rules-flushed' ] : false
		);

		if( $option_name ){ //If a specific option value is requested

			if( isset( $data[$option_name] ) ){ //Checking if value exists
				$data = $data[$option_name];
			}

		}

		return $data;

	}

	/**
	 * Update InvoiceBoo option
	 *
	 * @since    1.0
	 * @param    string     $option_name 		Specific option that should be updated
	 * @param    mixed		$value 		 	 	Option value that should be saved
	 */
	function update_data( $option_name, $value ) {

		$data = $this->get_data();
		$data[$option_name] = $value;
		update_option( 'invoiceboo_data', $data );

	}

	/**
	 * Adding custom action link on Plugin page under plugin name
	 *
	 * @since    1.0
	 * @return   array
	 * @param    array      $actions			Existing actions
	 * @param    string     $plugin_file    	Location of the plugin
	 */
	function add_plugin_action_links( $actions, $plugin_file ) {

		if ( ! is_array( $actions ) ) return $actions;

		$custom_links = array();
		$custom_links['invoiceboo_settings'] = array(
			'label' => esc_html__( 'Settings', 'invoiceboo-invoices-for-woocommerce' ),
			'url'   => menu_page_url( INVOICEBOO_SLUG, false )
		);
		return $this->add_display_plugin_action_links( $actions, $plugin_file, $custom_links, 'before' );

	}

	/**
	 * Merging existing Plugin action links with the new ones
	 *
	 * @since    1.0
	 * @return   array
	 * @param    array      $actions			Existing actions
	 * @param    string     $plugin_file    	Location of the plugin
	 * @param    array      $custom_links   	New links
	 * @param    string     $position       	Postition of the new custom links
	 */
	function add_display_plugin_action_links( $actions, $plugin_file, $custom_links = array(), $position = 'after' ){

		static $plugin;

		if ( ! isset( $plugin ) ) $plugin = INVOICEBOO_BASENAME;

		if ( $plugin === $plugin_file && ! empty( $custom_links ) ) {
			foreach ( $custom_links as $key => $value ) {
				$link = array( $key => '<a href="' . esc_url( $value['url'] ) . '">' . esc_html( $value['label'] ) . '</a>' );
				
				if ( 'after' === $position ) {
					$actions = array_merge( $actions, $link );

				} else {
					$actions = array_merge( $link, $actions );
				}
			}
		}
		return $actions;

	}

	/**
	 * Adds aditonal class to body tag if InvoiceBoo admin page open
	 *
	 * @since    1.0
	 * @return   string
	 * @param    object     $classes			Existing classes
	 */
	function add_body_class( $classes ){

		if( $this->is_invoiceboo_page() ){
			$classes .= ' ' .  INVOICEBOO_SLUG . ' ';
		}
		return $classes;

	}

	/**
	 * Register menu item under WooCommerce admin menu.
	 *
	 * @since    1.0
	 */
	function add_menu_page(){

		global $invoiceboo_admin_page;

		if( class_exists( 'WooCommerce' ) ){ //Check if WooCommerce plugin is active
			$invoiceboo_admin_page = add_submenu_page( 'woocommerce', INVOICEBOO_NAME, INVOICEBOO_NAME, 'manage_woocommerce', INVOICEBOO_SLUG, array( $this, 'display_page' ) ); //If the plugin is active - output menu under WooCommerce

		}else{
			$invoiceboo_admin_page = add_menu_page( INVOICEBOO_NAME, INVOICEBOO_NAME, 'manage_woocommerce', INVOICEBOO_SLUG, array( $this, 'display_page' ), 'dashicons-book' ); //Else output the menu as a Page
		}

	}

	/**
	 * Display settings under admin page
	 *
	 * @since    1.0
	 */
	function display_page(){

		ob_start(); ?>
		<div id="invoiceboo-wrapper" class="wrap">
			<form method="post" action="options.php">
				<?php
					settings_fields( 'invoiceboo-settings' );
					do_settings_sections( 'invoiceboo-settings' );
					$settings = $this->get_settings();
					$templates = $this->get_template_data();
					$fonts = $this->get_font_data();
				?>
				<div id="invoiceboo-head-container">
					<div class="invoiceboo-row">
						<div class="invoiceboo-col-md-12">
							<h1><?php echo esc_html( INVOICEBOO_NAME ); ?></h1>
						</div>
						<div class="invoiceboo-col-md-6">
							<div class="invoiceboo-page-description"><?php esc_html_e( 'Easy, quick, and user-friendly way of providing WooCommerce customers with Invoices.', 'invoiceboo-invoices-for-woocommerce' ); ?></div>
						</div>
						<div class="invoiceboo-hidden-xs invoiceboo-col-md-6 invoiceboo-right">
							<?php $this->add_buttons(); ?>
						</div>
					</div>
				</div>
				<div id="invoiceboo-content-container">
					<div class="invoiceboo-settings-container">
						<div class="invoiceboo-row">
							<div class="invoiceboo-titles-column invoiceboo-col-sm-4 invoiceboo-col-lg-3">
								<h2><?php esc_html_e( 'General', 'invoiceboo-invoices-for-woocommerce' ); ?></h2>
								<p class="invoiceboo-titles-column-description">
									<?php esc_html_e( 'Enable Invoices and provide your Company details that you want to include in your Invoices.', 'invoiceboo-invoices-for-woocommerce' ); ?>
								</p>
							</div>
							<div class="invoiceboo-settings-column invoiceboo-col-sm-8 invoiceboo-col-lg-9<?php if( $settings[ 'invoiceboo-enabled' ] ) echo ' invoiceboo-checked-parent'; ?>">
								<div class="invoiceboo-settings-group invoiceboo-toggle<?php if( $settings[ 'invoiceboo-enabled' ] ) echo ' invoiceboo-checked'; ?>">
									<label for="invoiceboo-enabled" class="invoiceboo-switch invoiceboo-control-visibility">
										<input id="invoiceboo-enabled" class="invoiceboo-checkbox" type="checkbox" name="invoiceboo_settings[enabled]" value="1" <?php echo checked( 1, $settings[ 'invoiceboo-enabled' ], false ); ?> autocomplete="off" />
										<span class="invoiceboo-slider round"></span>
									</label>
									<label for="invoiceboo-enabled" class="invoiceboo-control-visibility">
										<?php esc_html_e( 'Enable Invoices', 'invoiceboo-invoices-for-woocommerce' ); ?>
									</label>
								</div>
								<div class="invoiceboo-settings-group invoiceboo-hidden">
									<label for="invoiceboo-company-details"><?php esc_html_e( 'Company details', 'invoiceboo-invoices-for-woocommerce' ); ?></label>
									<textarea id="invoiceboo-company-details" class="invoiceboo-text" name="invoiceboo_settings[company-details]" rows="4" autocomplete="off"><?php echo esc_html( stripslashes( $settings[ 'company-details' ] ) ); ?></textarea>
								</div>

								<div class="invoiceboo-settings-group invoiceboo-hidden">
									<label for="invoiceboo-order-statuses"><?php esc_html_e( 'Automatically create Invoices for orders with status', 'invoiceboo-invoices-for-woocommerce' ); ?></label>
									<?php $this->display_order_statuses(); ?>
								</div>

							</div>
						</div>
						<div class="invoiceboo-row">
							<div class="invoiceboo-titles-column invoiceboo-col-sm-4 invoiceboo-col-lg-3">
								<h2><?php esc_html_e( 'Appearance', 'invoiceboo-invoices-for-woocommerce' ); ?></h2>
								<p class="invoiceboo-titles-column-description">
									<?php esc_html_e( 'Adjust the visual design and appearance of your PDF Invoices.', 'invoiceboo-invoices-for-woocommerce' ); ?>
								</p>
							</div>
							<div class="invoiceboo-settings-column invoiceboo-col-sm-8 invoiceboo-col-lg-9">
								<div class="invoiceboo-settings-group" style="display: none;">
									<h2><?php esc_html_e( 'Template', 'invoiceboo-invoices-for-woocommerce' ); ?></h2>
									<div class="invoiceboo-flex-container">
										<?php foreach( $templates as $key => $template ): ?>
											<div id="invoiceboo-template-<?php echo esc_attr( $key ); ?>" class="invoiceboo-template<?php if( $settings[ 'active-template' ] == $key ) echo ' invoiceboo-radio-active'; ?>">
												<label class="invoiceboo-image" for="invoiceboo-radiobutton-<?php echo esc_attr( $key ); ?>">
													<em>
														<i>
															<?php echo $this->get_icon( $key ); ?>
														</i>
													</em>
													<input id="invoiceboo-radiobutton-<?php echo esc_attr( $key ); ?>" class="invoiceboo-radiobutton" type="radio" name="invoiceboo_settings[active-template]" value="<?php echo esc_attr( $key ); ?>" <?php echo checked( $key, $settings[ 'active-template' ], false ); ?> autocomplete="off" />
													<?php echo esc_html( $template ); ?>
												</label>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
								<div class="invoiceboo-settings-group">
									<h2><?php esc_html_e( 'Font', 'invoiceboo-invoices-for-woocommerce' ); ?></h2>
									<div class="invoiceboo-flex-container">
										<?php foreach( $fonts as $key => $font ): ?>
											<div id="invoiceboo-font-<?php echo esc_attr( $key ); ?>" class="invoiceboo-template invoiceboo-font<?php if( $settings[ 'active-font' ] == $key ) echo ' invoiceboo-radio-active'; ?>">
												<label class="invoiceboo-image" for="invoiceboo-radiobutton-<?php echo esc_attr( $key ); ?>">
													<em>
														<i>
															<?php echo $this->get_icon( $key ); ?>
														</i>
													</em>
													<input id="invoiceboo-radiobutton-<?php echo esc_attr( $key ); ?>" class="invoiceboo-radiobutton" type="radio" name="invoiceboo_settings[active-font]" value="<?php echo esc_attr( $key ); ?>" <?php echo checked( $key, $settings[ 'active-font' ], false ); ?> autocomplete="off" />
													<?php echo esc_html( $font ); ?>
												</label>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
								<div class="invoiceboo-settings-group">
									<h2><?php esc_html_e( 'Colors', 'invoiceboo-invoices-for-woocommerce' ); ?></h2>
									<div class="invoiceboo-colors">
										<label for="invoiceboo-main-color"><?php esc_html_e( 'Main color', 'invoiceboo-invoices-for-woocommerce' ); ?></label>
										<input id="invoiceboo-main-color" type="text" name="invoiceboo_settings[main-color]" class="invoiceboo-color-picker invoiceboo-text" value="<?php echo esc_attr( $settings[ 'main-color' ] ); ?>" autocomplete="off" />
									</div>
									<div class="invoiceboo-colors">
										<label for="invoiceboo-secondary-color"><?php esc_html_e( 'Secondary color', 'invoiceboo-invoices-for-woocommerce' ); ?></label>
										<input id="invoiceboo-secondary-color" type="text" name="invoiceboo_settings[secondary-color]" class="invoiceboo-color-picker invoiceboo-text" value="<?php echo esc_attr( $settings[ 'secondary-color' ] ); ?>" autocomplete="off" />
									</div>
								</div>
								<div class="invoiceboo-settings-group">
									<?php
										if( !did_action( 'wp_enqueue_media' ) ){
											wp_enqueue_media();
										}
										$image = wp_get_attachment_image_src( $settings[ 'invoice-logo-id' ] );
									?>
									<h2><?php esc_html_e( 'Invoice Logo', 'invoiceboo-invoices-for-woocommerce' ); ?></h2>
									<p class='invoiceboo-additional-information'>
										<?php esc_html_e( 'Recommended dimensions:', 'invoiceboo-invoices-for-woocommerce' ); ?> 1024 x 200 px.
									</p>
									<div class="invoiceboo-action-container">
										<p id="invoiceboo-upload-invoice-logo" class="invoiceboo-upload-image">
											<?php if( $image ):?>
												<img src="<?php echo esc_url( $image[0] ); ?>" />
											<?php else: ?>
												<input type="button" value="<?php esc_attr_e( $this->get_setting_defaults( 'logo-upload-button-label' ) ); ?>" class="invoiceboo-button button-secondary button" />
											<?php endif; ?>
										</p>
										<a href="#" id="invoiceboo-remove-invoice-logo" class="invoiceboo-remove-image" <?php if( !$image ) echo 'style="display:none"'; ?>></a>
									</div>
									<input id="invoiceboo-invoice-logo" type="hidden" name="invoiceboo_settings[invoice-logo-id]" value="<?php if( $settings[ 'invoice-logo-id' ] ) echo esc_attr( $settings[ 'invoice-logo-id' ] ); ?>" autocomplete="off" />
								</div>
							</div>
						</div>
						<?php $this->add_buttons(); ?>
					</div>
				</div>
			</form>
			<form id="invoiceboo-preview-form" method="post" action="">
				<input type="hidden" name="invoiceboo-generate-pdf-preview" value="true" />
			</form>
		</div>
	<?php ob_end_flush();

	}

	/**
	 * Output settings buttons
	 *
	 * @since    1.0
	 */
	function add_buttons() {

		$disabled = false;

		if( !class_exists( 'WooCommerce' ) ){
			$disabled = true;
		};

		ob_start(); ?>
			<div class="invoiceboo-buttons">
				<button type='submit' class='invoiceboo-button button-primary invoiceboo-progress'><?php esc_html_e( 'Save', 'invoiceboo-invoices-for-woocommerce' ); ?></button><button type='button' <?php if( $disabled ) echo "disabled"; ?> class='invoiceboo-preview invoiceboo-button button-secondary invoiceboo-progress' data-action='invoiceboo-preview' data-nonce='<?php echo esc_attr( wp_create_nonce( 'invoiceboo-preview' ) ); ?>'><?php esc_html_e( 'Preview Invoice', 'invoiceboo-invoices-for-woocommerce' ); ?></button>
			</div>
		<?php ob_end_flush();

	}

	/**
	 * Return available invoice templates
	 *
	 * @since    1.0
	 * @return   array
	 */
	function get_template_data() {

		return $template_data = array(
			'sunrise' 	=> __( 'Sunrise', 'invoiceboo-invoices-for-woocommerce' )
		);

	}

	/**
	 * Return available invoice fonts
	 *
	 * @since    1.0
	 * @return   array
	 */
	function get_font_data() {

		return $template_data = array(
			'dejavusans' 		=> 'Dejavu Sans',
			'raleway' 			=> 'Raleway',
			'ibmplexsansjp' 	=> 'IBM Plex JP',
			'ibmplexsansarabic' => 'IBM Plex AR',
		);

	}

	/**
	 * Locating template file.
	 * Method returns the path to the template
	 *
	 * Search Order:
	 * 1. /themes/theme/templates/$template_name
	 * 2. /themes/theme/$template_name
	 * 3. /plugins/invoiceboo/templates/$template_name
	 *
	 * @since    1.0
	 * @return   string
	 * @param    string     $template_name 		  Template to load
	 * @param    string     $template_path 		  Path to templates
	 * @param    string     $default_path 		  Default path to template files
	 */
	function get_template_path( $template_name, $template_path = '', $default_path = '' ){

		$search_array = array();

		// Set variable to search folder of theme.
		if ( !$template_path ){
			$template_path = 'templates/';
		}

		//Add paths to look for template files
		$search_array[] = $template_path . $template_name;
		$search_array[] = $template_name;

		// Search template file in theme folder.
		$template = locate_template( $search_array );

		// Set default plugin templates path.
		if ( !$default_path ){
			$default_path = plugin_dir_path( __FILE__ ) . '../templates/'; // Path to the template folder
		}

		// Get plugins template file.
		if ( !$template ){
			$template = $default_path . $template_name;
		}

		return apply_filters( 'invoiceboo_get_template_path', $template, $template_name, $template_path, $default_path );

	}

	/**
	 * Get the template.
	 *
	 * @since    1.0
	 * @param    string     $template_name 		  Template to load
	 * @param    array      $args 				  Args passed for the template file
	 * @param    string     $template_path 		  Path to templates
	 * @param    string     $default_path 		  Default path to template files
	 */
	function get_template( $template_name, $args = array(), $tempate_path = '', $default_path = '' ) {

		$template_file = $this->get_template_path( $template_name, $tempate_path, $default_path );

		if ( is_array( $args ) && isset( $args ) ){
			extract( $args );
		}

		if ( !file_exists( $template_file ) ){ //Handling error output in case template file does not exist
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html( $template_file ) ), '1.0' );
			return;
		}

		include $template_file;

	}

	/**
	 * Return formatted date
	 * By default using date format set in WordPress
	 *
	 * @since    1.0
	 * @return   string
	 * @param    object     $order    		  	  WooCommerce Order object
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function get_order_date_created( $order, $preview ) {

		$date = $this->get_setting_defaults( 'date' );

		if( !$preview ){
			$date = $order->get_date_created();
		}

		if( empty( $date ) ) return;
		return $date->format( apply_filters( 'invoiceboo_date_format', get_option( 'date_format' ) ) );

	}

	/**
	 * Retrieve WooCommerce store company details
	 *
	 * @since    1.0
	 * @return   string
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function get_company_details( $preview = false ) {

		$company_details = $this->get_setting_defaults( 'company-details' );

		if( $preview ){

			if ( isset( $_POST['invoiceboo_settings']['company-details'] ) ) {
				$company_details = sanitize_textarea_field( $_POST['invoiceboo_settings']['company-details'] );
			}
			
		}else{
			$company_details = $this->get_settings( 'company-details' );
		}

		return wp_kses_post( stripslashes( $company_details ) );

	}

	/**
	 * Retrieve Customer company details
	 *
	 * @since    1.0
	 * @return   string
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function get_customer_company_details( $preview = false ) {

		$customer_company_details = $this->get_setting_defaults( 'customer-company-details' );

		if( $preview ){
			$customer_company_details = '-';

		}else{

			if( isset( $_POST['customer-company-details'] ) ){
				$customer_company_details = sanitize_textarea_field( $_POST['customer-company-details'] );
			}
		}

		return wp_kses_post( stripslashes( $customer_company_details ) );

	}

	/**
	 * Get active PDF template name
	 *
	 * @since    1.0
	 * @return   string
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function get_active_template_name( $preview = false ) {

		$active_template = $this->get_setting_defaults( 'active-template' );

		if( $preview ){

			if ( isset( $_POST['invoiceboo_settings']['active-template'] ) ) {
				$active_template = sanitize_text_field( $_POST['invoiceboo_settings']['active-template'] );
			}
			
		}else{
			$active_template = $this->get_settings( 'active-template' );
		}

		return $active_template;

	}

	/**
	 * Get invoice font
	 *
	 * @since    1.0
	 * @return   string
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function get_active_font( $preview = false ) {

		$active_font = $this->get_setting_defaults( 'active-font' );

		if( $preview ){

			if ( isset( $_POST['invoiceboo_settings']['active-font'] ) ) {
				$active_font = sanitize_text_field( $_POST['invoiceboo_settings']['active-font'] );
			}
			
		}else{
			$active_font = $this->get_settings( 'active-font' );
		}

		return apply_filters( 'invoiceboo_pdf_active_font', $active_font );

	}

	/**
	 * Get font used for titles inside PDF Invoice
	 *
	 * @since    1.0
	 * @return   string
	 */
	function get_title_font( $preview = false ) {

		$title_font = apply_filters( 'invoiceboo_pdf_title_font', $this->get_active_font( $preview ) );
		return $title_font;

	}

	/**
	 * Get main color
	 *
	 * @since    1.0
	 * @return   string
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function get_main_color( $preview = false ) {

		if( $preview ){

			if ( isset( $_POST['invoiceboo_settings']['main-color'] ) ) {
				$color = sanitize_text_field( $_POST['invoiceboo_settings']['main-color'] );
			}
			
		}else{
			$color = $this->get_settings( 'main-color' );
		}

		if( empty( $color ) ){
			$color = $this->get_setting_defaults( 'main-color' );
		}

		return $color;

	}

	/**
	 * Get main color
	 *
	 * @since    1.0
	 * @return   string
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function get_secondary_color( $preview = false ) {

		if( $preview ){

			if ( isset( $_POST['invoiceboo_settings']['secondary-color'] ) ) {
				$color = sanitize_text_field( $_POST['invoiceboo_settings']['secondary-color'] );
			}
			
		}else{
			$color = $this->get_settings( 'secondary-color' );
		}

		if( empty( $color ) ){
			$color = $this->get_setting_defaults( 'secondary-color' );
		}

		return $color;

	}

	/**
	 * Get invoice logo
	 *
	 * @since    1.0
	 * @return   string
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function get_invoice_logo( $preview = false ) {

		$image_url = $this->get_setting_defaults( 'invoice-logo-url' );

		if( $preview ){

			if ( isset( $_POST['invoiceboo_settings']['invoice-logo-id'] ) ) {
				$image_id = sanitize_text_field( $_POST['invoiceboo_settings']['invoice-logo-id'] );
			}
			
		}else{
			$image_id = $this->get_settings( 'invoice-logo-id' );
		}

		if( $image_id ){
			$image = wp_get_attachment_image_src( $image_id, 'full' );

			if( is_array( $image ) ){
				$image_url = $image[0];
			}
		}

		return $image_url;

	}

	/**
	 * Return ordered item column data
	 *
	 * @since    1.0
	 * @return   array
	 */
	function get_ordered_product_columns() {

		$columns = apply_filters(
			'invoiceboo_ordered_product_columns',
			array(
				'padding-left' 	=> array(
					'label' 	=> '',
					'width' 	=> '2%',
					'align' 	=> ''
				),
				'product' 	=> array(
					'label' 	=> __( 'Product', 'invoiceboo-invoices-for-woocommerce' ),
					'width' 	=> '51%',
					'align' 	=> 'left'
				),
				'price' 	=> array(
					'label' 	=> __( 'Price', 'invoiceboo-invoices-for-woocommerce' ),
					'width' 	=> '15%',
					'align' 	=> 'left'
				),
				'quantity' 	=> array(
					'label' 	=> __( 'Quantity', 'invoiceboo-invoices-for-woocommerce' ),
					'width' 	=> '15%',
					'align' 	=> 'left'
				),
				'amount' 	=> array(
					'label' 	=> __( 'Amount', 'invoiceboo-invoices-for-woocommerce' ),
					'width' 	=> '15%',
					'align' 	=> 'left'
				),
				'padding-right' => array(
					'label' 	=> '',
					'width' 	=> '2%',
					'align' 	=> ''
				)
			)
		);
		return $columns;

	}

	/**
	 * Return ordered products
	 *
	 * @since    1.0
	 * @return   array
	 * @param    object     $order    		  	  WooCommerce Order object
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function get_ordered_products( $order, $preview = false ) {

		$rows = array();

		if( class_exists( 'WooCommerce' ) ){
			
			$order_items = array( array(), array(), array() );

			if( !$preview ){
				$order_items = $order->get_items();
				$currency = $order->get_currency();
			}

			if( $order_items ){
				//Build ordered items
				foreach ( $order_items as $key => $item ) {

					$item_name = '-';
					$quantity = '-';
					$price = '-';
					$amount = '-';

					if( !$preview ){
						$product = $item->get_product();
						$item_name = $item['name'];
						$quantity = $item->get_quantity();
						$price = wc_price( $item->get_subtotal() / $quantity, array( 'currency' => $currency ) );
						$amount = wc_price( $item->get_subtotal(), array( 'currency' => $currency ) );
					}

					$rows[] = apply_filters(
						'invoiceboo_ordered_products',
						array(
							'padding-left' 	=> array(
								'label' 	=> '',
								'width' 	=> '2%',
								'align' 	=> 'left'
							),
							'product' 	=> array(
								'label' 	=> $item_name,
								'width' 	=> '51%',
								'align' 	=> 'left'
							),
							'price' 	=> array(
								'label' 	=> $price,
								'width' 	=> '15%',
								'align' 	=> 'left'
							),
							'quantity' 	=> array(
								'label' 	=> $quantity,
								'width' 	=> '15%',
								'align' 	=> 'left'
							),
							'amount' 	=> array(
								'label' 	=> $amount,
								'width' 	=> '15%',
								'align' 	=> 'left'
							),
							'padding-right' => array(
								'label' 	=> '',
								'width' 	=> '2%',
								'align' 	=> 'left'
							)
						),
						$order
					);
				}
			}
		}

		return $rows;

	}

	/**
	 * Return totals of the order
	 *
	 * @since    1.0
	 * @return   HTML
	 * @param    object     $order    		  	  WooCommerce Order object
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function get_order_totals( $order, $preview = false ) {

		$totals = array();
		$order_totals = array( //Data used during Invoice preview
			array(
				'label' => __( 'Subtotal:', 'invoiceboo-invoices-for-woocommerce' ),
				'value' => '-',
			),
			array(
				'label' => __( 'Tax:', 'invoiceboo-invoices-for-woocommerce' ),
				'value' => '-',
			),
			array(
				'label' => __( 'Payment method:', 'invoiceboo-invoices-for-woocommerce' ),
				'value' => '-',
			),
			array(
				'label' => __( 'Total:', 'invoiceboo-invoices-for-woocommerce' ),
				'value' => '-',
			)
		);

		if( !$preview ){
			$order_totals = $order->get_order_item_totals();

			if( apply_filters( 'invoiceboo_exclude_payment_method', false ) ){ //For allowing to exclude payment method if necessary
				unset( $order_totals['payment_method'] );
			}
		}

		foreach ( $order_totals as $key => $total ){
			
			$totals[] = apply_filters(
				'invoiceboo_order_totals',
				array(
					'label' 	=> $total['label'],
					'value' 	=> $total['value']
				),
				$order
			);

		}
		return $totals;

	}

	/**
	 * Return customer's order note
	 *
	 * @since    1.0
	 * @return   array
	 * @param    object     $order    		  	  WooCommerce Order object
	 * @param    boolean    $preview    		  Preview indicator
	 */
	function get_order_note( $order, $preview = false ) {
		
		$note = array();

		if( !$preview ){
			$customer_note = $order->get_customer_note();

			if( $customer_note ){
				$note = apply_filters(
					'invoiceboo_order_note',
					array(
						'label' 	=> esc_html__( 'Note:', 'invoiceboo-invoices-for-woocommerce' ),
						'value' 	=> wpautop( $customer_note )
					),
					$order
				);
			}
		}

		return $note;

	}

	/**
	 * Retrieve data for Invoice
	 *
	 * @since    1.0
	 * @return   array
	 * @param    object     $order    		  	  WooCommerce Order object
	 * @param    boolean    $preview    		  Preview indicator
	 * @param    boolean    $inside_pdf    		  If this is coming from PDF document generation
	 */
	function get_invoice_data( $order, $preview = false, $inside_pdf = false ) {

		if( !class_exists( 'WooCommerce' ) ) return;

		$invoice = new InvoiceBoo_Invoice();
		$active_font = 'unset';
		$title_font = 'unset';
		$small_size = '17px';
		$mini_size = '16px';
		$first_column_width = '';
		$second_column_width = '';
		$order_id = $this->get_setting_defaults( 'order-id' );

		if ( is_object( $order )) {
			$order_id = $order->get_ID();
		}

		if( $inside_pdf ){ //Data used for displaying content inside PDF
			$active_font = $this->get_active_font( $preview );
			$title_font = $this->get_title_font( $preview );
			$small_size = '11px';
			$mini_size = '10px';
			$first_column_width = '65%';
			$second_column_width = '35%';
		}

		$data = array(
			'company-details' 			=> $this->get_company_details( $preview ),
			'customer-company-details' 	=> $this->get_customer_company_details( $preview ),
			'site-url' 					=> get_site_url(),
			'invoice-logo-url' 			=> $this->get_invoice_logo( $preview ),
			'main-color' 				=> $this->get_main_color( $preview ),
			'secondary-color' 			=> $this->get_secondary_color( $preview ),
			'active-font' 				=> $active_font,
			'title-font' 				=> $title_font,
			'invoice-nr' 				=> $invoice->get_invoice_number( $order, $preview ),
			'order-nr' 					=> $order_id,
			'date-created' 				=> $this->get_order_date_created( $order, $preview ),
			'order-item-columns' 		=> $this->get_ordered_product_columns(),
			'order-item-products' 		=> $this->get_ordered_products( $order, $preview ),
			'order-totals' 				=> $this->get_order_totals( $order, $preview ),
			'order-note' 				=> $this->get_order_note( $order, $preview ),
			'small-font-size' 			=> $small_size,
			'mini-font-size' 			=> $mini_size,
			'inside-pdf' 				=> $inside_pdf
		);

		$args = apply_filters( 'invoiceboo_output_args', $data );
		return $args;

	}

	/**
	 * Preview Invoice data
	 *
	 * @since    1.0
	 * @return   array
	 */
	function preview_invoice() {
		
		if ( check_ajax_referer( 'invoiceboo-preview', 'nonce', false ) == false ) { //If the request does not include our nonce security check, stop executing the function
			wp_send_json_error( esc_html__( 'Sync failed. Looks like you are not allowed to do this.', 'invoiceboo-invoices-for-woocommerce' ) );
		}

		wp_send_json_success( esc_html__( 'Sync finished', 'invoiceboo-invoices-for-woocommerce' ) );

	}

	/**
	 * Method returns SVG icons
	 *
	 * @since    1.0
	 * @return 	 string
	 * @param    string     $label    		  	  Label of the icon
	 * @param    string     $current    		  Active icon label
	 * @param    string     $color    		  	  Hex color code
	 */
	public function get_icon( $label, $current = false, $color = '#c8c8c8' ){

		$svg = '';
		
		if( $current == $label ){ //If the icon is active
			$color = '#000';
		}

		switch ( $label ) {

			case 'sunrise':
				$svg = '<svg style="fill: '. esc_attr( $color ) .';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 547 539.59"><rect x="285" y="59.57" width="223" height="21" rx="10.5"/><rect x="285" y="101.78" width="123" height="21" rx="10.5"/><rect y="221.57" width="143" height="21" rx="10.5"/><rect y="264.57" width="123" height="21" rx="10.5"/><rect x="1" y="429.68" width="153" height="21" rx="10.5"/><rect x="197" y="429.68" width="153" height="21" rx="10.5"/><rect x="393" y="429.68" width="153" height="21" rx="10.5"/><rect x="1" y="385.12" width="153" height="21" rx="10.5"/><rect x="197" y="385.12" width="153" height="21" rx="10.5"/><rect x="393" y="385.12" width="153" height="21" rx="10.5"/><rect x="1" y="340.57" width="546" height="21" rx="10.5"/><circle cx="86.78" cy="82.29" r="82.29"/><rect x="393.36" y="518.59" width="153" height="21" rx="10.5"/><rect x="393.36" y="474.04" width="153" height="21" rx="10.5"/></svg>';
				break;

			case 'sunset':
				$svg = '<svg style="fill: '. esc_attr( $color ) .';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 547 539.59"><rect x="1" y="59.57" width="223" height="21" rx="10.5"/><rect x="1" y="101.78" width="123" height="21" rx="10.5"/><rect y="221.57" width="143" height="21" rx="10.5"/><rect y="264.57" width="123" height="21" rx="10.5"/><rect x="1" y="429.68" width="153" height="21" rx="10.5"/><rect x="197" y="429.68" width="153" height="21" rx="10.5"/><rect x="393" y="429.68" width="153" height="21" rx="10.5"/><rect x="1" y="385.12" width="153" height="21" rx="10.5"/><rect x="197" y="385.12" width="153" height="21" rx="10.5"/><rect x="393" y="385.12" width="153" height="21" rx="10.5"/><rect x="1" y="340.57" width="546" height="21" rx="10.5"/><circle cx="463.78" cy="82.29" r="82.29"/><rect x="393.36" y="518.59" width="153" height="21" rx="10.5"/><rect x="393.36" y="474.04" width="153" height="21" rx="10.5"/></svg>';
				break;

			case 'midnight':
				$svg = '<svg style="fill: '. esc_attr( $color ) .';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 547 539.59"><rect x="274" y="222.57" width="223" height="21" rx="10.5"/><rect x="274" y="264.78" width="123" height="21" rx="10.5"/><rect y="221.57" width="143" height="21" rx="10.5"/><rect y="264.57" width="123" height="21" rx="10.5"/><rect x="1" y="429.68" width="153" height="21" rx="10.5"/><rect x="197" y="429.68" width="153" height="21" rx="10.5"/><rect x="393" y="429.68" width="153" height="21" rx="10.5"/><rect x="1" y="385.12" width="153" height="21" rx="10.5"/><rect x="197" y="385.12" width="153" height="21" rx="10.5"/><rect x="393" y="385.12" width="153" height="21" rx="10.5"/><rect x="1" y="340.57" width="546" height="21" rx="10.5"/><circle cx="273.78" cy="82.29" r="82.29"/><rect x="393.36" y="518.59" width="153" height="21" rx="10.5"/><rect x="393.36" y="474.04" width="153" height="21" rx="10.5"/></svg>';
				break;

			case 'dejavusans':
				$svg = '<svg style="fill: '. esc_attr( $color ) .';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 447.6 277.61"><path d="M104,0h41.77L249.52,272.32h-38.3l-24.81-69.86H63.66L38.85,272.32H0Zm20.79,36.3-50,135.52H174.92Z"/><path d="M447.6,155.77V272.32H414v-31q-11.49,18.6-28.63,27.45t-42,8.85q-31.38,0-49.89-17.6t-18.51-47.15q0-34.47,23.07-52T367,143.37H414v-3.29q0-23.16-15.23-35.84T356,91.56a138.56,138.56,0,0,0-34.11,4.2A135.67,135.67,0,0,0,290,108.34v-31a224.16,224.16,0,0,1,35.75-10.67,167,167,0,0,1,33.75-3.56q44.33,0,66.21,23T447.6,155.77Zm-66.93,13.86q-40.68,0-56.37,9.3t-15.68,31.74q0,17.88,11.76,28.36t32,10.49q27.92,0,44.78-19.79T414,177.11v-7.48Z"/></svg>';
				break;

			case 'raleway':
				$svg = '<svg style="fill: '. esc_attr( $color ) .';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 431.08 268.96"><path d="M110.94,0h22l110.2,265.22H215.54l-34.37-82.93H62L28,265.22H0ZM175.2,161.75,121.78,30.63,67.61,161.75Z"/><path d="M322,269a68.86,68.86,0,0,1-25.59-4.67,64.51,64.51,0,0,1-20.54-12.89,58,58,0,0,1-18.49-43,48,48,0,0,1,6-23.53,57,57,0,0,1,16.81-18.68,82.46,82.46,0,0,1,25.78-12.14,118.82,118.82,0,0,1,32.87-4.3,180,180,0,0,1,30.26,2.62,131.44,131.44,0,0,1,27.27,7.47V142.32q0-25.77-14.57-40.9T341.43,86.29a85.85,85.85,0,0,0-30.64,6A147.8,147.8,0,0,0,279,109.45l-9-16.81q37.73-25.4,73.21-25.4,36.61,0,57.53,20.54t20.92,56.78v87.79q0,10.46,9.34,10.46v22.41a60.27,60.27,0,0,1-9.71,1.12q-9.72,0-15.13-4.85T400.45,248l-.75-15.32a88.25,88.25,0,0,1-33.81,26.9A104.07,104.07,0,0,1,322,269Zm6-19.43a87.66,87.66,0,0,0,36.42-7.47q16.62-7.47,25.21-19.8a23.16,23.16,0,0,0,5.05-7.28,18.62,18.62,0,0,0,1.68-7.29V175.94A157.15,157.15,0,0,0,370,168.47a147.72,147.72,0,0,0-27.83-2.61q-26.9,0-43.7,11.2t-16.81,29.51a40.23,40.23,0,0,0,3.55,16.81A42.5,42.5,0,0,0,294.92,237a45.42,45.42,0,0,0,14.75,9.15A49.83,49.83,0,0,0,328,249.53Z"/></svg>';
				break;

			case 'roboto':
				$svg = '<svg style="fill: '. esc_attr( $color ) .';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 421.34 269.22"><path d="M172.37,196.08H61.1l-25,69.49H0L101.41,0h30.65L233.65,265.57H197.72ZM71.68,167.44H162L116.73,43.23Z"/><path d="M386,265.57q-2.93-5.83-4.75-20.79Q357.69,269.21,325,269.22q-29.17,0-47.88-16.51t-18.69-41.86q0-30.82,23.44-47.88t65.93-17.05h32.84V130.41q0-17.68-10.58-28.18T338.9,91.75q-18.06,0-30.28,9.12T296.4,122.94H262.47q0-14.77,10.49-28.55t28.45-21.8a95.74,95.74,0,0,1,39.49-8q34.11,0,53.45,17.05t20.06,47v90.84q0,27.16,6.93,43.22v2.92Zm-56-25.72a59.28,59.28,0,0,0,30.09-8.2q14.24-8.22,20.62-21.35V169.81H354.22q-62,0-62,36.3,0,15.87,10.58,24.81T330,239.85Z"/></svg>';
				break;

			case 'robotoslab':
				$svg = '<svg style="fill: '. esc_attr( $color ) .';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 455.63 269.4"><path d="M0,242.59l19.7-2.74L112.54,0h30.83l91.19,239.85,19.52,2.74v23H177.84v-23l20.06-3.47-17.51-48.88H74.05L56,239.12l20.06,3.47v23H0Zm85.18-82.44h84.27l-41.22-114h-1.1Z"/><path d="M408.57,265.57q-1.63-8-2.55-14.41t-1.28-12.77a86.9,86.9,0,0,1-26,22.17,66.48,66.48,0,0,1-33.29,8.84q-30.82,0-46.87-15T282.53,212q0-28.09,22.71-43.32t62.29-15.23h37v-23q0-17.32-10.94-27.45T363.15,92.84a74.62,74.62,0,0,0-23.8,3.47,44.76,44.76,0,0,0-16.69,9.48l-3.83,20.79H291.11V88.28Q304.6,77.53,323.76,71a129.21,129.21,0,0,1,41.58-6.47q33.57,0,54.36,17.14t20.79,49.07v95c0,2.55,0,5.05.09,7.48s.21,4.86.46,7.29l14.59,2v23Zm-57.82-26.26a63.83,63.83,0,0,0,32.65-8.58q14.78-8.56,21.16-20.61V177.66h-38.3q-22.08,0-34.93,10.21t-12.86,24.81q0,12.94,8,19.79T350.75,239.31Z"/></svg>';
				break;

			case 'ptsans':
				$svg = '<svg style="fill: '. esc_attr( $color ) .';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 376.16 268.58"><path d="M152.92,193.13H57L31,265.6H0L99,0h14.2l99.36,265.6H179.68Zm-85.86-26.9h76.56l-29-79.57-9.29-39.59H105L95.68,87.41Z"/><path d="M241.31,92.64a107.5,107.5,0,0,1,33.06-12.7,184.61,184.61,0,0,1,39.41-4.11q19.43,0,31.19,5.23T362.9,94.7a46.62,46.62,0,0,1,8.22,18.49,100.7,100.7,0,0,1,2.05,20.17q0,22.41-1.12,43.7t-1.12,40.35q0,13.82,1.12,26.15a124.13,124.13,0,0,0,4.11,22.78h-22l-7.84-26.15h-1.87a62.38,62.38,0,0,1-8.4,10.28,54.22,54.22,0,0,1-12.14,9A76.57,76.57,0,0,1,307.43,266a79.3,79.3,0,0,1-21.29,2.61,64.79,64.79,0,0,1-22.23-3.73,51.84,51.84,0,0,1-17.74-10.65,49.47,49.47,0,0,1-11.77-16.62,54,54,0,0,1-4.3-22q0-16.44,6.73-27.46a48.53,48.53,0,0,1,18.86-17.55q12.13-6.54,29.14-9.34a231.36,231.36,0,0,1,37.54-2.8h10.27a73.83,73.83,0,0,1,10.28.74Q344,147.93,344,139q0-20.54-8.22-28.76T305.94,102a104.74,104.74,0,0,0-13.64,1c-4.85.64-9.84,1.54-14.94,2.69a123.92,123.92,0,0,0-14.57,4.23,72,72,0,0,0-12.14,5.57Zm53.05,149.8A57.68,57.68,0,0,0,313,239.63a54,54,0,0,0,14.2-7.09,45.28,45.28,0,0,0,9.9-9.53,42.61,42.61,0,0,0,5.79-10.09v-31c-3.49-.25-7-.43-10.65-.56s-7.16-.19-10.65-.19A191.8,191.8,0,0,0,299,182.48a71.67,71.67,0,0,0-19.43,4.86,34.17,34.17,0,0,0-13.45,9.71q-5,6.16-5,15.5,0,13.08,9.34,21.48T294.36,242.44Z"/></svg>';
				break;

			case 'rajdhani':
				$svg = '<svg style="fill: '. esc_attr( $color ) .';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 347.8 240.19"><path d="M14.59,240.19H3q-4.11,0-2.61-4.48L74.74,4.48Q75.85,0,80,0H97.52q3.74,0,5.23,4.48l74.34,231.23c.75,3-.13,4.48-2.61,4.48h-12q-3.74,0-4.85-4.48l-20.18-59.77H39.62L19.45,235.71C18.45,238.7,16.84,240.19,14.59,240.19ZM87.81,20.54l-43,138.59h87.79L89.31,20.54Z"/><path d="M288.78,240.19H264.87q-24.28,0-38.29-14.19t-14-38.85V102.73q0-24.66,14-38.85t38.29-14.2h79.19c2.49,0,3.74,1.5,3.74,4.48V235.71q0,4.48-3.74,4.48H333.23q-4.11,0-4.11-4.48V220.77h-1.49Q317.91,240.2,288.78,240.19Zm40.34-53.41V69.85c0-2.24-1.25-3.36-3.73-3.36h-59q-17.19,0-26.15,9.71t-9,27.27V186.4q0,17.57,9,27.27t26.15,9.71h24.28q17.93,0,28.2-9.52T329.12,186.78Z"/></svg>';
				break;

			case 'exo':
				$svg = '<svg style="fill: '. esc_attr( $color ) .';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 415.39 277.17"><path d="M0,274.93,94.14,0h38.1l94.13,274.93H195.74L168.1,196.49H57.53l-26.9,78.44ZM65,169.59h95.63L113.19,26.15Z"/><path d="M306.31,277.17q-22.05,0-36.23-13.07t-14.2-37v-14.2q0-23.53,15.5-38.1t46.88-14.57h67.62V135.6q0-12.33-4.3-21.11t-15.31-13.63Q355.25,96,334.33,96h-62V78.45q13.06-2.25,30.44-4.11a387.34,387.34,0,0,1,40.91-1.87q24.27-.37,40.34,6t23.72,20q7.65,13.63,7.66,35.67V274.93H392.6L387,252.15q-1.5,1.5-9.15,5.23t-19.43,8.4a194.65,194.65,0,0,1-25.4,8A110.87,110.87,0,0,1,306.31,277.17Zm12.33-22a57.53,57.53,0,0,0,15.31-1.5q8.6-1.86,17.56-4.29t16.62-5q7.65-2.61,12.52-4.48a50.77,50.77,0,0,1,5.23-1.87V175.2L325,177.81q-21.3.75-30.45,10.46t-9.15,25v9q0,12,5.05,19.24a27.3,27.3,0,0,0,12.7,10.27A48.58,48.58,0,0,0,318.64,255.14Z"/></svg>';
				break;

			case 'oxygen':
				$svg = '<svg style="fill: '. esc_attr( $color ) .';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 410.76 274.51"><path d="M203.92,270.31l-31.55-85.54H64.75L32.1,270.31H0L103.6,0h32.29l100.5,270.31Zm-41.77-112Q123.66,46.51,119.65,32.65L75.33,158.32Z"/><path d="M387.05,270.31q-2.74-11.67-5.29-25.17-16.61,16.61-31.19,23t-34.29,6.38q-28.09,0-45.15-14.68t-17-42.23q0-30.65,23.26-45.42t67-18.42q6-.54,18.15-1.55t18.15-1.55V133q0-21.16-9.85-31.37t-31-10.22q-29.55,0-59.65,14.59-.92-2.19-4.74-12.67t-4-10.86A125,125,0,0,1,303.05,70.5,157,157,0,0,1,340.36,66q37.38,0,53.89,16.32t16.51,55.54V270.31Zm-66.21-20.42q21.71,0,38.21-11.95a53.5,53.5,0,0,0,21.25-32.2V172c-.61,0-4.29.31-11,.91s-11.16,1-13.23,1.1q-39.59,3.47-55.17,13t-15.6,30.46q0,16.05,9.49,24.26T320.84,249.89Z"/></svg>';
				break;

			case 'ibmplexsansjp':
				$svg = '<svg style="fill: '. esc_attr( $color ) .';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10.39 10.39"><rect style="fill: none;" width="10.39" height="10.39"/><path d="M7.6,4.32A3,3,0,0,1,9,5.3a2.47,2.47,0,0,1,.49,1.52,2.44,2.44,0,0,1-.95,2,4.44,4.44,0,0,1-2.62.83L5.71,9a4.17,4.17,0,0,0,2.23-.62A1.78,1.78,0,0,0,8.7,6.78a1.79,1.79,0,0,0-.52-1.31,2.76,2.76,0,0,0-1.45-.71,9.81,9.81,0,0,1-.91,2.41A5.21,5.21,0,0,1,4.46,8.83a2.73,2.73,0,0,1-1.69.6,1.81,1.81,0,0,1-1.32-.49A1.73,1.73,0,0,1,.94,7.63a2.94,2.94,0,0,1,.72-1.92A4.59,4.59,0,0,1,3.55,4.38v0c0-.28,0-.76,0-1.45H3.46c-.79,0-1.54,0-2.27,0l0-.67c.88,0,1.63,0,2.22,0h.16c0-.59.05-1.1.1-1.52l.68,0c0,.4-.07.89-.09,1.48,1.84,0,3.31-.09,4.42-.19l0,.67c-.58,0-1.31.08-2.17.11l-2.29.06c0,.62,0,1,0,1.27A5.78,5.78,0,0,1,5.65,4,5.46,5.46,0,0,1,7.6,4.32ZM2,8.42a1.13,1.13,0,0,0,.83.31A2,2,0,0,0,4,8.28,13.08,13.08,0,0,1,3.57,5.1,3.86,3.86,0,0,0,2.18,6.16a2.24,2.24,0,0,0-.53,1.42A1.12,1.12,0,0,0,2,8.42ZM6,4.66h-.4a5,5,0,0,0-1.39.2c0,.52,0,1,.11,1.55s.14,1,.23,1.34A7.31,7.31,0,0,0,6,4.66Z"/></svg>';
				break;

			case 'ibmplexsansarabic':
				$svg = '<svg style="fill: '. esc_attr( $color ) .';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 11.79 11.79"><rect style="fill: none;" width="11.79" height="11.79"/><path d="M9.8,2.39H9.2V2l-.93.17-.5.07a1.28,1.28,0,0,1-.26,0c-.58,0-.93-.33-1.06-1L7,1a.78.78,0,0,0,.19.39.42.42,0,0,0,.33.12l.22,0,.42-.08.69-.14a1.21,1.21,0,0,1,.45,0,.7.7,0,0,1,.3.14.57.57,0,0,1,.16.26A1.13,1.13,0,0,1,9.8,2ZM8.62,10.64H7.73V3.23h.89Z"/><path d="M3.24,10.59a2,2,0,0,1-.68-.45,2,2,0,0,1-.42-.68A2.55,2.55,0,0,1,2,8.62,3,3,0,0,1,2.27,7.4a5.32,5.32,0,0,1,1-1.34l.33-.35H4.63l.4.47A5.86,5.86,0,0,1,6,7.49a2.69,2.69,0,0,1,.31,1.25,2.05,2.05,0,0,1-.14.79,1.87,1.87,0,0,1-.42.64,2,2,0,0,1-.68.43,2.57,2.57,0,0,1-.9.15A2.38,2.38,0,0,1,3.24,10.59Zm1-.87a1.1,1.1,0,0,0,.47-.1,1.09,1.09,0,0,0,.38-.25,1.15,1.15,0,0,0,.33-.81,1.38,1.38,0,0,0-.18-.68,6,6,0,0,0-.55-.74l-.49-.58H4.16l-.54.58A3.87,3.87,0,0,0,3,7.91a1.46,1.46,0,0,0-.19.71,1,1,0,0,0,.33.79A1.21,1.21,0,0,0,4,9.72Z"/></svg>';
				break;

		}

		return "<span class='invoiceboo-icon-container invoiceboo-icon-$label'><img src='data:image/svg+xml;base64," . esc_attr( base64_encode( $svg ) ) . "' alt='" . esc_attr( $label ) . "' /></span>";
	}

	/**
	 * Output admin notices
	 *
	 * @since    1.0
	 */
	function show_notices(){

		$this->missing_woocommerce_notice();

	}

	/**
	 * Display missing WooCommerce notice
	 *
	 * @since    1.0
	 */
	function missing_woocommerce_notice() {

		if( !class_exists( 'WooCommerce' ) ){
			$message = esc_html__( 'Please enable WooCommerce to use InvoiceBoo.', 'invoiceboo-invoices-for-woocommerce' );
			echo $this->prepare_notice( $message, $class = 'error' );
		}

	}

	/**
	 * Prepare notice contents for output
	 *
	 * @since    1.0
	 * @return   HTML
	 * @param    string   	$message   		Content of the message
	 * @param    string   	$class   		Additional classes required for the notice. Default empty
	 */
	function prepare_notice( $message, $class = '' ){

		$output = '';
		ob_start(); ?>
		<div class="notice notice<?php if( $class ) echo '-' . esc_attr( $class ); ?>">
			<p><?php echo wp_kses_post( $message ); ?></p>
		</div>
		<?php $output = ob_get_contents();
		ob_end_clean();

		return $output;

	}
	
}