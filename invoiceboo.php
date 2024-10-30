<?php

/**
 * @since             1.0
 * @package           InvoiceBoo
 * @author     		  InvoiceBoo <hello@invoiceboo.com>
 *
 * @wordpress-plugin
 * Plugin Name:       InvoiceBoo - Invoices for WooCommerce
 * Description:       A simple solution for offering an easy, quick, and user-friendly way of providing WooCommerce customers with Invoices
 * Version:           1.1
 * Author:            Streamline.lv
 * Author URI:        https://www.invoiceboo.com/
 * Requires at least: 4.0
 * Requires PHP:      7.0
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:       invoiceboo-invoices-for-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( !defined( 'INVOICEBOO_VERSION' ) )		define( 'INVOICEBOO_VERSION', '1.1' );
if ( !defined( 'INVOICEBOO_NAME' ) )		define( 'INVOICEBOO_NAME', 'InvoiceBoo' );
if ( !defined( 'INVOICEBOO_SLUG' ) )		define( 'INVOICEBOO_SLUG', 'invoiceboo' );
if ( !defined( 'INVOICEBOO_PAGE' ) )		define( 'INVOICEBOO_PAGE', 'https://www.invoiceboo.com/' );
if ( !defined( 'INVOICEBOO_BASENAME' ) )	define( 'INVOICEBOO_BASENAME', plugin_basename( __FILE__ ) );

//Register plugin settings
register_setting( 'invoiceboo-settings', 'invoiceboo_settings' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-invoiceboo-activator.php
 */
function activate_invoiceboo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-invoiceboo-activator.php';
	InvoiceBoo_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-invoiceboo-deactivator.php
 */
function deactivate_invoiceboo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-invoiceboo-deactivator.php';
	InvoiceBoo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_invoiceboo' );
register_deactivation_hook( __FILE__, 'deactivate_invoiceboo' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-invoiceboo.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0
 */
function run_invoiceboo() {

	$plugin = new InvoiceBoo();
	$plugin->run();

}
run_invoiceboo();
