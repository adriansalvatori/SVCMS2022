<?php
/**
 * Liker
 * Liker helps you rate and like articles on a website and keep track of results.
 * Exclusively on https://1.envato.market/liker
 *
 * @encoding        UTF-8
 * @version         2.2.3
 * @copyright       (C) 2018 - 2022 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmitry Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/

namespace Merkulove\Liker\Unity;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

/**
 * Plugin class used to prepare plugin variables.
 * It can be called in any time and work without any dependencies.
 *
 * @since 1.0.0
 *
 **/
final class Plugin {

    /**
	 * Plugin version.
	 *
     * @since 1.0.0
     * @access private
	 * @var string
	 **/
	private static $version;

	/**
	 * Plugin name.
	 *
     * @since 1.0.0
     * @access private
	 * @var string
	 **/
	private static $name;

	/**
	 * Using minified css and js files if SCRIPT_DEBUG is turned off.
     *
     * @since 1.0.0
     * @access private
     * @var string
	 **/
	private static $suffix;

	/**
	 * URL to plugin folder, with trailing slash.
	 *
     * @since 1.0.0
     * @access private
     * @var string
	 **/
    private static $url;

	/**
	 * Full PATH to plugin folder, with trailing slash.
	 *
     * @since 1.0.0
     * @access private
     * @var string
	 **/
	private static $path;

	/**
	 * Plugin base name.
	 *
     * @since 1.0.0
     * @access private
     * @var string
	 **/
	private static $basename;

	/**
	 * Plugin admin menu bases.
	 *
     * @since 1.0.0
     * @access private
     * @var array
	 **/
	private static $menu_bases = [];

	/**
	 * Full path to main plugin file.
	 *
     * @since 1.0.0
     * @access private
	 * @var string
	 **/
    private static $plugin_file;

	/**
	 * Plugin slug.
	 *
     * @since 1.0.0
     * @access private
	 * @var string
	 **/
    private static $slug;

    /**
     * Plugin type.
     *
     * @since 1.0.0
     * @access private
     * @var string
     **/
    private static $type;

    /**
     * Settings Tabs.
     *
     * Holds the list of all tabs and fields with settings.
     *
     * @since 1.0.0
     * @access private
     * @var array
     **/
    private static $tabs;

    /**
     * The one true Plugin.
     *
     * @since  1.0.0
     * @access private
     * @var Plugin
     **/
    private static $instance;

