<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * XT Framework Core class.
 *
 * This class will be extended by XT Plugins and handles all common things between plugins
 *
 * @since      1.0.0
 * @package    XT_Framework
 * @subpackage XT_Framework/includes
 * @author     XplodedThemes
 *
 * Constructor requires plugin params array:
 *
 * array(
 *   'version'       => 'x.x.x',
 *   'name'          => 'Plugin Name',
 *   'menu_name'     => 'Plugin Menu Name',
 *   'url'           => 'https://xplodedthemes.com/products/plugin-slug',
 *   'icon'          => 'dashicons-icon',
 *   'slug'          => 'slug',
 *   'prefix'        => 'prefix',
 *   'short_prefix'  => 'short_prefix',
 *   'market'        => 'market',
 *   'markets'       => array(
 *      'freemius' => array(
 *          'id' => 0,
 *          'key' => 'public_key',
 *          'url' => 'https://xplodedthemes.com/products/plugin-slug',
 *          'premium_slug'  => 'premium-slug',
 *          'freemium_slug' => 'freemium-slug',
 *      ),
 *      'envato' => array(
 *          'id' => 0,
 *          'url' => 'https://codecanyon.net/item/plugin-slug'
 *      )
 *   ),
 *   'dependencies' => array(
 *      array(
 *          'class' => 'WooCommerce',
 *          'name'  => 'WooCommerce',
 *          'url'   => 'https://en-ca.wordpress.org/plugins/woocommerce/'
 *      ),
 *      ...
 *   ),
 *   'conflicts' => array(
 *      array(
 *          'name'  => 'Plugin Name',
 *          'path' => 'plugin-path/plugin-file.php',
 *      ),
 *      ...
 *   ),
 *   'top_menu'     => false
 *   'file'         => __FILE__
 * );
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

abstract class XT_Framework
{

    /**
     * Var that holds an array of all instances instances loaded
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework[] $instances
     */
    private static $_instances = array();

    /**
     * Var that holds the plugin object.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Plugin $plugin
     */
    protected $plugin;

    /**
     * Dependencies Check instance to check if the plugin has passed required dependencies and is ready to be initialised.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Dependencies_Check dependencies_check
     */
    protected $dependencies_check;

    /**
     * Var that holds the framework slug.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $framework_slug
     */
    protected $framework_slug = 'xt-plugins';

    /**
     * Var that holds the framework name.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $framework_name
     */
    protected $framework_name = 'XT Plugins Framework';

    /**
     * Var that holds the framework menu name.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $framework_menu_name
     */
    protected $framework_menu_name = 'XT Plugins';

    /**
     * Framework Notices Instance
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Notices $framework_notices
     */
    protected $framework_notices;

    /**
     * The framework tabs instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Framework_Tabs $framework_tabs
     */
    protected $framework_tabs;

    /**
     * The access manager that's responsible for access management.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Access_Manager|Freemius $access_manager
     */
    protected $access_manager;

    /**
     * Base hooks responsible plugin activation, deactivation and uninstall hooks
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Base_Hooks $plugin_base_hooks
     */
    protected $plugin_base_hooks;

    /**
     * Admin Notices Instance
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Notices $plugin_notices
     */
    protected $plugin_notices;

    /**
     * Frontend Notices Instance
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Notices $plugin_frontend_notices
     */
    protected $plugin_frontend_notices;


    /**
     * Admin Messages Instance
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Admin_Messages $admin_messages
     */
    protected $admin_messages;

    /**
     * The system status instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_System_Status $system_status
     */
    protected $system_status;

    /**
     * The plugin locale instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_i18n $plugin_locale
     */
    protected $plugin_locale;

    /**
     * The plugin migrations instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Migration $plugin_migrations
     */
    protected $plugin_migrations;

    /**
     * The plugin revie notice instance.
     *
     * @since    2.0.3
     * @access   protected
     * @var      XT_Framework_Review_Notice $plugin_review_notice
     */
    protected $plugin_review_notice;

    /**
     * The plugin modules.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Modules $modules
     */
    protected $modules;

    /**
     * The plugin customizer instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Customizer $customizer
     */
    protected $customizer;

    /**
     * The settings instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Settings $settings
     */
    protected $settings;

    /**
     * The admin tabs instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Plugin_Tabs $plugin_tabs
     */
    protected $plugin_tabs;

    /**
     * Is network activated
     *
     * @since    1.0.0
     * @access   protected
     * @var      bool $is_network_active
     */
    protected $is_network_active = null;

    /**
     * The Ajax instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Ajax $ajax
     */
    protected $ajax;

    /**
     * The WC Ajax instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_WC_Ajax $wc_ajax
     */
    protected $wc_ajax;

    /**
     * The Cache instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Cache $cache
     */
    protected $cache;

    /**
     * The Transient instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Framework_Transient $transient
     */
    protected $transient;

    /**
     * Var that holds the public class instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Object $frontend
     */
    protected $frontend;

    /**
     * Var that holds the admin class instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Object $backend
     */
    protected $backend;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct($params)
    {
        if (self::instance_exists($params['slug'])) {
            return self::get_instance($params['slug']);
        }

        $this->init_plugin($params);

        add_action('plugins_loaded', array( $this, 'bootstrap') );

        add_action('plugins_loaded',function() {

            if($this->plugins_loaded()) {
                return;
            }
            do_action('xtfw_plugins_loaded');

        }, 999 );

        return $this;
    }


    // --------- INIT METHODS ------------- //

    /**
     * Initialize plugin instance from params
     *
     * @param $params
     * @since    1.0.0
     * @access   private
     */
    private function init_plugin($params)
    {
        if (empty($this->plugin)) {

            $this->plugin = new XT_Framework_Plugin($params);

            // Register the plugin globally
            self::register_instance($this->plugin->slug, $this);
        }
    }

    /**
     * Bootstrap plugin
     *
     * @since    1.0.0
     * @access   public
     */
    public function bootstrap()
    {

        $this->init_plugin_notices();
        $this->init_conflicts_check();
        $this->init_dependencies_check();

        if ( $this->plugin_dependencies()->passed() ) {

            $this->load_framework();
            $this->load_plugin();
        }
    }

    /**
     * Check if framework already loaded
     *
     * @since    1.0.0
     * @access   public
     * @return   bool
     */
    public function framework_loaded() {

        return did_action('xtfw_loaded');
    }

    /**
     * Check if all XT plugins have already loaded
     *
     * @since    1.0.0
     * @access   public
     * @return   bool
     */
    public function plugins_loaded() {

        return did_action('xtfw_plugins_loaded');
    }

