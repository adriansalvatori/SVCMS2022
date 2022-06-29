<?php
/**
 * Plugin Name: AutomateWoo - Birthdays Add-on
 * Plugin URI: https://woocommerce.com/products/automatewoo-birthdays/
 * Description: Birthdays add-on for AutomateWoo.
 * Version: 1.3.8
 * Author: WooCommerce
 * Author URI: https://woocommerce.com
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Text Domain: automatewoo-birthdays
 *
 * WC requires at least: 4.3
 * WC tested up to: 6.6
 * Woo: 4871155:978a1f1867e6a61a4e0b90f11f769ca4
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package AutomateWoo/Birthdays
 */

defined( 'ABSPATH' ) || exit;

/**
 * AW_Birthdays_Loader class.
 */
class AW_Birthdays_Loader {

	/**
	 * Plugin data.
	 *
	 * @var stdClass
	 */
	public static $data;

	/**
	 * Array of load errors.
	 *
	 * @var array
	 */
	public static $errors = array();

	/**
	 * Init the plugin loader.
	 */
	public static function init() {
		self::$data                          = new stdClass();
		self::$data->id                      = 'automatewoo-birthdays';
		self::$data->name                    = ''; // Replaced with translatable string on init hook
		self::$data->version                 = '1.3.8'; // WRCS: DEFINED_VERSION.
		self::$data->file                    = __FILE__;
		self::$data->min_automatewoo_version = '5.1.0';

		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'load' ) );

		// Load translations even if plugin requirements aren't met
		add_action( 'init', array( __CLASS__, 'load_textdomain' ), 5 );

		add_filter( 'woocommerce_translations_updates_for_automatewoo-birthdays', '__return_true' );

		register_activation_hook( self::$data->file, [ __CLASS__, 'plugin_activate' ] );
	}

	/**
	 * Loads the plugin if no errors.
	 */
	public static function load() {
		self::check();
		if ( empty( self::$errors ) ) {
			include 'includes/birthdays-addon.php';

			if ( 'yes' === get_option( self::$data->id . '-activated' ) ) {
				add_action( 'automatewoo_loaded', [ __CLASS__, 'addon_activate' ] );
			}
		}
	}

	/**
	 * Loads the plugin textdomain.
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'automatewoo-birthdays', false, 'automatewoo-birthdays/languages' );
	}

	/**
	 * Checks if the plugin can be loaded.
	 */
	protected static function check() {
		$inactive_text = '<strong>' . sprintf( __( '%s is inactive.', 'automatewoo-birthdays' ), __( 'AutomateWoo - Birthdays', 'automatewoo-birthdays' ) ) . '</strong>';

		if ( ! self::is_automatewoo_active() ) {
			self::$errors[] = sprintf( __( '%s The plugin requires AutomateWoo to be installed and activated.', 'automatewoo-birthdays' ), $inactive_text );
		} elseif ( ! self::is_automatewoo_version_ok() ) {
			self::$errors[] = sprintf( __( '%1$s The plugin requires AutomateWoo version %2$s or newer.', 'automatewoo-birthdays' ), $inactive_text, self::$data->min_automatewoo_version );
		} elseif ( ! self::is_automatewoo_directory_name_ok() ) {
			self::$errors[] = sprintf( __( '%s AutomateWoo plugin directory name is not correct.', 'automatewoo-birthdays' ), $inactive_text );
		}
	}

	/**
	 * Checks if AutomateWoo is active.
	 *
	 * @return bool
	 */
	protected static function is_automatewoo_active() {
		return function_exists( 'AW' );
	}

	/**
	 * Checks if the version of AutomateWoo is compatible.
	 *
	 * @return bool
	 */
	protected static function is_automatewoo_version_ok() {
		if ( ! function_exists( 'AW' ) ) {
			return false;
		}
		return version_compare( AW()->version, self::$data->min_automatewoo_version, '>=' );
	}

	/**
	 * Checks if AutomateWoo is in the correct location.
	 *
	 * @return bool
	 */
	protected static function is_automatewoo_directory_name_ok() {
		$active_plugins = (array) get_option( 'active_plugins', [] );
		return in_array( 'automatewoo/automatewoo.php', $active_plugins, true ) || array_key_exists( 'automatewoo/automatewoo.php', $active_plugins );
	}

	/**
	 * Outputs any errors as admin notices.
	 */
	public static function admin_notices() {
		if ( empty( self::$errors ) ) {
			return;
		}
		echo '<div class="notice notice-error"><p>';
		echo wp_kses_post( implode( '<br>', self::$errors ) );
		echo '</p></div>';
	}

	/**
	 * Save the activation event to activate on the next request.
	 *
	 * @since %VERSION%
	 */
	public static function plugin_activate() {
		update_option( self::$data->id . '-activated', 'yes' );
	}

	/**
	 * Call activation code in the addon.
	 *
	 * @since %VERSION%
	 */
	public static function addon_activate() {
		AW_Birthdays()->activate();
		update_option( self::$data->id . '-activated', 'no' );
	}
}

AW_Birthdays_Loader::init();
