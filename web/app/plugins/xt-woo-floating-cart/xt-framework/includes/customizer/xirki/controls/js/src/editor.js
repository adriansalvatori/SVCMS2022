/* global tinyMCE */
wp.customize.controlConstructor['xirki-editor'] = wp.customize.xirkiDynamicControl.extend( {

	initXirkiControl: function() {

		var control = this,
			element = control.container.find( 'textarea' ),
			id      = 'xirki-editor-' + control.id.replace( '[', '' ).replace( ']', '' ),
			editor;

		if ( wp.editor && wp.editor.initialize ) {
			wp.editor.initialize( id, {
				tinymce: {
					wpautop: true
				},
				quicktags: true,
				mediaButtons: true
			} );
		}

		editor = tinyMCE.get( id );

		if ( editor ) {
			editor.onChange.add( function( ed ) {
				var content;

				ed.save();
				content = editor.getContent();
				element.val( content ).trigger( 'change' );
				wp.customize.instance( control.id ).set( content );
			} );
		}
	}
} );
