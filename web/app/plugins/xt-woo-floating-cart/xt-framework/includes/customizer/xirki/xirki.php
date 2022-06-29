<?php
/**
 * Plugin Name:   Xirki Customizer Framework
 * Plugin URI:    https://xplodedthemes.com
 * Description:   The Ultimate WordPress Customizer Framework
 * Author:        XplodedThemes
 * Author URI:    https://xplodedthemes.com/
 * Version:       3.1.9
 * Text Domain:   xirki
 * Requires WP:   4.9
 * Requires PHP:  5.3
 * GitHub Plugin URI: xplodedthemes/xirki
 * GitHub Plugin URI: https://github.com/xplodedthemes/xirki
 *
 * @package   Xirki
 * @category  Core
 * @author    XplodedThemes (@XplodedThemes)
 * @copyright Copyright (c) 2020, XplodedThemes
 * @license   https://opensource.org/licenses/MIT
 * @since     1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// No need to proceed if Xirki already exists.
if ( class_exists( 'Xirki' ) ) {
	return;
}

// Include the autoloader.
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'class-xirki-autoload.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude
new Xirki_Autoload();

if ( ! defined( 'XIRKI_PLUGIN_FILE' ) ) {
	define( 'XIRKI_PLUGIN_FILE', __FILE__ );
}

// Define the XIRKI_VERSION constant.
if ( ! defined( 'XIRKI_VERSION' ) ) {
	define( 'XIRKI_VERSION', '3.1.9' );
}

// Make sure the path is properly set.
Xirki::$path = wp_normalize_path( dirname( __FILE__ ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
Xirki_Init::set_url();

new Xirki_Controls();

if ( ! function_exists( 'Xirki' ) ) {
	/**
	 * Returns an instance of the Xirki object.
	 */
	function xirki() {
		$xirki = Xirki_Toolkit::get_instance();
		return $xirki;
	}
}

// Start Xirki.
global $xirki;
$xirki = xirki();

// Instantiate the modules.
$xirki->modules = new Xirki_Modules();


// Instantiate classes.
new Xirki();
new Xirki_L10n();

// Include deprecated functions & methods.
require_once wp_normalize_path( dirname( __FILE__ ) . '/deprecated/deprecated.php' ); // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude

// Include the ariColor library.
require_once wp_normalize_path( dirname( __FILE__ ) . '/lib/class-aricolor.php' ); // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude

// Add an empty config for global fields.
Xirki::add_config( '' );

$custom_config_path = dirname( __FILE__ ) . '/custom-config.php';
$custom_config_path = wp_normalize_path( $custom_config_path );
if ( file_exists( $custom_config_path ) ) {
	require_once $custom_config_path; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude
}

// Add upgrade notifications.
require_once wp_normalize_path( dirname( __FILE__ ) . '/upgrade-notifications.php' ); // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude

/**
 * To enable tests, add this line to your wp-config.php file (or anywhere alse):
 * define( 'XIRKI_TEST', true );
 *
 * Please note that the example.php file is not included in the wordpress.org distribution
 * and will only be included in dev versions of the plugin in the github repository.
 */
if ( defined( 'XIRKI_TEST' ) && true === XIRKI_TEST && file_exists( dirname( __FILE__ ) . '/example.php' ) ) {
	include_once dirname( __FILE__ ) . '/example.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude
}
