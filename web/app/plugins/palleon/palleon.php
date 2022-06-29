<?php
/**
 * Plugin Name: Palleon
 * Plugin URI: https://palleon.website
 * Description: Photo Editor For WordPress
 * Version: 2.2
 * Author: ThemeMasters
 * Author URI: http://codecanyon.net/user/egemenerd
 * License: http://codecanyon.net/licenses
 * Text Domain: palleon
 * Domain Path: /languages
 *
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PALLEON_PLUGIN_URL' ) ) {
	define( 'PALLEON_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'PALLEON_SOURCE_URL' ) ) {
	define( 'PALLEON_SOURCE_URL', 'https://www.thememasters.club/palleon/' );
}

/* ---------------------------------------------------------
Custom Metaboxes - github.com/WebDevStudios/CMB2
----------------------------------------------------------- */

// Check for PHP version
$palleondir = ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) ? __DIR__ : dirname( __FILE__ );

if ( file_exists(  $palleondir . '/cmb2/init.php' ) ) {
    require_once($palleondir . '/cmb2/init.php');
} elseif ( file_exists(  $palleondir . '/CMB2/init.php' ) ) {
    require_once($palleondir . '/CMB2/init.php');
}

include_once('settingsClass.php');

/* ---------------------------------------------------------
Include required files
----------------------------------------------------------- */

include_once('library.php');
include_once('mainClass.php');
include_once('pexels.php');
include_once('customTemplates.php');
include_once('customFonts.php');