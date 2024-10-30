<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0
 * @package    InvoiceBoo
 * @subpackage InvoiceBoo/includes
 * @author     InvoiceBoo <hello@invoiceboo.com>
 */
class InvoiceBoo {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      InvoiceBoo_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0
	 */
	public function __construct() {
		if ( defined( 'INVOICEBOO_VERSION' ) ) {
			$this->version = INVOICEBOO_VERSION;
		} else {
			$this->version = '1.0';
		}
		$this->plugin_name = INVOICEBOO_SLUG;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_invoice_hooks();
		$this->define_pdf_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - InvoiceBoo_Loader. Orchestrates the hooks of the plugin.
	 * - InvoiceBoo_i18n. Defines internationalization functionality.
	 * - InvoiceBoo_Admin. Defines all hooks for the admin area.
	 * - InvoiceBoo_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-invoiceboo-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-invoiceboo-i18n.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-invoiceboo-invoice.php';

		/**
		 * The class responsible for defining all actions that occur with PDF creation
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/tcpdf/tcpdf.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-invoiceboo-pdf.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-invoiceboo-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-invoiceboo-public.php';

		$this->loader = new InvoiceBoo_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the InvoiceBoo_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new InvoiceBoo_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$admin = new InvoiceBoo_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'plugins_loaded', $admin, 'update_database' );
		$this->loader->add_action( 'admin_menu', $admin, 'add_menu_page', 10 ); //Creates admin menu
		$this->loader->add_filter( 'admin_body_class', $admin, 'add_body_class' ); //Adding InoiceBoo specific class to body tag
		$this->loader->add_filter( 'plugin_action_links_' . INVOICEBOO_BASENAME, $admin, 'add_plugin_action_links', 10, 2 );
		$this->loader->add_action( 'wp_ajax_invoiceboo-preview', $admin, 'preview_invoice' );
		$this->loader->add_action( 'admin_notices', $admin, 'show_notices' );
		$this->loader->add_filter( 'update_option_invoiceboo_settings', $admin, 'filter_settings', 10, 3 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$public = new InvoiceBoo_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles', 30 );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );
		$this->loader->add_filter( 'woocommerce_my_account_my_orders_actions', $public, 'add_download_button_my_account', 15, 2 );
		$this->loader->add_filter( 'woocommerce_thankyou', $public, 'add_download_button_thank_you', 10 );
		$this->loader->add_action( 'init', $public, 'invoiceboo_endpoint' );
		$this->loader->add_filter( 'pre_get_document_title', $public, 'set_invoiceboo_page_title' );
		$this->loader->add_action( 'template_redirect', $public, 'invoiceboo_template_redirect' );
		$this->loader->add_action( 'woocommerce_email_order_meta', $public, 'add_download_link_to_email', 10, 3 );
		$this->loader->add_action( 'invoiceboo_page_footer', $public, 'add_invoiceboo_branding', 10, 3 );
		
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function define_invoice_hooks() {

		$invoice = new InvoiceBoo_Invoice();

		$this->loader->add_action( 'woocommerce_new_order', $invoice, 'new_invoice', 10 );
		$this->loader->add_action( 'woocommerce_order_status_changed', $invoice, 'new_invoice', 10 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function define_pdf_hooks() {

		$pdf = new InvoiceBoo_PDF();

		$this->loader->add_action( 'init', $pdf, 'validate_invoice_pdf' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0
	 * @return    InvoiceBoo_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
