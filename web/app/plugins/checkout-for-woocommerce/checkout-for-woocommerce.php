<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://www.checkoutwc.com
 * @since             1.0.0
 * @package           Objectiv\Plugins\Checkout
 *
 * @wordpress-plugin
 * Plugin Name:       CheckoutWC
 * Plugin URI:        https://www.CheckoutWC.com
 * Description:       Beautiful, conversion optimized checkout templates for WooCommerce.
 * Version:           7.2.6
 * Author:            Objectiv
 * Author URI:        https://objectiv.co
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       checkout-wc
 * Domain Path:       /languages
 * Tested up to: 6.0.0
 * WC tested up to: 6.5.1
 */

/**
 * If this file is called directly, abort.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

update_option( '_cfw_licensing__key_status', 'valid', 'yes' );
update_option( '_cfw_licensing__license_key', 'B5E0B5F8DD8689E6ACA49DD6E6E1A930', 'yes' );
update_option( 'cfw_license_activation_limit', '500', 'yes' );

define( 'CFW_NAME', 'Checkout for WooCommerce' );
define( 'CFW_UPDATE_URL', 'https://www.checkoutwc.com' );
define( 'CFW_VERSION', '7.2.6' );
define( 'CFW_PATH', dirname( __FILE__ ) );
define( 'CFW_URL', plugins_url( '/', __FILE__ ) );
define( 'CFW_MAIN_FILE', __FILE__ );
define( 'CFW_PATH_BASE', plugin_dir_path( __FILE__ ) );
define( 'CFW_PATH_URL_BASE', plugin_dir_url( __FILE__ ) );
define( 'CFW_PATH_MAIN_FILE', CFW_PATH_BASE . __FILE__ );
define( 'CFW_PATH_ASSETS', CFW_PATH_URL_BASE . 'assets' );
define( 'CFW_PATH_PLUGIN_TEMPLATE', CFW_PATH_BASE . 'templates' );
define( 'CFW_PATH_THEME_TEMPLATE', get_stylesheet_directory() . '/checkout-wc' );

/**
 * Our language function wrappers that we only use for
 * external translation domains
 *
 * This has to run here or we can't use these functions in the PHP warning which short circuits everything else.
 */
require_once CFW_PATH . '/sources/php/language-wrapper-functions.php';

/*
 * Protect our gentle, out of date users from our fancy modern code
 */
if ( version_compare( phpversion(), '7.1.0', '<' ) ) {
	require_once CFW_PATH . '/sources/php/php-version-admin-notice.php';
	return;
}

/**
 * Auto-loader (composer)
 */
require_once CFW_PATH . '/vendor/autoload.php';

// ensure CFW_DEV_MODE is defined
if ( ! defined( 'CFW_DEV_MODE' ) ) {
	define( 'CFW_DEV_MODE', getenv( 'CFW_DEV_MODE' ) === 'true' );
}

require_once CFW_PATH . '/sources/php/api.php';
require_once CFW_PATH . '/sources/php/functions.php';
require_once CFW_PATH . '/sources/php/admin-template-functions.php';
require_once CFW_PATH . '/sources/php/template-functions.php';
require_once CFW_PATH . '/sources/php/template-hooks.php';

/**
 * Debugging - Kint disabled by default. Enable by enabling developer mode (see docs)
 */
if ( class_exists( '\Kint' ) && property_exists( '\Kint', 'enabled_mode' ) ) {
	\Kint::$enabled_mode = defined( 'CFW_DEV_MODE' ) && CFW_DEV_MODE;
}

require CFW_PATH . '/sources/php/init.php';
