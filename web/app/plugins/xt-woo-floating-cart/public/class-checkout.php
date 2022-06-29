<?php
/**
 * The checkout-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the checkout-specific stylesheet and JavaScript.
 *
 * @package    XT_Woo_Floating_Cart
 * @subpackage XT_Woo_Floating_Cart/checkout
 * @author     XplodedThemes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class XT_Woo_Floating_Cart_Checkout
{
    /**
     * Core class reference.
     *
     * @since    1.0.0
     * @access   private
     * @var      XT_Woo_Floating_Cart $core
     */
    private $core;

    public $checkout_frame_query = 'xt-woofc-checkout';

    /**
     * Core class reference.
     *
     * @since    1.0.0
     * @access   private
     * @var      XT_Woo_Floating_Cart    $core    Core Class
     */
    public function __construct($core)
    {
        $this->core = $core;

        add_filter('xt_woofc_script_vars', array($this, 'append_script_vars'));

        add_filter('xt_woofc_checkout_link', array($this, 'filter_checkout_button_link'));
        add_filter('xt_woofc_checkout_label', array($this, 'filter_checkout_button_label'));
        add_filter('xt_woofc_checkout_btn_show_subtotal', '__return_false');
        add_action('xt_woofc_after_cart_body_footer', array($this, 'append_checkout_form_submitted_message'), 20);

        if($this->is_checkout_frame()) {

            xtfw_maybe_define_constant('XT_FRAME', true);

            add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_filter('xt_woofc_should_not_load', '__return_true');

            add_filter('show_admin_bar', '__return_false');
            add_filter('woocommerce_get_checkout_url', array($this, 'add_checkout_query_arg'), 10, 1);
            add_filter('woocommerce_get_cart_url', array($this, 'add_checkout_query_arg'), 10, 1);
            add_filter('woocommerce_ajax_get_endpoint', array($this, 'add_checkout_query_arg'), 10, 1);
            add_action('template_redirect', array($this, 'filter_checkout_frame_redirect'), 10);

            if (!is_admin()) {
                add_filter('wc_get_template', array($this, 'wc_get_template_cart_shipping'), 10, 2);
            }
        }
    }

    public function append_script_vars($vars) {

        return xtfw_array_merge_recursive_distinct($vars, array(
            'cart_checkout_form' => $this->checkout_form_enabled(),
            'checkout_frame_url' => wc_get_checkout_url().'?'.$this->checkout_frame_query.'=1',
            'checkout_frame_query' => $this->checkout_frame_query,
            'checkout_complete_action' => $this->core->customizer()->get_option('cart_checkout_complete_action', 'redirect'),
            'lang' => array(
                'back_to_cart' => esc_html__('Back to cart', 'woo-floating-cart'),
                'place_order' => esc_html__('Place Order', 'woo-floating-cart'),
                'placing_order' => esc_html__('Placing Order...', 'woo-floating-cart'),
                'order_received_title' => esc_html__('Order Received', 'woo-floating-cart')
            )
        ));
    }

    public function filter_checkout_button_link() {

        return wc_get_checkout_url();
    }

    public function filter_checkout_button_label() {

        return esc_html__( 'Checkout', 'woo-floating-cart' );
    }

    public function is_checkout_frame() {

        return $this->core->frontend()->is_checkout_frame();
    }

    public function checkout_form_enabled()
    {
        return $this->core->frontend()->checkout_form_enabled();
    }

    function add_checkout_query_arg( $url ) {

        if($this->is_checkout_frame() && strpos($url, $this->checkout_frame_query) === false) {

            if(strpos($url, 'wc-ajax') !== false) {
                $url .= '&'.$this->checkout_frame_query.'=1';
            }else {
                $url = add_query_arg($this->checkout_frame_query, '1', urldecode($url));
            }
        }

        return $url;
    }

    public function append_checkout_form_submitted_message() {
        if($this->checkout_form_enabled()) {
            $this->core->get_template('parts/checkout/thank-you');
        }
    }

    public function filter_checkout_frame_redirect() {

        if(is_checkout() && !is_wc_endpoint_url( 'order-received' )) {

            $wrapClasses = array('xt_woofc');

            $override_woo_notices  = $this->core->customizer()->get_option_bool( 'override_woo_notices', false );
            $woo_success_hide  = $this->core->customizer()->get_option_bool( 'woo_success_notice_hide', false );
            $woo_info_hide  = $this->core->customizer()->get_option_bool( 'woo_info_notice_hide', false );

            if($override_woo_notices) {
                $wrapClasses[] = 'xt_woofc-override-woo-notices';
            }

            if($woo_success_hide) {
                $wrapClasses[] = 'xt_woofc-success-notice-hide';
            }

            if($woo_info_hide) {
                $wrapClasses[] = 'xt_woofc-info-notice-hide';
            }

            $wrapClasses = implode(" ", $wrapClasses);
            ?>
            <!DOCTYPE html>
            <html class="no-js" <?php language_attributes(); ?>>
            <head>
                <?php wp_head(); ?>
            </head>
            <body <?php body_class('xt_woofc-checkout-page'); ?>>
                <div class="woocommerce">
                    <div id="xt_woofc" class="<?php echo esc_attr($wrapClasses)?>">
                        <div class="xt_woofc-checkout-wrap">
                            <?php echo do_shortcode('[woocommerce_checkout]'); ?>
                        </div>
                    </div>
                </div>
                <?php wp_footer();?>
            </body>
            </html>
        <?php
        die();
        }
    }

    public function wc_get_template_cart_shipping($template_name, $args = array())
    {

        if (
            strpos($template_name, 'cart/cart-shipping.php') !== false &&
            empty($args['show_shipping_calculator']) &&
            $this->checkout_form_enabled() &&
            $this->is_checkout_frame()
        ) {

            $template_name = $this->core->get_template('parts/cart/shipping', $args, false, true);
        }

        return $template_name;
    }


    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        wp_enqueue_style(
            $this->core->plugin_slug('checkout'),
            $this->core->plugin_url('public/assets/css', 'checkout.css'),
            array(),
            $this->core->plugin_version(),
            'all'
        );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        wp_enqueue_script(
            $this->core->plugin_slug('checkout'),
            $this->core->plugin_url('public/assets/js', 'checkout' . XTFW_SCRIPT_SUFFIX . '.js'),
            array('jquery'),
            $this->core->plugin_version(),
            true
        );
    }
}


