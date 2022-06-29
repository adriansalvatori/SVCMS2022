<?php
/**
 * Handles the CSS Output of fields.
 *
 * @package     Xirki
 * @category    Modules
 * @author      XplodedThemes (@XplodedThemes)
 * @copyright   Copyright (c) 2019, XplodedThemes (@XplodedThemes)
 * @license     https://opensource.org/licenses/MIT
 * @since       3.0.0
 */

/**
 * The Xirki_Modules_CSS object.
 */
class Xirki_Modules_CSS {

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
	 * The CSS array
	 *
	 * @access public
	 * @var array
	 */
	public static $css_array = array();

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct() {
		$class_files = array(
			'Xirki_Modules_CSS_Generator'               => '/class-xirki-modules-css-generator.php',
			'Xirki_Output'                              => '/class-xirki-output.php',
			'Xirki_Output_Field_Background'             => '/field/class-xirki-output-field-background.php',
			'Xirki_Output_Field_Image'                  => '/field/class-xirki-output-field-image.php',
			'Xirki_Output_Field_Multicolor'             => '/field/class-xirki-output-field-multicolor.php',
			'Xirki_Output_Field_Dimensions'             => '/field/class-xirki-output-field-dimensions.php',
			'Xirki_Output_Field_Typography'             => '/field/class-xirki-output-field-typography.php',
			'Xirki_Output_Property'                     => '/property/class-xirki-output-property.php',
			'Xirki_Output_Property_Background_Image'    => '/property/class-xirki-output-property-background-image.php',
			'Xirki_Output_Property_Background_Position' => '/property/class-xirki-output-property-background-position.php',
			'Xirki_Output_Property_Font_Family'         => '/property/class-xirki-output-property-font-family.php',
		);

		foreach ( $class_files as $class_name => $file ) {
			if ( ! class_exists( $class_name ) ) {
				include_once wp_normalize_path( dirname( __FILE__ ) . $file ); // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude
			}
		}
		add_action( 'init', array( $this, 'init' ) );
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
	 * Init.
	 *
	 * @access public
	 */
	public function init() {

		Xirki_Modules_Webfonts::get_instance();

		// Allow completely disabling Xirki CSS output.
		if ( ( defined( 'XIRKI_NO_OUTPUT' ) && true === XIRKI_NO_OUTPUT ) || ( isset( $config['disable_output'] ) && true === $config['disable_output'] ) ) {
			return;
		}

		// Admin styles, adds compatibility with the new WordPress editor (Gutenberg).
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_styles' ), 100 );

		add_action( 'wp', array( $this, 'print_styles_action' ) );

		if ( ! apply_filters( 'xirki_output_inline_styles', true ) ) {
			$config   = apply_filters( 'xirki_config', array() );
			$priority = 999;
			if ( isset( $config['styles_priority'] ) ) {
				$priority = absint( $config['styles_priority'] );
			}
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), $priority );
		} else {
			add_action( 'wp_head', array( $this, 'print_styles_inline' ), 999 );
		}
	}

	/**
	 * Print styles inline.
	 *
	 * @access public
	 * @since 3.0.36
	 * @return void
	 */
	public function print_styles_inline() {
		echo '<style id="xirki-inline-styles">';
		$this->print_styles();
		echo '</style>';
	}

	/**
	 * Enqueue the styles.
	 *
	 * @access public
	 * @since 3.0.36
	 * @return void
	 */
	public function enqueue_styles() {

		$args = array(
			'action' => apply_filters( 'xirki_styles_action_handle', 'xirki-styles' ),
		);
		if ( is_admin() && ! is_customize_preview() ) {
			$args['editor'] = '1';
		}

		// Enqueue the dynamic stylesheet.
		wp_enqueue_style(
            'xirki-dynamic-styles',
			add_query_arg( $args, site_url() ),
			array(),
			XIRKI_VERSION
		);
	}

	/**
	 * Prints the styles as an enqueued file.
	 *
	 * @access public
	 * @since 3.0.36
	 * @return void
	 */
	public function print_styles_action() {
		/**
		 * Note to code reviewers:
		 * There is no need for a nonce check here, we're only checking if this is a valid request or not.
		 */
		if ( empty( $_GET['action'] ) || apply_filters( 'xirki_styles_action_handle', 'xirki-styles' ) !== $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		// This is a stylesheet.
		header( 'Content-type: text/css' );
		$this->print_styles();
		exit;
	}

	/**
	 * Prints the styles.
	 *
	 * @access public
	 */
	public function print_styles() {

		// Go through all configs.
		$configs = Xirki::$config;
		foreach ( $configs as $config_id => $args ) {
			if ( isset( $args['disable_output'] ) && true === $args['disable_output'] ) {
				continue;
			}
			$styles = self::loop_controls( $config_id );
			$styles = apply_filters( "xirki_{$config_id}_dynamic_css", $styles );
			if ( ! empty( $styles ) ) {
				/**
				 * Note to code reviewers:
				 *
				 * Though all output should be run through an escaping function, this is pure CSS.
				 *
				 * When used in the print_styles_action() method the PHP header() call makes the browser interpret it as such.
				 * No code, script or anything else can be executed from inside a stylesheet.
				 *
				 * When using in the print_styles_inline() method the wp_strip_all_tags call we use below
				 * strips anything that has the possibility to be malicious, and since this is inslide a <style> tag
				 * it can only be interpreted by the browser as such.
				 * wp_strip_all_tags() excludes the possibility of someone closing the <style> tag and then opening something else.
				 */
				echo wp_strip_all_tags( $styles ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}
		do_action( 'xirki_dynamic_css' );
	}

	/**
	 * Loop through all fields and create an array of style definitions.
	 *
	 * @static
	 * @access public
	 * @param string $config_id The configuration ID.
	 */
	public static function loop_controls( $config_id ) {

		// Get an instance of the Xirki_Modules_CSS_Generator class.
		// This will make sure google fonts and backup fonts are loaded.
		Xirki_Modules_CSS_Generator::get_instance();

		$fields = Xirki::$fields;
		$css    = array();

		// Early exit if no fields are found.
		if ( empty( $fields ) ) {
			return;
		}

		foreach ( $fields as $field ) {

			// Only process fields that belong to $config_id.
			if ( $config_id !== $field['xirki_config'] ) {
				continue;
			}

			if ( true === apply_filters( "xirki_{$config_id}_css_skip_hidden", true ) ) {

				// Only continue if field dependencies are met.
				if ( ! empty( $field['required'] ) ) {
					$valid = true;

					foreach ( $field['required'] as $requirement ) {
						if ( isset( $requirement['setting'] ) && isset( $requirement['value'] ) && isset( $requirement['operator'] ) ) {
							$controller_value = Xirki_Values::get_value( $config_id, $requirement['setting'] );
							if ( ! Xirki_Helper::compare_values( $controller_value, $requirement['value'], $requirement['operator'] ) ) {
								$valid = false;
							}
						}
					}

					if ( ! $valid ) {
						continue;
					}
				}
			}

			// Only continue if $field['output'] is set.
			if ( isset( $field['output'] ) && ! empty( $field['output'] ) ) {
				$css = Xirki_Helper::array_replace_recursive( $css, Xirki_Modules_CSS_Generator::css( $field ) );

				// Add the globals.
				if ( isset( self::$css_array[ $config_id ] ) && ! empty( self::$css_array[ $config_id ] ) ) {
					Xirki_Helper::array_replace_recursive( $css, self::$css_array[ $config_id ] );
				}
			}
		}

		$css = apply_filters( "xirki_{$config_id}_styles", $css );

		if ( is_array( $css ) ) {
			return Xirki_Modules_CSS_Generator::styles_parse( Xirki_Modules_CSS_Generator::add_prefixes( $css ) );
		}
	}

	/**
	 * The FA field got deprecated in v3.0.42.
	 * This is here for backwards-compatibility in case a theme was calling it directly.
	 *
	 * @static
	 * @since 3.0.26
	 * @access public
	 * @return void
	 */
	public static function add_fontawesome_script() {}

	/**
	 * The FA field got deprecated in v3.0.42.
	 * This is here for backwards-compatibility in case a theme was calling it directly.
	 *
	 * @static
	 * @since 3.0.35
	 * @access public
	 * @return false
	 */
	public static function get_enqueue_fa() {
		return false;
	}
}
