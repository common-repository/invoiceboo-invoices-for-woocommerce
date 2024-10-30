<?php
/**
 * Default InvoiceBoo template for displaying InvoiceBoo Invoice preparation page
 *
 * This template can be overridden by copying it to 
 * yourtheme/templates/invoiceboo-page.php or
 * yourtheme/invoiceboo-page.php
 *
 * If a new InvoiceBoo version is released with an updated template file, you
 * may need to replace the old template file with the new one to maintain compatibility.
 *
 * @since      1.0
 * @package    InvoiceBoo
 * @subpackage InvoiceBoo/templates
 * @author     InvoiceBoo <hello@invoiceboo.com>
 */

if ( !defined( 'ABSPATH' ) ) exit;
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php wp_head(); ?>
	</head>
	<body id="invoiceboo-page" class="<?php if( !$args['order-id'] ) echo 'invoiceboo-no-invoice'; ?>">
		<div class="container">
			<div class="invoiceboo-row">
				<div class="invoiceboo-col-xs-6 invoiceboo-column-first">
					<?php if( $args['order-id'] ): ?>
						<h1><?php esc_html_e( 'Order', 'invoiceboo-invoices-for-woocommerce' ); ?> #<?php echo esc_html( $args['order-id'] ); ?></h1>
					<?php endif; ?>
				</div>
				<div class="invoiceboo-col-xs-6 invoiceboo-column-second">
					<?php do_action( 'invoiceboo_page_header' ); ?>
				</div>
			</div>
			<div id="invoiceboo-invoice-container" class="invoiceboo-row">
				<div class="invoiceboo-col-xs-12">
					<?php echo wp_kses_post( $args['invoice-content'] ); ?>
				</div>
			</div>
			<?php do_action( 'invoiceboo_page_footer' ); ?>
		</div>
	</body>
</html>