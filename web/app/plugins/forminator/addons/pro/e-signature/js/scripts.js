function base64DecodeUnicode(str) {
    // Convert Base64 encoded bytes to percent-encoding, and then get the original string.
    percentEncodedStr = atob(str).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join('');


    return decodeURIComponent(percentEncodedStr);
}

function forminatorSignInit() {
	window.signObjects = [];
	jQuery ( ".forminator-signature" ).each( function() {
		var $el = jQuery( this );
		var id = $el.find( ".forminator-signature-canvas" ).attr( "id" );

		window.signObjects.push( id );
	} );

	jQuery ( ".forminator-signature" ).each( function() {
		var $el = jQuery( this );
		var id = $el.find( ".forminator-signature-canvas" ).attr( "id" );

		window[ 'loadSignField_' + id ]();
	} );

	jQuery( '.forminator-button.forminator-button-next').on( 'click', debounce( function() {
		if ( jQuery( ".forminator-signature--container" ).length > 0 ) {
			forminatorSignatureResize();
		}
	}, 250 ) );
}

// Resize signature
function forminatorSignatureResize() {
	if ( 'undefined' === typeof window.signObjects ) {
		return;
	}
	jQuery ( ".forminator-signature" ).each( function() {
		var $el = jQuery( this );
		var id = $el.find( ".forminator-signature-canvas" ).attr( "id" );

		if ( typeof window[ 'obj' + id ] !== "undefined" ) {
			var element = $el.closest( '.forminator-field' );
			var width = element.css( 'width' ).replace( 'px', '' );
			var height = $el.data( 'elementheight' );

			$el.find( "div[id$='_toolbar']" ).each( function( index, toolbar ) {
				if ( index > 0 ) {
					jQuery( toolbar ).remove();
				}
			});

			var data = false,
			$fieldVal = $el.find( 'input[name$="_data"]:eq( 0 )' ).val();

			if ( $fieldVal ) {
				data = base64DecodeUnicode( $fieldVal );
			}

			jQuery( document ).on( "forminator.front.loaded", function() {
				jQuery( window ).trigger( "resize" );
			});

			$el.on( "mouseover", function() {
				// Set hover class.
				jQuery( this ).closest( ".forminator-field" ).addClass( "forminator-is_hover" );

				jQuery( "#" + id ).on( "mousedown", function() {
					if ( "" !== jQuery( "#" + id + "_data" ).val() ) {
						jQuery( this ).closest( ".forminator-field" ).addClass( "forminator-is_filled" );
					}
				});
			}).on( "mouseleave", function() {
				// Remove hover class.
				jQuery( this ).closest( ".forminator-field" ).removeClass( "forminator-is_hover" );

				// Check if field has content.
				if ( "" === jQuery( "#" + id + "_data" ).val() ) {

					// Remove filled class.
					jQuery( this ).closest( ".forminator-field" ).removeClass( "forminator-is_filled" );
				} else {

					// Add filled class.
					jQuery( this ).closest( ".forminator-field" ).addClass( "forminator-is_filled" );
				}
			});

			// Trigger changes in mobile touchend event
			$el.find( "#" + id ).on( "touchend", function() {
				var sigCanvas = jQuery( this );

				setTimeout( function() {

					// Trigger change.
					if ( "" !== jQuery( "#" + id + "_data" ).val() ) {
						sigCanvas.closest( ".forminator-field-signature" ).change();
						sigCanvas.closest( ".forminator-field" ).addClass( "forminator-is_filled" );
					}

				}, 50 );
			});

			if ( width > 0 ) {
				window.ResizeSignature( id, width, height );
				window.ClearSignature( id );
			}

			if ( data ) {
				window.LoadSignature( id, data, 1 );
			}

			// Remove the init-wall to allow signing
			setTimeout( function() {
				jQuery( ".forminator-signature" ).each( function() {
					jQuery( this ).find( '.init-wall' ).remove();
				} );
			}, 1000 );
		}
	} );
}

window.debounce = function (func, wait, immediate) {
     var timeout;

     return function() {
         var context = this, args = arguments;
         var later = function() {
                 timeout = null;
                 if (!immediate) func.apply(context, args);
         };
         var callNow = immediate && !timeout;
         clearTimeout(timeout);
         timeout = setTimeout(later, wait);
         if (callNow) func.apply(context, args);
     };
};

// Trigger resize on gutenberg block
function forminatorLoadGutenberg() {
	if ( jQuery( ".forminator-signature" ).length === 0 ) {
		setTimeout( function () {
			forminatorLoadGutenberg();
		}, 300 );
	} else {
		forminatorSignInit();
		forminatorSignatureResize();

		jQuery( '.forminator-custom-form' ).on( 'forminator:field:condition:toggled', debounce( function() {
			if ( jQuery( this ).find( ".forminator-signature--container" ).length > 0 ) {
				forminatorSignatureResize();
			}
		}, 250 ) );
	}
}

// Resize signature field on window resize.
jQuery( window ).on( "resize", function() {
	forminatorSignatureResize();
});

jQuery( window ).on( "load", function() {
	forminatorSignInit();
	forminatorSignatureResize();

	jQuery( '.forminator-custom-form' ).on( 'forminator:field:condition:toggled', debounce( function() {
		if ( jQuery( this ).find( ".forminator-signature--container" ).length > 0 ) {
			forminatorSignatureResize();
		}
	}, 250 ) );
});

// Initialize signature field inside Preview mode for admins.
jQuery( function () {
	if ( jQuery("body").hasClass("wp-admin") ) {
		forminatorLoadGutenberg();
	}

	// Prevent signatures initially
	jQuery( ".forminator-signature" ).each( function() {
		jQuery( this ).prepend( '<div class="init-wall" style="position:absolute; width:100%; height:100%; top:0;left:0;right:0;bottom:0;"></div>' );
	} );
});

jQuery( document ).on( 'forminator.gutenberg.form.loaded', function( id ) {
	forminatorLoadGutenberg();
} );

jQuery( document ).on( 'after.load.forminator', function( id ) {
	forminatorLoadGutenberg();
} );
