<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    XT_Woo_Floating_Cart
 * @subpackage XT_Woo_Floating_Cart_Menu_Item/public
 * @author     XplodedThemes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class XT_Woo_Floating_Cart_Shortcode {

	/**
	 * Core class reference.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      XT_Woo_Floating_Cart $core
	 */
	private $core;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param XT_Woo_Floating_Cart $core Plugin core class
	 *
	 * @since    1.0.0
	 */
	public function __construct( $core ) {

		$this->core = $core;

		add_filter( 'woocommerce_add_to_cart_fragments', array($this, 'cart_fragments'), 1, 1 );

		$this->register_shortcode();
	}

	public function register_shortcode() {

		add_shortcode('xt_woofc_shortcode', array($this, 'shortcode'));
	}

	/**
	 * Add Cart Menu to menu
	 *
	 * @return string Menu items + Menu Cart item
	 */
	public function shortcode() {

        if(empty(WC()->cart)) {
            return '';
        }

		$classes = array(
			'xt_woofc-shortcode'
		);

		if(WC()->cart->get_cart_contents_count() === 0) {
			$classes[] = 'xt_woofc-shortcode-empty';
		}

		$classes = apply_filters( 'xt_woofc_shortcode_classes', $classes );
		$classes = implode(" ", $classes);

		$content = '';

		foreach(array('desktop', 'tablet', 'mobile') as $screen) {

            $screen_setting = ($screen !== 'desktop') ? '_'.$screen : '';
            $hidden = $this->core->customizer()->get_option_bool('cart_shortcode_hidden'.$screen_setting, false);

            $screen_class = 'xt_woofc-shortcode-' . esc_attr($screen);
            $hidden_class = '';

            if($hidden) {
                $hidden_class = ' xt_woofc-shortcode-hidden';
            }

            $content .= '<span class="' . esc_attr($screen_class.$hidden_class) . ' ' . esc_attr($classes) . '">' . $this->cart_shortcode_link($screen) . '</span>';

        }

		return $content;

	}

	/**
	 * Cart Menu Fragments
	 */
	public function cart_fragments( $fragments ) {

		$fragments['.xt_woofc-shortcode-desktop a.xt_woofc-shortcode-link'] = $this->cart_shortcode_link('desktop');
        $fragments['.xt_woofc-shortcode-tablet a.xt_woofc-shortcode-link'] = $this->cart_shortcode_link('tablet');
        $fragments['.xt_woofc-shortcode-mobile a.xt_woofc-shortcode-link'] = $this->cart_shortcode_link('mobile');

        return $fragments;
	}

	/**
	 * Create HTML for Menu Cart item
	 */
	public function cart_shortcode_link($screen) {

        $screen_setting = ($screen !== 'desktop') ? '_'.$screen : '';

        $shortcode_display = $this->core->customizer()->get_option('cart_shortcode_display'.$screen_setting, 'items');
        $shortcode_counter_type_class = $this->core->customizer()->get_option('cart_shortcode_counter_type'.$screen_setting, 'text');
        $shortcode_cart_click_action = $this->core->customizer()->get_option('cart_shortcode_click_action', 'toggle');
        $icon_only_when_empty = $this->core->customizer()->get_option_bool('cart_shortcode_icon_only_on_empty', false);

        $cart = (object) array(
			'total'	=> xt_woofc_checkout_total(),
			'count'	=> WC()->cart->get_cart_contents_count(),
		);

        if($cart->count === 0 && $icon_only_when_empty) {
            $shortcode_display = 'icon';
        }

		$shortcode_title = esc_html__('View your shopping cart', 'woo-floating-cart');
		$cart_count = sprintf(_n('%s%d%s %sitem%s', '%s%d%s %sitems%s', $cart->count, 'woo-floating-cart'), '<span>', $cart->count, '</span>', '<span>', '</span>');

		if(!in_array($shortcode_cart_click_action, array('toggle', 'cart'))) {
			$shortcode_href = apply_filters('xt_woofc_shortcode_url', wc_get_checkout_url() );
		}else{
			$shortcode_href = apply_filters('xt_woofc_shortcode_url', wc_get_cart_url() );
		}

		$shortcode_title = apply_filters('xt_woofc_shortcode_title', $shortcode_title );

		$shortcode_classes = array(
			'xt_woofc-shortcode-link'
		);

		$shortcode_classes = apply_filters ('xt_woofc_shortcode_link_classes', $shortcode_classes );
		$shortcode_classes = implode(" ", $shortcode_classes);

		$shortcode = '<a class="'.esc_attr($shortcode_classes).'" href="'.esc_url($shortcode_href).'" title="'.esc_attr($shortcode_title).'">';

		$shortcode_cart_icon = $this->core->customizer()->get_option('cart_shortcode_icon', null);

		$shortcode_content = '';
		if (!empty($shortcode_cart_icon)) {
			$shortcode_icon = '<span class="xt_woofc-shortcode-icon '.esc_attr($shortcode_cart_icon).'" role="img" aria-label="'.esc_html__( 'Cart','woo-floating-cart' ).'"></span>';
			$shortcode_content .= $shortcode_icon;
		} else {
			$shortcode_icon = '';
		}

		$counter_classes = array('xt_woofc-shortcode-count', 'xt_woofc-counter-type-'.$shortcode_counter_type_class);

		if($shortcode_counter_type_class === 'badge') {

			$counter_classes[] = 'xt_woofc-counter-position-'.$this->core->customizer()->get_option( 'cart_shortcode_counter_badge_position'.$screen_setting, 'above' );

			if($cart->count > 999) {
				$counter_classes[] = 'xt_woofc-count-bigger';
			}else if($cart->count > 99) {
				$counter_classes[] = 'xt_woofc-count-big';
			}
		}

		$counter_classes = implode(" ", $counter_classes);

		switch ($shortcode_display) {
			case 'items': //items only
				$shortcode_content .= '<span class="'.esc_attr($counter_classes).'">'.$cart_count.'</span>';
				break;
			case 'price': //price only
				$shortcode_content .= '<span class="xt_woofc-shortcode-amount">'.$cart->total.'</span>';
				break;
			case 'both': //items & price
				$shortcode_content .= '<span class="'.esc_attr($counter_classes).'">'.$cart_count.'</span><span class="xt_woofc-shortcode-amount">'.$cart->total.'</span>';
				break;
		}

		$shortcode_content = apply_filters('xt_woofc_shortcode_link_content', $shortcode_content, $shortcode_icon, $cart_count, $cart->total, $cart );

		$shortcode .= $shortcode_content . '</a>';

		$shortcode = apply_filters('xt_woofc_shortcode_link', $shortcode, $shortcode_content, $cart_count, $cart->total, $cart);

		if( !empty( $shortcode ) ) {
			return $shortcode;
		}

		return null;
	}
}
