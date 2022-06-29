<?php
/**
 * Initializes Xirki
 *
 * @package     Xirki
 * @category    Core
 * @author      XplodedThemes (@XplodedThemes)
 * @copyright   Copyright (c) 2019, XplodedThemes (@XplodedThemes)
 * @license     https://opensource.org/licenses/MIT
 * @since       1.0
 */

/**
 * Initialize Xirki
 */
class Xirki_Init {

	/**
	 * Control types.
	 *
	 * @access private
	 * @since 3.0.0
	 * @var array
	 */
	private $control_types = array();

	/**
	 * Should we show a nag for the deprecated fontawesome field?
	 *
	 * @static
	 * @access private
	 * @since 3.0.42
	 * @var bool
	 */
	private static $show_fa_nag = false;

	/**
	 * The class constructor.
	 */
	public function __construct() {
		self::set_url();
		add_action( 'after_setup_theme', array( $this, 'set_url' ) );
		add_action( 'wp_loaded', array( $this, 'add_to_customizer' ), 1 );
		add_filter( 'xirki_control_types', array( $this, 'default_control_types' ) );

		add_action( 'customize_register', array( $this, 'remove_panels' ), 99999 );
		add_action( 'customize_register', array( $this, 'remove_sections' ), 99999 );
		add_action( 'customize_register', array( $this, 'remove_controls' ), 99999 );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_init', array( $this, 'dismiss_nag' ) );

		new Xirki_Values();
		new Xirki_Sections();
	}

	/**
	 * Properly set the Xirki URL for assets.
	 *
	 * @static
	 * @access public
	 */
	public static function set_url() {
		if ( Xirki_Util::is_plugin() ) {
			return;
		}

		Xirki::$url = plugins_url( '', XIRKI_PLUGIN_FILE );

		// Make sure the right protocol is used.
		Xirki::$url = set_url_scheme( Xirki::$url );
	}

	/**
	 * Add the default Xirki control types.
	 *
	 * @access public
	 * @since 3.0.0
	 * @param array $control_types The control types array.
	 * @return array
	 */
	public function default_control_types( $control_types = array() ) {
		$this->control_types = array(
			'checkbox'              => 'Xirki_Control_Checkbox',
			'xirki-background'      => 'Xirki_Control_Background',
			'code_editor'           => 'Xirki_Control_Code',
			'xirki-color'           => 'Xirki_Control_Color',
			'xirki-color-palette'   => 'Xirki_Control_Color_Palette',
			'xirki-custom'          => 'Xirki_Control_Custom',
			'xirki-date'            => 'Xirki_Control_Date',
			'xirki-dashicons'       => 'Xirki_Control_Dashicons',
			'xirki-dimension'       => 'Xirki_Control_Dimension',
			'xirki-dimensions'      => 'Xirki_Control_Dimensions',
			'xirki-editor'          => 'Xirki_Control_Editor',
			'xirki-image'           => 'Xirki_Control_Image',
			'xirki-multicolor'      => 'Xirki_Control_Multicolor',
			'xirki-multicheck'      => 'Xirki_Control_MultiCheck',
			'xirki-number'          => 'Xirki_Control_Number',
			'xirki-palette'         => 'Xirki_Control_Palette',
			'xirki-radio'           => 'Xirki_Control_Radio',
			'xirki-radio-buttonset' => 'Xirki_Control_Radio_ButtonSet',
			'xirki-radio-image'     => 'Xirki_Control_Radio_Image',
			'repeater'              => 'Xirki_Control_Repeater',
			'xirki-select'          => 'Xirki_Control_Select',
			'xirki-slider'          => 'Xirki_Control_Slider',
			'xirki-sortable'        => 'Xirki_Control_Sortable',
			'xirki-spacing'         => 'Xirki_Control_Dimensions',
			'xirki-switch'          => 'Xirki_Control_Switch',
			'xirki-generic'         => 'Xirki_Control_Generic',
			'xirki-toggle'          => 'Xirki_Control_Toggle',
			'xirki-typography'      => 'Xirki_Control_Typography',
			'image'                 => 'Xirki_Control_Image',
			'cropped_image'         => 'Xirki_Control_Cropped_Image',
			'upload'                => 'Xirki_Control_Upload',
		);
		return array_merge( $this->control_types, $control_types );
	}

