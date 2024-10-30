=== InvoiceBoo - Invoices for WooCommerce ===
Contributors: streamlinestar, nauriskolats
Donate link: https://www.invoiceboo.com/
Tags: invoice, woocommerce invoices, woocommerce, pdf, download
Requires at least: 4.0
Tested up to: 6.7
Requires PHP: 7.0
Stable tag: 1.1
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Easy, quick, and user-friendly way of providing WooCommerce customers with Invoices.

== Description ==

[InvoiceBoo](https://www.invoiceboo.com/ "Invoices for WooCommerce") is a small and simple plugin which allows an easy way of providing WooCommerce customers with Invoices.

## Checkout first and ask Business details later

InvoiceBoo allows to keep checkout process short as customers can provide company details (e.g., company name, reg. number, VAT) required for the Invoice after the order has been placed. This not only provides a faster checkout, but also minimizes [cart abandonment](https://www.cartbounty.com/ "Abandoned cart recovery") and can increase sales 🤑.

Once the order is placed and is assigned any of the selected WooCommerce order statuses ("Processing", "Completed", "On Hold" etc.), customer receives a unique link to their Invoice page where they can include additional business details that are required for accounting purposes. From here customers can Print and Download their invoices as PDF documents.

This plugin can be especially useful for businesses selling virtual or downloadable products as they do not require shipping options.

## For guests and registered customers

InvoiceBoo does not require customers to be registered or signed in to view, update or download their invoices. Guest customers will be able to easily view the Invoice, add custom business details and save it.

Customers can find their Invoices in the following areas:

* Order confirmation email
* WooCommerce Thank you page
* My Account > Orders page if the user has an account

Business details are kept inside a single text area field to improve customer experience and allow faster data input.

## Administration options and settings

There aren't many configuration options and the setup is quite simple. Store administrators can:

* Select order statuses after which the Invoice is generated
* Tailor visual appearance of the Invoice to match your brand identity by customizing colors, fonts and adding company logo
* [Customize Invoice template](https://wordpress.org/plugins/invoiceboo-invoices-for-woocommerce/#how%20to%20customize%20pdf%20invoice%20template%3F)
* Preview Invoice PDF before enabling or saving it

Since we love to keep everything clean and efficient, InvoiceBoo will automatically clean up after itself once you deactivate and delete it from your store (all database options and tables created by Invoiceboo will be removed). We believe that we must clean after ourselves both in life and digital realm 🙃.

## What will be added in the future

InvoiceBoo is still young and we do know that there are features that might be missing. We would really ❤️ [love to hear from you](https://wordpress.org/support/plugin/invoiceboo-invoices-for-woocommerce/ "Share your ideas and feedback") and learn what additional features you would like to see.

Here are features that are in our wish-list right now:

* Sequential Invoice numbering
* Save company details inside database that customer adds before downloading the Invoice
* Ability for store administrators to manually download customer's Invoice
* Additional Invoice design templates
* Invoice download stats
* RTL support

== Installation ==

1. Navigate to your **WordPress dashboard > Plugins > Add new**
1. Search for "InvoiceBoo"
1. Install and activate InvoiceBoo
1. Navigate to **WooCommerce > InvoiceBoo** and enable Invoices

== Frequently Asked Questions ==

= Missing a feature or have a new idea? =

If you find that there is a feature that you would like to have or have an idea for improvement, please let us know and [contact us](https://www.invoiceboo.com/support/ "Contact InvoiceBoo").

= How to customize PDF Invoice template? =

InvoiceBoo offers a template file which provides Invoice layout and content customization options.

Template file is located inside **/plugins/invoiceboo/templates** folder. Please copy the template file to your active theme to keep your customization intact after plugin updates. You can copy them to either one of these locations:

* yourtheme/templates/ or
* yourtheme/

Please be aware that if a new InvoiceBoo version is released with an updated template file, you may have to replace the old template file with the new one to maintain compatibility.

= Anything for developers? =

InvoiceBoo includes various filters for additional plugin and Invoice customization.

Filters

* invoiceboo_approved_order_statuses
* invoiceboo_date_format
* invoiceboo_ordered_product_columns
* invoiceboo_ordered_products
* invoiceboo_exclude_payment_method
* invoiceboo_order_totals
* invoiceboo_order_note
* invoiceboo_output_args
* invoiceboo_pdf_active_font
* invoiceboo_pdf_title_font
* invoiceboo_pdf_output_destination
* invoiceboo_pdf_filename_prefix
* invoiceboo_pdf_filename_sufix
* invoiceboo_page_title
* invoiceboo_authorization_required

== Screenshots ==

1. InvoiceBoo settings
2. Customer Invoice update and download page
3. Add Business details to Invoice after placing the order
4. Downloaded PDF Invoice example
5. WooCommerce My Account > Orders page with Invoice buttons
6. WooCommerce Thank you page with Download Invoice button

== Changelog ==

= 1.1 =
* Added Japanese and Arabic font options

= 1.0 =
* Birthday