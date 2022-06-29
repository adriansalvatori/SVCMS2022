<?php
/**
 * The public-facing functionality of the premium plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    XT_Woo_Floating_Cart
 * @subpackage XT_Woo_Floating_Cart/public_pro
 * @author     XplodedThemes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class XT_Woo_Floating_Cart_Public_Pro extends XT_Woo_Floating_Cart_Public
{

    /**
     * Var that holds the menu class object.
     *
     * @since    1.0.0
     * @access   public
     * @var      XT_Woo_Floating_Cart_Menu $menu
     */
    public $menu;

    /**
     * Var that holds the shortcode class object.
     *
     * @since    1.0.0
     * @access   public
     * @var      XT_Woo_Floating_Cart_Shortcode $shortcode
     */
    public $shortcode;

    /**
     * Var that holds the checkout class object.
     *
     * @since    1.0.0
     * @access   public
     * @var      XT_Woo_Floating_Cart_Checkout $checkout
     */
    public $checkout;

    /**
     * Var that holds custom payment buttons
     *
     * @since    2.1.2
     * @access   public
     * @var      array $payment_buttons buttons
     */
    public $payment_buttons = array();

    /**
     * Var that holds custom payment buttons enabled count
     *
     * @since    2.1.2
     * @access   public
     * @var      int $payment_buttons_enabled count
     */
    public $payment_buttons_enabled = 0;

    public function __construct($core)
    {
        parent::__construct($core);

        add_action('wp_loaded', array($this, 'init_custom_payment_buttons'));
        add_action('template_redirect', array($this, 'define_woocommerce_constants'), 10);

        add_action('xt_woofc_cart_body_header', array($this, 'render_coupon_form'), 0);
        add_action('xt_woofc_cart_body_header', array($this, 'render_header_message'), 10);
        add_action('xt_woofc_cart_body_header', array($this, 'render_shipping_bar'), 20);
        add_action('xt_woofc_before_product_body', array($this, 'do_woocommerce_after_cart_item_name'), 10, 2);

        add_filter('xt_woofc_checkout_btn_show_subtotal', array($this, 'filter_checkout_btn_show_subtotal'));
        add_action('xt_woofc_cart_body_footer', array($this, 'render_totals'), 20);

        add_action('xt_woofc_after_coupon_form', array($this, 'render_coupon_list'), 10);
        add_filter('woocommerce_cart_totals_coupon_html', array($this, 'filter_coupon_html'), 10, 1);
        add_filter('woocommerce_loop_add_to_cart_args', array($this, 'woocommerce_loop_add_to_cart_args'), 10, 2);
        add_action('woocommerce_after_add_to_cart_button', array($this, 'woocommerce_single_add_product_image_info'), 10);
        // Support Fly to cart for Woo Variation Swatches
        add_action('xt_woovs_after_add_to_cart_button', array($this, 'woocommerce_single_add_product_image_info'), 10);

        if (!is_admin()) {
            add_filter('wc_get_template', array($this, 'wc_get_template_cart_shipping'), 10, 2);
        }

        add_action('init', array($this, 'suggested_products_hooks'));
        add_action('init', array($this, 'total_savings_hooks'));
        add_action('init', array($this, 'init_dependencies'));

    }

    public function suggested_products_hooks()
    {

        if (!$this->suggested_products_enabled()) {
            return;
        }

        $position = $this->core->customizer()->get_option('suggested_products_position', 'below_list');

        $priority = 10;
        if ($position === 'above_totals') {
            $priority = 19;
        } else if ($position === 'below_totals') {
            $priority = 25;
        }

        add_action('xt_woofc_cart_body_footer', array($this, 'render_suggested_products'), $priority);

    }

    public function total_savings_hooks()
    {
        $total_savings_enabled = $this->core->customizer()->get_option_bool('enable_total_savings', false);

        if ($total_savings_enabled) {
            // Hook our values to the Basket and Checkout pages
            add_action('woocommerce_cart_totals_after_order_total', array($this, 'total_savings'), 99);
            add_action('woocommerce_review_order_after_order_total', array($this, 'total_savings'), 99);
        }
    }

    public function init_dependencies()
    {
        if ($this->menu_item_enabled()) {

            $this->menu = new XT_Woo_Floating_Cart_Menu($this->core);
        }

        if ($this->shortcode_enabled()) {

            $this->shortcode = new XT_Woo_Floating_Cart_Shortcode($this->core);
        }

        if ($this->checkout_form_enabled() || $this->is_checkout_frame()) {

            $this->checkout = new XT_Woo_Floating_Cart_Checkout($this->core);
        }

    }

    public function menu_item_enabled()
    {

        return $this->core->customizer()->get_option_bool('cart_menu_enabled', false);
    }

    public function shortcode_enabled()
    {

        return $this->core->customizer()->get_option_bool('cart_shortcode_enabled', false);
    }

    public function checkout_form_enabled()
    {

        return $this->core->customizer()->get_option_bool('cart_checkout_form', false);
    }

    public function is_checkout_frame() {

        return !empty($_GET['xt-woofc-checkout']);
    }

    public function totals_enabled()
    {

        return $this->core->customizer()->get_option_bool('enable_totals', false);
    }

    public function coupon_form_enabled()
    {

        return $this->core->customizer()->get_option_bool('enable_coupon_form', false);
    }

    public function coupon_list_enabled()
    {

        return $this->coupon_form_enabled() && $this->core->customizer()->get_option_bool('enable_coupon_list', false);
    }

    public function suggested_products_enabled()
    {

        $enabled = $this->core->customizer()->get_option_bool('suggested_products_enabled', false);
        $enabled_mobile = $this->core->customizer()->get_option_bool('suggested_products_mobile_enabled', false);

        return $enabled || ($enabled_mobile && wp_is_mobile());
    }

    public function suggested_products_slider_enabled()
    {

        return $this->suggested_products_enabled() && $this->core->customizer()->get_option('suggested_products_display_type', 'slider') === 'slider';
    }

    public function filter_checkout_btn_show_subtotal($show_subtotal) {

        if($this->totals_enabled() || $this->coupon_form_enabled()){
            $show_subtotal = false;
        }

        return $show_subtotal;
    }

    public function wc_get_template_cart_shipping($template_name, $args = array())
    {

        if (
            strpos($template_name, 'cart/cart-shipping.php') !== false &&
            empty($args['show_shipping_calculator']) &&
            ($this->enabled() && $this->totals_enabled()) &&
            did_action('xt_woofc_before_totals')
        ) {

            $template_name = $this->core->get_template('parts/cart/shipping', $args, false, true);
        }

        return $template_name;
    }

    public function define_woocommerce_constants()
    {

        do_action('xt_woofc_before_woocommerce_constants');

        if ($this->enabled() && wp_doing_ajax() && $this->totals_enabled()) {

            $this->define_cart_constant();
        }

    }

    function total_savings($tableWrap = false)
    {

        $discount_total = WC()->cart->get_cart_discount_total();

        foreach (WC()->cart->get_cart() as $values) {

            $_product = $values['data'];

            if ($_product->is_on_sale()) {
                $regular_price = floatval($_product->get_regular_price());
                $sale_price = floatval($_product->get_sale_price());
                $discount = ($regular_price - $sale_price) * $values['quantity'];
                $discount_total += $discount;
            }
        }

        if ($discount_total > 0) {

            if ($tableWrap) {
                echo '
		        <div class="woocommerce-checkout-review-order">
                    <table class="shop_table shop_table_responsive">';
            }

            echo '
			<tr class="xt_woofc-total-savings">
			    <th>' . esc_html__('Total savings', 'woo-floating-cart') . '</th>
			    <td data-title=" ' . esc_html__('Total savings', 'woo-floating-cart') . ' ">
					<strong>' . wc_price($discount_total) . '</strong>
			    </td>
		    </tr>';

            if ($tableWrap) {
                echo '</table>
                </div>';
            }
        }
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        parent::enqueue_styles();

        if ($this->menu_item_enabled() || $this->shortcode_enabled()) {
            wp_enqueue_style('xt-woo-custom', $this->core->plugin_url('public/assets/css', 'woo-custom.css'), array(), $this->core->plugin_version(), 'all');
            wp_enqueue_style('xt-icons');
        }

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        parent::enqueue_scripts();

        if (!$this->enabled() && !$this->menu_item_enabled() && !$this->shortcode_enabled()) {
            return;
        }

        wp_enqueue_script('xt-gsap', $this->core->plugin_url('public') . 'assets/vendors/xt-gsap.min.js', array('jquery'), $this->core->plugin_version(), true);

        if ($this->suggested_products_slider_enabled()) {
            wp_enqueue_script('xt-lightslider', $this->core->plugin_url('public/assets/vendors/lightslider/js', 'lightslider' . XTFW_SCRIPT_SUFFIX . '.js'), array('jquery'), $this->core->plugin_version(), false);
            wp_enqueue_style('xt-lightslider', $this->core->plugin_url('public/assets/vendors/lightslider/css', 'lightslider.css'), array(), $this->core->plugin_version(), 'all');
        }

        if ($this->totals_enabled()) {
            wp_enqueue_script('wc-country-select');
            wp_enqueue_script('wc-address-i18n');
        }

        if ($this->custom_payment_buttons_enabled('paypal')) {
            wp_enqueue_script('wc-gateway-ppec-smart-payment-buttons');
        }

        if (is_customize_preview()) {

            wp_add_inline_script($this->core->plugin_slug(), '

                XT_WOOFC.is_customize_preview = "1";
                
                var disableClickSelectors = [".xt_woofc-remove-coupon"];

                if(XT_WOOFC.cart_checkout_form === "1") {
                    disableClickSelectors.push(".xt_woofc-checkout");
                }
                 
				if(XT_WOOFC.cart_menu_enabled === "1" && XT_WOOFC.cart_menu_click_action === "toggle") {
					disableClickSelectors.push(".xt_woofc-menu-link");
				}

				if(XT_WOOFC.cart_shortcode_enabled === "1" && XT_WOOFC.cart_shortcode_click_action === "toggle") {
					disableClickSelectors.push(".xt_woofc-shortcode-link");
				}

                disableClickSelectors = disableClickSelectors.join(",");

                jQuery(document).on("mouseenter", disableClickSelectors, function() {

                    jQuery(this).attr("data-href", jQuery(this).attr("href")).attr("href", "#");

                }).on("mouseleave", disableClickSelectors, function() {

                    jQuery(this).attr("href", jQuery(this).attr("data-href"));
                });
            ');
        }

        wp_enqueue_script($this->core->plugin_slug());
    }

    /**
     * @return array
     */
    public function get_script_vars()
    {

        $customizer = $this->core->customizer();
        $vars = parent::get_script_vars();

        return xtfw_array_merge_recursive_distinct($vars, array(
            'premium' => $this->core->access_manager()->can_use_premium_code__premium_only(),
            'sp_slider_enabled' => $this->suggested_products_slider_enabled(),
            'sp_slider_arrow' => $customizer->get_option('suggested_products_arrow', 'xt_wooqvicon-arrows-18'),
            'cart_autoheight_enabled' => $customizer->get_option_bool('cart_autoheight_enabled', false),
            'enable_totals' => $this->totals_enabled(),
            'enable_coupon_form' => $customizer->get_option_bool( 'enable_coupon_form', false ),
            'enable_coupon_list' => $customizer->get_option_bool( 'enable_coupon_form', false ) && $customizer->get_option_bool( 'enable_coupon_list', false ),
            'cart_menu_enabled' => $this->menu_item_enabled(),
            'cart_menu_click_action' => $customizer->get_option('cart_menu_click_action', 'toggle'),
            'cart_shortcode_enabled' => $this->shortcode_enabled(),
            'cart_shortcode_click_action' => $customizer->get_option('cart_shortcode_click_action', 'toggle'),
            'cart_shipping_bar_enabled' => $customizer->get_option_bool('cart_shipping_bar_enabled', false),
            'custom_payments' => $this->custom_payment_buttons_enabled(),
            'trigger_selectors' => XT_Framework_Customizer_Helpers::repeater_fields_string_to_array($customizer->get_option('trigger_extra_selectors', array())),
            'lang' => array(
                'coupons' => esc_html__('Coupons', 'woo-floating-cart'),
                'back_to_cart' => esc_html__('Back to cart', 'woo-floating-cart'),
                'clear_confirm' => sprintf(esc_html__('Want to clear the cart? %s', 'woo-floating-cart'), '<a href="#" class="xt_woofc-header-clear-confirm">'.esc_html__('Yes', 'woo-floating-cart').'</a>&nbsp; | &nbsp;<a href="#" class="xt_woofc-header-clear-cancel">'.esc_html__('No', 'woo-floating-cart').'</a>')
            )
        ));

    }

    public function init_custom_payment_buttons()
    {

        if (is_admin() || xtfw_is_rest_request()) {
            return;
        }

        $button_template = '<div data-or="' . esc_html__('OR', 'woo-floating-cart') . '" class="xt_woofc-payment-btn widget_shopping_cart xt_woofc-%2$s-btn">%1$s</div>';

        if ($this->core->customizer()->get_option_bool('paypal_express_checkout') ) {

            // Paypal button
            // https://wordpress.org/plugins/woocommerce-paypal-payments/
            if (class_exists('\WooCommerce\PayPalCommerce\PluginModule')) {

                $gateway_settings = get_option('woocommerce_ppcp-gateway_settings');
                $paypal_settings = get_option('woocommerce-ppcp-settings');

                if(
                    !empty($gateway_settings['enabled']) && $gateway_settings['enabled'] === "yes" &&
                    !empty($paypal_settings['button_mini-cart_enabled'])
                ) {

                    $button = '<p id="ppc-button-minicart" class="woocommerce-mini-cart__buttons buttons"></p>';

                    if (!empty($button)) {

                        $this->payment_buttons['paypal'] = sprintf($button_template, $button, 'ppc-paypal');
                    }

                    $this->payment_buttons_enabled++;
                }


            // Paypal button
            // https://wordpress.org/plugins/woocommerce-gateway-paypal-express-checkout/
            }else if (function_exists('wc_gateway_ppec')) {
                ob_start();
                wc_gateway_ppec()->cart->display_mini_paypal_button();
                $button = ob_get_clean();

                if (!empty($button)) {

                    $this->payment_buttons['paypal'] = sprintf($button_template, $button, 'ppec-paypal');
                }

                $this->payment_buttons_enabled++;
            }

        }

        $this->payment_buttons = apply_filters('xt_woofc_custom_payment_buttons', $this->payment_buttons, $button_template);

        add_action('xt_woofc_custom_payment_buttons', array($this, 'render_custom_payment_buttons'));

    }

    // Render payment buttons
    public function render_custom_payment_buttons()
    {

        $buttons_template = '<div class="xt_woofc-payment-btns">%s</div>';

        echo sprintf($buttons_template, implode("", $this->payment_buttons));
    }

    public function custom_payment_buttons_enabled($id = null)
    {

        if (!empty($id)) {
            return !empty($this->payment_buttons[$id]);
        }

        return $this->payment_buttons_enabled > 0;
    }

    public function render_coupon_form()
    {

        if (!$this->coupon_form_enabled()) {
            return;
        }

        $this->core->get_template('parts/cart/coupon', array());
    }

    public function render_coupon_list($partial = false)
    {

        if (!$this->coupon_list_enabled()) {
            return;
        }

        $this->core->get_template('parts/cart/coupon-list', array('partial' => $partial));
    }

    public function render_shipping_bar($partial = false){

        if (!$this->core->customizer()->get_option_bool('cart_shipping_bar_enabled')) {
            return;
        }

        $data = $this->get_shipping_bar_data();

        if(empty( $data ) ){
            echo '<div class="xt_woofc-shipping-bar"></div>';
            return;
        }

        $free_text = $this->core->customizer()->get_option('cart_shipping_bar_free_text', esc_html__('Congrats! You get free shipping.', 'woo-floating-cart'));
        $free_text = esc_html__(htmlspecialchars_decode($free_text, ENT_QUOTES), 'woo-floating-cart');
        $remaining_text = $this->core->customizer()->get_option('cart_shipping_bar_remaining_text', esc_html__("You're %s away from free shipping.", 'woo-floating-cart'));
        $remaining_text = esc_html__(htmlspecialchars_decode($remaining_text, ENT_QUOTES), 'woo-floating-cart');

        $previous_perc = WC()->session->get('xt_woofc_shipping_bar_perc');

        $args = array();
        $args['partial'] = $partial;
        $args['shipping_text'] = $data['is_free'] ? $free_text : str_replace( '%s', '<strong>'.wc_price( $data['amount_left'] ).'</strong>', $remaining_text);
        $args['fill_percentage'] = $data['fill_percentage'];
        $args['fill_prev_percentage'] = !empty($previous_perc) ? $previous_perc : 0;

        WC()->session->set('xt_woofc_shipping_bar_perc', $args['fill_percentage']);

        $args = apply_filters( 'xt_woofc_shipping_bar_args', $args );

        $this->core->get_template('parts/cart/header-shipping-bar', $args);
    }

    public function get_shipping_bar_data(){

        $data = array();

        if ( WC()->cart->display_prices_including_tax() ) {
            $subtotal = WC()->cart->get_subtotal() + WC()->cart->get_subtotal_tax();
        } else {
            $subtotal = WC()->cart->get_subtotal();
        }

        $hasFreeShipping 	= false;
        $amountLeft 		= $fillPercentage = null;

        $packages = WC()->shipping()->get_packages();

        if( empty( $packages ) ) return $data;

        //Support for 1 package only
        $package = $packages[0];

        $available_methods = $package['rates'];

        foreach ( $available_methods as $obj ) {
            if( $obj instanceof WC_Shipping_Free_Shipping ){
                $hasFreeShipping = true;
                break;
            }
        }

        if( !$hasFreeShipping ){
            $shipping_zone 		= WC_Shipping_Zones::get_zone_matching_package( $package );
            $shipping_methods 	= $shipping_zone->get_shipping_methods(true);

            foreach ( $shipping_methods as $obj ) {

                if( $obj instanceof WC_Shipping_Free_Shipping && ( $obj->requires === 'min_amount' || $obj->requires === 'either' ) ){

                    if( $obj->ignore_discounts === "no" && !empty( WC()->cart->get_coupon_discount_totals() ) ){
                        foreach ( WC()->cart->get_coupon_discount_totals() as $coupon_code => $coupon_value ) {
                            $subtotal -= $coupon_value;
                        }
                    }

                    $min_amount = $obj->min_amount;

                    if( $subtotal >= $min_amount ){
                        $hasFreeShipping = true;
                    }
                    else{
                        $amountLeft 	= $min_amount - $subtotal;
                        $fillPercentage =  ceil( ($subtotal/$min_amount) * 100 );
                    }

                }

                // Support FlexibleShipping plugin https://fr.wordpress.org/plugins/flexible-shipping/
                if(defined('FLEXIBLE_SHIPPING_VERSION') && get_class($obj) === 'WPDesk\FS\TableRate\ShippingMethodSingle' && !empty($obj->instance_settings["method_free_shipping"]) ) {

                    $min_amount = floatval($obj->instance_settings["method_free_shipping"]);

                    if( $subtotal >= $min_amount ){
                        $hasFreeShipping = true;
                    }
                    else{
                        $amountLeft 	= $min_amount - $subtotal;
                        $fillPercentage =  ceil( ($subtotal/$min_amount) * 100 );
                    }
                }
            }
        }

        if( !$hasFreeShipping && is_null( $amountLeft ) ) return $data;

        $data = array(
            'is_free' 			=> $hasFreeShipping,
            'amount_left' 		=> $amountLeft,
            'fill_percentage' 	=> $hasFreeShipping ? 100 : $fillPercentage
        );

        return apply_filters( 'xt_woofc_shipping_bar_data', $data );

    }

    public function do_woocommerce_after_cart_item_name() {

        do_action('do_woocommerce_after_cart_item_name');
    }

    public function render_header_message($partial = false)
    {

        if (!$this->core->customizer()->get_option_bool('cart_header_msg_enabled')) {
            return;
        }

        $message = $this->core->customizer()->get_option('cart_header_msg');

        if (!empty($message)) {
            $this->core->get_template('parts/cart/header-message', array('message' => $message, 'partial' => $partial));
        }
    }

    public function render_totals()
    {

        if ($this->totals_enabled()) {

            $this->core->get_template('parts/cart/totals', array());

        } else {

            // If totals not enabled while total savings enabled, show only the savings!
            $total_savings_enabled = $this->core->customizer()->get_option_bool('enable_total_savings', false);
            if ($total_savings_enabled) {
                $this->total_savings(true);
            }
        }
    }

    public function render_suggested_products()
    {
        if(!$this->suggested_products_enabled()) {
            return;
        }

        $customizer = $this->core->customizer();

        $display_type = $customizer->get_option('suggested_products_display_type', 'slider');
        $query_type = $customizer->get_option('suggested_products_type', 'cross_sells');
        $items_count = $customizer->get_option('suggested_products_count', 5);
        $hide_add_to_cart_button = $customizer->get_option_bool('suggested_products_hide_atc', false);

        $title = $customizer->get_option('suggested_products_title', esc_html__('Products you may like', 'woo-floating-cart'));
        $title = esc_html__(htmlspecialchars_decode($title, ENT_QUOTES), 'woo-floating-cart');

        $cart = WC()->cart->get_cart();
        $cart_is_empty = WC()->cart->is_empty();

        $suggested_products = array();
        $exclude_ids = array();

        if (!$cart_is_empty) {
            foreach ($cart as $cart_item) {
                $exclude_ids[] = $cart_item['product_id'];
            }

            switch ($query_type) {
                case 'cross_sells':
                    $suggested_products = WC()->cart->get_cross_sells();
                    break;

                case 'up_sells':

                    $last_cart_item = end($cart);
                    $product_id = $last_cart_item['product_id'];
                    $variation_id = $last_cart_item['variation_id'];

                    if ($variation_id) {
                        $product = wc_get_product($product_id);
                        $suggested_products = $product->get_upsell_ids();
                    } else {
                        $suggested_products = $last_cart_item['data']->get_upsell_ids();
                    }
                    break;

                case 'related':

                    shuffle($cart);

                    foreach ($cart as $cart_item) {
                        if (count($suggested_products) >= $items_count) {
                            break;
                        }

                        $product_id = $cart_item['product_id'];

                        $related_products = wc_get_related_products($product_id, $items_count, $exclude_ids);
                        $suggested_products = array_merge($suggested_products, $related_products);
                    }
                    break;

                case 'selection':

                    $selection = $customizer->get_option('suggested_products_selection', '');
                    if (!empty($selection)) {
                        $selection = str_replace(" ", "", $selection);
                        $selection = explode(",", $selection);
                        foreach ($selection as $id) {

                            if (count($suggested_products) >= $items_count) {
                                break;
                            }

                            $suggested_products[] = intval($id);
                        }
                    }

                    break;
            }
        }

        $suggested_products = array_diff($suggested_products, $exclude_ids);

        $vars = array(
            'display_type' => $display_type,
            'suggested_products' => $suggested_products,
            'items_count' => $items_count,
            'exclude_ids' => $exclude_ids,
            'title' => $title,
            'hide_add_to_cart_button' => $hide_add_to_cart_button
        );

        $vars = apply_filters('xt_woofc_suggested_product_vars', $vars);

        add_filter('xt_woovs_shop_swatches_enabled', '__return_false');

        $this->core->get_template('parts/cart/suggested-products', $vars);

        add_filter('xt_woovs_shop_swatches_enabled', '__return_true');

    }

    public function filter_coupon_html($coupon_html)
    {

        if (did_action('xt_woofc_before_totals') && !did_action('xt_woofc_after_totals')) {

            $coupon_html = str_replace('woocommerce-remove-coupon', 'xt_woofc-remove-coupon', $coupon_html);

            if (strpos($coupon_html, '<a') !== false) {

                $coupon_imploded = explode('<a', $coupon_html);
                $coupon_imploded[1] = '<a' . $coupon_imploded[1];

                $coupon_imploded = array_reverse($coupon_imploded);

                $coupon_html = implode("", $coupon_imploded);
            }
        }

        return $coupon_html;
    }

    public function woocommerce_loop_add_to_cart_args($args, $product)
    {

        $image_data = $this->get_product_image_data($product);

        if (!empty($image_data)) {
            $args['attributes']['data-product_image_src'] = $image_data[0];
            $args['attributes']['data-product_image_width'] = $image_data[1];
            $args['attributes']['data-product_image_height'] = $image_data[2];
        }

        return $args;
    }

    public function woocommerce_single_add_product_image_info()
    {

        global $product;

        $image_data = $this->get_product_image_data($product);

        if (!empty($image_data)) {
            echo '<meta class="xt_woofc-product-image" data-product_image_src="' . esc_attr($image_data[0]) . '" data-product_image_width="' . esc_attr($image_data[1]) . '" data-product_image_height="' . esc_attr($image_data[2]) . '" />';
        }
    }

    public function get_coupons()
    {

        $cache_key = 'xt_woofc_coupons';

        $coupons = wp_cache_get($cache_key);

        if (false === $coupons) {

            $showCouponList = $this->coupon_list_enabled();

            if (!$showCouponList) return array();

            $couponListType = $this->core->customizer()->get_option('coupon_list_type', 'all');
            $totalCoupons = intval($this->core->customizer()->get_option('coupon_list_total', 20));

            $includes = array();

            if ($couponListType === 'selection') {
                $selection = trim($this->core->customizer()->get_option('coupon_list_selection', ''));
                if (!empty($selection)) {
                    $includes = array_map('trim', explode(',', $selection));
                }
            }

            $args = array(
                'posts_per_page' => $totalCoupons,
                'include' => $includes,
                'orderby' => 'title',
                'order' => 'asc',
                'post_type' => 'shop_coupon',
                'post_status' => 'publish'
            );

            $coupons_post = get_posts($args);

            if (empty($coupons_post)) return array();

            $coupons = array('valid' => array(), 'invalid' => array());

            $hide_for_error_codes = array(
                105, //Not exists.
                107, //Expired
            );

            $hide_for_error_codes = apply_filters('xt_woofc_coupon_hide_invalid_codes', $hide_for_error_codes);

            $applied_coupons = WC()->cart->get_applied_coupons();

            foreach ($coupons_post as $coupon_post) {

                $coupon = new WC_Coupon($coupon_post->ID);
                $discounts = new WC_Discounts(WC()->cart);

                $valid = $discounts->is_coupon_valid($coupon);
                $code = $coupon->get_code();

                if (in_array($code, $applied_coupons)) {
                    continue;
                }

                $off_amount = $coupon->get_amount();

                $off_value = 'percent' === $coupon->get_discount_type() ? $off_amount . '%' : wc_price($off_amount);

                $data = array(
                    'code' => $code,
                    'coupon' => $coupon,
                    'notice' => '',
                    'off_value' => $off_value
                );

                if (is_wp_error($valid)) {

                    if ($couponListType !== 'all') continue;

                    $error_code = $valid->get_error_code();

                    if (in_array($error_code, $hide_for_error_codes)) continue;

                    $data['notice'] = $valid->get_error_message();

                }

                $coupons[is_wp_error($valid) ? 'invalid' : 'valid'][] = $data;
            }

            wp_cache_set($cache_key, $coupons);
        }

        return apply_filters('xt_woofc_coupons_list', $coupons);
    }

}