	/**
	 * Helper function that adds the fields, sections and panels to the customizer.
	 */
	public function add_to_customizer() {
		$this->fields_from_filters();
		add_action( 'customize_register', array( $this, 'register_control_types' ) );
		add_action( 'customize_register', array( $this, 'add_panels' ), 97 );
		add_action( 'customize_register', array( $this, 'add_sections' ), 98 );
		add_action( 'customize_register', array( $this, 'add_fields' ), 99 );
	}

	/**
	 * Register control types
	 */
	public function register_control_types() {
		global $wp_customize;

		$section_types = apply_filters( 'xirki_section_types', array() );
		foreach ( $section_types as $section_type ) {
			$wp_customize->register_section_type( $section_type );
		}

		$this->control_types = $this->default_control_types();
		if ( ! class_exists( 'WP_Customize_Code_Editor_Control' ) ) {
			unset( $this->control_types['code_editor'] );
		}
		foreach ( $this->control_types as $key => $classname ) {
			if ( ! class_exists( $classname ) ) {
				unset( $this->control_types[ $key ] );
			}
		}

		$skip_control_types = apply_filters(
			'xirki_control_types_exclude',
			array(
				'Xirki_Control_Repeater',
				'WP_Customize_Control',
			)
		);

		foreach ( $this->control_types as $control_type ) {
			if ( ! in_array( $control_type, $skip_control_types, true ) && class_exists( $control_type ) ) {
				$wp_customize->register_control_type( $control_type );
			}
		}
	}

	/**
	 * Register our panels to the WordPress Customizer.
	 *
	 * @access public
	 */
	public function add_panels() {
		if ( ! empty( Xirki::$panels ) ) {
			foreach ( Xirki::$panels as $panel_args ) {
				// Extra checks for nested panels.
				if ( isset( $panel_args['panel'] ) ) {
					if ( isset( Xirki::$panels[ $panel_args['panel'] ] ) ) {
						// Set the type to nested.
						$panel_args['type'] = 'xirki-nested';
					}
				}

				new Xirki_Panel( $panel_args );
			}
		}
	}

	/**
	 * Register our sections to the WordPress Customizer.
	 *
	 * @var object The WordPress Customizer object
	 */
	public function add_sections() {
		if ( ! empty( Xirki::$sections ) ) {
			foreach ( Xirki::$sections as $section_args ) {
				// Extra checks for nested sections.
				if ( isset( $section_args['section'] ) ) {
					if ( isset( Xirki::$sections[ $section_args['section'] ] ) ) {
						// Set the type to nested.
						$section_args['type'] = 'xirki-nested';
						// We need to check if the parent section is nested inside a panel.
						$parent_section = Xirki::$sections[ $section_args['section'] ];
						if ( isset( $parent_section['panel'] ) ) {
							$section_args['panel'] = $parent_section['panel'];
						}
					}
				}
				new Xirki_Section( $section_args );
			}
		}
	}

	/**
	 * Create the settings and controls from the $fields array and register them.
	 *
	 * @var object The WordPress Customizer object.
	 */
	public function add_fields() {
		global $wp_customize;
		foreach ( Xirki::$fields as $args ) {

			// Create the settings.
			new Xirki_Settings( $args );

			// Check if we're on the customizer.
			// If we are, then we will create the controls, add the scripts needed for the customizer
			// and any other tweaks that this field may require.
			if ( $wp_customize ) {

				// Create the control.
				new Xirki_Control( $args );

			}
		}
	}

