<?php
/**
 * Plugin Name:         Uncanny Automator Pro
 * Description:         Adds anonymous recipes, advanced log filtering, automatic log pruning and many more triggers and actions to Uncanny Automator!
 * Author:              Uncanny Owl
 * Author URI:          https://www.uncannyowl.com/
 * Plugin URI:          https://automatorplugin.com/
 * Text Domain:         uncanny-automator-pro
 * Domain Path:         /languages
 * License:             GPLv3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 * Version:             4.1
 * Requires at least:   5.3
 * Requires PHP:        5.6
 */

use Uncanny_Automator\Automator_Functions;
use Uncanny_Automator_Pro\Automator_Pro_Load;

if ( ! defined( 'AUTOMATOR_PRO_PLUGIN_VERSION' ) ) {
	/**
	 * Specify Automator Pro version.
	 */
	define( 'AUTOMATOR_PRO_PLUGIN_VERSION', '4.1' );
}

if ( ! defined( 'AUTOMATOR_PRO_FILE' ) ) {
	/**
	 * Specify Automator Pro base file.
	 */
	define( 'AUTOMATOR_PRO_FILE', __FILE__ );
}


// Add other global variables for plugin.
require __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'globals.php';
// Add InitializePlugin class for other plugins checking for version.
require __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'legacy.php';

/**
 * If Automator function is not defined AND Automator < 3.0, add Automator fallback
 *
 * @return Automator_Functions
 */
function Automator_Pro() { //phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	if ( defined( 'AUTOMATOR_PLUGIN_VERSION' ) && function_exists( 'Automator' ) ) {
		return Automator();
	}
	// this global variable stores many functions that can be used for integrations, triggers, actions, and closures.
	global $uncanny_automator;

	return $uncanny_automator;
}

// Include the Automator_Load class and kickstart Automator Pro.
if ( ! class_exists( '\Uncanny_Automator_Pro\Automator_Pro_Load', false ) ) {
	include_once UAPro_ABSPATH . 'src/class-automator-pro-load.php';
}

Automator_Pro_Load::get_instance();
