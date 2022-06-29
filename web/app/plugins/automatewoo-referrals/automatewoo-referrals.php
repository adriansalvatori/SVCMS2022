<?php
/**
 * Plugin Name: AutomateWoo - Refer A Friend Add-on
 * Plugin URI: https://automatewoo.com/addons/refer-a-friend/
 * Description: Refer A Friend add-on for AutomateWoo.
 * Version: 2.6.7
 * Author: WooCommerce
 * Author URI: https://woocommerce.com
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Text Domain: automatewoo-referrals
 * Domain Path: /languages/
 *
 * WC requires at least: 4.5
 * WC tested up to: 6.6
 * Woo: 4871154:3fd134b42d7c710d96a6e6abd38718bc
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
 * @package AutomateWoo/Referrals
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound

/**
 * Class AW_Referrals_Plugin_Data
 */
class AW_Referrals_Plugin_Data {

	/**
	 * AW_Referrals_Plugin_Data constructor.
	 */
	public function __construct() {
		$this->id                      = 'automatewoo-referrals';
		$this->name                    = ''; // Replaced with translatable string on init hook
		$this->version                 = '2.6.7'; // WRCS: DEFINED_VERSION.
		$this->file                    = __FILE__;
		$this->min_automatewoo_version = '5.3.0';
	}
}

/**
 * Class AW_Referrals_Loader
 */
class AW_Referrals_Loader {

	/**
	 * The extension data (e.g. name, version, etc.)
	 *
	 * @var AW_Referrals_Plugin_Data
	 */
	public static $data;

	/**
	 * An array of errors
	 *
	 * @var array
	 */
	public static $errors = array();

	/**
	 * Register the required hooks and actions
	 *
	 * @param AW_Referrals_Plugin_Data $data
	 */
	public static function init( $data ) {
		self::$data = $data;

		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'load' ) );

		// Load translations even if plugin requirements aren't met
		add_action( 'init', array( __CLASS__, 'load_textdomain' ), 5 );

		// Subscribe to automated translations.
		add_action( 'woocommerce_translations_updates_for_automatewoo-referrals', '__return_true' );
		register_activation_hook( self::$data->file, [ __CLASS__, 'plugin_activate' ] );
	}

	/**
	 * Load the extension
	 */
	public static function load() {
		self::check();
		if ( empty( self::$errors ) ) {
			include 'includes/automatewoo-referrals.php';

			if ( 'yes' === get_option( self::$data->id . '-activated' ) ) {
				add_action( 'automatewoo_loaded', [ __CLASS__, 'addon_activate' ] );
			}
		}
	}

	/**
	 * Load translated strings.
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'automatewoo-referrals', false, 'automatewoo-referrals/languages' );
	}


	/**
	 * Check if extension can be activated
	 */
	protected static function check() {
		$inactive_text = '<strong>' . sprintf( __( '%s is inactive.', 'automatewoo-referrals' ), __( 'AutomateWoo - Refer A Friend', 'automatewoo-referrals' ) ) . '</strong>';

		if ( ! self::is_automatewoo_active() ) {
			self::$errors[] = sprintf( __( '%s The plugin requires AutomateWoo to be installed and activated.', 'automatewoo-referrals' ), $inactive_text );
		} elseif ( ! self::is_automatewoo_version_ok() ) {
			self::$errors[] = sprintf( __( '%1$s The plugin requires AutomateWoo version %2$s or newer.', 'automatewoo-referrals' ), $inactive_text, self::$data->min_automatewoo_version );
		} elseif ( ! self::is_automatewoo_directory_name_ok() ) {
			self::$errors[] = sprintf( __( '%s AutomateWoo plugin directory name is not correct.', 'automatewoo-referrals' ), $inactive_text );
		}
	}


	/**
	 * Check if AutomateWoo is active
	 *
	 * @return bool
	 */
	protected static function is_automatewoo_active() {
		return function_exists( 'AW' );
	}


	/**
	 * Check if AutomateWoo meets the minimum version requirement
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
	 * Check if AutomateWoo directory name is valid
	 *
	 * @return bool
	 */
	protected static function is_automatewoo_directory_name_ok() {
		$active_plugins = (array) get_option( 'active_plugins', [] );
		return in_array( 'automatewoo/automatewoo.php', $active_plugins, true ) || array_key_exists( 'automatewoo/automatewoo.php', $active_plugins );
	}

	/**
	 * Display errors as admin notices
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
		AW_Referrals()->activate();
		update_option( self::$data->id . '-activated', 'no' );
	}

}

AW_Referrals_Loader::init( new AW_Referrals_Plugin_Data() );
