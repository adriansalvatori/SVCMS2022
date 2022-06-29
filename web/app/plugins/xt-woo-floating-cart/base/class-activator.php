<?php

/**
 * Fired during plugin activation
 *
 * @link       http://xplodedthemes.com
 * @since      1.0.0
 *
 * @package    XT_Woo_Floating_Cart
 * @subpackage XT_Woo_Floating_Cart/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    XT_Woo_Floating_Cart
 * @subpackage XT_Woo_Floating_Cart/includes
 * @author     XplodedThemes
 */
class XT_Woo_Floating_Cart_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

        // Purge entire WP Rocket cache if plugin is active
        if ( function_exists( 'rocket_clean_domain' ) ) {

            rocket_clean_domain();
        }

	}
}

XT_Woo_Floating_Cart_Activator::activate();