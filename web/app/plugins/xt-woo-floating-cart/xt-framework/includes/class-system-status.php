<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'XT_Framework_System_Status' ) ) {

    /**
     * XT_Framework System Status Panel
     *
     * Setting Page to Manage Plugins
     *
     * @class      XT_Framework_System_Status
     * @package    XT_Framework
     * @since      1.0
     * @author     XplodedThemes
     */
    class XT_Framework_System_Status {

        /**
         * Core class reference.
         *
         * @since    1.0.0
         * @access   private
         * @var      XT_Framework $core
         */
        private $core;

        /**
         * @var string the page slug
         */
        protected $slug = 'system-status';

        /**
         * @var array The settings require to add the submenu page "System Status"
         */
        protected $_settings = array();

        /**
         * @var array Theme info
         */
        protected $_theme = array();

        /**
         * @var array plugins requirements list
         */
        protected $_plugins_requirements = array();

        /**
         * @var array requirements labels
         */
        protected $_requirement_labels = array();

        /**
         * Single instance of the class
         *
         * @var \XT_Framework_System_Status
         * @since 1.0.0
         */
        protected static $_instance = null;

        /**
         * Constructor
         *
         * @param XT_Framework $core
         * @since  1.0.0
         * @author XplodedThemes
         */
        public function __construct( $core ) {

            $this->core = $core;

            $this->_requirement_labels = array(
                'min_wp_version'    => esc_html__( 'WordPress Version', 'xt-framework' ),
                'min_wc_version'    => esc_html__( 'WooCommerce Version', 'xt-framework' ),
                'wp_memory_limit'   => esc_html__( 'Available Memory', 'xt-framework' ),
                'min_php_version'   => esc_html__( 'PHP Version', 'xt-framework' ),
                'min_tls_version'   => esc_html__( 'TLS Version', 'xt-framework' ),
                'wp_cron_enabled'   => esc_html__( 'WordPress Cron', 'xt-framework' ),
                'simplexml_enabled' => esc_html__( 'SimpleXML', 'xt-framework' ),
                'mbstring_enabled'  => esc_html__( 'MultiByte String', 'xt-framework' ),
                'gd_enabled'        => esc_html__( 'GD Library', 'xt-framework' ),
                'iconv_enabled'     => esc_html__( 'Iconv Module', 'xt-framework' ),
                'opcache_enabled'   => esc_html__( 'OPCache Save Comments', 'xt-framework' ),
                'url_fopen_enabled' => esc_html__( 'URL FOpen', 'xt-framework' ),
            );

            add_filter( 'xtfw_admin_tabs', array( $this, 'add_system_status_tab'), 1, 1 );
            add_filter( 'xtfw_global_menu_badges', array( $this, 'global_menu_badges'), 1, 1 );
            add_action( 'upgrader_process_complete', array( $this, 'flush_cache'), 10, 2 );
            add_action( 'after_switch_theme', array( $this, 'flush_cache'), 10, 2 );

            add_action('xtfw_plugins_loaded', function() {

                if($this->core->framework_tabs()->is_tab($this->slug)) {
                    $this->flush_cache(false);
                }

                $this->render_notices();

            }, 99);
        }

        public function flush_cache($flushSystemInfo = true) {

            if($flushSystemInfo || !empty($_GET['xtfw-refresh-sysinfo'])) {
                delete_option('xt_framework_system_info');
            }

            delete_transient( 'xt_framework_system_info_templates' );
        }

        public function get_cached_template_overrides() {

            $cache_key = 'xt_framework_system_info_templates';
            $template_overrides = wp_cache_get( $cache_key );

            if ( false === $template_overrides ) {

                $template_overrides = get_transient( $cache_key );

                wp_cache_set($cache_key, $template_overrides);
            }

            return $template_overrides;
        }

        public function get_badges() {

            $badges = array();

            $system_info  = get_option( 'xt_framework_system_info' );

            if(!empty($system_info['errors'])) {
                $badges[] = array(
                    'title' => esc_html__( 'The system check has detected some compatibility issues on your installation.', 'xt-framework' ),
                    'content' => '!'
                );
            }

            $template_overrides = $this->get_cached_template_overrides();

            if(!empty($template_overrides['outdated'])) {
                $badges[] = array(
                    'title' => esc_html__( 'The system check has detected some outdated plugin templates', 'xt-framework' ),
                    'content' => $template_overrides['outdated']
                );
            }

            return $badges;
        }

        public function global_menu_badges($badges) {

            return array_merge($badges, $this->get_badges());
        }

        public function add_system_status_tab( $tabs ) {

            $tabs[] = array(
                'id'        => $this->slug,
                'title'     => esc_html__( 'System Status', 'xt-framework' ),
                'badges'    => array($this, 'get_badges'),
                'order'     => 0,
                'show_menu' => true,
                'content'   => array(
                    'type'     => 'function',
                    'function' => array( $this, 'show_information_panel' )
                ),
                'callback' => array($this, 'system_status_tab_callback')
            );

            return $tabs;
        }

        /**
         * System status callback function
         *
         * @since    1.0.0
         */
        public function system_status_tab_callback() {

            add_action( 'admin_enqueue_scripts', array($this, 'enqueue_assets'));
        }

        /**
         * Register the JavaScript & stylesheets for the System Status tabs area.
         *
         * @since    1.0.0
         */
        public function enqueue_assets() {

            wp_add_inline_script( 'xt-jquery-tiptip', "
			    
			    jQuery(document).on('ready', function() {
                    jQuery( '.xtfw-help-tip' ).tipTip( {
                        'attribute': 'data-tip',
                        'fadeIn': 50,
                        'fadeOut': 50,
                        'delay': 200
                    });
                    
                    jQuery('.template-overrides thead').on('click', function() {
                    
                        var table = jQuery(this).closest('table');
                        if(!table.hasClass('active-meta-box')) {
                            table.addClass('active-meta-box');
                        }else{
                            table.removeClass('active-meta-box');
                        }
                            
                    });
			    });
			");

            wp_enqueue_style('xt-jquery-tiptip');
            wp_enqueue_script('xt-jquery-tiptip');
        }

        /**
         * Get latest version of a theme by slug.
         *
         * @param  object $theme WP_Theme object.
         * @return string Version number if found.
         */
        public function get_latest_theme_version( $theme ) {

            include_once ABSPATH . 'wp-admin/includes/theme.php';

            $api = themes_api(
                'theme_information',
                array(
                    'slug'   => $theme->get_stylesheet(),
                    'fields' => array(
                        'sections' => false,
                        'tags'     => false,
                    ),
                )
            );

            $update_theme_version = 0;

            // Check .org for updates.
            if ( is_object( $api ) && ! is_wp_error( $api ) ) {
                $update_theme_version = $api->version;
            } elseif ( strstr( $theme->{'Author URI'}, 'woothemes' ) ) { // Check WooThemes Theme Version.
                $theme_dir          = substr( strtolower( str_replace( ' ', '', $theme->Name ) ), 0, 45 ); // @codingStandardsIgnoreLine.
                $theme_version_data = get_transient( $theme_dir . '_version_data' );

                if ( ! empty( $theme_version_data['version'] ) ) {
                    $update_theme_version = $theme_version_data['version'];
                }
            }

            return $update_theme_version;
        }

        /**
         * Scan the template files.
         *
         * @param  string $template_path Path to the template directory.
         * @return array
         */
        public function scan_template_files( $template_path ) {

            $result = array();

            if(!is_dir($template_path)) {
                return $result;
            }

            $files  = @scandir( $template_path ); // @codingStandardsIgnoreLine.

            if ( ! empty( $files ) ) {

                foreach ( $files as $key => $value ) {

                    if ( ! in_array( $value, array( '.', '..' ), true ) ) {

                        if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ) {
                            $sub_files = $this->scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
                            foreach ( $sub_files as $sub_file ) {
                                $result[] = $value . DIRECTORY_SEPARATOR . $sub_file;
                            }
                        } else {
                            $result[] = basename($value, ".php");
                        }
                    }
                }
            }
            return $result;
        }

        /**
         * Retrieve metadata from a file. Based on WP Core's get_file_data function.
         *
         * @since  2.1.1
         * @param  string $file Path to the file.
         * @return string
         */
        public function get_file_version( $file ) {

            // Avoid notices if file does not exist.
            if ( ! file_exists( $file ) ) {
                return '';
            }

            // We don't need to write to the file, so just open for reading.
            $fp = fopen( $file, 'r' ); // @codingStandardsIgnoreLine.

            // Pull only the first 8kiB of the file in.
            $file_data = fread( $fp, 8192 ); // @codingStandardsIgnoreLine.

            // PHP will close file handle, but we are good citizens.
            fclose( $fp ); // @codingStandardsIgnoreLine.

            // Make sure we catch CR-only line endings.
            $file_data = str_replace( "\r", "\n", $file_data );
            $version   = '';

            if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( '@version', '/' ) . '(.*)$/mi', $file_data, $match ) && $match[1] ) {
                $version = _cleanup_header_comment( $match[1] );
            }
            if ( empty($version) && preg_match( '/^[ \t\/*#@]*' . preg_quote( '@since', '/' ) . '(.*)$/mi', $file_data, $match ) && $match[1] ) {
                $version = _cleanup_header_comment( $match[1] );
            }

            return $version;
        }

        /**
         * Get info on the current active theme, info on parent theme (if present)
         * and a list of template overrides.
         *
         * @return array
         */
        public function get_theme_info() {

            $active_theme = wp_get_theme();

            // Get parent theme info if this theme is a child theme, otherwise
            // pass empty info in the response.
            if ( is_child_theme() ) {
                $parent_theme      = wp_get_theme( $active_theme->template );
                $parent_theme_info = array(
                    'parent_name'           => $parent_theme->name,
                    'parent_version'        => $parent_theme->version,
                    'parent_version_latest' => $this->get_latest_theme_version( $parent_theme ),
                    'parent_author_url'     => $parent_theme->{'Author URI'},
                );
            } else {
                $parent_theme_info = array(
                    'parent_name'           => '',
                    'parent_version'        => '',
                    'parent_version_latest' => '',
                    'parent_author_url'     => '',
                );
            }

            /**
             * Scan the theme directory for all XT plugins templates to see if our theme
             * overrides any of them.
             */

            $template_overrides = $this->get_cached_template_overrides();

            if( false === $template_overrides ) {

                $overrides = array();
                $outdated  = 0;
                $total_overrides = 0;

                foreach ( $this->core->instances() as $instance ) {

                    $plugin_name = $instance->plugin_name();

                    $plugin_template_path = $instance->plugin_path( 'public/templates' );
                    $scan_templates       = $this->scan_template_files( $plugin_template_path );

                    if ( ! empty( $scan_templates ) ) {
                        $overrides[ $plugin_name ] = array(
                            'outdated'  => 0,
                            'overrides' => array()
                        );

                        foreach ( $scan_templates as $template ) {

                            $located    = $instance->get_theme_template( $template );
                            $theme_file = file_exists( $located ) ? $located : false;

                            if ( ! empty( $theme_file ) ) {

                                $core_version  = $this->get_file_version( $plugin_template_path . $template . ".php" );
                                $theme_version = $this->get_file_version( $theme_file );

                                if ( $core_version && ( empty( $theme_version ) || version_compare( $theme_version, $core_version, '<' ) ) ) {
                                    $outdated ++;
                                    $overrides[ $plugin_name ]['outdated']++;
                                }
                                $overrides[ $plugin_name ]['overrides'][] = array(
                                    'plugin_name'  => $instance->plugin_name(),
                                    'plugin_slug'  => $instance->plugin_slug(),
                                    'file'         => str_replace( WP_CONTENT_DIR . '/themes/', '', $theme_file ),
                                    'version'      => $theme_version,
                                    'core_version' => $core_version,
                                );

                                $total_overrides++;

                            }
                        }
                    }
                }

                $template_overrides = !empty($total_overrides) ? array(
                    'outdated' => $outdated,
                    'overrides' => $overrides
                ) : array();

                set_transient('xt_framework_system_info_templates', $template_overrides, WEEK_IN_SECONDS);
            }

            $active_theme_info = array(
                'name'                    => $active_theme->name,
                'version'                 => $active_theme->version,
                'version_latest'          => $this->get_latest_theme_version( $active_theme ),
                'author_url'              => esc_url_raw( $active_theme->{'Author URI'} ),
                'is_child_theme'          => is_child_theme(),
                'has_woocommerce_support' => current_theme_supports( 'woocommerce'),
                'templates'               => $template_overrides
            );

            return array_merge( $active_theme_info, $parent_theme_info );

        }

        /**
         * Add "System Information" page template under the framework menu
         *
         * @return void
         * @since  1.0.0
         * @author XplodedThemes
         */
        public function show_information_panel() {

            $this->check_system_status();

            $labels = $this->_requirement_labels;

            $system_info        = get_option( 'xt_framework_system_info' );

            $recommended_memory = 134217728;
            $output_ip          = 'n/a';

            if ( apply_filters( 'xt_framework_system_status_check_ip', true ) ) {

                // Using 3rd party api service (https://api.ipify.org) to retrieve client IP to be displayed in system info page
                $output_ip = wp_remote_retrieve_body(wp_remote_get( 'https://api.ipify.org', array('sslverify' => false)));
            }

            ?>
            <div id="xt-framework-sysinfo" class="wrap xt-framework-system-info">

                <?php if ( ! isset( $_GET['xtfw-phpinfo'] ) || $_GET['xtfw-phpinfo'] !== 'true' ): ?>

                    <h3><?php echo esc_html__( 'General Info', 'xt-framework' ); ?></h3>
                    <table class="widefat striped general-info-table">
                        <tr>
                            <th>
                                <?php esc_html_e( 'Site URL', 'xt-framework' ); ?>
                            </th>
                            <td class="requirement-value">
                                <a target="_blank"
                                   href="<?php echo esc_url( get_site_url() ); ?>"><?php echo esc_url( get_site_url() ); ?></a>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php esc_html_e( 'Output IP Address', 'xt-framework' ); ?>
                            </th>
                            <td class="requirement-value">
                                <?php echo esc_html($output_ip); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php esc_html_e( 'XT Framework Active PATH', 'xt-framework' ); ?>
                            </th>
                            <td class="requirement-value">
                                <?php echo '/'.basename(WP_PLUGIN_DIR).str_replace(WP_PLUGIN_DIR, "", $this->core->plugin_framework_path(null, null, true)); ?>

                            </td>
                        </tr>
                        <tr>
                            <th>
                                <?php esc_html_e( 'XT Framework Active Version', 'xt-framework' ); ?>
                            </th>
                            <td class="requirement-value success">
                                <strong><?php echo esc_html($this->core->framework_version()); ?></strong>
                            </td>
                        </tr>
                    </table>

                    <h3><?php echo esc_html__( 'Active XT Plugins', 'xt-framework' ); ?><?php echo (is_network_admin() ? ' <span class="xtfw-heading-badge">'.esc_html__('Network Activated').'</span>' : ''); ?></h3>
                    <?php if(count($this->core->instances())):?>
                        <table class="widefat striped xt-plugins-table">
                            <thead>
                            <tr>
                                <th>
                                    <?php echo esc_html('Plugin Name', 'xt-framework');?>
                                </th>
                                <th>
                                    <?php echo esc_html('Version', 'xt-framework');?>
                                </th>
                                <th>
                                    <?php echo esc_html('FW Version', 'xt-framework');?>
                                </th>
                                <th>
                                    <?php echo esc_html('Path', 'xt-framework');?>
                                </th>
                                <th>
                                    <?php echo esc_html('License Type', 'xt-framework');?>
                                </th>
                                <th>
                                </th>
                            </tr>
                            </thead>
                            <?php

                            foreach ( $this->core->instances() as $instance ) : ?>

                                <?php
                                $version_class = !$instance->plugin_is_latest_framework_version() ? 'error' : 'success';
                                ?>
                                <tr>
                                    <th class="requirement-name">
                                        <a href="<?php echo esc_url($instance->plugin_self_admin_url()); ?>">
                                            <?php echo esc_html($instance->plugin_name()); ?>
                                        </a>
                                    </th>
                                    <td class="requirement-value">
                                        <strong>v.<?php echo esc_html($instance->plugin_version()); ?></strong>
                                    </td>
                                    <td class="requirement-value <?php echo esc_attr($version_class);?>">
                                        <strong>v.<?php echo esc_html($instance->plugin_framework_version()); ?></strong>
                                    </td>
                                    <td class="requirement-value">
                                        <?php echo '/' . basename( WP_PLUGIN_DIR ) . '/' . basename( $instance->plugin_path() ); ?>
                                    </td>
                                    <td class="requirement-value">
                                        <img src="<?php echo esc_url( xtfw_dir_url( XTFW_DIR_ADMIN_TABS_ASSETS ) ); ?>/images/markets/<?php echo esc_attr( $instance->market() ); ?>.svg" class="xtfw-market-logo xtfw-market-<?php echo esc_attr( $instance->market() ) ?>"/>
                                    </td>
                                    <td class="requirement-value align-right">
                                        <?php echo wp_kses_post($instance->plugin_tabs()->render_header_badges( false )); ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                        </table>
                    <?php else : ?>
                        <table class="widefat striped xt-module-na" cellspacing="0">
                            <tr>
                                <td class="requirement-name" data-export-label="XT Modules"><?php esc_html_e( 'No Active XT Plugins Found', 'xt-framework'); ?> <?php echo (is_network_admin() ? esc_html__('at the Network Level') : ''); ?></td>
                            </tr>
                        </table>
                    <?php endif; ?>

                    <h3><?php echo esc_html__( 'Active XT Shared Modules', 'xt-framework' ); ?></h3>
                    <?php if($this->core->has_modules()):?>
                        <table class="widefat striped xt-plugins-table">
                            <thead>
                            <tr>
                                <th>
                                    <?php echo esc_html__('Module Name', 'xt-framework');?>
                                </th>
                                <th>
                                    <?php echo esc_html__('Loaded From', 'xt-framework');?>
                                </th>
                                <th>
                                    <?php echo esc_html__('Shared By', 'xt-framework');?>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ( $this->core->modules()->all() as $module ) : ?>
                                <tr>
                                    <th class="requirement-name">
                                        <?php if(!is_network_admin()):?>
                                            <a href="<?php echo esc_url($module->admin_url()); ?>">
                                                <?php echo esc_html($module->menu_name()); ?>
                                            </a>
                                        <?php else: ?>
                                            <strong><?php echo esc_html($module->menu_name()); ?></strong>
                                        <?php endif; ?>
                                    </th>
                                    <td class="requirement-value">
                                        <?php echo esc_url(str_replace(WP_PLUGIN_DIR, "", $module->path())); ?>
                                    </td>
                                    <td class="requirement-value">
                                        <ul class="module_shared_by">
                                            <?php if(!empty($module->shared_by())):?>
                                                <?php foreach($module->shared_by() as $plugin): ?>
                                                    <li><?php echo esc_html($plugin);?></li>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <li><?php echo esc_html__('None', 'xt-framework');?></li>
                                            <?php endif; ?>
                                        </ul>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <table class="widefat striped xt-module-na" cellspacing="0">
                            <tr>
                                <td class="requirement-name" data-export-label="XT Modules"><?php esc_html_e( 'No shared modules found', 'xt-framework'); ?></td>
                            </tr>
                        </table>
                    <?php endif; ?>

                    <h3><?php echo esc_html__( 'Active Theme', 'xt-framework' ); ?></h3>
                    <table class="widefat striped active-theme" cellspacing="0">
                        <tbody>
                        <tr>
                            <th class="requirement-name" style="width: 25%" data-export-label="Name"><?php esc_html_e( 'Name', 'xt-framework'); ?>:</th>
                            <td style="width: 3%" class="help"><?php xtfw_help_tip( esc_html__( 'The name of the current active theme.', 'xt-framework') ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
                            <td><?php echo esc_html( $this->_theme['name'] ); ?></td>
                        </tr>
                        <tr>
                            <th class="requirement-name" data-export-label="Version"><?php esc_html_e( 'Version', 'xt-framework'); ?>:</th>
                            <td class="help"><?php xtfw_help_tip( esc_html__( 'The installed version of the current active theme.', 'xt-framework') ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
                            <td>
                                <?php
                                if ( version_compare( $this->_theme['version'], $this->_theme['version_latest'], '<' ) ) {
                                    /* translators: 1: current version. 2: latest version */
                                    echo esc_html( sprintf( __( '%1$s (update to version %2$s is available)', 'xt-framework'), $this->_theme['version'], $this->_theme['version_latest'] ) );
                                } else {
                                    echo esc_html( $this->_theme['version'] );
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="requirement-name" data-export-label="Author URL"><?php esc_html_e( 'Author URL', 'xt-framework'); ?>:</th>
                            <td class="help"><?php xtfw_help_tip( esc_html__( 'The theme developers URL.', 'xt-framework') ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
                            <td><a target="_blank" href="<?php echo esc_url( $this->_theme['author_url'] ); ?>"><?php echo esc_html( $this->_theme['author_url'] ); ?></a></td>
                        </tr>
                        <tr>
                            <th class="requirement-name" data-export-label="Child Theme"><?php esc_html_e( 'Child theme', 'xt-framework'); ?>:</th>
                            <td class="help"><?php xtfw_help_tip( esc_html__( 'Displays whether or not the current theme is a child theme.', 'xt-framework') ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
                            <td>
                                <?php
                                if ( $this->_theme['is_child_theme'] ) {
                                    echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                                } else {
                                    /* Translators: %s docs link. */
                                    echo '<span class="dashicons dashicons-no-alt"></span> &ndash; ' . wp_kses_post( sprintf( __( 'If you are modifying WooCommerce on a parent theme that you did not build personally we recommend using a child theme. See: <a href="%s" target="_blank">How to create a child theme</a>', 'xt-framework'), 'https://developer.wordpress.org/themes/advanced-topics/child-themes/' ) );
                                }
                                ?>
                            </td>
                        </tr>
                        <?php if ( $this->_theme['is_child_theme'] ) : ?>
                            <tr>
                                <th class="requirement-name" data-export-label="Parent Theme Name"><?php esc_html_e( 'Parent theme name', 'xt-framework'); ?>:</th>
                                <td class="help"><?php xtfw_help_tip( esc_html__( 'The name of the parent theme.', 'xt-framework') ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
                                <td><?php echo esc_html( $this->_theme['parent_name'] ); ?></td>
                            </tr>
                            <tr>
                                <th class="requirement-name" data-export-label="Parent Theme Version"><?php esc_html_e( 'Parent theme version', 'xt-framework'); ?>:</th>
                                <td class="help"><?php xtfw_help_tip( esc_html__( 'The installed version of the parent theme.', 'xt-framework') ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
                                <td>
                                    <?php
                                    echo esc_html( $this->_theme['parent_version'] );
                                    if ( version_compare( $this->_theme['parent_version'], $this->_theme['parent_version_latest'], '<' ) ) {
                                        /* translators: %s: parent theme latest version */
                                        echo ' &ndash; <strong style="color:red;">' . sprintf( esc_html__( '%s is available', 'xt-framework'), esc_html( $this->_theme['parent_version_latest'] ) ) . '</strong>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="requirement-name" data-export-label="Parent Theme Author URL"><?php esc_html_e( 'Parent theme author URL', 'xt-framework'); ?>:</th>
                                <td class="help"><?php xtfw_help_tip( esc_html__( 'The parent theme developers URL.', 'xt-framework') ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
                                <td><a target="_blank" href="<?php echo esc_url( $this->_theme['parent_author_url'] ); ?>"><?php echo esc_html( $this->_theme['parent_author_url'] ); ?></a></td>
                            </tr>
                        <?php endif ?>
                        <tr>
                            <th class="requirement-name" data-export-label="WooCommerce Support"><?php esc_html_e( 'WooCommerce support', 'xt-framework'); ?>:</th>
                            <td class="help"><?php xtfw_help_tip( esc_html__( 'Displays whether or not the current active theme declares WooCommerce support.', 'xt-framework') ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
                            <td>
                                <?php
                                if ( ! $this->_theme['has_woocommerce_support'] ) {
                                    echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html__( 'Not declared', 'xt-framework') . '</mark>';
                                } else {
                                    echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                                }
                                ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <h3 id="template-overrides">
                        <?php echo esc_html__( 'Template Overrides', 'xt-framework' ); ?>
                        <span class="info-link">
                            <a href="https://docs.xplodedthemes.com/article/127-template-structure" target="_blank">
                                <?php xtfw_help_tip(esc_html__( 'Click here to learn how to override plugin templates within your theme', 'xt-framework')); ?>
                            </a>
                        </span>
                        <?php if ( !empty($this->_theme['templates']) && $this->_theme['templates']['outdated'] > 0) : ?>
                            <span class="action-link">
                            <mark class="error">
                                <span class="dashicons dashicons-warning"></span>
                            </mark>
                            <a href="https://docs.xplodedthemes.com/article/128-fixing-outdated-plugin-templates" target="_blank">
                                <?php esc_html_e( 'Learn how to update outdated templates', 'xt-framework'); ?>
                            </a>
                        </span>
                        <?php endif; ?>
                    </h3>

                    <?php if ( !empty($this->_theme['templates']) && ! empty( $this->_theme['templates']['overrides'] ) ) :

                        foreach ($this->_theme['templates']['overrides'] as $plugin_name => $data) : ?>
                            <table class="widefat striped template-overrides">
                                <thead>
                                <tr>
                                    <th class="requirement-name" style="width: 25%;font-weight: bold;vertical-align: top;" data-export-label="Overrides">
                                        <?php echo esc_html($plugin_name); ?>
                                        <?php if(!empty($data['outdated'])): ?>
                                            <mark class="error"><?php echo sprintf( esc_html(_n( '%s outdated template', '%s outdated templates', $data['outdated'], 'xt-framework' )), number_format_i18n( $data['outdated'] ) ); ?> </mark>
                                        <?php endif; ?>
                                        <span class="arrow-up dashicons dashicons-arrow-up-alt2"></span>
                                        <span class="arrow-down dashicons dashicons-arrow-down-alt2"></span>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td class="requirement-value">
                                        <?php
                                        $i = 0;
                                        foreach($data['overrides'] as $override) {
                                            if ( $override['core_version'] && ( empty( $override['version'] ) || version_compare( $override['version'], $override['core_version'], '<' ) ) ) {
                                                $current_version = $override['version'] ? $override['version'] : '-';
                                                printf(
                                                /* Translators: %1$s: Template name, %2$s: Template version, %3$s: Core version. */
                                                    esc_html__( '%1$s version %2$s is out of date. The core version is %3$s', 'xt-framework' ),
                                                    '<code>' . esc_html( $override['file'] ) . '</code>',
                                                    '<strong style="color:red">' . esc_html( $current_version ) . '</strong>',
                                                    '<strong style="color:green">' .esc_html( $override['core_version'] ) . '</strong>'
                                                );
                                            } else {
                                                echo '<code>' . esc_html( $override['file'] ) . '</code>';
                                            }
                                            echo '<br>';
                                            $i++;
                                        }
                                        ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                        <?php endforeach; ?>

                    <?php else : ?>
                        <table class="widefat striped template-overrides-na" cellspacing="0">
                            <tr>
                                <td class="requirement-name" data-export-label="Overrides"><?php esc_html_e( 'No template overrides found', 'xt-framework'); ?></td>
                            </tr>
                        </table>
                    <?php endif; ?>

                    <h3>
                        <?php echo esc_html__( 'System Info', 'xt-framework' ); ?>
                        <span class="action-link">
                            <a href="<?php echo add_query_arg( array( 'xtfw-refresh-sysinfo' => 'true' ) ) ?>" title="<?php esc_attr_e( 'Refresh System Info', 'xt-framework'); ?>">
                                <span class="dashicons dashicons-image-rotate"></span>
                            </a>
                        </span>
                    </h3>
                    <table class="widefat striped system-info-table">
                        <?php foreach ( $system_info['system_info'] as $key => $item ): ?>
                            <?php
                            $to_be_enabled = strpos( $key, '_enabled' ) !== false;
                            $has_errors    = isset( $item['errors'] );
                            $has_warnings  = false;

                            if ( $key == 'wp_memory_limit' && ! $has_errors ) {
                                $has_warnings = $item['value'] < $recommended_memory;
                            } elseif ( ( $key == 'min_tls_version') && ! $has_errors ) {
                                $has_warnings = $item['value'] == 'n/a';
                            }

                            if(empty($labels[ $key ])) {
                                continue;
                            }
                            ?>
                            <tr>
                                <th class="requirement-name">
                                    <?php echo wp_kses_post($labels[ $key ]); ?>
                                </th>
                                <td class="requirement-value <?php echo( $has_errors ? 'has-errors' : '' ) ?> <?php echo( $has_warnings ? 'has-warnings' : '' ) ?>">
                                    <span class="dashicons dashicons-<?php echo( $has_errors || $has_warnings ? 'warning' : 'yes' ) ?>"></span>

                                    <?php if ( $to_be_enabled ) {
                                        echo ($item['value']) ? esc_html__( 'Enabled', 'xt-framework' ) : esc_html__( 'Disabled', 'xt-framework' );
                                    } elseif ( $key == 'wp_memory_limit' ) {
                                        echo esc_html( size_format( $item['value'] ) );
                                    } else {

                                        if ( $item['value'] == 'n/a' ) {
                                            echo esc_html__( 'N/A', 'xt-framework' );
                                        } else {
                                            echo esc_html($item['value']);
                                        }

                                    } ?>

                                </td>
                                <td class="requirement-messages">
                                    <?php if ( $has_errors ) : ?>
                                        <ul>
                                            <?php foreach ( $item['errors'] as $plugin => $requirement ) : ?>
                                                <li>
                                                    <?php
                                                    if ( $to_be_enabled ) {
                                                        echo sprintf( esc_html__( '%s needs %s enabled', 'xt-framework' ), '<b>' . esc_html($plugin) . '</b>', '<b>' . esc_html($labels[ $key ]) . '</b>' );
                                                    } elseif ( $key == 'wp_memory_limit' ) {
                                                        echo sprintf( esc_html__( '%s needs at least %s of available memory', 'xt-framework' ), '<b>' . esc_html($plugin) . '</b>', '<span class="error">' . esc_html( size_format( $this->memory_size_to_num( $requirement ) ) ) . '</span>' );
                                                        if ( $this->memory_size_to_num( $requirement ) < $recommended_memory ) {
                                                            echo '<br/>';
                                                            echo sprintf( esc_html__( 'For optimal functioning of our plugins, we suggest setting at least %s of available memory', 'xt-framework' ), '<span class="error">' . esc_html( size_format( $recommended_memory ) ) . '</span>' );
                                                        }
                                                    } else {
                                                        echo sprintf( esc_html__( '%s needs at least %s version', 'xt-framework' ), '<b>' . esc_html($plugin) . '</b>', '<span class="error">' . esc_html($requirement) . '</span>' );
                                                    }
                                                    ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php switch ( $key ) {

                                            case 'min_wp_version':
                                            case 'min_wc_version':
                                                echo esc_html__( 'Update it to the latest version in order to benefit of all new features and security updates.', 'xt-framework' );
                                                break;
                                            case 'min_php_version':
                                            case 'min_tls_version':
                                                if ( $item['value'] != 'n/a' ) {
                                                    echo esc_html__( 'Contact your hosting company in order to update it.', 'xt-framework' );
                                                }
                                                break;
                                            case 'wp_cron_enabled':
                                                echo sprintf( esc_html__( 'Remove %s from %s file', 'xt-framework' ), '<code>define( "DISABLE_WP_CRON", true );</code>', '<b>wp-config.php</b>' );
                                                break;
                                            case 'mbstring_enabled':
                                            case 'simplexml_enabled':
                                            case 'gd_enabled':
                                            case 'iconv_enabled':
                                            case 'opcache_enabled':
                                            case 'url_fopen_enabled':
                                                echo esc_html__( 'Contact your hosting company in order to enable it.', 'xt-framework' );
                                                break;
                                            case 'wp_memory_limit':
                                                echo sprintf( esc_html__( 'Read more %s here%s or contact your hosting company in order to increase it.', 'xt-framework' ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">', '</a>' );
                                                break;
                                            default:
                                                echo apply_filters( 'xt_framework_system_generic_message', '', $item );

                                        } ?>
                                    <?php endif; ?>

                                    <?php if ( $has_warnings ) {

                                        if ( $item['value'] != 'n/a' ) {

                                            echo sprintf( esc_html__( 'For optimal functioning of our plugins, we suggest setting at least %s of available memory', 'xt-framework' ), '<span class="error">' . esc_html( size_format( $recommended_memory ) ) . '</span>' );
                                            echo '<br/>';
                                            echo sprintf( esc_html__( 'Read more %s here%s or contact your hosting company in order to increase it.', 'xt-framework' ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">', '</a>' );

                                        } else {

                                            switch ( $key ) {
                                                case 'min_tls_version':
                                                    echo sprintf( esc_html__( 'We cannot determine which %1$sTLS%2$s version is installed.', 'xt-framework' ), '<strong>', '</strong>' );
                                                    break;
                                            }

                                        }

                                    } ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <br>
                    <p>
                        <a href="<?php echo add_query_arg( array( 'xtfw-phpinfo' => 'true' ) ) ?> "><?php esc_html_e( 'Show Full PHP Info', 'xt-framework' ) ?></a>
                    </p>
                <?php else : ?>
                    <p>
                        <a href="<?php echo add_query_arg( array( 'xtfw-phpinfo' => 'false' ) ) ?> "><?php esc_html_e( 'Back to System Status', 'xt-framework' ) ?></a>
                    </p>
                    <?php

                    ob_start();
                    phpinfo( 61 );
                    $pinfo = ob_get_contents();
                    ob_end_clean();

                    $pinfo = preg_replace( '%^.*<div class="center">(.*)</div>.*$%ms', '$1', $pinfo );
                    $pinfo = preg_replace( '%(^.*)<a name=\".*\">(.*)</a>(.*$)%m', '$1$2$3', $pinfo );
                    $pinfo = str_replace( '<table>', '<table class="widefat striped xtfw-phpinfo">', $pinfo );
                    $pinfo = str_replace( '<td class="e">', '<th class="e">', $pinfo );
                    echo wp_kses_post($pinfo);
                    ?>

                    <a href="#xt-framework-sysinfo"><?php esc_html_e( 'Back to top', 'xt-framework' ) ?></a>

                <?php endif; ?>
            </div>
            <?php
        }

        /**
         * Perform system status check
         *
         * @return void
         * @since  1.0.0
         * @author XplodedThemes
         */
        public function check_system_status() {

            global $wp_customize;

            if(!empty($wp_customize)) {
                return;
            }

            $this->_theme = $this->get_theme_info();

            if ( '' == get_option( 'xt_framework_system_info' ) ) {

                $this->add_requirements( $this->core->framework_menu_name(), array(
                    'min_wp_version'  => '4.9',
                    'min_wc_version'  => '3.4',
                    'min_php_version' => '5.6.20'
                ) );
                $this->add_requirements( esc_html__( 'WooCommerce', 'xt-framework' ), array(
                    'wp_memory_limit' => '64M'
                ) );

                $system_info   = $this->get_system_info();
                $check_results = array();
                $errors        = false;

                foreach ( $system_info as $key => $value ) {
                    $check_results[ $key ] = array( 'value' => $value );

                    if ( isset( $this->_plugins_requirements[ $key ] ) ) {

                        foreach ( $this->_plugins_requirements[ $key ] as $plugin_name => $required_value ) {

                            switch ( $key ) {
                                case 'wp_cron_enabled'  :
                                case 'mbstring_enabled' :
                                case 'simplexml_enabled':
                                case 'gd_enabled':
                                case 'iconv_enabled':
                                case 'url_fopen_enabled':
                                case 'opcache_enabled'  :

                                    if ( ! $value ) {
                                        $check_results[ $key ]['errors'][ $plugin_name ] = $required_value;
                                        $errors                                          = true;
                                    }
                                    break;

                                case 'wp_memory_limit'  :
                                    $required_memory = $this->memory_size_to_num( $required_value );

                                    if ( $required_memory > $value ) {
                                        $check_results[ $key ]['errors'][ $plugin_name ] = $required_value;
                                        $errors                                          = true;
                                    }
                                    break;

                                default:
                                    if ( ! version_compare( $value, $required_value, '>=' ) && $value != 'n/a' ) {
                                        $check_results[ $key ]['errors'][ $plugin_name ] = $required_value;
                                        $errors                                          = true;
                                    }

                            }

                        }

                    }

                }

                update_option( 'xt_framework_system_info', array(
                    'system_info' => $check_results,
                    'errors'      => $errors
                ) );

            }

        }

        /**
         * Handle plugin requirements
         *
         * @param $plugin_name  string
         * @param $requirements array
         *
         * @return void
         * @since  1.0.0
         *
         * @author XplodedThemes
         */
        public function add_requirements( $plugin_name, $requirements ) {

            $allowed_requirements = array_keys( $this->_requirement_labels );

            foreach ( $requirements as $requirement => $value ) {

                if ( in_array( $requirement, $allowed_requirements ) ) {
                    $this->_plugins_requirements[ $requirement ][ $plugin_name ] = $value;
                }
            }
        }

        /**
         * Show system notice
         *
         * @return  void
         * @since   1.0.0
         * @author  XplodedThemes
         */
        public function render_notices() {

            $system_info = get_option( 'xt_framework_system_info', '' );
            if (!empty($system_info) && !empty($system_info['errors']) && ! $this->core->framework_is_admin_url() && ! $this->core->framework_is_admin_url( $this->slug ) ) {
                $message = sprintf( esc_html__( '{title:}: The system check has detected some compatibility issues on your installation. %sClick here%s to know more', 'xt-framework' ), '<a href="' . esc_url( $this->core->framework_admin_url( $this->slug ) ) . '"><strong>', '</strong></a>' );
                $this->core->framework_notices()->add_warning_message( $message );
            }

            $template_overrides = $this->get_cached_template_overrides();

            if(!empty($template_overrides) && $template_overrides['outdated'] > 0) {
                $message =  sprintf( _n( '{title:}: The system check has detected %s%s outdated plugin template%s ', '{title:}: The system check has detected %s%s outdated plugin templates%s', $template_overrides['outdated'], 'xt-framework' ), '<a href="' . esc_url( $this->core->framework_admin_url( $this->slug ).'#template-overrides' ) . '"><strong>', number_format_i18n( $template_overrides['outdated'] ), '</strong></a>' );
                $this->core->framework_notices()->add_warning_message( $message );
            }
        }

        /**
         * Get system information
         *
         * @return  array
         * @since   1.0.0
         * @author  XplodedThemes
         */
        public function get_system_info() {

            $tls = 'n/a';

            if ( apply_filters( 'xt_framework_system_status_check_ssl', true ) ) {

                // Using 3rd party api service (https://www.howsmyssl.com/a/check) to retrieve client TLS version to be displayed in system info page
                $data = wp_remote_retrieve_body(wp_remote_get( 'https://www.howsmyssl.com/a/check', array('sslverify' => false)));
                $json = json_decode( $data );
                $tls  = $json != null ? str_replace( 'TLS ', '', $json->tls_version ) : '';
            }

            //Get PHP version
            $php_version = phpversion();

            // WP memory limit.
            $wp_memory_limit = $this->memory_size_to_num( WP_MEMORY_LIMIT );
            if ( function_exists( 'memory_get_usage' ) ) {
                $wp_memory_limit = max( $wp_memory_limit, $this->memory_size_to_num( @ini_get( 'memory_limit' ) ) );
            }

            return apply_filters( 'xt_framework_system_additional_check', array(
                'min_wp_version'    => get_bloginfo( 'version' ),
                'min_wc_version'    => function_exists( 'WC' ) ? WC()->version : 'n/a',
                'wp_memory_limit'   => $wp_memory_limit,
                'min_php_version'   => $php_version,
                'min_tls_version'   => $tls,
                'wp_cron_enabled'   => ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
                'mbstring_enabled'  => extension_loaded( 'mbstring' ),
                'simplexml_enabled' => extension_loaded( 'simplexml' ),
                'gd_enabled'        => extension_loaded( 'gd' ) && function_exists( 'gd_info' ),
                'iconv_enabled'     => extension_loaded( 'iconv' ),
                'opcache_enabled'   => ini_get( 'opcache.save_comments' ),
                'url_fopen_enabled' => ini_get( 'allow_url_fopen' ),
            ) );

        }

        /**
         * Convert site into number
         *
         * @param   $memory_size string
         *
         * @return  integer
         * @since   1.0.0
         *
         * @author  XplodedThemes
         */
        public function memory_size_to_num( $memory_size ) {
            $unit = strtoupper( substr( $memory_size, - 1 ) );
            $size = substr( $memory_size, 0, - 1 );

            $multiplier = array(
                'P' => 5,
                'T' => 4,
                'G' => 3,
                'M' => 2,
                'K' => 1,
            );

            if ( isset( $multiplier[ $unit ] ) ) {
                for ( $i = 1; $i <= $multiplier[ $unit ]; $i ++ ) {
                    $size *= 1024;
                }
            }

            return $size;
        }

        /**
         * Main plugin Instance
         *
         * @return XT_Framework_System_Status
         * @since  1.0.0
         * @author XplodedThemes
         */
        public static function instance( $core ) {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self( $core );
            }

            return self::$_instance;
        }
    }
}