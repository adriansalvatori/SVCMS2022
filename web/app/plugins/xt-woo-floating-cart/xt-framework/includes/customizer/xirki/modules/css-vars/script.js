/* global xirkiCssVarFields */
var xirkiCssVars = {

	/**
	 * Get styles.
	 *
	 * @since 3.0.28
	 * @returns {Object}
	 */
	getStyles: function() {
		var style     = jQuery( '#xirki-css-vars' ),
			styles    = style.html().replace( ':root{', '' ).replace( '}', '' ).split( ';' ),
			stylesObj = {};

		// Format styles as a object we can then tweak.
		_.each( styles, function( style ) {
			style = style.split( ':' );
			if ( style[0] && style[1] ) {
				stylesObj[ style[0] ] = style[1];
			}
		} );
		return stylesObj;
	},

	/**
	 * Builds the styles from an object.
	 *
	 * @since 3.0.28
	 * @param {Object} vars - The vars.
	 * @returns {string}
	 */
	buildStyle: function( vars ) {
		var style = '';

		_.each( vars, function( val, name ) {
			style += name + ':' + val + ';';
		} );
		return ':root{' + style + '}';
	}
};

jQuery( document ).ready( function() {
	_.each( xirkiCssVarFields, function( field ) {
		wp.customize( field.settings, function( value ) {
			value.bind( function( newVal ) {
				var styles = xirkiCssVars.getStyles();

				_.each( field.css_vars, function( cssVar ) {
					if ( 'object' === typeof newVal ) {
						if ( cssVar[2] && newVal[ cssVar[2] ] ) {
							styles[ cssVar[0] ] = cssVar[1].replace( '$', newVal[ cssVar[2] ] );
						}
					} else {
						styles[ cssVar[0] ] = cssVar[1].replace( '$', newVal );
					}
				} );
				jQuery( '#xirki-css-vars' ).html( xirkiCssVars.buildStyle( styles ) );
			} );
		} );
	} );
} );

wp.customize.bind( 'preview-ready', function() {
	wp.customize.preview.bind( 'active', function() {
		_.each( xirkiCssVarFields, function( field ) {
			wp.customize( field.settings, function( value ) {
				var styles = xirkiCssVars.getStyles(),
					newVal = window.parent.wp.customize( value.id ).get();
				_.each( field.css_vars, function( cssVar ) {
					if ( 'object' === typeof newVal ) {
						if ( cssVar[2] && newVal[ cssVar[2] ] ) {
							styles[ cssVar[0] ] = cssVar[1].replace( '$', newVal[ cssVar[2] ] );
						}
					} else {
						styles[ cssVar[0] ] = cssVar[1].replace( '$', newVal );
					}
				} );
				jQuery( '#xirki-css-vars' ).html( xirkiCssVars.buildStyle( styles ) );
			} );
		} );
	} );
} );
