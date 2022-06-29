<?php
// phpcs:ignoreFile

add_filter( 'xirki_config', function( $args ) {
	return apply_filters( 'xirki/config', $args );
}, 99 );

add_filter( 'xirki_control_types', function( $args ) {
	return apply_filters( 'xirki/control_types', $args );
}, 99 );

add_filter( 'xirki_section_types', function( $args ) {
	return apply_filters( 'xirki/section_types', $args );
}, 99 );

add_filter( 'xirki_section_types_exclude', function( $args ) {
	return apply_filters( 'xirki/section_types/exclude', $args );
}, 99 );

add_filter( 'xirki_control_types_exclude', function( $args ) {
	return apply_filters( 'xirki/control_types/exclude', $args );
}, 99 );

add_filter( 'xirki_controls', function( $args ) {
	return apply_filters( 'xirki/controls', $args );
}, 99 );

add_filter( 'xirki_fields', function( $args ) {
	return apply_filters( 'xirki/fields', $args );
}, 99 );

add_filter( 'xirki_modules', function( $args ) {
	return apply_filters( 'xirki/modules', $args );
}, 99 );

add_filter( 'xirki_panel_types', function( $args ) {
	return apply_filters( 'xirki/panel_types', $args );
}, 99 );

add_filter( 'xirki_setting_types', function( $args ) {
	return apply_filters( 'xirki/setting_types', $args );
}, 99 );

add_filter( 'xirki_variable', function( $args ) {
	return apply_filters( 'xirki/variable', $args );
}, 99 );

add_filter( 'xirki_values_get_value', function( $arg1, $arg2 ) {
	return apply_filters( 'xirki/values/get_value', $arg1, $arg2 );
}, 99, 2 );

add_action( 'init', function() {
	$config_ids = Xirki_Config::get_config_ids();
	global $xirki_deprecated_filters_iteration;
	foreach ( $config_ids as $config_id ) {
		foreach( array(
			'/dynamic_css',
			'/output/control-classnames',
			'/css/skip_hidden',
			'/styles',
			'/output/property-classnames',
			'/webfonts/skip_hidden',
		) as $filter_suffix ) {
			$xirki_deprecated_filters_iteration = array( $config_id, $filter_suffix );
			add_filter( "xirki_{$config_id}_{$filter_suffix}", function( $args ) {
				global $xirki_deprecated_filters_iteration;
				$xirki_deprecated_filters_iteration[1] = str_replace( '-', '_', $xirki_deprecated_filters_iteration[1] );
				return apply_filters( "xirki/{$xirki_deprecated_filters_iteration[0]}/{$xirki_deprecated_filters_iteration[1]}", $args );
			}, 99 );
			if ( false !== strpos( $xirki_deprecated_filters_iteration[1], '-' ) ) {
				$xirki_deprecated_filters_iteration[1] = str_replace( '-', '_', $xirki_deprecated_filters_iteration[1] );
				add_filter( "xirki_{$config_id}_{$filter_suffix}", function( $args ) {
					global $xirki_deprecated_filters_iteration;
					$xirki_deprecated_filters_iteration[1] = str_replace( '-', '_', $xirki_deprecated_filters_iteration[1] );
					return apply_filters( "xirki/{$xirki_deprecated_filters_iteration[0]}/{$xirki_deprecated_filters_iteration[1]}", $args );
				}, 99 );
			}
		}
	}
}, 99 );

add_filter( 'xirki_enqueue_google_fonts', function( $args ) {
	return apply_filters( 'xirki/enqueue_google_fonts', $args );
}, 99 );

add_filter( 'xirki_styles_array', function( $args ) {
	return apply_filters( 'xirki/styles_array', $args );
}, 99 );

add_filter( 'xirki_dynamic_css_method', function( $args ) {
	return apply_filters( 'xirki/dynamic_css/method', $args );
}, 99 );

add_filter( 'xirki_postmessage_script', function( $args ) {
	return apply_filters( 'xirki/postmessage/script', $args );
}, 99 );

add_filter( 'xirki_fonts_all', function( $args ) {
	return apply_filters( 'xirki/fonts/all', $args );
}, 99 );

add_filter( 'xirki_fonts_standard_fonts', function( $args ) {
	return apply_filters( 'xirki/fonts/standard_fonts', $args );
}, 99 );

add_filter( 'xirki_fonts_google_fonts', function( $args ) {
	return apply_filters( 'xirki/fonts/google_fonts', $args );
}, 99 );

add_filter( 'xirki_googlefonts_load_method', function( $args ) {
	return apply_filters( 'xirki/googlefonts_load_method', $args );
}, 99 );