    /**
     * Load Framework
     *
     * @since    1.0.0
     * @access   public
     */
    public function load_framework()
    {
        if($this->framework_loaded()) {
            return;
        }

        if ( is_admin() ) {

            add_action('admin_enqueue_scripts', array( $this, 'enqueue_framework_assets' ), 1);
            add_action('admin_enqueue_scripts', array($this, 'register_common_assets'), 1);

            $this->init_framework_notices();
            $this->init_recommended_plugins();
            $this->init_system_status();
            $this->init_admin_messages();
            $this->init_framework_tabs();
        }

        do_action('xtfw_loaded');
    }

    /**
     * Load Plugin
     *
     * @since    1.0.0
     * @access   public
     */
    public function load_plugin()
    {

        if(!is_admin()) {
            add_action('wp_enqueue_scripts', array($this, 'register_common_assets'), 1);
        }

        $this->init_cache();
        $this->init_transient();
        $this->init_plugin_frontend_notices();
        $this->init_access_manager();
        $this->init_base_hooks();
        $this->init_plugin_locale();

        $this->require_functions();
        $this->require_classes();

        $this->init_classes();
        $this->init_modules();
        $this->init_customizer();
        $this->init_settings();

        $this->init_ajax();
        $this->init_wc_ajax();

        if ( is_admin() ) {

            $this->init_plugin_tabs();
            $this->init_plugin_migrations();
            $this->init_plugin_review_notice();
        }
    }

    /**
     * Enqueue framework assets
     *
     * @since    1.0.0
     * @access   private
     */
    public function enqueue_framework_assets()
    {
        // Enqueue Framework Assets
        wp_enqueue_style('xtfw', xtfw_dir_url(XTFW_DIR_ASSETS) . '/css/admin.css', array(), XTFW_VERSION, 'all');
    }

