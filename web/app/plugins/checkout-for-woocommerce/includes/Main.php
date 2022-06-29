<?php

namespace Objectiv\Plugins\Checkout;

use Objectiv\Plugins\Checkout\AddressFieldsAugmenter;
use Objectiv\Plugins\Checkout\Managers\TemplatesManager;
use Objectiv\Plugins\Checkout\Managers\UpdatesManager;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * The core plugin class.
 *
 * In previous iterations of the plugin this class served as the middle man access point to all plugin functionality.
 * This has been deprecated and the remaining functions serve as legacy functions to be removed at a later date.
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout
 * @deprecated
 * @author Brandon Tassone <brandontassone@gmail.com>
 */

class Main extends SingletonAbstract {
	/**
	 * Returns the template manager
	 *
	 * @since 1.0.0
	 * @access public
	 * @deprecated 5.0.0
	 * @return TemplatesManager
	 */
	public function get_templates_manager(): TemplatesManager {
		_deprecated_function( 'Main::get_templates_manager', 'CheckoutWC 5.0.0', 'TemplatesManager::instance' );
		return TemplatesManager::instance();
	}

	/**
	 * @deprecated
	 */
	public function get_settings_manager(): SettingsManager {
		_deprecated_function( 'Main::get_settings_manager', 'CheckoutWC 5.0.0', 'SettingsManager::instance()' );
		return SettingsManager::instance();
	}

	/**
	 * Get the updater object
	 *
	 * @since 1.0.0
	 * @access public
	 * @return UpdatesManager The updater object
	 * @deprecated
	 */
	public function get_updater(): UpdatesManager {
		_deprecated_function( 'Main::get_updater', 'CheckoutWC 5.0.0', 'UpdatesManager::instance()' );
		return UpdatesManager::instance();
	}

	/**
	 * @return AddressFieldsAugmenter The form object
	 *@deprecated 5.0.0
	 * @since 1.1.5
	 * @access public
	 */
	public function get_form(): AddressFieldsAugmenter {
		_deprecated_function( 'Main::get_form', 'CheckoutWC 5.0.0', 'FormAugmenter::instance' );
		return AddressFieldsAugmenter::instance();
	}

	/**
	 * @deprecated
	 * @return mixed|void
	 */
	public static function is_checkout(): bool {
		_deprecated_function( 'Main::is_checkout', 'CheckoutWC 5.0.0', 'cfw_is_checkout' );
		return cfw_is_checkout();
	}

	/**
	 * @deprecated
	 * @return bool
	 */
	public static function is_checkout_pay_page(): bool {
		_deprecated_function( 'Main::is_checkout_pay_page', 'CheckoutWC 5.0.0', 'cfw_is_checkout_pay_page()' );
		return cfw_is_checkout_pay_page();
	}

	/**
	 * @deprecated
	 * @return bool
	 */
	public static function is_order_received_page(): bool {
		_deprecated_function( 'Main::is_order_received_page', 'CheckoutWC 5.0.0', 'cfw_is_order_received_page()' );
		return cfw_is_order_received_page();
	}

	/**
	 * @deprecated
	 * @return bool
	 */
	public static function is_cfw_page(): bool {
		_deprecated_function( 'Main::is_cfw_page', 'CheckoutWC 5.0.0', 'is_cfw_page()' );
		return is_cfw_page();
	}
}
