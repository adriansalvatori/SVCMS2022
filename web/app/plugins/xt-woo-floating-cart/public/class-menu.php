<?php
/**
 * The Cart Menu Item functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    XT_Woo_Floating_Cart
 * @subpackage XT_Woo_Floating_Cart_Menu/public
 * @author     XplodedThemes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class XT_Woo_Floating_Cart_Menu {

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

		$this->filter_nav_menus();
		add_filter( 'woocommerce_add_to_cart_fragments', array($this, 'cart_fragments'), 1, 1 );
	}

	/**
	 * Add filters to selected menus to add cart item <li>
	 */
	public function filter_nav_menus() {

		$desktop_menus = $this->core->customizer()->get_option('cart_menu_menus', array());
        $tablet_menus = $this->core->customizer()->get_option('cart_menu_menus_tablet', array());
        $mobile_menus = $this->core->customizer()->get_option('cart_menu_menus_mobile', array());

		if ( !empty($desktop_menus) ) {
			foreach ($desktop_menus as $menu_slug) {
				add_filter( 'wp_nav_menu_' . $menu_slug . '_items', array( $this, 'add_to_desktop_menu'), 10, 2 );
			}
		}

        if ( !empty($tablet_menus) ) {
            foreach ($tablet_menus as $menu_slug) {
                add_filter( 'wp_nav_menu_' . $menu_slug . '_items', array( $this, 'add_to_tablet_menu'), 10, 2 );
            }
        }

        if ( !empty($mobile_menus) ) {
            foreach ($mobile_menus as $menu_slug) {
                add_filter( 'wp_nav_menu_' . $menu_slug . '_items', array( $this, 'add_to_mobile_menu'), 10, 2 );
            }
        }
	}

    /**
     * Add Cart Menu to desktop menu
     *
     * @return string Menu items + Menu Cart item
     */
    public function add_to_desktop_menu( $items) {

        return $this->add_to_menu($items, 'desktop');
    }

    /**
     * Add Cart Menu to tablet menu
     *
     * @return string Menu items + Menu Cart item
     */
    public function add_to_tablet_menu( $items ) {

        return $this->add_to_menu($items, 'tablet');
    }

    /**
     * Add Cart Menu to mobile menu
     *
     * @return string Menu items + Menu Cart item
     */
    public function add_to_mobile_menu( $items ) {

        return $this->add_to_menu($items, 'mobile');
    }

	/**
	 * Add Cart Menu to menu
	 *
	 * @return string Menu items + Menu Cart item
	 */
	public function add_to_menu( $items, $screen ) {

		$cart_count = WC()->cart->get_cart_contents_count();

        $screen_setting_suffix = $screen !== 'desktop' ? $screen : '_'.$screen;
        $menu_alignment_default = $screen === 'desktop' ? 'left' : 'inherit';

		$menu_display_empty = $this->core->customizer()->get_option_bool('cart_menu_display_empty', false);
		$menu_alignment = $this->core->customizer()->get_option('cart_menu_alignment'.$screen_setting_suffix, $menu_alignment_default);
		$menu_position = $this->core->customizer()->get_option('cart_menu_position', 'last');

		$classes = array(
			'menu-item',
			'xt_woofc-menu',
			'xt_woofc-menu-'.$screen.'-align-'.$menu_alignment
		);

		if(!$menu_display_empty) {
			$classes[] = 'xt_woofc-menu-hide-empty';
		}

		if($cart_count === 0) {
			$classes[] = 'xt_woofc-menu-empty';
		}

		$common_classes = $this->get_common_li_classes($items);
		if (!empty($common_classes)) {
			$classes = array_merge($classes, $common_classes);
		}

        if (($key = array_search("menu-item-has-children", $classes, true)) !== false) {
            unset($classes[$key]);
        }
        if (($key = array_search("has-dropdown", $classes, true)) !== false) {
            unset($classes[$key]);
        }


		// Filter for <li> item classes
		/* Usage (in the themes functions.php):
		function theme_prefix_xt_woofc_menu_class ($classes) {
			$classes[] = 'yourclass';
			return $classes;
		}
		add_filter('xt_woofc_menu_classes', 'theme_prefix_xt_woofc_menu_class', 1, 1);
		*/

        $classes = array_unique(apply_filters( 'xt_woofc_menu_classes', $classes ));
        $classes = implode(" ", $classes);

        $menu_li  = '<li class="xt_woofc-menu-'.esc_attr($screen).' '.esc_attr($classes).'">' . $this->cart_menu_link($screen) . '</li>';

        if ( $menu_position === 'first' ) {
			$items = apply_filters( 'xt_woofc_menu_wrapper', $menu_li ) . $items;
		} else {
			$items .= apply_filters( 'xt_woofc_menu_wrapper', $menu_li );
		}

		return $items;
	}

	/**
	 * Get a flat list of common classes from all menu items in a menu
	 * @param  string $items nav_menu HTML containing all <li> menu items
	 * @return array        flat (imploded) list of common classes
	 */
	public function get_common_li_classes($items) {
		if (empty($items)) return array();
		if (!class_exists('DOMDocument')) return array();

		$libxml_previous_state = libxml_use_internal_errors(true); // enable user error handling

		$dom_items = new DOMDocument;
		$dom_items->loadHTML( $items );
		$lis = $dom_items->getElementsByTagName('li');

		if (empty($lis)) {
			libxml_clear_errors();
			libxml_use_internal_errors($libxml_previous_state);
			return array();
		}

		$li_classes = array();
		foreach($lis as $li) {
			if ($li->parentNode->tagName != 'ul')
				$li_classes[] = explode( ' ', $li->getAttribute('class') );
		}

		// Uncomment to dump DOM errors / warnings
		//$errors = libxml_get_errors();
		//print_r ($errors);

		// clear errors and reset to previous error handling state
		libxml_clear_errors();
		libxml_use_internal_errors($libxml_previous_state);

		$common_li_classes = array();
		if ( !empty($li_classes) ) {
			$common_li_classes = array_shift($li_classes);
			foreach ($li_classes as $li_class) {
				$common_li_classes = array_intersect($li_class, $common_li_classes);
			}
		}

		if (($key = array_search('xt-is-option', $common_li_classes)) !== false) {
			unset($common_li_classes[$key]);
		}

		return $common_li_classes;
	}

	/**
	 * Cart Menu Fragments
	 */
	public function cart_fragments( $fragments ) {

		$fragments['.xt_woofc-menu-desktop a.xt_woofc-menu-link'] = $this->cart_menu_link('desktop');
        $fragments['.xt_woofc-menu-tablet a.xt_woofc-menu-link'] = $this->cart_menu_link('tablet');
        $fragments['.xt_woofc-menu-mobile a.xt_woofc-menu-link'] = $this->cart_menu_link('mobile');

        return $fragments;
	}

	/**
	 * Create HTML for Menu Cart item
	 */
	public function cart_menu_link($screen) {

        $screen_setting = ($screen !== 'desktop') ? '_'.$screen : '';

        $menu_display = $this->core->customizer()->get_option('cart_menu_display'.$screen_setting, 'items');
        $menu_counter_type_class = $this->core->customizer()->get_option('cart_menu_counter_type'.$screen_setting, 'text');
        $menu_cart_click_action = $this->core->customizer()->get_option('cart_menu_click_action', 'toggle');
        $icon_only_when_empty = $this->core->customizer()->get_option_bool('cart_menu_icon_only_on_empty', false);

		$cart = (object) array(
			'total'	=> xt_woofc_checkout_total(),
			'count'	=> WC()->cart->get_cart_contents_count(),
		);

        if($cart->count === 0 && $icon_only_when_empty) {
            $menu_display = 'icon';
        }

		$menu_title = esc_html__('View your shopping cart', 'woo-floating-cart');
		$cart_count = sprintf(_n('%s%d%s %sitem%s', '%s%d%s %sitems%s', $cart->count, 'woo-floating-cart'), '<span>', $cart->count, '</span>', '<span>', '</span>');

		if(!in_array($menu_cart_click_action, array('toggle', 'cart'))) {
			$menu_href = apply_filters('xt_woofc_menu_url', wc_get_checkout_url() );
		}else{
			$menu_href = apply_filters('xt_woofc_menu_url', wc_get_cart_url() );
		}

		$menu_title = apply_filters('xt_woofc_menu_title', $menu_title );

		$menu_classes = array(
			'xt_woofc-menu-link'
		);

		if($cart->count > 999) {
			$menu_classes[] = 'xt_woofc-count-bigger';
		}else if($cart->count > 99) {
			$menu_classes[] = 'xt_woofc-count-big';
		}

		if(in_array($menu_display, array('items')) && $menu_counter_type_class === 'badge') {
			$menu_classes[] = 'xt_woofc-menu-has-badge';
		}

		if(defined('UBERMENU_VERSION') && (version_compare(UBERMENU_VERSION, '3.0.0') >= 0)){
			$menu_classes[] = 'ubermenu-target';
		}

		$menu_classes = apply_filters ('xt_woofc_menu_link_classes', $menu_classes );
		$menu_classes = implode(" ", $menu_classes);

		$menu = '<a class="'.esc_attr($menu_classes).'" href="'.esc_url($menu_href).'" title="'.esc_attr($menu_title).'">';

		$menu_cart_icon = $this->core->customizer()->get_option('cart_menu_icon');

		$menu_content = '';
		if (!empty($menu_cart_icon)) {
			$menu_icon = '<span class="xt_woofc-menu-icon '.esc_attr($menu_cart_icon).'" role="img" aria-label="'.esc_html__( 'Cart','woo-floating-cart' ).'"></span>';
			$menu_content .= $menu_icon;
		} else {
			$menu_icon = '';
		}

		$counter_classes = array('xt_woofc-menu-count', 'xt_woofc-counter-type-'.$menu_counter_type_class);

		if($menu_counter_type_class === 'badge') {

			$counter_classes[] = 'xt_woofc-counter-position-'.$this->core->customizer()->get_option( 'cart_menu_counter_badge_position'.$screen_setting, 'above' );
		}

		$counter_classes = implode(" ", $counter_classes);

		switch ($menu_display) {
			case 'items': //items only
				$menu_content .= '<span class="'.esc_attr($counter_classes).'">'.$cart_count.'</span>';
				break;
			case 'price': //price only
				$menu_content .= '<span class="xt_woofc-menu-amount">'.$cart->total.'</span>';
				break;
			case 'both': //items & price
				$menu_content .= '<span class="'.esc_attr($counter_classes).'">'.$cart_count.'</span><span class="xt_woofc-menu-amount">'.$cart->total.'</span>';
				break;
		}
		$menu_content = apply_filters('xt_woofc_menu_link_content', $menu_content, $menu_icon, $cart_count, $cart->total, $cart );

		$menu .= $menu_content . '</a>';

		$menu = apply_filters('xt_woofc_menu_link', $menu, $menu_content, $cart_count, $cart->total, $cart);

		if( !empty( $menu ) ) {
			return $menu;
		}

		return null;
	}
}