    /**
     * Register common assets for on demand plugin use
     *
     * @since    1.0.0
     * @access   private
     */
    public function register_common_assets()
    {
        // Register common styles for on demand plugin use
        wp_register_style('xt-icons', xtfw_dir_url(XTFW_DIR_CUSTOMIZER) . '/controls/xt_icons/css/xt-icons.css', array(), XTFW_VERSION );
        wp_register_style('xt-jquery-ui', xtfw_dir_url( XTFW_DIR_ASSETS ) . '/css/jquery-ui/jquery-ui.css', array(), XTFW_VERSION );
        wp_register_style('xt-jquery-tiptip', xtfw_dir_url(XTFW_DIR_ASSETS) . '/css/jquery.tiptip.css', array(), XTFW_VERSION );
        wp_register_style('xt-jquery-select2', xtfw_dir_url(XTFW_DIR_ASSETS) . '/css/jquery.select2.css', array(), XTFW_VERSION );
        wp_register_style('xt-follow', xtfw_dir_url(XTFW_DIR_ASSETS) . '/css/xt-follow.css', array(), XTFW_VERSION );

        // Register common scripts for on demand plugin use
        wp_register_script('xt-observers-polyfill', 'https://polyfill.io/v3/polyfill.min.js?features=ResizeObserver%2CMutationObserver', array(), XTFW_VERSION);
        wp_register_script('xt-jquery-ajaxqueue', xtfw_dir_url(XTFW_DIR_ASSETS) . '/js/jquery.ajaxqueue-min.js', array('jquery'), XTFW_VERSION);
        wp_register_script('xt-jquery-touch', xtfw_dir_url(XTFW_DIR_ASSETS) . '/js/jquery.touch-min.js', array('jquery'), XTFW_VERSION);
        wp_register_script('xt-jquery-tiptip', xtfw_dir_url(XTFW_DIR_ASSETS) . '/js/jquery.tiptip-min.js', array('jquery'), XTFW_VERSION);
        wp_register_script('xt-jquery-inline-confirm', xtfw_dir_url(XTFW_DIR_ASSETS) . '/js/jquery.inline-confirm-min.js', array('jquery'), XTFW_VERSION);
        wp_register_script('xt-jquery-select2', xtfw_dir_url(XTFW_DIR_ASSETS) . '/js/jquery.select2-min.js', array('jquery'), XTFW_VERSION);
        wp_register_script('xt-color-picker', xtfw_dir_url(XTFW_DIR_ASSETS) . '/js/wp-color-picker-alpha-min.js', array('wp-color-picker'), XTFW_VERSION);
        wp_register_script('xt-sticky-sidebar', xtfw_dir_url(XTFW_DIR_ASSETS) . '/js/sticky-sidebar-min.js', array(), XTFW_VERSION);
        wp_register_script('xt-follow', xtfw_dir_url(XTFW_DIR_ASSETS) . '/js/xt-follow-min.js', array(), XTFW_VERSION);

        wp_register_style( 'xtfw-inline', false, array(), XTFW_VERSION );
        wp_register_script( 'xtfw-inline', false, array('jquery'), XTFW_VERSION );

        if(!wp_script_is('xtfw-inline')) {

            if(!is_admin()) {

                wp_add_inline_script('xtfw-inline', apply_filters('xtfw_inline_scripts', '
                    window.XT = (typeof window.XT !== "undefined") ? window.XT : {};
        
                    XT.isTouchDevice = function () {
                        return ("ontouchstart" in document.documentElement);
                    };
                    
                    (function( $ ) {
                        if (XT.isTouchDevice()) {
                            $("html").addClass("xtfw-touchevents");
                        }else{        
                            $("html").addClass("xtfw-no-touchevents");
                        }
                    })( jQuery );
                '));

            }else{

                wp_add_inline_script('xtfw-inline', apply_filters('xtfw_admin_inline_scripts', '
                    (function( $ ) {
                        $(function() {
                            // Hide XT Plugins menu divider if no plugins are shown
                            $(".xtfw-admin-menu-divider").each(function() {
                                var $li = $(this).closest("li.wp-first-item");
                                if($li.length) {
                                    $li.remove();
                                }
                            });
                        });
                    })( jQuery );
                '));
            }

        }

        wp_enqueue_style('xtfw-inline');
        wp_enqueue_script('xtfw-inline');
    }

    /**
     * Initialize Admin Notices
     *
     * @since    1.0.0
     * @access   public
     */
    public function init_framework_notices()
    {
        if (empty($this->framework_notices)) {
            $this->framework_notices = new XT_Framework_Notices($this->framework_slug(), $this->framework_menu_name());
        }
    }

    /**
     * Initialize Admin Notices
     *
     * @since    1.0.0
     * @access   public
     */

    public function init_plugin_notices()
    {
        if (empty($this->plugin_notices)) {
            $this->plugin_notices = new XT_Framework_Notices($this->plugin_slug(), $this->plugin_name());
        }
    }

    /**
     * Initialize Cache
     *
     * @since    1.0.0
     */
    public function init_cache()
    {
        if (empty($this->cache)) {
            $this->cache = new XT_Framework_Cache($this->plugin_short_prefix());
        }
    }

    /**
     * Initialize Transient
     *
     * @since    1.0.0
     */
    public function init_transient()
    {
        if (empty($this->transient)) {
            $this->transient = new XT_Framework_Transient($this->plugin_short_prefix());
        }
    }

    /**
     * Initialize Frontend Notices
     *
     * @since    1.0.0
     * @access   public
     */

    public function init_plugin_frontend_notices()
    {
        if (empty($this->plugin_frontend_notices)) {
            $is_plugin_for_woocommerce = $this->plugin_dependencies()->depends_on('WooCommerce');
            $this->plugin_frontend_notices = new XT_Framework_Notices($this->plugin_slug(), null, true, $is_plugin_for_woocommerce);
        }
    }

    /**
     * Check if conflict plugins are active, if yes, disabled them and show error notice
     *
     * @since    1.0.0
     */
    public function init_conflicts_check()
    {
        new XT_Framework_Conflicts_Check($this);
    }

    /**
     * Check if required plugin dependencies are loaded, if not, show error notice
     *
     * @since    1.0.0
     */
    public function init_dependencies_check()
    {
        if (empty($this->dependencies_check)) {
            $this->dependencies_check = new XT_Framework_Dependencies_Check($this);
        }
    }

    /**
     * Initialize the Access Manager
     *
     * @since    1.0.0
     * @access   protected
     */
    protected function init_access_manager()
    {

        if (!empty($this->access_manager) || $this->is_free()) {
            return;
        }

        if ($this->is_freemius()) {

            $this->access_manager = $this->freemius_access_manager();

            $this->access_manager->add_filter('hide_account_tabs', '__return_true');
            $this->access_manager->add_filter('is_submenu_visible', array($this, 'is_submenu_visible'), 10, 2);

            $this->access_manager->add_filter('checkout/purchaseCompleted', array($this, 'after_purchase_js'));
            $this->access_manager->add_filter('templates/checkout.php', array($this, 'checkout_gtm_script'));

            $this->access_manager->add_filter( 'hide_freemius_powered_by', '__return_true' );
            $this->access_manager->add_filter( 'hide_billing_and_payments_info', '__return_true' );
            
            $this->access_manager->add_filter('plugin_icon', function () {
                return dirname($this->plugin->file) . '/admin/assets/images/icon.png';
            });

            $this->access_manager->add_filter('default_currency', function() {
                return 'auto';
            });

            $this->access_manager->override_i18n( array(
                'contact-us' => esc_html__( "Support", 'xt-framework' )
            ));

            // Signal that Freemius SDK was initiated.
            do_action($this->plugin_short_prefix('fs_loaded'));

        }else{

            $this->access_manager = $this->local_access_manager();
        }

    }

    public function is_submenu_visible($is_visible, $menu_id) {

        $page = !empty($_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

        $is_admin_tab = !empty($page) && (strpos($page, $this->plugin_slug()) !== false);

        if(!$is_admin_tab) {
            $is_visible = false;
        }

        if($menu_id === 'affiliation') {
            $is_visible = false;
        }

        return $is_visible;
    }

    public function after_purchase_js() {

        return 'function ( data ) {
                    
                 /**
                 * Since the user just entered their personal & billing information, agreed to the TOS & privacy,
                 * know they are running within a secure iframe from an external domain, they implicitly permit tracking
                 * this purchase. So initializing GTM here (after the purchase), is legitimate.
                 */
                 
                var is_subscription = typeof(data.purchase.initial_amount) !== "undefined";
                var is_trial = typeof(data.purchase.trial_ends) !== "undefined" && data.purchase.trial_ends !== null;
                var total = is_subscription ? data.purchase.initial_amount : data.purchase.gross;
                total = is_trial ? 0 : total;
    
                var coupon = data && data.coupon ? data.coupon.code : "";
                var currency = data && data.currency ? data.currency.toUpperCase() : "USD";

                var item = {
                    item_name: "'.$this->plugin_name().'",
                    item_id: '.$this->market_product()->id.',
                    item_brand: "XplodedThemes",
                    affiliation: "XplodedThemes",
                    price: data.total,
                    discount: 0,
                    currency: currency,
                    coupon: coupon,
                    index: 1,
                    quantity: 1
                };
        
                // Purchase Event
                dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
                dataLayer.push({
                    event: "purchase",
                    ecommerce: {
                        transaction_id: data.purchase.id,
                        affiliation: item.affiliation,
                        tax: 0,
                        shipping: 0,
                        items: [item],
                        currency: item.currency,
                        coupon: item.coupon,
                        value: total
                    }
                });
        }';
    }

    public function checkout_gtm_script($html) {

        return "
        <script>window.dataLayer = window.dataLayer || [];</script>
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer', '".XTFW_GTM_ID."');</script>
        ".$html;

    }

    /**
     * Load Freemius License Manager
     *
     * @return Freemius
     * @since    1.0.0
     */
    abstract protected function freemius_access_manager();

    /**
     * Load Local Access Manager
     *
     * @return XT_Framework_Access_Manager
     * @since    1.0.0
     */
    private function local_access_manager()
    {
        require_once $this->plugin_framework_path( 'includes/license/includes', 'class-license.php' );
        require_once $this->plugin_framework_path( 'includes/license/includes', 'class-access-manager.php' );
        require_once $this->plugin_framework_path( 'includes/license/includes', 'plugin-update-checker/plugin-update-checker.php' );
        require_once $this->plugin_framework_path( 'includes/license/includes', 'class-plugin-updater.php' );

        // Init License Manager
        return new XT_Framework_Access_Manager( $this );
    }

    /**
     * Plugin base hooks to handle activation, deactivation and uninstall
     */
    public function init_base_hooks()
    {
        if (empty($this->plugin_base_hooks)) {
            $this->plugin_base_hooks = new XT_Framework_Base_Hooks($this);
        }
    }


    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the XT_Framework_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function init_plugin_locale()
    {
        if (empty($this->plugin_locale)) {
            ;
            $this->plugin_locale = new XT_Framework_i18n($this->plugin_data('TextDomain'), $this->plugin_file());
        }
    }

    /**
     * Initialize plugin customizer
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_customizer()
    {
        if (empty($this->customizer) && has_filter($this->plugin_prefix('customizer_fields'))) {

            $this->customizer = new XT_Framework_Customizer($this);
        }
    }

    /**
     * Initialize settings
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_settings()
    {
        if (empty($this->settings) && has_filter($this->plugin_prefix('setting_tabs'))) {

            $this->settings = new XT_Framework_Settings($this);
        }
    }

    /**
     * Initialize framework tabs
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_framework_tabs()
    {
        if ($this->total_instances_within_framework() === 0) {
            return;
        }

        if (empty($this->framework_tabs)) {

            $this->framework_tabs = XT_Framework_Framework_Tabs::instance($this);
        }
    }

    /**
     * Initialize plugin admin tabs
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_plugin_tabs()
    {
        $this->plugin_tabs = new XT_Framework_Plugin_Tabs($this);
    }

    /**
     * Initialize system status tabs
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_system_status()
    {
        if (empty($this->system_status)) {
            $this->system_status = XT_Framework_System_Status::instance($this);
        }
    }

    /**
     * Initialize migrations
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_plugin_migrations()
    {
        if (empty($this->plugin_migrations)) {
            $this->plugin_migrations = new XT_Framework_Migration($this);
        }
    }

    /**
     * Initialize Recommended Plugins
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_recommended_plugins()
    {
        XT_Framework_Recommended_Plugins::instance($this);
    }

    /**
     * Initialize Plugin Review Notice
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_plugin_review_notice() {

        if(empty($this->plugin_review_notice) && !$this->plugin()->premium_only() && !empty($this->plugin()->market_product()->freemium_slug)) {
            $this->plugin_review_notice = new XT_Framework_Review_Notice($this);
        }
    }

    /**
     * Initialize Admin Messages
     *
     * @since    1.0.0
     * @access   private
     */

    public function init_admin_messages()
    {
        if (empty($this->admin_messages)) {
            $this->admin_messages = new XT_Framework_Admin_Messages($this);
        }
    }

    /**
     * Initialize plugin Ajax events
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_ajax()
    {
        if (
            empty($this->ajax) &&
            (has_filter($this->plugin_prefix('ajax_add_events')) || has_filter($this->plugin_prefix('ajax_remove_events')))
        ) {

            $this->ajax = new XT_Framework_Ajax($this);
        }
    }

    /**
     * Initialize plugin WC Ajax events
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_wc_ajax()
    {
        if (
            empty($this->wc_ajax) &&
            $this->plugin_dependencies()->depends_on('WooCommerce') &&
            (has_filter($this->plugin_prefix('wc_ajax_add_events')) || has_filter($this->plugin_prefix('wc_ajax_remove_events'))) &&
            function_exists('wc')
        ) {

            $this->wc_ajax = new XT_Framework_WC_Ajax($this);
        }
    }

    /**
     * Load the required functions for this plugin.
     *
     * @param $path
     * @param array $exclude
     * @since    1.0.0
     * @access   private
     */
	protected function require_files($path, $exclude = array())
	{

		$files = glob($path);

        if(empty($files)) {
            return;
        }

		usort($files, 'xtfw_sort_pro_files');

		foreach ($files as $filename) {
			if(!empty($exclude) && in_array(basename($filename), $exclude)) {
				continue;
			}

            require_once $filename;
		}
	}

    /**
     * Load the required functions for this plugin.
     *
     * @param string $base_path
     * @since    1.0.0
     * @access   private
     */
    protected function require_functions($base_path = '')
    {

	    /**
	     * Require common functions responsible for defining all common actions
	     * side of the site.
	     */
	    $this->require_files($this->plugin_path($base_path, 'functions-*.php'));

	    /**
         * Require admin functions
         */
		$this->require_files($this->plugin_path($base_path.'/admin', 'functions-*.php'));

        /**
         * Require public functions
         */
	    $this->require_files($this->plugin_path($base_path.'/public', 'functions-*.php'));

    }

    /**
     * Load the required classes for this plugin.
     *
     * @param string $base_path
     * @since    1.0.0
     * @access   private
     */
    protected function require_classes($base_path = '')
    {

        /**
         * Require common classes responsible for defining all common actions
         * side of the site.
         */
	    $this->require_files($this->plugin_path($base_path, 'class-*.php'), array('class-core.php'));

	    /**
	     * Require widget classes responsible for defining all widgets.
	     */
	    $this->require_files($this->plugin_path($base_path.'/widgets', 'class-*.php'));

        /**
         * Require admin classes responsible for defining all admin actions.
         */
	    $this->require_files($this->plugin_path($base_path.'/admin', 'class-*.php'));

        /**
         * Require public classes responsible for defining all public actions
         * side of the site.
         */
	    $this->require_files($this->plugin_path($base_path.'/public', 'class-*.php'));

    }

    /**
     * Initialize plugin modules
     *
     * @since    1.0.0
     * @access   private
     */
    public function init_modules()
    {
        if (empty($this->modules) && has_filter($this->plugin_prefix('modules'))) {

            $this->modules = new XT_Framework_Modules($this);
        }
    }

    /**
     * Init main plugin classes
     *
     * @since    1.0.0
     * @access   protected
     */
    protected function init_classes()
    {

        $className = get_class($this).'_Admin';
	    $classNamePro = get_class($this).'_Admin_Pro';

	    if(empty($this->backend)) {
			if(class_exists($classNamePro)) {
				$this->backend = new $classNamePro($this);
			}else if(class_exists($className)) {
				$this->backend = new $className($this);
			}
	    }

        $className = get_class($this).'_Public';
	    $classNamePro = get_class($this).'_Public_Pro';

        if(empty($this->frontend)) {
			if(class_exists($classNamePro)) {
				$this->frontend = new $classNamePro($this);
			}else if(class_exists($className)) {
				$this->frontend = new $className($this);
			}
        }
    }

    // --------- GETTERS ------------- //


    /**
     * The name of the framework
     *
     * @return    string    The name of the framework.
     * @since     1.0.0
     */
    public function framework_name()
    {
        return $this->framework_name;
    }

    /**
     * The menu name of the framework
     *
     * @return    string    The menu name of the framework.
     * @since     1.0.0
     */
    public function framework_menu_name()
    {
        return $this->framework_menu_name;
    }

    /**
     * The slug of the framework used to uniquely identify it
     *
     * @return    string    The slug of the framework.
     * @since     1.0.0
     */
    public function framework_slug($suffix = null)
    {
        return $this->framework_slug . (!empty($suffix) ? '-' . $suffix : '');
    }

    /**
     * Get framework icon
     *
     * @since    1.0.0
     * @access   private
     */
    public function framework_icon()
    {
        return xtfw_dir_url(XTFW_DIR_ADMIN_TABS_ASSETS) . '/images/icon.svg';
    }

    /**
     * Get framework logo
     *
     * @since    1.0.0
     * @access   private
     */
    public function framework_logo()
    {
        return xtfw_dir_url(XTFW_DIR_ADMIN_TABS_ASSETS) . '/images/logo.svg';
    }

    /**
     * The framework admin URL
     *
     * @param string $slug
     * @param array|null $params
     * @param bool $network
     *
     * @return    string    The framework admin url.
     * @since     1.0.0
     */
    public function framework_admin_url($slug = '', $params = null, $network = false)
    {
        $admin_url_function = $network ? 'network_admin_url' : 'admin_url';

        $url = $admin_url_function('admin.php?page=' . $this->framework_slug($slug));

        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }

        return $url;
    }

    /**
     * The framework network admin URL
     *
     * @param string $slug
     * @param array|null $params
     *
     * @return    string    The framework network admin url.
     * @since     1.0.0
     */
    public function framework_network_admin_url($slug = '', $params = null)
    {
        return $this->framework_admin_url($slug, $params, true);
    }

    /**
     * The framework self admin URL based on context
     *
     * @param string $slug
     * @param array|null $params
     *
     * @return    string    The framework context admin url.
     * @since     1.0.0
     */
    public function framework_self_admin_url($slug = '', $params = null)
    {
        return $this->framework_admin_url($slug, $params, is_network_admin());
    }

    /**
     * Check is current page is framework admin URL
     *
     * @param string $slug
     *
     * @return    bool
     * @since     1.0.0
     */
    public function framework_is_admin_url($slug = '')
    {

        $page = !empty($_GET['page']) ? sanitize_text_field( $_GET['page'] ) : '';

        return $page === $this->framework_slug($slug);
    }

    /**
     * The plugin info
     *
     * @return    XT_Framework_Plugin    The plugin info.
     * @since     1.0.0
     */
    public function plugin()
    {
        return $this->plugin;
    }

    /**
     * The name of the plugin
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function plugin_name()
    {
        return $this->plugin()->name;
    }

    /**
     * The menu name of the plugin
     *
     * @return    string    The menu name of the plugin.
     * @since     1.0.0
     */
    public function plugin_menu_name()
    {
        return $this->plugin()->menu_name;
    }

    /**
     * The slug of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @param string $suffix
     *
     * @return    string    The slug of the plugin.
     * @since     1.0.0
     */
    public function plugin_slug($suffix = null)
    {
        return $this->plugin()->slug . (!empty($suffix) ? '-' . $suffix : '');
    }

    /**
     * Generate plugin id based on plugin short prefix
     *
     * @return    string    id.
     * @since     1.0.0
     */
    public function plugin_id()
    {
        return $this->plugin()->short_prefix;
    }

    /**
     * Generate plugin prefix for hooks or ids based on plugin prefix
     *
     * @param string $suffix
     *
     * @return    string    prefix.
     * @since     1.0.0
     */
    public function plugin_prefix($suffix = null)
    {
        return $this->plugin()->prefix . (!empty($suffix) ? '_' . $suffix : '');
    }

    /**
     * Generate plugin prefix for hooks or ids based on plugin short prefix
     *
     * @param string $suffix
     *
     * @return    string    prefix.
     * @since     1.0.0
     */
    public function plugin_short_prefix($suffix = null)
    {
        return $this->plugin()->short_prefix . (!empty($suffix) ? '_' . $suffix : '');
    }

    /**
     * The dashicon of the plugin
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function plugin_icon()
    {
        return $this->plugin()->icon;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function plugin_version()
    {
        return $this->plugin()->version;
    }

    /**
     * Retrieve the version number of the framework.
     *
     * @return    string    The version number of the framework.
     * @since     1.0.0
     */
    public function framework_version()
    {
        return XTFW_VERSION;
    }

    /**
     * Retrieve the version number of the current plugin framework.
     *
     * @return    string    The version number of the framework.
     * @since     1.0.0
     */
    public function plugin_framework_version() {

        $readme_file = $this->plugin_framework_path('/', 'start.php');

        $data = file_get_contents( $readme_file, false, null, 0, 450 );

        preg_match('/\$this_fw_version.=.\'(.+?)\'\;/', $data, $match);

        return !empty($match[1]) ? trim($match[1]) : '1.0.0';
    }

    /**
     * Check if current plugin framework version is the latest compared to the active framework version.
     *
     * @return    string    The version number of the framework.
     * @since     1.0.0
     */
    public function plugin_is_latest_framework_version() {

        $fw_version = $this->framework_version();
        $plugin_fw_version = $this->plugin_framework_version();

        return version_compare($plugin_fw_version, $fw_version, '>=');
    }

    /**
     * The plugin file
     *
     * @return    string    The plugin file.
     * @since     1.0.0
     */
    public function plugin_file()
    {
        return $this->plugin()->file;
    }

    /**
     * The plugin directory
     *
     * @return    string    The plugin directory.
     * @since     1.0.0
     */
    public function plugin_dir()
    {
        return basename(dirname($this->plugin_file()));
    }

    /**
     * The plugin path
     *
     * @param string $dir
     * @param string $file
     *
     * @return    string    The plugin path.
     * @since     1.0.0
     */
    public function plugin_path($dir = null, $file = null)
    {
        $path = plugin_dir_path($this->plugin_file());

        if (!empty($dir)) {
            $path .= $dir . "/";
        }

        if (!empty($file)) {
            $path .= $file;
        }

        return preg_replace('/(\/+)/','/', $path);
    }

    /**
     * The plugin URL
     *
     * @param string $dir
     * @param string $file
     *
     * @return    string    The plugin url.
     * @since     1.0.0
     */
    public function plugin_url($dir = null, $file = null)
    {
        $url = plugin_dir_url($this->plugin_file());

        if (!empty($dir)) {
            $url .= $dir . "/";
        }

        if (!empty($file)) {
            $url .= $file;
        }

        return $url;
    }

    /**
     * The plugin admin URL
     *
     * @param string $slug
     * @param array|null $params
     * @param bool $network
     *
     * @return    string    The plugin admin url.
     * @since     1.0.0
     */
    public function plugin_admin_url($slug = '', $params = null, $network = false)
    {
        $admin_url_function = $network ? 'network_admin_url' : 'admin_url';

        $url = $admin_url_function('admin.php?page=' . $this->plugin_slug($slug));

        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }

        return $url;
    }

    /**
     * The plugin network admin URL
     *
     * @param string $slug
     * @param array|null $params
     *
     * @return    string    The plugin network admin url.
     * @since     1.0.0
     */
    public function plugin_network_admin_url($slug = '', $params = null)
    {
        return $this->plugin_admin_url($slug, $params, true);
    }

    /**
     * The plugin self admin URL based on context
     *
     * @param string $slug
     * @param array|null $params
     *
     * @return    string    The plugin context admin url.
     * @since     1.0.0
     */
    public function plugin_self_admin_url($slug = '', $params = null)
    {
        return $this->plugin_admin_url($slug, $params, is_network_admin());
    }

    /**
     * Check is current page is plugin admin URL
     *
     * @param string $slug
     * @param string $tab
     * @return    bool
     * @since     1.0.0
     */
    public function plugin_is_admin_url($slug = '', $tab = '')
    {
        $_page = !empty($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        $_tab = !empty($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';

        return (empty($tab) && $_page === $this->plugin_slug($slug)) || ($tab === $_tab && $_page === $this->plugin_slug($slug));
    }

    /**
     * The plugin framework file
     *
     * @param bool $active_fw
     *
     * @return    string    The plugin framework file.
     * @since     1.0.0
     */
    public function plugin_framework_file($active_fw = false)
    {
        if($active_fw) {
            return XTFW_FILE;
        }

        return $this->plugin_path( 'xt-framework/start.php' );
    }

    /**
     * The plugin framework directory
     *
     * @param bool $active_fw
     *
     * @return    string    The plugin framework directory.
     * @since     1.0.0
     */
    public function plugin_framework_dir($active_fw = false)
    {
        return dirname($this->plugin_framework_file($active_fw));
    }

    /**
     * The plugin framework path
     *
     * @param string $dir
     * @param string $file
     * @param bool $active_fw
     *
     * @return    string    The plugin framework path.
     * @since     1.0.0
     */
    public function plugin_framework_path($dir = null, $file = null, $active_fw = false)
    {
        $path = plugin_dir_path( $this->plugin_framework_file($active_fw) );

        if ( !empty($dir) ) {
            $path .= $dir . "/";
        }

        if(!is_dir($path)) {

            foreach(self::instances() as $instance) {

                if(is_dir($instance->plugin_framework_path($dir))) {
                    return $instance->plugin_framework_path($dir, $file);
                }
            }
        }

        if ( !empty($file) ) {
            $path .= $file;
        }

        return $path;
    }

    /**
     * The plugin framework URL
     *
     * @param string $dir
     * @param string $file
     * @param bool $active_fw
     *
     * @return    string    The plugin framework url.
     * @since     1.0.0
     */
    public function plugin_framework_url($dir = null, $file = null, $active_fw = false)
    {
        $url = plugin_dir_url($this->plugin_framework_file($active_fw));

        if (!empty($dir)) {
            $url .= $dir . "/";
        }

        if (!empty($file)) {
            $url .= $file;
        }

        return $url;
    }

    /**
     * Retrieve the plugin marketplace
     *
     * @return    string    The plugin marketplace.
     * @since     1.0.0
     */
    public function market()
    {
        return $this->plugin()->market ?: 'wordpress';
    }

    /**
     * Check if current market
     *
     * @return    bool
     * @since     1.0.0
     */
    public function market_is($market)
    {
        return $this->market() === $market;
    }

    /**
     * Check if is a free plugin
     *
     * @return    bool
     * @since     1.0.0
     */
    public function is_free()
    {
        return $this->market_is('wordpress');
    }

    /**
     * Check if plugin has premium version
     *
     * @return    bool
     * @since     1.0.0
     */
    public function has_premium_version()
    {
        return $this->access_manager() && !empty($this->market_product()->premium_slug);
    }

    /**
     * Check if is a freemius plugin
     *
     * @return    bool
     * @since     1.0.0
     */
    public function is_freemius()
    {
        return $this->market_is('freemius');
    }

    /**
     * Retrieve the plugin marketplace product
     *
     * @return    object    The plugin marketplace.
     * @since     1.0.0
     */
    public function market_product()
    {

        return $this->plugin()->market_product;
    }

    /**
     * Check if current market
     *
     * @return    bool
     * @since     1.0.0
     */
    public function market_product_is($market, $product_id)
    {

        return $this->market_is($market) && ($this->market_product()->id === $product_id);
    }

    /**
     * The reference to the class that manages the modules
     *
     * @return    XT_Framework_Modules
     * @since     1.0.0
     */
    public function modules()
    {

        if (empty($this->modules)) {
            $this->init_modules();
        }

        return $this->modules;
    }

    /**
     * Check if has modules
     *
     * @return    bool
     * @since     1.0.0
     */
    public function has_modules($nofilter = false)
    {
        return !empty($this->modules()) && $this->modules()->count($nofilter) > 0;
    }

    /**
     * The reference to the class that manages the cache
     *
     * @return    XT_Framework_Cache
     * @since     1.0.0
     */
    public function cache()
    {

        if (empty($this->cache)) {
            $this->init_cache();
        }

        return $this->cache;
    }

    /**
     * The reference to the class that manages the cache
     *
     * @return    XT_Framework_Transient
     * @since     1.0.0
     */
    public function transient()
    {

        if (empty($this->transient)) {
            $this->init_transient();
        }

        return $this->transient;
    }

    /**
     * The reference to the class that manages the frontend side of the plugin.
     *
     * @return    Object    Frontend side of the plugin.
     * @since     1.0.0
     */
    public function frontend()
    {

        if (empty($this->frontend)) {
            $this->init_classes();
        }

        return $this->frontend;
    }

    /**
     * The reference to the class that manages the backend side of the plugin.
     *
     * @return    Object    Backend side of the plugin.
     * @since     1.0.0
     */
    public function backend()
    {

        if (empty($this->backend)) {
            $this->init_classes();
        }

        return $this->backend;
    }

    /**
     * Get Base Hooks Instance
     *
     * @return    XT_Framework_Base_Hooks
     * @since     1.0.0
     */
    public function plugin_base_hooks()
    {

        if (empty($this->plugin_base_hooks)) {
            $this->init_base_hooks();
        }

        return $this->plugin_base_hooks;
    }

    /**
     * Get Dependencies Check Instance
     *
     * @return    XT_Framework_Dependencies_Check
     * @since     1.0.0
     */
    public function plugin_dependencies()
    {

        if (empty($this->dependencies_check)) {
            $this->init_dependencies_check();
        }

        return $this->dependencies_check;
    }

    /**
     * Get License Manager Instance
     *
     * @return    XT_Framework_Access_Manager|Freemius
     * @since     1.0.0
     */
    public function access_manager()
    {

        if (empty($this->access_manager)) {
            $this->init_access_manager();
        }

        return $this->access_manager;
    }

    /**
     * Get Framework Notices Instance
     *
     * @return    XT_Framework_Notices
     * @since     1.0.0
     */
    public function framework_notices()
    {

        if (empty($this->framework_notices)) {
            $this->init_framework_notices();
        }

        return $this->framework_notices;
    }

    /**
     * Get Plugin Notices Instance
     *
     * @return    XT_Framework_Notices
     * @since     1.0.0
     */
    public function plugin_notices()
    {

        if (empty($this->plugin_notices)) {
            $this->init_plugin_notices();
        }

        return $this->plugin_notices;
    }

    /**
     * Get Plugin Frontend Notices Instance
     *
     * @return    XT_Framework_Notices
     * @since     1.0.0
     */
    public function plugin_frontend_notices()
    {

        if (empty($this->plugin_frontend_notices)) {
            $this->init_plugin_frontend_notices();
        }

        return $this->plugin_frontend_notices;
    }

    /**
     * Get Plugin Review Notice
     *
     * @return    XT_Framework_Review_Notice
     * @since     2.0.3
     */
    public function plugin_review_notice()
    {
        if (empty($this->plugin_review_notice)) {
            $this->init_plugin_review_notice();
        }

        return $this->plugin_review_notice;
    }

    /**
     * Get Admin Messages Instance
     *
     * @return    XT_Framework_Admin_Messages
     * @since     1.0.0
     */
    public function admin_messages()
    {

        if (empty($this->admin_messages)) {
            $this->init_admin_messages();
        }

        return $this->admin_messages;
    }

    /**
     * Get System Status Instance
     *
     * @return    XT_Framework_System_Status
     * @since     1.0.0
     */
    public function system_status()
    {

        if (empty($this->system_status)) {
            $this->init_system_status();
        }

        return $this->system_status;
    }

    /**
     * Get Plugin Migrations Instance
     *
     * @return    XT_Framework_Migration
     * @since     1.0.0
     */
    public function plugin_migrations()
    {

        if (empty($this->plugin_migrations)) {
            $this->init_plugin_migrations();
        }

        return $this->plugin_migrations;
    }

    /**
     * Get Plugin Locale Instance
     *
     * @return    XT_Framework_i18n
     * @since     1.0.0
     */
    public function plugin_locale()
    {

        if (empty($this->plugin_locale)) {
            $this->init_plugin_locale();
        }

        return $this->plugin_locale;
    }

    /**
     * Get Plugin Customizer Instance
     *
     * @return    XT_Framework_Customizer
     * @since     1.0.0
     */
    public function customizer()
    {

        if (empty($this->customizer)) {
            $this->init_customizer();
        }

        return $this->customizer;
    }

    /**
     * Get Plugin Settings Instance
     *
     * @return    XT_Framework_Plugin_Settings
     * @since     1.0.0
     */
    public function settings()
    {

        if (empty($this->settings)) {
            $this->init_settings();
        }

        return $this->settings;
    }

    /**
     * Get Framework Tabs Instance
     *
     * @return    XT_Framework_Framework_Tabs
     * @since     1.0.0
     */
    public function framework_tabs()
    {

        if (empty($this->framework_tabs)) {
            $this->init_framework_tabs();
        }

        return $this->framework_tabs;
    }

    /**
     * Get Plugin Tabs Instance
     *
     * @return    XT_Framework_plugin_tabs
     * @since     1.0.0
     */
    public function plugin_tabs()
    {

        if (empty($this->plugin_tabs)) {
            $this->init_plugin_tabs();
        }

        return $this->plugin_tabs;
    }

    /**
     * Get Plugin Ajax
     *
     * @return    XT_Framework_Ajax
     * @since     1.0.0
     */
    public function ajax()
    {

        if (empty($this->ajax)) {
            $this->init_ajax();
        }

        return $this->ajax;
    }

    /**
     * Get Plugin WC Ajax
     *
     * @return    XT_Framework_WC_Ajax
     * @since     1.0.0
     */
    public function wc_ajax()
    {

        if (empty($this->wc_ajax)) {
            $this->init_wc_ajax();
        }

        return $this->wc_ajax;
    }

    /**
     * Get Plugin Header Data
     *
     * @param string $id
     *
     * @return    array
     * @since     1.0.4
     */
    public function plugin_data($id = null) {

        $data = get_plugin_data($this->plugin_file());

        if(!empty($id)) {
            return ! empty( $data[ $id ] ) ? $data[ $id ] : null;
        }

        return $data;
    }

    /**
     * Get Plugin Activate or Upgrade License Url
     *
     * @return    string
     * @since     1.1.1
     */
    public function plugin_upgrade_url()
    {

        if ($this->is_freemius()) {
            if($this->access_manager()->is_premium() && $this->access_manager()->is_registered()) {
                return $this->access_manager()->get_account_url();
            }else {
                return $this->access_manager()->get_upgrade_url();
            }
        } else {
            return $this->access_manager()->get_account_url();
        }
    }

    /**
     * Check if doing ajax
     *
     * @return    bool
     * @since     1.0.0
     */
    public function doing_ajax()
    {
        return xtfw_doing_ajax();
    }

    /**
     * Check if is active theme
     *
     * @param mixed $theme
     *
     * @return    bool
     * @since     1.0.0
     */
    public function is_theme($theme) {

        $active_theme = $this->cache()->result('wp_get_theme', function() {
            return wp_get_theme(get_template());
        });

        if(!empty($active_theme)) {
            if ( is_array( $theme ) ) {
                return in_array( $active_theme->get('Name'), $theme );
            } else {
                return $active_theme->get('Name') === $theme;
            }
        }

        return false;
    }

    /**
     * The plugin theme templates path
     *
     * @return    string    The plugin theme templates path.
     * @since     1.0.0
     */

    public function template_path()
    {
        return apply_filters($this->plugin_prefix('template_path'), $this->plugin_slug() . '/');
    }

    /**
     * Get plugin template. Look within the plugin and active theme
     *
     * @param string $slug
     * @param array $vars
     * @param bool $return
     * @param bool $locateOnly
     *
     * @return    string    The template
     * @since     1.0.0
     */

    public function get_template($slug, $vars = array(), $return = false, $locateOnly = false)
    {
        $template = null;

        // Look in your theme
        if (!XTFW_TEMPLATE_DEBUG_MODE) {
            $template = $this->get_theme_template( $slug );
        }

        // Get default slug.php
        if (empty($template)) {
            $template = $this->get_plugin_template( $slug );
        }

        // Allow 3rd party plugins to filter template file from their plugin.
        $template = apply_filters($this->plugin_prefix('template'), $template, $slug);

        if ($locateOnly) {
            return $template;
        }

        if ($template) {
            extract($vars);

            if (!$return) {

                require($template);
                return '';

            } else {

                ob_start();
                require($template);

                return ob_get_clean();
            }
        }
    }

    /**
     * Get plugin template within active theme
     *
     * @param string $slug
     *
     * @return    string    The template
     * @since     1.0.0
     */
    public function get_theme_template($slug) {

        // Look in your theme
        $template_path = $this->template_path();

        $template = locate_template(array($template_path . "{$slug}.php"));

        // Try legacy slugs
        if(empty($template)) {
            $template_path = str_replace( "xt-", "", $template_path );
            $template = locate_template(array($template_path . "{$slug}.php"));
        }

        return $template;
    }

    /**
     * Get plugin template
     *
     * @param string $slug
     *
     * @return    string    The template
     * @since     1.0.0
     */
    public function get_plugin_template($slug) {

        $plugin_path = $this->plugin_path('public');

        $template = null;

        // Get default slug.php
        if (file_exists($plugin_path . "templates/{$slug}.php")) {
            $template = $plugin_path . "templates/{$slug}.php";
        }

        return $template;
    }

    /**
     * Get license activation link
     *
     * @param string $title
     * @param array $linkClasses
     *
     * @return string $link;
     * @since     1.0.0
     */
    public function get_license_activation_link__premium_only($title = null, $linkClasses = array()) {

        $title = !empty($title) ? $title : esc_html__('Activate License', 'xt-framework');

        $classes = array('activate-license', $this->market_product()->freemium_slug);

        if($this->is_freemius() && !did_action($this->plugin_short_prefix('license_activation_dialog_added'))) {
            $url = '#';
        }else{
            $url = esc_url( $this->access_manager()->get_account_url() );
        }

        $classes = implode(" ", $classes);
        $linkClasses = implode(" ", $linkClasses);

        $link = '<span class="'.esc_attr($classes).'"><a class="'.esc_attr($linkClasses).'" href="'.esc_url($url).'">'.$title.'</a></span>';

        if(!$this->doing_ajax()) {
            $this->add_license_activation_dialog_box__premium_only();
        }

        return $link;
    }

    /**
     * Add license dialog boxes
     *
     * @since     1.0.0
     */
    public function add_license_activation_dialog_box__premium_only() {

        if($this->is_freemius() && !did_action($this->plugin_short_prefix('license_activation_dialog_added')) && !$this->plugin_tabs()->is_tab('account')) {

            $this->access_manager()->_add_license_activation_dialog_box();
            fs_enqueue_local_style( 'fs_dialog_boxes', '/admin/dialog-boxes.css' );
            do_action($this->plugin_short_prefix('license_activation_dialog_added'));
        }
    }

    /**
     * Get account link
     *
     * @param string $title
     * @param array $linkClasses
     *
     * @return string $link;
     * @since     1.0.0
     */
    public function get_account_link__premium_only($title = null, $linkClasses = array()) {

        if ( $this->access_manager()->is_premium() && $this->access_manager()->is_registered() ) {
            $account_url = $this->access_manager()->get_account_url();
        } else {
            $account_url = $this->plugin_admin_url();
        }

        $linkClasses = implode(" ", $linkClasses);

        return '<span><a class="'.esc_attr($linkClasses).'" href="'.esc_url($account_url).'">'.$title.'</a></span>';

    }

    /**
     * Check whether the plugin is active for the entire network.
     *
     * Only plugins installed in the plugins/ folder can be active.
     *
     * Plugins in the mu-plugins/ folder can't be "activated," so this function will
     * return false for those plugins.
     *
     * @since 1.7.6
     *
     * @return bool True, if active for the network, otherwise false.
     */
    public function is_network_active() {

        if(!isset($this->is_network_active)) {

            $plugin = basename($this->plugin_dir()) . '/' . basename($this->plugin_file());

            $this->is_network_active = is_plugin_active_for_network($plugin);
        }

        return $this->is_network_active;
    }

    /**
     * Check whether the plugin is active and registered for the entire network.
     *
     * Only plugins installed in the plugins/ folder can be active.
     *
     * Plugins in the mu-plugins/ folder can't be "activated," so this function will
     * return false for those plugins.
     *
     * @since 1.7.6
     *
     * @return bool True, if active for the network, otherwise false.
     */
    public function is_network_active_and_registered() {

        return $this->is_network_active() && $this->is_freemius() && $this->access_manager()->is_registered();
    }

    /**
     * Get external url with utm campaign / content
     *
     * @param string|null $utm_content
     * @param string|null $url
     *
     * @return string $link;
     * @since     1.0.0
     */
    public function get_xt_url($utm_content = null, $url = null) {

        $url = !empty($url) ? $url : 'https://xplodedthemes.com/';

        if(!empty($utm_content)) {
            $plugin = $this->plugin_short_prefix();
            $url .= '?utm_source=XplodedThemes&utm_medium=Plugin&utm_campaign=' . $plugin . '&utm_content='.$utm_content;
        }

        return $url;
    }

    /**
     * Get loaded instances array
     *
     * @return XT_Framework[]
     * @since 1.0.0
     * @static
     */
    public static function instances()
    {
        return !is_network_admin() ? self::$_instances :  self::first_instance()->framework_tabs()->network_registered_plugins;
    }

    /**
     * Get total loaded instances
     *
     * @return integer
     * @since 1.0.0
     * @static
     */
    public static function total_instances()
    {
        return count(self::$_instances);
    }

    /**
     * Get total loaded instances that are loaded within the framework
     *
     * @return integer
     * @since 1.0.0
     * @static
     */
    public static function total_instances_within_framework()
    {

        $count = 0;
        foreach (self::$_instances as $instance) {
            if (!$instance->plugin()->top_menu() && $instance->plugin_dependencies()->passed()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get total loaded instances that are loaded outside the framework
     *
     * @return integer
     * @since 1.0.0
     * @static
     */
    public static function total_instances_outside_framework()
    {
        $count = 0;
        foreach (self::$_instances as $instance) {
            if ($instance->plugin()->top_menu()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Register plugin
     *
     * @param $slug
     * @param $instance XT_Framework
     *
     * @since 1.0.0
     * @static
     */
    public static function register_instance($slug, &$instance)
    {
        self::$_instances[$slug] = $instance;
    }

    /**
     * Check if instance exists
     *
     * @param $slug
     *
     * @return bool
     * @since 1.0.0
     * @static
     */
    public static function instance_exists($slug)
    {
        return !empty(self::$_instances[$slug]);
    }

    /**
     * Check if a premium instance exists of a freemium version
     *
     * @param $freemium_slug
     *
     * @return bool
     * @since 1.0.0
     * @static
     */
    public static function premium_instance_exists($freemium_slug)
    {
        return !empty(self::get_premium_instance($freemium_slug));
    }

    /**
     * Get existing instance
     *
     * @param $slug
     *
     * @return XT_Framework
     * @since 1.0.0
     * @static
     */
    public static function get_instance($slug)
    {
        return self::instance_exists($slug) ? self::$_instances[$slug] : null;
    }

    /**
     * Get existing premium instance
     *
     * @param $freemium_slug
     *
     * @return XT_Framework
     * @since 1.0.0
     * @static
     */
    public static function get_premium_instance($freemium_slug)
    {
        foreach(self::$_instances as $instance) {

            if($instance->is_freemius() && $instance->market_product()->freemium_slug === $freemium_slug && $instance->access_manager()->is_premium()) {

                return $instance;
            }
        }

        return null;
    }

    /**
     * Get first instance
     *
     * @return XT_Framework
     * @since 1.7.0
     * @static
     */
    public static function first_instance()
    {
        return current(self::$_instances);
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'xt-framework'), $this->framework_version());
    } // End __clone()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'xt-framework'), $this->framework_version());
    } // End __wakeup()
}