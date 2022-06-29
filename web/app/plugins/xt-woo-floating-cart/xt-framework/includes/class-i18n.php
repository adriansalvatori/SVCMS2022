<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'XT_Framework_i18n' ) ) {

	/**
	 * Define the internationalization functionality.
	 *
	 * Loads and defines the internationalization files for this plugin
	 * so that it is ready for translation.
	 *
	 * @since      1.0.0
	 * @package    XT_Framework
	 * @subpackage XT_Framework/includes
	 * @author     XplodedThemes
	 */
	class XT_Framework_i18n {

		private $textdomain;
		private $file;

		public function __construct( $textdomain, $file ) {

			$this->textdomain = $textdomain;
			$this->file       = $file;

			if ( did_action( 'plugins_loaded' ) ) {
				$this->load_plugin_textdomain();
			} else {
				add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
			}
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain() {

			load_plugin_textdomain(
				$this->textdomain,
				false,
				basename( dirname( $this->file ) ) . '/languages/'
			);
		}
	}
}