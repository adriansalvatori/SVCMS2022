/* global xirkiBranding */
jQuery( document ).ready( function() {

	'use strict';

	if ( '' !== xirkiBranding.logoImage ) {
		jQuery( 'div#customize-info .preview-notice' ).replaceWith( '<img src="' + xirkiBranding.logoImage + '">' );
	}

	if ( '' !== xirkiBranding.description ) {
		jQuery( 'div#customize-info > .customize-panel-description' ).replaceWith( '<div class="customize-panel-description">' + xirkiBranding.description + '</div>' );
	}

} );
