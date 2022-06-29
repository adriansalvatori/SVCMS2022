<?php
// phpcs:ignoreFile

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'xirki_get_option' ) ) {
	/**
	 * Get the value of a field.
	 * This is a deprecated function that we used when there was no API.
	 * Please use the Xirki::get_option() method instead.
	 * Documentation is available for the new method on https://github.com/xplodedthemes/xirki/wiki/Getting-the-values
	 *
	 * @return mixed
	 */
	function xirki_get_option( $option = '' ) {
		_deprecated_function( __FUNCTION__, '1.0.0', sprintf( esc_html__( '%1$s or %2$s', 'xirki' ), 'get_theme_mod', 'get_option' ) );
		return Xirki::get_option( '', $option );
	}
}

if ( ! function_exists( 'xirki_sanitize_hex' ) ) {
	function xirki_sanitize_hex( $color ) {
		_deprecated_function( __FUNCTION__, '1.0.0', 'ariColor::newColor( $color )->toCSS( \'hex\' )' );
		return Xirki_Color::sanitize_hex( $color );
	}
}

if ( ! function_exists( 'xirki_get_rgb' ) ) {
	function xirki_get_rgb( $hex, $implode = false ) {
		_deprecated_function( __FUNCTION__, '1.0.0', 'ariColor::newColor( $color )->toCSS( \'rgb\' )' );
		return Xirki_Color::get_rgb( $hex, $implode );
	}
}

if ( ! function_exists( 'xirki_get_rgba' ) ) {
	function xirki_get_rgba( $hex = '#fff', $opacity = 100 ) {
		_deprecated_function( __FUNCTION__, '1.0.0', 'ariColor::newColor( $color )->toCSS( \'rgba\' )' );
		return Xirki_Color::get_rgba( $hex, $opacity );
	}
}

if ( ! function_exists( 'xirki_get_brightness' ) ) {
	function xirki_get_brightness( $hex ) {
		_deprecated_function( __FUNCTION__, '1.0.0', 'ariColor::newColor( $color )->lightness' );
		return Xirki_Color::get_brightness( $hex );
	}
}

if ( ! function_exists( 'Xirki' ) ) {
	function Xirki() {
		return xirki();
	}
}
