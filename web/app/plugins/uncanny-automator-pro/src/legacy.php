<?php

namespace Uncanny_Automator_Pro;

/**
 * @deprecated 3.1
 */
class InitializePlugin {

	/**
	 * The parent plugins Namespace
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 * @deprecated 3.1
	 */
	const PARENT_PLUGIN_NAMESPACE = 'Uncanny_Automator';

	/**
	 * The plugin name
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 * @deprecated 3.1
	 */
	const PLUGIN_NAME = AUTOMATOR_PRO_ITEM_NAME;

	/**
	 * The plugin name acronym
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 * @deprecated 3.1
	 */
	const PLUGIN_PREFIX = AUTOMATOR_PRO_ITEM_NAME;

	/**
	 * Min PHP Version
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 * @deprecated 3.1
	 */
	const MIN_PHP_VERSION = '7.0';

	/**
	 * The plugin version number
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 * @deprecated 3.1
	 */
	const PLUGIN_VERSION = AUTOMATOR_PRO_PLUGIN_VERSION;

	/**
	 * The full path and filename
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 * @deprecated 3.1
	 */
	const MAIN_FILE = __FILE__;

	/**
	 * The instance of the class
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Object
	 * @deprecated 3.1
	 */
	private static $instance = null;

	/**
	 * Creates singleton instance of class
	 *
	 * @return InitializePlugin $instance The InitializePlugin Class
	 * @since 1.0.0
	 * @deprecated 3.1
	 *
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

// Let's run it
InitializePlugin::get_instance();
