<?php
    /**
     * @package     Freemius Migration
     * @copyright   Copyright (c) 2016, Freemius, Inc.
     * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
     * @since       1.0.3
     */

    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    if ( ! class_exists( 'FS_PH_Client_License_Abstract_v2' ) ) {
        require_once dirname( __FILE__ ) . '/class-fs-client-license-abstract.php';
    }

    if ( ! class_exists( 'FS_PH_EDD_Client_Migration_v2' ) ) {
        require_once dirname( __FILE__ ) . '/class-fs-edd-client-migration.php';
    }

    /**
     * You should use your own unique CLASS name, and be sure to replace it
     * throughout this file. For example, if your product's name is "Awesome Product"
     * then you can rename it to "Awesome_Product_EDD_License_Key".
     *
     * Class ProjectHuddle_EDD_License_Key
     */
    class ProjectHuddle_EDD_License_Key extends FS_PH_Client_License_Abstract_v2 {
        /**
         * @var bool
         */
        private $_is_bundle = false;
        /**
         * @var string
         */
        private $_child_identifier;
        /**
         * @var FS_Logger
         */
        private $_logger;

        /**
         * @todo When migrating a store that sell bundles, set this array to include the child products identifiers as the keys, and the values should include the Freemius SDK shortcodes of those child products, correspondingly.
         *
         * Example:
         *      array(
         *          'Child1_Class' => array('name' => 'Child1', 'shortcode' => 'fs_child1'),
         *
         *          ...
         *
         *          'ChildN_Class' => array('name' => 'ChildN', 'shortcode' => 'fs_childN'),
         *      )
         *
         * @var array<string,array<string,mixed>>
         */
        private static $paid_children = array(
            'PH_File_Uploads' => array(
                'name'      => 'File Uploads',
                'fs_id'     => '5369',
                'shortcode' => 'fu_fs',
            ),
            'PH_PDF_Mockups'  => array(
                'name'      => 'Pdf Mockups',
                'fs_id'     => '5370',
                'shortcode' => 'pm_fs',
            ),
        );

        /**
         * ProjectHuddle_EDD_License_Key constructor.
         *
         * @todo This constructor can be removed when migrating regular products (no bundles).
         *
         * @param bool   $is_bundle
         * @param string $child_identifier A unique identifier of a child product (you can use the child product's main class name).
         * @param string $child_name
         */
        function __construct( $is_bundle = false, $child_identifier = '', $child_name = '' ) {
            $this->_is_bundle        = $is_bundle;
            $this->_child_identifier = $child_identifier;

            $this->_logger = FS_Logger::get_logger(
                WP_FS__SLUG . '_project-huddle_migration_' . ($is_bundle ? 'bundle' : $child_identifier),
                WP_FS__DEBUG_SDK,
                WP_FS__ECHO_DEBUG_SDK
            );
        }

        /**
         * @author   Vova Feldman (@svovaf)
         * @since    1.0.3
         *
         * @param int|null $blog_id
         *
         * @return string
         */
        function get( $blog_id = null ) {
            $license_key = is_multisite() ?
                // If multisite, get the license key from the main blog.
                get_blog_option( get_network()->blog_id, 'ph_license_key', '' ) :
                get_site_option( 'ph_license_key', '' );

            return trim( $license_key );
        }

        /**
         * When migrating a bundle license and the sales platform creates a different
         * license key for every product in the bundle which is the key that actually
         * used for activation, this method should return the collection of all
         * child license keys that were activated on the current website.
         *
         * @author   Vova Feldman (@svovaf)
         * @since    1.1.0
         *
         * @param int|null $blog_id
         *
         * @return string[]
         */
        function get_children( $blog_id = null ) {
            $blog_ids = FS_PH_EDD_Client_Migration_v2::get_blog_ids();

            $children_license_keys = array();
            foreach ( $blog_ids as $blog_id ) {
                $license_key = trim( get_blog_option( $blog_id, 'edd_sample_addon_license_key', '' ) );

                if ( ! empty( $license_key ) ) {
                    $children_license_keys[] = $license_key;
                }
            }

            return $children_license_keys;
        }

        /**
         * @author   Vova Feldman (@svovaf)
         * @since    1.0.3
         *
         * @param string   $license_key
         * @param int|null $blog_id
         *
         * @return bool True if successfully updated.
         */
        function set( $license_key, $blog_id = null ) {
            return update_site_option( 'ph_license_key', $license_key );
        }

        /**
         * Override this only when the product supports a network level integration.
         *
         * @author   Vova Feldman (@svovaf)
         * @since    1.1.0
         *
         * @return bool
         */
        public function is_network_migration() {
            /**
             * Comment the line below if you'd like to support network level licenses migration.
             * This is only relevant if you have a special network level integration with your plugin
             * and you're utilizing the Freemius SDK's multisite network integration mode.
             */
            // return false;

            return is_multisite();
        }

        /**
         * This method is only relevant when you're using the network level migration mode.
         * The method should return true only if you restrict a network level license activation
         * to apply the exact same license for the products network wide.
         *
         * For example, if a network with 5-sites can have license1 on sub-sites 1-3,
         * and license2 on sub-sites 4-5, then the result of this method should be set to `false`.
         * BUT, if you the only way to activate the license is that it will be the same license on
         * all sub-sites 1-5, then this method should return `true`.
         *
         * @return bool
         */
        public function are_licenses_network_identical() {
            return false;
        }

        /**
         * Activates a bundle license on the installed child products, after successfully migrating the license.
         *
         * @author   Vova Feldman (@svovaf)
         * @since    2.0.0
         *
         * @param \FS_User    $user
         * @param string|null $bundle_license_key
         * @param array       $sites
         */
        public function activate_bundle_license_after_migration( FS_User $user, $bundle_license_key = null, $sites = array() ) {
            $this->_logger->entrance( "bundle_license_key=" . var_export( $bundle_license_key, true ) );

            if ( $this->_is_bundle || empty( $bundle_license_key ) ) {
                $bundle_license_key = $this->get();
            }

            // Iterate over the installed add-ons and try to activate the bundle's license for each add-on.
            foreach ( self::$paid_children as $child_identifier => $data ) {
                if ( ! $this->is_child_installed_and_active( $child_identifier ) ) {
                    $this->_logger->log( "{$child_identifier} does not exist." );

                    continue;
                }

                $shortcode = $this->get_child_freemius_shortcode( $child_identifier );

                if ( ! function_exists( $shortcode ) ) {
                    $this->_logger->log( "Function {$shortcode} does not exist." );

                    continue;
                }

                /**
                 * Initiate the Freemius instance before migrating.
                 *
                 * @var Freemius $child_fs
                 */
                $child_fs = call_user_func( $shortcode );

                $this->_logger->log( 'Starting activation of the migrated license for ' . str_replace( '_', ' ', $child_identifier) . '.' );

                if ( empty( $sites ) || is_plugin_active_for_network( $child_fs->get_plugin_basename() ) ) {
                    $child_fs->activate_migrated_license( $bundle_license_key );
                } else {
                    foreach ( $sites as $site ) {
                        $child_fs->activate_migrated_license(
                            $bundle_license_key,
                            null,
                            null,
                            array(),
                            $site['blog_id']
                        );
                    }
                }
            }
        }

        /**
         * Checks if the child/add-on is installed and activated.
         *
         * @author   Vova Feldman (@svovaf)
         * @since    2.0.0
         *
         * @param string|mixed $child_identifier
         *
         * @return bool
         */
        public function is_child_installed_and_active( $child_identifier ) {
            /**
             * @todo This should be replaced with some logic that checks if the child/add-on is installed and activated.
             */
            return class_exists( $child_identifier );
//            return function_exists( $child_identifier );
        }

        /**
         * Gets the child/add-on's Freemius shortcode string.
         *
         * @author   Vova Feldman (@svovaf)
         * @since    2.0.0
         *
         * @param string|mixed $child_identifier
         *
         * @return string
         */
        public function get_child_freemius_shortcode( $child_identifier ) {
            /**
             * @todo This should be replaced with some logic that gets the child/add-on's Freemius shortcode string.
             */
            return self::$paid_children[ $child_identifier ]['shortcode'];
        }
    }

    if ( ! class_exists( 'FS_PH_Client_Addon_Migration_Abstract_v2' ) ) {
        require_once dirname( __FILE__ ) . '/class-fs-client-addon-migration-abstract.php';
    }

    /**
     * @todo For add-ons migration change the if condition from `false` to `true` an update the class according to the inline instructions.
     *
     * @author   Vova Feldman (@svovaf)
     * @since    2.0.0
     */
    if ( false ) {
        /**
         * You should use your own unique CLASS name, and be sure to replace it
         * throughout this file. For example, if your product's name is "Awesome Product"
         * then you can rename it to "Awesome_Product_EDD_Addon_Migration".
         *
         * @author   Vova Feldman (@svovaf)
         * @since    2.0.0
         *
         * Class ProjectHuddle_EDD_Addon_Migration
         */
        class ProjectHuddle_EDD_Addon_Migration extends FS_PH_Client_Addon_Migration_Abstract_v2 {

            #region Singleton

            /**
             * @var FS_PH_Client_Addon_Migration_Abstract_v2[]
             */
            protected static $_INSTANCES = array();

            /**
             * @param string $addon_shortcode
             *
             * @return FS_PH_Client_Addon_Migration_Abstract_v2
             */
            public static function instance( $addon_shortcode ) {
                if ( ! isset( self::$_INSTANCES[ $addon_shortcode ] ) ) {
                    self::$_INSTANCES[ $addon_shortcode ] = new self( $addon_shortcode );
                }

                return self::$_INSTANCES[ $addon_shortcode ];
            }

            /**
             * ProjectHuddle_EDD_Addon_Migration constructor.
             *
             * @param string $addon_shortcode
             */
            private function __construct( $addon_shortcode ) {
                $this->_addon_shortcode = $addon_shortcode;
            }

            #endregion

            /**
             * The parent product's shortcode.
             *
             * @author   Vova Feldman (@svovaf)
             * @since    2.0.0
             *
             * @return string
             */
            protected function get_parent_shortcode() {
                // @todo Replace with the shortcode set in the SDK INTEGRATION of the parent product Developer Dashboard.
                return 'ph_licensing';
            }

            /**
             * @todo     Update the logic to identify if the parent product is running. If you are using namespaces, make sure to add the relevant namespace within the checks. For example, if your product's main class name is My_Class and the namespace of the file in which the class is defined is \my\namespace then you'll need to replace '<PARENT_MAIN_CLASS_NAME>' with '\my\namespace]\My_Class'.
             *
             * @author   Vova Feldman (@svovaf)
             * @since    2.0.0
             *
             * @return bool
             */
            protected function is_parent_included() {
                // If you are using classes, replace <PARENT_MAIN_CLASS_NAME> with your parent product main class name.
                return class_exists( 'Project_Huddle' );

                // If your parent defines a unique define, replace <PARENT_DEFINE> with its name.
//            return defined( '<PARENT_DEFINE>' );

                // If your parent defines a unique function, replace <PARENT_FUNCTION> with its name.
//            return function_exists( '<PARENT_FUNCTION>' );
            }

            /**
             * @todo     Set the array to represent the common config for all addons SDK init-s.
             *
             * @author   Vova Feldman (@svovaf)
             * @since    2.0.0
             *
             * @return array
             */
            protected function get_addons_sdk_init_common_config() {
                return array(
                    'type'            => 'plugin',
                    'is_premium'      => true,
                    'is_premium_only' => true,
                    'has_paid_plans'  => true,
                    'parent'          => array(
                        'id'         => '5368',
                        'slug'       => 'project-huddle',
                        'public_key' => 'pk_e696bf90f1c97c0fa370a8e826a67',
                        'name'       => 'ProjectHuddle Dashboard Plugin',
                    ),
                    'menu'            => array(
                        'first-path' => 'plugins.php',
                        'support'    => false,
                    ),
                );
            }

            /**
             * @author   Vova Feldman (@svovaf)
             * @since    2.0.0
             *
             * @param bool   $is_bundle
             * @param string $addon_class
             * @param string $addon_name
             *
             * @return FS_PH_Client_License_Abstract_v2
             */
            protected function get_new_license_key_manager( $is_bundle, $addon_class = '', $addon_name = '' ) {
                return new ProjectHuddle_EDD_License_Key( $is_bundle, $addon_class, $addon_name );
            }

            /**
             * @todo This should point to your EDD store root URL.
             *
             * @author   Vova Feldman (@svovaf)
             * @since    2.0.0
             *
             * @return string
             */
            protected function get_store_url() {
                return 'https://projecthuddle.com';
            }
        }
    }

    $is_migration_debug = FS_PH_Client_Addon_Migration_Abstract_v2::is_migration_debug();

    if ( FS_PH_Client_Addon_Migration_Abstract_v2::is_migration_on() ) {
        if ( ! $is_migration_debug ||
             ( ! FS_PH_Client_Addon_Migration_Abstract_v2::is_wp_ajax() && ! FS_PH_Client_Addon_Migration_Abstract_v2::is_wp_cron() )
        ) {
            /**
             * @todo When migrating a bundle, set this var to `true`.
             */
            $is_bundle_migration = true;

            $bundle_license_manager = new ProjectHuddle_EDD_License_Key( $is_bundle_migration );

            // @todo We need to make sure that if there's both a bundle license and individual add-on license, it first migrates the bundle’s license, and only later migrate the individual license, but only if the bundle’s migration failed.

            // Bundle license is set, try to migrate the bundle's license.
            new FS_PH_EDD_Client_Migration_v2(
            // This should be replaced with your custom Freemius shortcode.
                ph_licensing(),

                // @todo This should point to your EDD store root URL.
                'https://projecthuddle.com',

                // The bundle's download ID.
                '54',

                new ProjectHuddle_EDD_License_Key( $is_bundle_migration ),

                // Migration type.
                ( $is_bundle_migration ?
                    FS_PH_Client_Migration_Abstract_v2::TYPE_BUNDLE_TO_BUNDLE :
                    FS_PH_Client_Migration_Abstract_v2::TYPE_CHILDREN_TO_PRODUCT ),

                // Freemius was NOT included in the previous (last) version of the product.
                false,

                // For testing, you can change that argument to TRUE to trigger the migration in the same HTTP request.
                $is_migration_debug
            );
        }
    }
