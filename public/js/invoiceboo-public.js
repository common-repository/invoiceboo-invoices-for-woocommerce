(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	jQuery(document).ready(function(){

		loadBillingData();

		function openInvoiceForm( e ){

			e.preventDefault();
			MicroModal.show( 'invoiceboo-modal', {
				onClose(){
					loadBillingData();
				}
			});

		}

		function loadBillingData(){ //Add entered billing data to Invoice (for visual purposes only)
			
			var billing_placeholder = jQuery( '#billing-placeholder' );
			var company_details = jQuery( '#customer-company-details' );

			if( billing_placeholder.length == 0 || company_details.length == 0 ) return; //Exit if either one of these fields exist

			billing_placeholder.html( company_details.val().replace( /\n/g, '<br>' ) );
			
			if( jQuery( '#customer-company-details' ).val().length > 0 ){
				billing_placeholder.css( { display: 'block' } );
			}
			
		}

		jQuery( '.invoiceboo-open-form' ).on( 'click', openInvoiceForm );
		
	});

})( jQuery );