    /**
     * Sets up a new Plugin instance.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function __construct() {

        /** Initialize main variables. */
	    $this->initialization();

    }

    /**
     * Initialize main variables.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function initialization() {

        /** Full path to main plugin file. */
        self::$plugin_file = dirname( dirname( dirname( __DIR__ ) ) ) . '/liker.php';

		/** Set Plugin version. */
        self::$version = self::get_plugin_data( 'Version' );

        /** Set Plugin name. */
		self::$name = self::get_plugin_data( 'Name' );

		/** Plugin slug. */
		self::$slug = self::get_plugin_data( 'TextDomain' );

        /** Plugin type. */
        self::$type = self::extract_plugin_type();

		/** Gets the plugin URL (with trailing slash). */
		self::$url = plugin_dir_url( self::$plugin_file );

		/** Gets the plugin PATH. */
		self::$path = plugin_dir_path( self::$plugin_file );

		/** Using minified css and js files if SCRIPT_DEBUG is turned off. */
		self::$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		/** Set plugin basename. */
		self::$basename = plugin_basename( self::$plugin_file  );

		/** Plugin settings page menu base. There may be several. */
        /** For Elementor plugins. */
        if ( in_array( self::$type, [ 'elementor', 'wpbakery' ] ) ) {

            self::$menu_bases[] = 'elementor_page_mdp_liker_settings';
            self::$menu_bases[] = 'settings_page_mdp_liker_settings';

        /** For general WordPress plugins. */
        } else {

            self::$menu_bases[] = 'toplevel_page_mdp_liker_settings';
            self::$menu_bases[] = 'liker_page_mdp_liker_settings';

        }

		/** Fill $tabs field with default settings. */
		$this->default_settings();

    }

    /**
     * Fill $tabs field with default settings.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function default_settings() {

        $this->add_general_tab();
        $this->add_custom_css_tab();
        $this->add_assignments_tab();
        $this->add_activation_tab();
        $this->add_migration_tab();
        $this->add_status_tab();
        $this->add_updates_tab();

    }

    /**
     * Add General Tab to settings page.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function add_general_tab() {

        /** Create empty General Tab. */
        self::$tabs['general'] = [
            'enabled'       => true,
            'class'         => TabGeneral::class,
            'label'         => esc_html__( 'General', 'liker' ),
            'title'         => esc_html__( 'General Settings', 'liker' ),
            'show_title'    => true,
            'icon'          => 'tune',
            'fields'        => []
        ];

        /** Special config for Elementor & WPBakery plugins. */
        if ( in_array( self::extract_plugin_type(), [ 'elementor', 'wpbakery' ] ) ) {
            unset( self::$tabs['general'] );
        }

    }

    /**
     * Add Custom Css Tab to settings page.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function add_custom_css_tab() {

        self::$tabs['custom_css'] = [
            'enabled'       => true,
            'class'         => TabCustomCSS::class,
            'label'         => esc_html__( 'Custom CSS', 'liker' ),
            'title'         => esc_html__( 'Custom CSS', 'liker' ),
            'show_title'    => true,
            'icon'          => 'code',
            'fields'        => [
                'custom_css' => [
                    'type'              => 'css_editor',
                    'description'       => esc_html__( 'Add custom CSS here.', 'liker' ),
                    'show_description'  => true,
                    'default'           => '',
                ]
            ]
        ];

        /** Special config for Elementor & WPBakery plugins. */
        if ( in_array( self::extract_plugin_type(), [ 'elementor', 'wpbakery' ] ) ) {
            unset( self::$tabs['custom_css'] );
        }

    }

    /**
     * Add Assignments Tab to settings page.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function add_assignments_tab() {

        self::$tabs['assignments'] = [
            'enabled'       => true,
            'class'         => TabAssignments::class,
            'label'         => esc_html__( 'Assignments', 'liker' ),
            'title'         => esc_html__( 'Assignments Settings', 'liker' ),
            'show_title'    => true,
            'icon'          => 'flag',
            'assignments'   => [
                'matching_method'   => true,
                'wordpress_content' => true,
                'home_page'         => true,
                'menu_items'        => true,
                'date_time'         => true,
                'languages'         => true,
                'user_roles'        => true,
                'url'               => true,
                'devices'           => true,
                'operating_systems' => true,
                'browsers'          => true,
                'ip_addresses'      => true,
                'custom_php'        => true,
            ],
            'fields'        => [
                'assignments' => [
                    'type'              => 'hidden',
                    'default'           => '{|matchingMethod|:1,|WPContent|:0,|WPContentVal|:||,|homePage|:0,|menuItems|:0,|menuItemsVal|:||,|dateTime|:0,|dateTimeStart|:||,|dateTimeEnd|:||,|languages|:0,|languagesVal|:||,|userRoles|:0,|userRolesVal|:||,|URL|:0,|URLVal|:||,|devices|:0,|devicesVal|:||,|os|:0,|osVal|:||,|browsers|:0,|browsersVal|:||,|mobileBrowsersVal|:||,|IPs|:0,|IPsVal|:||,|PHP|:0,|PHPVal|:||}',
                ]
            ]
        ];

        /** Special config for Elementor & WPBakery plugins. */
        if ( in_array( self::extract_plugin_type(), [ 'elementor', 'wpbakery' ] ) ) {
            unset( self::$tabs['assignments'] );
        }

    }

    /**
     * Add Activation Tab to settings page.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function add_activation_tab() {

        /** Not show if plugin don't have Envato ID. */
        if ( ! EnvatoItem::get_instance()->get_id() ) { return; }

        self::$tabs['activation'] = [
            'enabled'       => true,
            'class'         => TabActivation::class,
            'label'         => esc_html__( 'Activation', 'liker' ),
            'title'         => esc_html__( 'Plugin Activation', 'liker' ),
            'show_title'    => false,
            'icon'          => 'vpn_key',
            'fields'        => [
                'envato_purchase_code_' . EnvatoItem::get_instance()->get_id() => [
                    'type'              => 'text',
                    'default'           => '',
                ]
            ]
        ];

    }

    /**
     * Add Status Tab to settings page.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function add_status_tab() {

        self::$tabs['status'] = [
            'enabled'       => true,
            'class'         => TabStatus::class,
            'label'         => esc_html__( 'Status', 'liker' ),
            'title'         => esc_html__( 'System Status', 'liker' ),
            'show_title'    => true,
            'icon'          => 'insert_chart_outlined',
            'reports'       => [
                'server'    => [
                    'enabled'               => true,
                    'os'                    => true,
                    'software'              => true,
                    'mysql_version'         => true,
                    'php_version'           => true,
                    'write_permissions'     => true,
                    'zip_installed'         => true,
                    'curl_installed'        => true,
                    'elementor_installed'   => false,
                    'wpbakery_installed'    => false,
                    'allow_url_fopen'       => true,
                    'dom_installed'         => true,
                    'xml_installed'         => true,
                    'bcmath_installed'      => true,
                ],
                'wordpress' => [
                    'enabled' => true
                ],
                'plugins'   => [
                    'enabled' => true
                ],
                'theme'     => [
                    'enabled' => true
                ],
            ]
        ];

        /** Special config for Elementor plugins. */
        if ( 'elementor' === self::extract_plugin_type() ) {
            self::$tabs['status']['reports']['server']['elementor_installed'] = true;
            self::$tabs['status']['reports']['server']['allow_url_fopen'] = false;
            self::$tabs['status']['reports']['server']['dom_installed'] = false;
            self::$tabs['status']['reports']['server']['xml_installed'] = false;
            self::$tabs['status']['reports']['server']['bcmath_installed'] = false;
        }

        /** Special config for WPBakery plugins. */
        if ( 'wpbakery' === self::extract_plugin_type() ) {
            self::$tabs['status']['reports']['server']['wpbakery_installed'] = true;
            self::$tabs['status']['reports']['server']['allow_url_fopen'] = false;
            self::$tabs['status']['reports']['server']['dom_installed'] = false;
            self::$tabs['status']['reports']['server']['xml_installed'] = false;
            self::$tabs['status']['reports']['server']['bcmath_installed'] = false;
        }

    }

    /**
     * Add Updates Tab to settings page.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function add_updates_tab() {

        self::$tabs['updates'] = [
            'enabled'       => true,
            'class'         => TabUpdates::class,
            'label'         => esc_html__( 'Updates', 'liker' ),
            'title'         => esc_html__( 'Updates', 'liker' ),
            'show_title'    => true,
            'icon'          => 'update',
            'fields'        => [
                'check_ssl' => [
                    'type'              => 'switcher',
                    'default'           => 'off',
                ]
            ]
        ];

    }

    /**
     * Add Uninstall Tab to settings page.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function add_migration_tab() {

        self::$tabs['migration'] = [
            'enabled'       => true,
            'class'         => TabMigration::class,
            'label'         => esc_html__( 'Migration', 'liker' ),
            'title'         => esc_html__( 'Migration Settings', 'liker' ),
            'show_title'    => true,
            'icon'          => 'multiple_stop',
            'fields'        => [
                'delete_plugin' => [
                    'type'              => 'select',
                    'options'           => [
                        'plugin'                => esc_html__( 'Delete plugin only', 'liker' ),
                        'plugin+settings'       => esc_html__( 'Delete plugin and settings', 'liker' ),
                        'plugin+settings+data'  => esc_html__( 'Delete plugin, settings and data', 'liker' ),
                    ],
                    'default'           => 'plugin',
                ]
            ]
        ];

        /** Special config for Elementor & WPBakery plugins. */
        if ( in_array( self::extract_plugin_type(), [ 'elementor', 'wpbakery' ] ) ) {
            unset( self::$tabs['migration']['fields']['delete_plugin']['options']['plugin+settings+data'] );
        }

    }

    /**
     * Return current plugin metadata.
     *
     * @param string $field - Field name from plugin data.
     *
     * @since 1.0.0
     * @access private
     *
     * @return string|array {
     *     Plugin data. Values will be empty if not supplied by the plugin.
     *
     *     @type string $Name        Name of the plugin. Should be unique.
     *     @type string $Title       Title of the plugin and link to the plugin's site (if set).
     *     @type string $Description Plugin description.
     *     @type string $Author      Author's name.
     *     @type string $AuthorURI   Author's website address (if set).
     *     @type string $Version     Plugin version.
     *     @type string $TextDomain  Plugin textdomain.
     *     @type string $DomainPath  Plugins relative directory path to .mo files.
     *     @type bool   $Network     Whether the plugin can only be activated network-wide.
     *     @type string $RequiresWP  Minimum required version of WordPress.
     *     @type string $RequiresPHP Minimum required version of PHP.
     * }
     **/
    private static function get_plugin_data( $field ) {

        static $plugin_data;

        /** We already have $plugin_data. */
        if ( $plugin_data !== null ) {
            return $plugin_data[$field];
        }

        /** This is first time call of method, so prepare $plugin_data. */
        if ( ! function_exists('get_plugin_data') ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $plugin_data = get_plugin_data( self::get_plugin_file() );

        return $plugin_data[$field];

    }

    /**
     * Get Plugin version.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string
     **/
    public static function get_version() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) { self::get_instance(); }

        return self::$version;

    }

    /**
     * Get Plugin Name.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string
     **/
    public static function get_name() {

        return self::get_plugin_data( 'Name' );

    }

    /**
     * Get .min suffix.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string
     **/
    public static function get_suffix() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) { self::get_instance(); }

        return self::$suffix;

    }

    /**
     * Get URL to plugin folder with trailing slash.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string
     **/
    public static function get_url() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) { self::get_instance(); }

        return self::$url;

    }

    /**
     * Get full Path to plugin folder with trailing slash.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string
     **/
    public static function get_path() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) { self::get_instance(); }

        return self::$path;

    }

    /**
     * Get Plugin Basename.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string
     **/
    public static function get_basename() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) { self::get_instance(); }

        return self::$basename;

    }

    /**
     * Get plugin menu bases.
     *
     * @since 1.0.0
     * @access public
     *
     * @return array
     **/
    public static function get_menu_bases() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) { self::get_instance(); }

        return self::$menu_bases;

    }

    /**
     * Get path to plugin file.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string
     **/
    public static function get_plugin_file() {

        return dirname( dirname( dirname( __DIR__ ) ) ) . '/liker.php';

    }

    /**
     * Get Plugin slug.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string
     **/
    public static function get_slug() {

        return self::get_plugin_data( 'TextDomain' );

    }

    /**
     * Get Plugin type.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string
     **/
    public static function get_type() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) { self::get_instance(); }

        return self::$type;

    }

    /**
     * Set Plugin type.
     *
     * @param string $type - Allow change plugin type.
     *
     * @since  1.0.0
     * @access public
     *
     * @return void
     **/
    public static function set_type( $type ) {

        if ( empty( $type ) ) { return; }

        self::$type = $type;

    }

    /**
     * Extract plugin type from slug.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string
     **/
    private static function extract_plugin_type() {

        $slug = self::get_plugin_data( 'TextDomain' );
        $type = 'wordpress';

        if ( strpos( $slug, 'elementor' ) !== false ) {
            $type = 'elementor';
        }

        if ( strpos( $slug, 'wpbakery' ) !== false ) {
            $type = 'wpbakery';
        }

        return $type;

    }

    /**
     * Get Plugin tabs.
     *
     * @since 1.0.0
     * @access public
     *
     * @return array
     **/
    public static function get_tabs() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) { self::get_instance(); }

        return self::$tabs;

    }

    /**
     * Set Plugin tabs.
     *
     * @param array $tabs - Tabs and fields with settings.
     *
     * @since  1.0.0
     * @access public
     *
     * @return void
     **/
    public static function set_tabs( $tabs ) {

        self::$tabs = $tabs;

    }

    /**
     * Get instance of Plugin.
     *
     * Insures that only one instance of Plugin exists in memory at any one time.
     *
     * @static
     * @since 1.0.0
     *
     * @return Plugin
     **/
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

            self::$instance = new self;

        }

        return self::$instance;

    }

}
