<?php
/**
 * Cache Functions
 *
 * Functions for disabling cache and minification
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable caching on our page
 *
 * @since 1.0
 * @return void
 */
function ph_disable_caching() {
	if ( is_singular( 'ph-project' ) || is_singular( 'ph-website' ) ) {
		define( 'DONOTCACHEPAGE', true );
		define( 'DONOTCACHEDB', true );
		define( 'DONOTMINIFY', true );
		define( 'DONOTCDN', true );
		define( 'DONOTCACHCEOBJECT', true );
	}
}

add_action( 'init', 'ph_disable_caching', 0 );

/**
 * Disable Better WordPress Minify
 * @since 1.0
 * @return bool
 */
function ph_disable_minify() {
	if ( is_singular( 'ph-project' ) ) {
		return false;
	}

	return true;
}

add_filter( 'bwp_minify_is_loadable', 'ph_disable_minify' );
