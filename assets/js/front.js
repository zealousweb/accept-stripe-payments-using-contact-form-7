( function( $ ) {

	document.addEventListener('wpcf7mailsent', function( event ) {

		var contactform_id = event.detail.contactFormId;
		var redirection_url = event.detail.apiResponse.redirection_url;
		if ( redirection_url != '' && redirection_url != undefined ) {
			window.location = redirection_url;
		}

	} );

	jQuery( document ).ready( function() {
		jQuery( '.wpcf7 form.wpcf7-form.cf7sa' ).off( 'submit' );
	});

} )( jQuery );
