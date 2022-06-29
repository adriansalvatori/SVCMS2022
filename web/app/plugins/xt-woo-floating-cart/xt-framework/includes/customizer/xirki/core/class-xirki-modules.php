<?php
/**
 * Handles modules loading.
 *
 * @package     Xirki
 * @category    Core
 * @author      XplodedThemes (@XplodedThemes)
 * @copyright   Copyright (c) 2019, XplodedThemes (@XplodedThemes)
 * @license     https://opensource.org/licenses/MIT
 * @since       3.0.0
 */

/**
 * The Xirki_Modules class.
 */
class Xirki_Modules {

	/**
	 * An array of available modules.
	 *
	 * @static
	 * @access private
	 * @since 3.0.0
	 * @var array
	 */
	private static $modules = array();

	/**
	 * An array of active modules (objects).
	 *
	 * @static
	 * @access private
	 * @since 3.0.0
	 * @var array
	 */
	private static $active_modules = array();

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 3.0.0
	 */
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'setup_default_modules' ), 10 );
		add_action( 'after_setup_theme', array( $this, 'init' ), 11 );
	}

	/**
	 * Set the default modules and apply the 'xirki_modules' filter.
	 * In v3.0.35 this method was renamed from default_modules to setup_default_modules,
	 * and its visibility changed from private to public to fix https://xplodedthemes.com/issues/2023
	 *
	 * @access public
	 * @since 3.0.0
	 */
	public function setup_default_modules() {
		self::$modules = apply_filters(
			'xirki_modules',
			array(
				'css'                => 'Xirki_Modules_CSS',
				'css-vars'           => 'Xirki_Modules_CSS_Vars',
				'customizer-styling' => 'Xirki_Modules_Customizer_Styling',
				'icons'              => 'Xirki_Modules_Icons',
				'loading'            => 'Xirki_Modules_Loading',
				'tooltips'           => 'Xirki_Modules_Tooltips',
				'branding'           => 'Xirki_Modules_Customizer_Branding',
				'postMessage'        => 'Xirki_Modules_PostMessage',
				'selective-refresh'  => 'Xirki_Modules_Selective_Refresh',
				'field-dependencies' => 'Xirki_Modules_Field_Dependencies',
				'custom-sections'    => 'Xirki_Modules_Custom_Sections',
				'webfonts'           => 'Xirki_Modules_Webfonts',
				'webfont-loader'     => 'Xirki_Modules_Webfont_Loader',
				'preset'             => 'Xirki_Modules_Preset',
				'gutenberg'          => 'Xirki_Modules_Gutenberg',
				'telemetry'          => 'Xirki_Modules_Telemetry',
			)
		);
	}

	/**
	 * Instantiates the modules.
	 * In v3.0.35 the visibility for this method was changed
	 * from private to public to fix https://xplodedthemes.com/issues/2023
	 *
	 * @access public
	 * @since 3.0.0
	 */
	public function init() {
		foreach ( self::$modules as $key => $module_class ) {
			if ( class_exists( $module_class ) ) {
				// Use this syntax instead of $module_class::get_instance()
				// for PHP 5.2 compatibility.
				self::$active_modules[ $key ] = call_user_func( array( $module_class, 'get_instance' ) );
			}
		}
	}

	/**
	 * Add a module.
	 *
	 * @static
	 * @access public
	 * @param string $module The classname of the module to add.
	 * @since 3.0.0
	 */
	public static function add_module( $module ) {
		if ( ! in_array( $module, self::$modules, true ) ) {
			self::$modules[] = $module;
		}
	}

	/**
	 * Remove a module.
	 *
	 * @static
	 * @access public
	 * @param string $module The classname of the module to add.
	 * @since 3.0.0
	 */
	public static function remove_module( $module ) {
		$key = array_search( $module, self::$modules, true );
		if ( false !== $key ) {
			unset( self::$modules[ $key ] );
		}
	}

	/**
	 * Get the modules array.
	 *
	 * @static
	 * @access public
	 * @since 3.0.0
	 * @return array
	 */
	public static function get_modules() {
		return self::$modules;
	}

	/**
	 * Get the array of active modules (objects).
	 *
	 * @static
	 * @access public
	 * @since 3.0.0
	 * @return array
	 */
	public static function get_active_modules() {
		return self::$active_modules;
	}
}
