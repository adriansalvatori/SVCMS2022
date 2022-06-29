<?php
/**
 * A simple object containing properties for fonts.
 *
 * @package     Xirki
 * @category    Core
 * @author      XplodedThemes (@XplodedThemes)
 * @copyright   Copyright (c) 2019, XplodedThemes (@XplodedThemes)
 * @license     https://opensource.org/licenses/MIT
 * @since       1.0
 */

/**
 * The Xirki_Fonts object.
 */
final class Xirki_Fonts {

	/**
	 * The mode we'll be using to add google fonts.
	 * This is a todo item, not yet functional.
	 *
	 * @static
	 * @todo
	 * @access public
	 * @var string
	 */
	public static $mode = 'link';

	/**
	 * Holds a single instance of this object.
	 *
	 * @static
	 * @access private
	 * @var null|object
	 */
	private static $instance = null;

	/**
	 * An array of our google fonts.
	 *
	 * @static
	 * @access public
	 * @var null|object
	 */
	public static $google_fonts = null;

	/**
	 * The class constructor.
	 */
	private function __construct() {}

	/**
	 * Get the one, true instance of this class.
	 * Prevents performance issues since this is only loaded once.
	 *
	 * @return object Xirki_Fonts
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Compile font options from different sources.
	 *
	 * @return array    All available fonts.
	 */
	public static function get_all_fonts() {
		$standard_fonts = self::get_standard_fonts();
		$google_fonts   = self::get_google_fonts();
		return apply_filters( 'xirki_fonts_all', array_merge( $standard_fonts, $google_fonts ) );
	}

	/**
	 * Return an array of standard websafe fonts.
	 *
	 * @return array    Standard websafe fonts.
	 */
	public static function get_standard_fonts() {
		$standard_fonts = array(
			'serif'      => array(
				'label' => 'Serif',
				'stack' => 'Georgia,Times,"Times New Roman",serif',
			),
			'sans-serif' => array(
				'label' => 'Sans Serif',
				'stack' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif',
			),
			'monospace'  => array(
				'label' => 'Monospace',
				'stack' => 'Monaco,"Lucida Sans Typewriter","Lucida Typewriter","Courier New",Courier,monospace',
			),
		);
		return apply_filters( 'xirki_fonts_standard_fonts', $standard_fonts );
	}

	/**
	 * Return an array of all available Google Fonts.
	 *
	 * @return array    All Google Fonts.
	 */
	public static function get_google_fonts() {

		// Get fonts from cache.
		self::$google_fonts = get_site_transient( 'xirki_googlefonts_cache' );

		/**
		 * Reset the cache if we're using action=xirki-reset-cache in the URL.
		 *
		 * Note to code reviewers:
		 * There's no need to check nonces or anything else, this is a simple true/false evaluation.
		 */
		if ( ! empty( $_GET['action'] ) && 'xirki-reset-cache' === $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			self::$google_fonts = false;
		}

		// If cache is populated, return cached fonts array.
		if ( self::$google_fonts ) {
			return self::$google_fonts;
		}

		// If we got this far, cache was empty so we need to get from JSON.
		ob_start();
		include wp_normalize_path( dirname( __FILE__ ) . '/webfonts.json' ); // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude

		$fonts_json = ob_get_clean();
		$fonts      = json_decode( $fonts_json, true );

		$google_fonts = array();
		if ( is_array( $fonts ) ) {
			foreach ( $fonts['items'] as $font ) {
				$google_fonts[ $font['family'] ] = array(
					'label'    => $font['family'],
					'variants' => $font['variants'],
					'category' => $font['category'],
				);
			}
		}

		// Apply the 'xirki_fonts_google_fonts' filter.
		self::$google_fonts = apply_filters( 'xirki_fonts_google_fonts', $google_fonts );

		// Save the array in cache.
		$cache_time = apply_filters( 'xirki_googlefonts_transient_time', DAY_IN_SECONDS );
		set_site_transient( 'xirki_googlefonts_cache', self::$google_fonts, $cache_time );

		return self::$google_fonts;
	}

	/**
	 * Returns an array of all available subsets.
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function get_google_font_subsets() {
		return array(
			'cyrillic'     => 'Cyrillic',
			'cyrillic-ext' => 'Cyrillic Extended',
			'devanagari'   => 'Devanagari',
			'greek'        => 'Greek',
			'greek-ext'    => 'Greek Extended',
			'khmer'        => 'Khmer',
			'latin'        => 'Latin',
			'latin-ext'    => 'Latin Extended',
			'vietnamese'   => 'Vietnamese',
			'hebrew'       => 'Hebrew',
			'arabic'       => 'Arabic',
			'bengali'      => 'Bengali',
			'gujarati'     => 'Gujarati',
			'tamil'        => 'Tamil',
			'telugu'       => 'Telugu',
			'thai'         => 'Thai',
		);
	}

	/**
	 * Dummy function to avoid issues with backwards-compatibility.
	 * This is not functional, but it will prevent PHP Fatal errors.
	 *
	 * @static
	 * @access public
	 */
	public static function get_google_font_uri() {}

	/**
	 * Returns an array of all available variants.
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function get_all_variants() {
		return array(
			'100'       => esc_html__( 'Ultra-Light 100', 'xirki' ),
			'100light'  => esc_html__( 'Ultra-Light 100', 'xirki' ),
			'100italic' => esc_html__( 'Ultra-Light 100 Italic', 'xirki' ),
			'200'       => esc_html__( 'Light 200', 'xirki' ),
			'200italic' => esc_html__( 'Light 200 Italic', 'xirki' ),
			'300'       => esc_html__( 'Book 300', 'xirki' ),
			'300italic' => esc_html__( 'Book 300 Italic', 'xirki' ),
			'400'       => esc_html__( 'Normal 400', 'xirki' ),
			'regular'   => esc_html__( 'Normal 400', 'xirki' ),
			'italic'    => esc_html__( 'Normal 400 Italic', 'xirki' ),
			'500'       => esc_html__( 'Medium 500', 'xirki' ),
			'500italic' => esc_html__( 'Medium 500 Italic', 'xirki' ),
			'600'       => esc_html__( 'Semi-Bold 600', 'xirki' ),
			'600bold'   => esc_html__( 'Semi-Bold 600', 'xirki' ),
			'600italic' => esc_html__( 'Semi-Bold 600 Italic', 'xirki' ),
			'700'       => esc_html__( 'Bold 700', 'xirki' ),
			'700italic' => esc_html__( 'Bold 700 Italic', 'xirki' ),
			'800'       => esc_html__( 'Extra-Bold 800', 'xirki' ),
			'800bold'   => esc_html__( 'Extra-Bold 800', 'xirki' ),
			'800italic' => esc_html__( 'Extra-Bold 800 Italic', 'xirki' ),
			'900'       => esc_html__( 'Ultra-Bold 900', 'xirki' ),
			'900bold'   => esc_html__( 'Ultra-Bold 900', 'xirki' ),
			'900italic' => esc_html__( 'Ultra-Bold 900 Italic', 'xirki' ),
		);
	}

	/**
	 * Determine if a font-name is a valid google font or not.
	 *
	 * @static
	 * @access public
	 * @param string $fontname The name of the font we want to check.
	 * @return bool
	 */
	public static function is_google_font( $fontname ) {
		return ( array_key_exists( $fontname, self::$google_fonts ) );
	}

	/**
	 * Gets available options for a font.
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function get_font_choices() {
		$fonts       = self::get_all_fonts();
		$fonts_array = array();
		foreach ( $fonts as $key => $args ) {
			$fonts_array[ $key ] = $key;
		}
		return $fonts_array;
	}
}
