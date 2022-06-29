<?php

namespace Uncanny_Automator_Pro;

/**
 * This class is used to run any configurations before the plugin is initialized
 *
 * @package Uncanny Automator Pro
 * @deprecated 3.1
 */
class Config {

	/**
	 * The instance of the class
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Boot
	 */
	private static $instance = null;

	/**
	 * Creates singleton instance of class
	 *
	 * @return Config $instance
	 * @since 1.0.0
	 *
	 */
	public static function get_instance() {

		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
