<?php
/**
 * Default InvoiceBoo template for generating PDF invoice
 *
 * This template can be overridden by copying it to 
 * yourtheme/templates/invoiceboo-sunrise.php or
 * yourtheme/invoiceboo-sunrise.php
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
$total_row_count = count( $args['order-totals']) - 1;
?>
<div id="template-sunrise">
	<div id="invoiceboo-invoice-head">
		<table width="100%">
			<tr>
				<td class="invoiceboo-first-column" valign="top" width="65%">
					<?php if( $args['invoice-logo-url'] ):?>
					<div>
						<a href="<?php echo esc_url( $args['site-url'] ); ?>" target="_blank"><img id="invoiceboo-logo" style="width: 19em;" src="<?php echo esc_attr( $args['invoice-logo-url'] ); ?>"/></a>
					</div>
					<?php endif; ?>
					<?php if( $args['company-details'] ):?>
					<h3 style="font-family: <?php echo esc_attr( $args['title-font'] ); ?>; font-size: <?php echo esc_attr( $args['small-font-size'] ); ?>;"><strong><?php esc_html_e( 'Billed from', 'invoiceboo-invoices-for-woocommerce' ); ?></strong></h3>
					<?php echo nl2br( esc_html( $args['company-details'] ) ); ?>
					<?php endif; ?>
					<?php if( $args['inside-pdf'] && $args['customer-company-details'] ):?>
					<br>
					<h3 style="font-family: <?php echo esc_attr( $args['title-font'] ); ?>; font-size: <?php echo esc_attr( $args['small-font-size'] ); ?>;"><strong><?php esc_html_e( 'Billed to', 'invoiceboo-invoices-for-woocommerce' ); ?></strong></h3>
					<?php echo nl2br( esc_html( $args['customer-company-details'] ) ); ?>
					<?php elseif( !$args['inside-pdf'] ): ?>
					<h3 style="font-family: <?php echo esc_attr( $args['title-font'] ); ?>; font-size: <?php echo esc_attr( $args['small-font-size'] ); ?>;"><strong><?php esc_html_e( 'Billed to', 'invoiceboo-invoices-for-woocommerce' ); ?></strong></h3>
					<span id="billing-placeholder"></span>
					<a href="#" class="invoiceboo-open-form">Add / Edit</a>
					<?php endif; ?>
				</td>
				<td class="invoiceboo-second-column" valign="top" width="35%">
					<?php if( $args['inside-pdf'] ): ?>
					<h2 style="font-family: <?php echo esc_attr( $args['title-font'] ); ?>; font-size: 18px; font-weight: normal;"><?php esc_html_e( 'Invoice', 'invoiceboo-invoices-for-woocommerce' ); ?></h2>
					<?php endif; ?>
					<table id="invoiceboo-invoice-meta-data">
						<tr>
							<td><strong style="font-family: <?php echo esc_attr( $args['title-font'] ); ?>;"><?php esc_html_e( 'Invoice Nr.', 'invoiceboo-invoices-for-woocommerce' ); ?>:</strong></td>
							<td><?php echo esc_html( $args['invoice-nr'] ); ?></td>
						</tr>
						<tr>
							<td><strong style="font-family: <?php echo esc_attr( $args['title-font'] ); ?>;"><?php esc_html_e( 'Date', 'invoiceboo-invoices-for-woocommerce' ); ?>:</strong></td>
							<td><?php echo esc_html( $args['date-created'] ); ?></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
	<div id="invoiceboo-invoice-data">
		<table width="100%" cellpadding="6" cellspacing="0" border="0">
			<tr style="background-color: <?php echo esc_attr( $args['main-color'] ); ?>; color: <?php echo esc_attr( $args['secondary-color'] ); ?>">
			<?php foreach ( $args['order-item-columns'] as $key => $column ): ?>
				<th valign="top" width="<?php echo esc_attr( $column['width'] ); ?>" style="font-family: <?php echo esc_attr( $args['title-font'] ); ?>; font-size: <?php echo esc_attr( $args['small-font-size'] ); ?>; border: 0 solid <?php echo esc_attr( $args['main-color'] ); ?>;">
					<strong><?php esc_html_e( $column['label'] ); ?></strong>
				</th>
			<?php endforeach; ?>
			</tr>
			<?php foreach ( $args['order-item-products'] as $key => $rows ): ?>
				<tr>
				<?php foreach ( $rows as $key => $row ): ?>
					<td valign="top" width="<?php echo esc_attr( $row['width'] ); ?>" align="<?php echo esc_attr( $row['align'] ); ?>" style="font-size: <?php echo esc_attr( $args['mini-font-size'] ); ?>; border-bottom: 0.3px solid #d7d7d7; text-align: <?php echo esc_attr( $row['align'] ); ?>">
						<?php echo wp_kses_post( $row['label'] ); ?>
					</td>
				<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
			<?php foreach ( $args['order-totals'] as $key => $total ): ?>
				<tr>
					<td valign="top" colspan="4" align="right" style="text-align: right;<?php if( $total_row_count == $key ) echo "font-family: ". esc_attr( $args['title-font'] ) ."; font-weight: bold;"?>"><?php esc_html_e( $total['label'] ); ?></td>
					<td valign="top" colspan="2" style="<?php if( $total_row_count == $key ) echo "font-family: ". esc_attr( $args['title-font'] ) ."; font-weight: bold;"?>"><?php echo wp_kses_post( $total['value'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php if( $args['order-note'] ):?>
		<table id="invoiceboo-customer-note" width="50%" cellpadding="6" cellspacing="0" border="0">
			<tr>
				<td valign="top"><?php esc_html_e( $args['order-note']['label'] ); ?>
					<p><?php echo wp_kses_post( $args['order-note']['value'] ); ?></p>
				</td>
			</tr>
		</table>
		<?php endif; ?>
	</div>
</div>