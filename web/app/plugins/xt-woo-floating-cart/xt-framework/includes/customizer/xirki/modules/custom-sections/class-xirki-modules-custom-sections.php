<?php
/**
 * Custom Sections.
 *
 * @package     Xirki
 * @category    Modules
 * @subpackage  Custom Sections Module
 * @author      XplodedThemes (@XplodedThemes)
 * @copyright   Copyright (c) 2019, XplodedThemes (@XplodedThemes)
 * @license     https://opensource.org/licenses/MIT
 * @since       3.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds styles to the customizer.
 */
class Xirki_Modules_Custom_Sections {

	/**
	 * The object instance.
	 *
	 * @static
	 * @access private
	 * @since 3.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Constructor.
	 *
	 * @access protected
	 * @since 3.0.0
	 */
	protected function __construct() {

		// Register the new section types.
		add_filter( 'xirki_section_types', array( $this, 'set_section_types' ) );

		// Register the new panel types.
		add_filter( 'xirki_panel_types', array( $this, 'set_panel_types' ) );

		// Include the section-type files.
		add_action( 'customize_register', array( $this, 'include_sections_and_panels' ) );

		// Enqueue styles & scripts.
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_scrips' ), 999 );
	}

	/**
	 * Gets an instance of this object.
	 * Prevents duplicate instances which avoid artefacts and improves performance.
	 *
	 * @static
	 * @access public
	 * @since 3.0.0
	 * @return object
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Add the custom section types.
	 *
	 * @access public
	 * @since 3.0.0
	 * @param array $section_types The registered section-types.
	 * @return array
	 */
	public function set_section_types( $section_types ) {
		$new_types = array(
			'xirki-default'  => 'Xirki_Sections_Default_Section',
			'xirki-expanded' => 'Xirki_Sections_Expanded_Section',
			'xirki-nested'   => 'Xirki_Sections_Nested_Section',
			'xirki-link'     => 'Xirki_Sections_Link_Section',
		);
		return array_merge( $section_types, $new_types );
	}

	/**
	 * Add the custom panel types.
	 *
	 * @access public
	 * @since 3.0.0
	 * @param array $panel_types The registered section-types.
	 * @return array
	 */
	public function set_panel_types( $panel_types ) {
		$new_types = array(
			'xirki-nested' => 'Xirki_Panels_Nested_Panel',
		);
		return array_merge( $panel_types, $new_types );
	}

	/**
	 * Include the custom-section classes.
	 *
	 * @access public
	 * @since 3.0.0
	 */
	public function include_sections_and_panels() {

		// Sections.
		$folder_path   = dirname( __FILE__ ) . '/sections/';
		$section_types = apply_filters( 'xirki_section_types', array() );

		foreach ( $section_types as $id => $class ) {
			if ( ! class_exists( $class ) ) {
				$path = wp_normalize_path( $folder_path . 'class-xirki-sections-' . $id . '-section.php' );
				if ( file_exists( $path ) ) {
					include_once $path; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude
					continue;
				}
				$path = str_replace( 'class-xirki-sections-xirki-', 'class-xirki-sections-', $path );
				if ( file_exists( $path ) ) {
					include_once $path; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude
				}
			}
		}

		// Panels.
		$folder_path = dirname( __FILE__ ) . '/panels/';
		$panel_types = apply_filters( 'xirki_panel_types', array() );

		foreach ( $panel_types as $id => $class ) {
			if ( ! class_exists( $class ) ) {
				$path = wp_normalize_path( $folder_path . 'class-xirki-panels-' . $id . '-panel.php' );
				if ( file_exists( $path ) ) {
					include_once $path; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude
					continue;
				}
				$path = str_replace( 'class-xirki-panels-xirki-', 'class-xirki-panels-', $path );
				if ( file_exists( $path ) ) {
					include_once $path; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude
				}
			}
		}
	}

	/**
	 * Enqueues any necessary scripts and styles.
	 *
	 * @access public
	 * @since 3.0.0
	 */
	public function enqueue_scrips() {
		wp_enqueue_style( 'xirki-custom-sections', trailingslashit( Xirki::$url ) . 'modules/custom-sections/sections.css', array(), XIRKI_VERSION );
		wp_enqueue_script( 'xirki-custom-sections', trailingslashit( Xirki::$url ) . 'modules/custom-sections/sections.js', array( 'jquery', 'customize-base', 'customize-controls' ), XIRKI_VERSION, false );
	}
}