	/**
	 * Process fields added using the 'xirki_fields' and 'xirki_controls' filter.
	 * These filters are no longer used, this is simply for backwards-compatibility.
	 *
	 * @access private
	 * @since 2.0.0
	 */
	private function fields_from_filters() {
		$fields = apply_filters( 'xirki_controls', array() );
		$fields = apply_filters( 'xirki_fields', $fields );

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				Xirki::add_field( 'global', $field );
			}
		}
	}

	/**
	 * Alias for the is_plugin static method in the Xirki_Util class.
	 * This is here for backwards-compatibility purposes.
	 *
	 * @static
	 * @access public
	 * @since 3.0.0
	 * @return bool
	 */
	public static function is_plugin() {
		return Xirki_Util::is_plugin();
	}

	/**
	 * Alias for the get_variables static method in the Xirki_Util class.
	 * This is here for backwards-compatibility purposes.
	 *
	 * @static
	 * @access public
	 * @since 2.0.0
	 * @return array Formatted as array( 'variable-name' => value ).
	 */
	public static function get_variables() {

		// Log error for developers.
		_doing_it_wrong( __METHOD__, esc_html__( 'We detected you\'re using Xirki_Init::get_variables(). Please use Xirki_Util::get_variables() instead.', 'xirki' ), '3.0.10' );
		return Xirki_Util::get_variables();
	}

	/**
	 * Remove panels.
	 *
	 * @since 3.0.17
	 * @param object $wp_customize The customizer object.
	 * @return void
	 */
	public function remove_panels( $wp_customize ) {
		foreach ( Xirki::$panels_to_remove as $panel ) {
			$wp_customize->remove_panel( $panel );
		}
	}

	/**
	 * Remove sections.
	 *
	 * @since 3.0.17
	 * @param object $wp_customize The customizer object.
	 * @return void
	 */
	public function remove_sections( $wp_customize ) {
		foreach ( Xirki::$sections_to_remove as $section ) {
			$wp_customize->remove_section( $section );
		}
	}

	/**
	 * Remove controls.
	 *
	 * @since 3.0.17
	 * @param object $wp_customize The customizer object.
	 * @return void
	 */
	public function remove_controls( $wp_customize ) {
		foreach ( Xirki::$controls_to_remove as $control ) {
			$wp_customize->remove_control( $control );
		}
	}

	/**
	 * Shows an admin notice.
	 *
	 * @access public
	 * @since 3.0.42
	 * @return void
	 */
	public function admin_notices() {

		// No need for a nag if we don't need to recommend installing the FA plugin.
		if ( ! self::$show_fa_nag ) {
			return;
		}

		// No need for a nag if FA plugin is already installed.
		if ( defined( 'FONTAWESOME_DIR_PATH' ) ) {
			return;
		}

		// No need for a nag if current user can't install plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// No need for a nag if user has dismissed it.
		$dismissed = get_user_meta( get_current_user_id(), 'xirki_fa_nag_dismissed', true );
		if ( true === $dismissed || 1 === $dismissed || '1' === $dismissed ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<?php esc_html_e( 'Your theme uses a Font Awesome field for icons. To avoid issues with missing icons on your frontend we recommend you install the official Font Awesome plugin.', 'xirki' ); ?>
			</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=font-awesome&TB_iframe=true&width=600&height=550' ) ); ?>"><?php esc_html_e( 'Install Plugin', 'xirki' ); ?></a>
				<a class="button button-secondary" href="<?php echo esc_url( wp_nonce_url( admin_url( '?dismiss-nag=font-awesome-xirki' ), 'xirki-dismiss-nag', 'nonce' ) ); ?>"><?php esc_html_e( 'Don\'t show this again', 'xirki' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Dismisses the nag.
	 *
	 * @access public
	 * @since 3.0.42
	 * @return void
	 */
	public function dismiss_nag() {
		if ( isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'xirki-dismiss-nag' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( get_current_user_id() && isset( $_GET['dismiss-nag'] ) && 'font-awesome-xirki' === $_GET['dismiss-nag'] ) {
				update_user_meta( get_current_user_id(), 'xirki_fa_nag_dismissed', true );
			}
		}
	}

	/**
	 * Handles showing a nag if the theme is using the deprecated fontawesome field
	 *
	 * @static
	 * @access protected
	 * @since 3.0.42
	 * @param array $args The field arguments.
	 * @return void
	 */
	protected static function maybe_show_fontawesome_nag( $args ) {

		// If we already know we want it, skip check.
		if ( self::$show_fa_nag ) {
			return;
		}

		// Check if the field is fontawesome.
		if ( isset( $args['type'] ) && in_array( $args['type'], array( 'fontawesome', 'xirki-fontawesome' ), true ) ) {

			// Skip check if theme has disabled FA enqueueing via a filter.
			if ( ! apply_filters( 'xirki_load_fontawesome', true ) ) {
				return;
			}

			// If we got this far, we need to show the nag.
			self::$show_fa_nag = true;
		}
	}
}
