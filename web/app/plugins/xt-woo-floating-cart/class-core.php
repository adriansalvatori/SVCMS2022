<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://xplodedthemes.com
 * @since      1.0.0
 * @package    XT_Woo_Floating_Cart
 * @author     XplodedThemes
*/
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
class XT_Woo_Floating_Cart extends XT_Framework
{
    /**
     * The single instance of XT_Woo_Floating_Cart.
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static  $_instance = null ;
    /**
     * Bootstrap plugin
     *
     * This hack is needed. Overriding parent for Freemius to work properly.
     * Freemius needs to be called from each plugin and not from the XT Framework instance.
     * This way, when Freemius calls the function "get_caller_main_file_and_type", it will return the correct plugin path
     * Otherwise, the main path will be seen for all plugins and will cause issues
     *
     * Waiting for a fix from Freemius
     *
     * @since    1.0.0
     * @access   public
     */
    public function bootstrap()
    {
        parent::bootstrap();
    }
    
    /**
     * Load Freemius License Manager
     *
     * This hack is needed. Implementing this abstract XT Framework method for Freemius to work properly.
     * Freemius fs_dynamic_init needs to be called from each plugin and not from the XT Framework instance,
     * This way the "is_premium" param will correctly be generated for both free and premium versions
     *
     * Waiting for a fix from Freemius
     *
     * @return mixed
     * @since    1.0.0
     */
    protected function freemius_access_manager()
    {
        // Activate multisite network integration.
        if ( !defined( 'WP_FS__PRODUCT_' . $this->market_product()->id . '_MULTISITE' ) ) {
            define( 'WP_FS__PRODUCT_' . $this->market_product()->id . '_MULTISITE', true );
        }
        // Include Freemius SDK.
        require_once $this->plugin_framework_path( 'includes/freemius', 'start.php' );
        $menu = array(
            'slug'    => $this->plugin_slug(),
            'contact' => false,
            'support' => false,
            'network' => true,
        );
        if ( !$this->plugin()->top_menu() ) {
            $menu['parent'] = array(
                'slug' => $this->framework_slug(),
            );
        }
        $has_premium = !empty($this->market_product()->premium_slug);
        return fs_dynamic_init( array(
            'id'               => $this->market_product()->id,
            'slug'             => $this->market_product()->freemium_slug,
            'premium_slug'     => ( $has_premium ? $this->market_product()->premium_slug : null ),
            'type'             => 'plugin',
            'public_key'       => $this->market_product()->key,
            'is_premium'       => true,
            'is_premium_only'  => $this->plugin()->premium_only(),
            'premium_suffix'   => ( $has_premium ? 'Pro' : null ),
            'has_addons'       => false,
            'has_paid_plans'   => $has_premium,
            'is_org_compliant' => !$this->plugin()->premium_only(),
            'has_affiliation'  => ( $has_premium ? 'all' : null ),
            'trial'            => ( $has_premium ? array(
            'days'               => !$this->plugin()->trial_days(),
            'is_require_payment' => true,
        ) : null ),
            'menu'             => $menu,
            'navigation'       => 'menu',
            'is_live'          => true,
        ) );
    }
    
    /**
     * The reference to the class that manages the frontend side of the plugin.
     *
     * @return   XT_Woo_Floating_Cart_Public $frontend
     * @since    1.0.0
     */
    public function frontend()
    {
        return parent::frontend();
    }
    
    /**
     * The reference to the class that manages the backend side of the plugin.
     *
     * @return   XT_Woo_Floating_Cart_Admin $backend
     * @since    1.0.0
     */
    public function backend()
    {
        return parent::backend();
    }
    
    /**
     * Main XT_Woo_Floating_Cart Instance
     *
     * Ensures only one instance of XT_Woo_Floating_Cart is loaded or can be loaded.
     *
     * @return XT_Framework instance
     * @see XT_Woo_Floating_Cart()
     * @since 1.0.0
     * @static
     */
    public static function instance( $params )
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self( $params );
        }
        return self::$_instance;
    }

}