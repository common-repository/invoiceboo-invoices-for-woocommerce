(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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

		jQuery('.invoiceboo-color-picker').wpColorPicker(); //Activating color picker
		jQuery('.invoiceboo-multiple').selectize({ //Prepare custom select for selecting multiple items like categories
			plugins: ['remove_button']
		});

		function addLoadingIndicator(){ //Adding loading indicator once Submit button pressed

			jQuery( this ).addClass( 'invoiceboo-loading' );

		}

		function addCheckedClass(){ //Adding checked state to the parent in case if the Toggle checkbox is turned on
			
			if( jQuery( this ).find( 'input' ).prop( 'checked' ) ){
				jQuery( this ).parent().addClass( 'invoiceboo-checked' ); //Necessary to show/hide small text additions
				jQuery( this ).parent().parent().addClass( 'invoiceboo-checked-parent' );

			}else{
				jQuery( this ).parent().removeClass( 'invoiceboo-checked' ); //Necessary to show/hide small text additions
				jQuery( this ).parent().parent().removeClass( 'invoiceboo-checked-parent' );
			}

		}

		function getPreviewFields( preview_form_name ){ //Retrieving fields that are used for Preview generation

			return jQuery( preview_form_name + '#invoiceboo-company-details, ' + preview_form_name + '.invoiceboo-radiobutton, ' + preview_form_name + '#invoiceboo-main-color, ' + preview_form_name + '#invoiceboo-secondary-color, ' + preview_form_name + '#invoiceboo-invoice-logo' );

		}

		function previewInvoice( e ){ //Preview Invoice

			var button = jQuery( this );
			var data = {
				nonce			: button.data( 'nonce' ),
				action			: button.data( 'action' )
			};

			jQuery.post( invoiceboo_admin_data.ajaxurl, data, function( response ){

				button.removeClass( 'invoiceboo-loading' );

				if ( response.success == true ){
					var preview_form_name = '#invoiceboo-preview-form';
					var preview_form = jQuery( preview_form_name );
					var preview_fields = getPreviewFields( '' );

					preview_fields.clone( true ).appendTo( preview_form ); //Cloning input fields to hidden Preview form to be able to retrieve data that has not been submited yet
					preview_form.submit(); //Submit Preview form
					preview_fields = getPreviewFields( preview_form_name + ' ' );
					preview_fields.remove(); //Removing cloned fields after Preview has been generated
				}

			});

		}

		function addCustomImage( e ){ //Adding a custom image

			e.preventDefault();
			var button = jQuery( this ),
			custom_uploader = wp.media( {
				title: invoiceboo_admin_data.logo_upload_button.label,
				library : {
					type : 'image'
				},
				button: {
					text: invoiceboo_admin_data.logo_upload_button.insert_label
				},
				multiple: false
			} ).on( 'select', function(){ //It also has "open" and "close" events
				var automation = button.data( 'automation' ); //Number ID that has been triggered
				var attachment = custom_uploader.state().get( 'selection' ).first().toJSON();
				var image_url = attachment.url;

 				if( typeof attachment.sizes.thumbnail !== "undefined" ){ //Checking if the selected image has a thumbnail image size
 					var thumbnail = attachment.sizes.thumbnail.url;
 					image_url = thumbnail;
 				}
				button.html( '<img src="' + image_url + '">' );

				if(typeof automation !== 'undefined'){ //If multiple items exist on the page
					var input_field = jQuery( '#invoiceboo-invoice-logo-' + automation );
					var remove_button = jQuery( '#invoiceboo-remove-invoice-logo-' + automation );

				}else{ //In case of a single item on page
					var input_field = jQuery( '#invoiceboo-invoice-logo' );
					var remove_button = jQuery( '#invoiceboo-remove-invoice-logo' );
				}
				input_field.val(attachment.id);
				remove_button.show();

			} ).open();

		}

		function removeCustomImage( e ){ //Removing Custom image

			e.preventDefault();
			var button = jQuery( this ).hide();
			var automation = button.data( 'automation' ); //Number ID that has been triggered

			if(typeof automation !== 'undefined'){ //If multiple items exist on the page
				var input_field = jQuery( '#invoiceboo-invoice-logo-' + automation );
				var add_button = jQuery( '#invoiceboo-upload-invoice-logo-' + automation );

			}else{ //In case of a single item on page
				var input_field = jQuery( '#invoiceboo-invoice-logo' );
				var add_button = jQuery( '#invoiceboo-upload-invoice-logo' );
			}

			input_field.val( '' );
			add_button.html( '<input type="button" class="invoiceboo-button button-secondary button" value="' + invoiceboo_admin_data.logo_upload_button.label + '">' );

		};

		function addActiveClass(){ //Adding active class when changing radio button

			jQuery( this ).siblings().removeClass( 'invoiceboo-radio-active' );
			jQuery( this ).addClass( 'invoiceboo-radio-active' );

		}

		jQuery( '.invoiceboo-progress' ).on( 'click', addLoadingIndicator );
		jQuery( '.invoiceboo-toggle .invoiceboo-control-visibility' ).on( 'click', addCheckedClass );
		jQuery( '.invoiceboo-preview' ).on( 'click', previewInvoice );
		jQuery( '.invoiceboo-template' ).on( 'click', addActiveClass );
		jQuery( '.invoiceboo-upload-image' ).on( 'click', addCustomImage );
		jQuery( '.invoiceboo-remove-image' ).on( 'click', removeCustomImage );

	})

})( jQuery );
