<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    XT_Woo_Floating_Cart
 * @subpackage XT_Woo_Floating_Cart/public
 * @author     XplodedThemes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class XT_Woo_Floating_Cart_Public
{

    /**
     * Core class reference.
     *
     * @since    1.0.0
     * @access   protected
     * @var      XT_Woo_Floating_Cart $core
     */
    protected $core;

    /**
     * Var that holds the menu class object.
     *
     * @since    1.0.0
     * @access   public
     * @var      XT_Woo_Floating_Cart_Theme_Fixes $theme_fixes Theme Fixes
     */
    public $theme_fixes;

    /**
     * Initialize the class and set its properties.
     *
     * @param XT_Woo_Floating_Cart $core Plugin core class
     *
     * @since    1.0.0
     */
    public function __construct($core)
    {

        $this->core = $core;

        new XT_Woo_Floating_Cart_Ajax($this->core);
        $this->theme_fixes = new XT_Woo_Floating_Cart_Theme_Fixes($this->core);

        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        add_filter('rocket_cache_wc_empty_cart', '__return_false');
        add_filter('woocommerce_cart_item_price', array($this, 'change_cart_price_display'), 30, 3);
        add_action('xt_woofc_cart_body_header', array($this, 'render_wc_notices'), 99);
        add_action('xt_woofc_cart_body_header', array($this, 'after_wc_notices'), 99);
        add_filter('body_class', array($this, 'body_class') );
        add_action('wp_footer', array($this, 'render'));

    }

    public function enabled()
    {

        if ($this->should_not_load() || $this->is_cart_page() || is_checkout()) {
            return false;
        }

        $exclude_pages = $this->core->customizer()->get_option('hidden_on_pages', array());
        if (!empty($exclude_pages)) {
            foreach ($exclude_pages as $page) {
                if (!empty($page) && is_page($page)) {
                    return false;
                }
            }
        }

        return true;
    }

    function body_class( $classes ) {

        $body_color              = $this->core->customizer()->get_option('cart_body_bg_color', '#ffffff' );
        $body_is_light           = xtfw_hex_is_light($body_color);

        $classes[] = 'xt_woofc-'.($body_is_light ? 'is-light' : 'is-dark');

        return $classes;
    }

    public function is_cart_page()
    {

        $cart_page_id = wc_get_page_id('cart');

        return is_page($cart_page_id);
    }

    public function should_not_load()
    {

        $should_not_load = false;

        // skip if Divi or Elementor builder
        if (!empty($_GET['et_fb']) || !empty($_GET['elementor-preview'])) {
            $should_not_load = true;
        }

        // skip if within and admin iframe
        if(defined('IFRAME_REQUEST')) {
            $should_not_load = true;
        }

        return apply_filters('xt_woofc_should_not_load', $should_not_load);
    }


    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        if (!$this->enabled()) {
            return;
        }

        wp_enqueue_style('xt-icons');

        wp_register_style(
            $this->core->plugin_slug(),
            $this->core->plugin_url('public/assets/css', 'frontend.css'),
            array(),
            $this->core->plugin_version(),
            'all'
        );
        wp_enqueue_style($this->core->plugin_slug());

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        if ($this->core->customizer()->get_option_bool('active_cart_body_lock_scroll', false)) {
            wp_enqueue_script('xt-body-scroll-lock', $this->core->plugin_url('public') . 'assets/vendors/bodyScrollLock' . XTFW_SCRIPT_SUFFIX . '.js', array(), $this->core->plugin_version(), false);
        }

        if (!$this->is_cart_page()) {
            wp_dequeue_script('wc-cart');
        }

        // MAIN SCRIPT
        wp_register_script(
            $this->core->plugin_slug(),
            $this->core->plugin_url('public/assets/js', 'frontend' . XTFW_SCRIPT_SUFFIX . '.js'),
            array(
                'jquery',
                'wc-cart-fragments',
                'xt-jquery-touch',
                'xt-jquery-ajaxqueue',
                'xt-observers-polyfill'
            ),
            $this->core->plugin_version(),
            true
        );

        wp_localize_script($this->core->plugin_slug(), 'XT_WOOFC', apply_filters('xt_woofc_script_vars', $this->get_script_vars()));


        if (!$this->enabled()) {
            return;
        }

        wp_enqueue_script($this->core->plugin_slug());
    }

    /**
     * @return array
     */
    public function get_script_vars()
    {

        return array(
            'home_url' => home_url(),
            'is_customize_preview' => is_customize_preview(),
            'wc_ajax_url' => $this->core->wc_ajax()->get_ajax_url(),
            'layouts' => $this->core->customizer()->breakpointsJson(),
            'body_lock_scroll' => $this->core->customizer()->get_option_bool('active_cart_body_lock_scroll', false),
            'lang' => array(
                'loading' => esc_html__('Loading', 'woo-floating-cart'),
                'min_qty_required' => esc_html__('Min quantity required', 'woo-floating-cart'),
                'max_stock_reached' => esc_html__('Stock limit reached', 'woo-floating-cart'),
                'title' => esc_html__('Cart', 'woo-floating-cart'),
                'checkout' => xt_woofc_checkout_label(),
                'wait' => xt_woofc_checkout_processing_label()
            )
        );
    }

    public function change_cart_price_display($price, $values, $cart_item_key)
    {
        $slashed_price = $values['data']->get_price_html();
        $is_on_sale = $values['data']->is_on_sale();
        if ($is_on_sale) {
            $price = $slashed_price;
        }
        return $price;
    }

    public function get_product_image_data($product)
    {

        $image_id = $product->get_image_id();

        return wp_get_attachment_image_src($image_id, 'woocommerce_thumbnail', 0);
    }

    public function render_wc_notices() {

        $notices = force_balance_tags(xtfw_ob_get_clean(function() {
            do_action('woocommerce_check_cart_items');
            do_action('xt_woofc_set_notices');
            wc_print_notices();
        }));

        $this->core->frontend()->deduplicate_notices();
        ?>
        <div class="xt_woofc-wc-notices">
            <?php echo $notices;?>
        </div>
        <?php
    }

    public function after_wc_notices() {

        echo force_balance_tags(xtfw_ob_get_clean(function() {
            do_action('xt_woofc_after_notices');
        }));
    }

    public function deduplicate_notices() {

        $all_notices  = WC()->session->get( 'wc_notices', array() );
        $notice_types = array( 'error' );

        $exists = [];
        $duplicates = 0;
        foreach ( $notice_types as $notice_type ) {
            if (wc_notice_count($notice_type) > 0) {

                foreach ($all_notices[$notice_type] as $key => $notice) {
                    $message = isset($notice['notice']) ? $notice['notice'] : $notice;
                    if(in_array($message, $exists)) {
                        unset($all_notices[$notice_type][$key]);
                        $duplicates++;
                    }else{
                        $exists[] = $message;
                    }
                }
            }
        }

        if($duplicates > 0) {
            WC()->session->set('wc_notices', $all_notices);
        }
    }

    public function define_cart_constant()
    {
        wc_maybe_define_constant('WOOCOMMERCE_CART', true);
    }

    public function render()
    {

        if (!$this->enabled()) {
            return false;
        }

        ?>
        <div id="xt_woofc" class="<?php echo esc_attr(xt_woofc_class()); ?>" <?php xt_woofc_attributes(); ?>>

            <?php
            // for some reason, woocommerce-product-addon plugin does not work when this is added.
            if(!function_exists('PPOM')): ?>
            <form class="cart xt_woofc-hide"></form>
            <?php endif; ?>

            <?php do_action('xt_woofc_before_cart'); ?>

            <?php $this->core->get_template('parts/cart'); ?>

            <?php do_action('xt_woofc_after_cart'); ?>

        </div>
        <?php
    }
}
