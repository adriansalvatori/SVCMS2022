<?php

namespace Objectiv\Plugins\Checkout\Model;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use Symfony\Component\Finder\Finder;

/**
 * Template handler for associated template piece. Typically there should only be 3 of these in total (header, footer,
 * content)
 *
 * @link checkoutwc.com
 * @since 2.0.0
 * @package Objectiv\Plugins\Checkout\Core
 * @author Clifton Griffin <clif@checkoutwc.com>
 */

class Template {
	private $_stylesheet_file_name = 'style.min.css';
	private $_basepath;
	private $_baseuri;
	private $_name;
	private $_description;
	private $_author;
	private $_author_uri;
	private $_version;
	private $_supports  = array();
	private $_templates = array();
	private $_slug;

	/**
	 * @since 2.0.0
	 * @access private
	 * @static
	 * @var array $default_headers
	 */
	public static $default_headers = array(
		'Name'        => 'Template Name',
		'Description' => 'Description',
		'Author'      => 'Author',
		'AuthorURI'   => 'Author URI',
		'Version'     => 'Version',
		'Supports'    => 'Supports',
	);

	/**
	 * Template constructor.
	 *
	 * @param string $slug
	 */
	public function __construct( string $slug ) {
		/**
		 * Locate the template
		 *
		 * Search WordPress theme template folder first, then plugin
		 */
		if ( is_dir( trailingslashit( CFW_PATH_THEME_TEMPLATE ) . $slug ) ) {
			$this->_basepath = trailingslashit( CFW_PATH_THEME_TEMPLATE ) . $slug;
			$this->_baseuri  = trailingslashit( get_stylesheet_directory_uri() ) . 'checkout-wc/' . $slug;
		} elseif ( is_dir( trailingslashit( CFW_PATH_PLUGIN_TEMPLATE ) . $slug ) ) {
			$this->_basepath = trailingslashit( CFW_PATH_PLUGIN_TEMPLATE ) . $slug;
			$this->_baseuri  = trailingslashit( CFW_PATH_URL_BASE ) . 'templates/' . $slug;
		} else {
			// Otherwise, load the default template
			return new Template( 'default' );
		}

		$this->_slug = $slug;

		$this->_load();
	}

	/**
	 * Load template information for given path
	 *
	 * @param $basepath
	 */
	private function _load() {
		/**
		 * Template Information
		 */
		$stylesheet_path = $this->get_stylesheet_path();

		if ( $stylesheet_path ) {
			$data = get_file_data( $stylesheet_path, self::$default_headers );

			$data['Name']     = ( '' === $data['Name'] ) ? ucfirst( basename( $this->get_basepath() ) ) : $data['Name'];
			$data['Supports'] = isset( $data['Supports'] ) ? explode( ', ', $data['Supports'] ) : array();

			foreach ( $data as $key => $value ) {
				$key             = str_replace( ' ', '_', $key );
				$key             = sanitize_key( $key );
				$this->{"_$key"} = $value;
			}
		}

		/**
		 * Template Files
		 */
		$finder = new Finder();
		$finder->files()->depth( 0 )->in( $this->get_basepath() )->name( '*.php' )->notName( 'functions.php' )->notName( 'header.php' )->notName( 'footer.php' );

		foreach ( $finder as $template_file ) {
			$this->_templates[] = $template_file->getFilename();
		}
	}

	/**
	 * Load the theme template functions file
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function load_functions() {
		$functions_path = trailingslashit( $this->get_basepath() ) . 'functions.php';

		if ( file_exists( $functions_path ) ) {
			require_once $functions_path;
		}
	}

	/**
	 * Load the template init settings file
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {
		$init_path = trailingslashit( $this->get_basepath() ) . 'init.php';

		$defaults = $this->get_default_settings();

		$settings_manager = SettingsManager::instance();

		foreach ( $defaults as $setting => $value ) {
			if ( defined( 'CFW_FORCE_TEMPLATE_RESET' ) ) {
				$settings_manager->update_setting( $setting, $value, true, array( $this->get_slug() ) );
			} else {
				$settings_manager->add_setting( $setting, $value, array( $this->get_slug() ) );
			}
		}

		if ( file_exists( $init_path ) ) {
			require_once $init_path;
		}
	}

	/**
	 * Get the default settings array
	 *
	 * @return array
	 */
	function get_default_settings(): array {
		$default_path = trailingslashit( $this->get_basepath() ) . 'defaults.php';

		if ( file_exists( $default_path ) ) {
			require $default_path;

			return $defaults;
		}

		return $this->get_standard_default_settings();
	}

	/**
	 * @param $setting
	 *
	 * @return mixed|string
	 */
	function get_default_setting( $setting ) {
		$defaults = $this->get_default_settings();

		return ! empty( $defaults[ $setting ] ) ? $defaults[ $setting ] : '';
	}

	/**
	 * @return string[]
	 */
	function get_standard_default_settings(): array {
		return array(
			'body_background_color'             => '#ffffff',
			'body_text_color'                   => '#333333',
			'header_background_color'           => '#ffffff',
			'footer_background_color'           => '#ffffff',
			'header_text_color'                 => '#2b2b2b',
			'footer_color'                      => '#999999',
			'link_color'                        => '#0073aa',
			'button_color'                      => '#333333',
			'button_text_color'                 => '#ffffff',
			'button_hover_color'                => '#555555',
			'button_text_hover_color'           => '#ffffff',
			'secondary_button_color'            => '#999999',
			'secondary_button_text_color'       => '#ffffff',
			'secondary_button_hover_color'      => '#666666',
			'secondary_button_text_hover_color' => '#ffffff',
			'summary_background_color'          => '#fafafa',
			'summary_mobile_background_color'   => '#fafafa',
			'summary_text_color'                => '#333333',
			'summary_link_color'                => '#0073aa',
			'cart_item_quantity_color'          => '#7f7f7f',
			'cart_item_quantity_text_color'     => '#ffffff',
			'breadcrumb_completed_text_color'   => '#7f7f7f',
			'breadcrumb_current_text_color'     => '#333333',
			'breadcrumb_next_text_color'        => '#7f7f7f',
			'breadcrumb_completed_accent_color' => '#333333',
			'breadcrumb_current_accent_color'   => '#333333',
			'breadcrumb_next_accent_color'      => '#333333',
		);
	}

	public function view( $filename, $parameters = array() ) {
		$filename_with_basepath = trailingslashit( $this->get_basepath() ) . $filename;
		$template_name          = $this->get_slug();
		$template_piece_name    = basename( $filename, '.php' );

		if ( file_exists( $filename_with_basepath ) ) {
			/**
			 * Fires before template is output
			 *
			 * @since 3.0.0
			 */
			do_action( "cfw_template_load_before_{$template_name}_{$template_piece_name}" );

			// Extract any parameters for use in the template
			extract( $parameters ); // phpcs:ignore

			// Pass the parameters to the view
			require $filename_with_basepath;

			/**
			 * Fires after template has been echoed out
			 *
			 * @since 3.0.0
			 */
			do_action( "cfw_template_load_after_{$template_name}_{$template_piece_name}" );
		}
	}

	/**
	 * @param $capability
	 *
	 * @return bool
	 */
	public function supports( $capability ): bool {
		return in_array( $capability, $this->get_supports(), true );
	}

	/**
	 * @return string
	 */
	public function get_template_uri(): string {
		return $this->_baseuri;
	}

	/**
	 * Return fully qualified path to stylesheet
	 *
	 * @return string|bool $stylesheet
	 */
	public function get_stylesheet_path() {
		$stylesheet = trailingslashit( $this->get_basepath() ) . $this->get_stylesheet_filename();

		return file_exists( $stylesheet ) ? $stylesheet : false;
	}

	/**
	 * @return string
	 */
	public function get_stylesheet_filename(): string {
		if ( defined( 'CFW_DEV_MODE' ) && CFW_DEV_MODE ) {
			return 'style.css';
		}

		return $this->_stylesheet_file_name;
	}

	/**
	 * @param string $_stylesheet_file_name
	 */
	public function set_stylesheet_filename( string $_stylesheet_file_name ) {
		$this->_stylesheet_file_name = $_stylesheet_file_name;
	}

	/**
	 * @return string
	 */
	public function get_basepath(): string {
		return $this->_basepath;
	}

	/**
	 * @param string $basepath
	 */
	public function set_basepath( string $basepath ) {
		$this->_basepath = $basepath;
	}

	/**
	 * @return mixed
	 */
	public function get_name() {
		return $this->_name;
	}

	/**
	 * @param mixed $name
	 */
	public function set_name( $name ) {
		$this->_name = $name;
	}

	/**
	 * @return mixed
	 */
	public function get_description() {
		return $this->_description;
	}

	/**
	 * @param mixed $description
	 */
	public function set_description( $description ) {
		$this->_description = $description;
	}

	/**
	 * @return mixed
	 */
	public function get_author() {
		return $this->_author;
	}

	/**
	 * @param mixed $author
	 */
	public function set_author( $author ) {
		$this->_author = $author;
	}

	/**
	 * @return mixed
	 */
	public function get_author_uri() {
		return $this->_author_uri;
	}

	/**
	 * @param mixed $author_uri
	 */
	public function set_author_uri( $author_uri ) {
		$this->_author_uri = $author_uri;
	}

	/**
	 * @return mixed
	 */
	public function get_version() {
		return $this->_version;
	}

	/**
	 * @param mixed $version
	 */
	public function set_version( $version ) {
		$this->_version = $version;
	}

	/**
	 * @return array
	 */
	public function get_supports(): array {
		return (array) $this->_supports;
	}

	/**
	 * @param mixed $supports
	 */
	public function set_supports( $supports ) {
		$this->_supports = $supports;
	}

	/**
	 * @return array
	 */
	public function get_templates(): array {
		return $this->_templates;
	}

	/**
	 * @return string
	 */
	public function get_slug(): string {
		return $this->_slug;
	}


	public function run_on_plugin_activation() {
		$this->init();
	}

	public static function get_all_available(): array {
		$templates       = array();
		$finder          = new Finder();
		$active_template = cfw_get_active_template();

		$finder->directories()->depth( 0 )->in( cfw_get_plugin_template_path() );

		foreach ( $finder as $template ) {
			$templates[ $template->getBasename() ] = new self( $template->getBasename() );
		}

		if ( is_dir( CFW_PATH_THEME_TEMPLATE ) ) {
			$finder = new Finder();
			$finder->directories()->depth( 0 )->in( CFW_PATH_THEME_TEMPLATE );

			foreach ( $finder as $template ) {
				$templates[ $template->getBasename() ] = new self( $template->getBasename() );
			}
		}

		ksort( $templates );

		return array( $active_template->get_slug() => $templates[ $active_template->get_slug() ] ) + $templates;
	}

	public static function init_active_template( self $template ) {
		add_action(
			'cfw_load_template_assets',
			function() use ( $template ) {
				$min = ( ! CFW_DEV_MODE ) ? '.min' : '';

				wp_enqueue_style( 'cfw_front_template_css', $template->get_template_uri() . "/style{$min}.css", array(), CFW_VERSION );
				wp_enqueue_script( 'wc-checkout', $template->get_template_uri() . "/theme{$min}.js", array( 'jquery' ), CFW_VERSION, true );
			}
		);
		$template->load_functions();
	}
}
