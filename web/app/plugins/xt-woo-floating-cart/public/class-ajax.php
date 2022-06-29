<?php
/**
 * The Ajax functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    XT_Woo_Floating_Cart
 * @subpackage XT_Woo_Floating_Cart_Ajax/public
 * @author     XplodedThemes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class XT_Woo_Floating_Cart_Ajax {

    /**
     * Core class reference.
     *
     * @since    1.0.0
     * @access   private
     * @var      XT_Woo_Floating_Cart $core
     */
    private $core;

	/**
	 * Var that holds the cart notice
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string  $notice   Notice
	 */
	public $notice = '';

	/**
	 * Core class reference.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      XT_Woo_Floating_Cart    $core    Core Class
	 */
	public function __construct($core) {

		$this->core = $core;

        // Add WC Ajax Events
		add_filter($this->core->plugin_prefix('wc_ajax_add_events'), array( $this, 'ajax_add_events'), 1);

		// Set Fragments
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_fragments'), 1);

		// Remove / Restore hooks
		add_filter( 'woocommerce_remove_cart_item', array( $this, 'remove_cart_item'), 10, 2 );
		add_filter( 'woocommerce_cart_item_restored', array(  $this, 'cart_item_restored'), 10, 2 );

		// Added to cart action
		add_action( 'woocommerce_add_to_cart', array( $this, 'added_to_cart'), 10, 0 );

	}

	/**
	 * Add ajax events
	 */
	public function ajax_add_events($ajax_events) {

		$ajax_events[] = array(
			'function' => 'xt_woofc_update',
			'callback' => array($this, 'update_qty'),
			'nopriv' => true
		);

		$ajax_events[] = array(
			'function' => 'xt_woofc_remove',
			'callback' => array($this, 'remove_item'),
			'nopriv' => true
		);

		$ajax_events[] = array(
			'function' => 'xt_woofc_restore',
			'callback' => array($this, 'restore_item'),
			'nopriv' => true
		);

		if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

            $ajax_events[] = array(
                'function' => 'xt_woofc_clear',
                'callback' => array($this, 'clear'),
                'nopriv' => true
            );

            $ajax_events[] = array(
                'function' => 'xt_woofc_clear_restore',
                'callback' => array($this, 'clear_restore'),
                'nopriv' => true
            );

			$ajax_events[] = array(
				'function' => 'xt_woofc_remove_coupon',
				'callback' => array($this, 'remove_coupon'),
				'nopriv' => true
			);

			$ajax_events[] = array(
				'function' => 'xt_woofc_apply_coupon',
				'callback' => array($this, 'apply_coupon'),
				'nopriv' => true
			);

			$ajax_events[] = array(
				'function' => 'xt_woofc_update_shipping_method',
				'callback' => array($this, 'update_shipping_method'),
				'nopriv' => true
			);

		}

		return $ajax_events;
	}

	public function set_notice( $notice, $type = 'success' ){
		$this->notice = '<span class="xt_woofc-notice xt_woofc-notice-'.esc_attr($type).'" data-type="'.esc_attr($type).'">'.$notice.'</span>';
	}

	public function get_notice(){

		if(empty($this->notice)) {
			return null;
		}

		$notice = $this->notice;

		$notice = apply_filters( 'xt_woofc_notice_html', $notice );

		$this->notice = '';

		return $notice;
	}

	public function cart_fragments( $fragments ) {

        /* @var $frontend XT_Woo_Floating_Cart_Public */
        $frontend = $this->core->frontend();

        $frontend->define_cart_constant();

		WC()->cart->calculate_totals();

		$type               = ! empty( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : null;
		$single_add_to_cart = ! empty( $_GET['wc-ajax'] ) && $_GET['wc-ajax'] === 'xt_atc_single';

        $add_to_cart_module = $this->core->modules()->get('add-to-cart');
        if($single_add_to_cart && $add_to_cart_module->customizer()->get_option_bool('single_refresh_fragments', true)) {
            return $fragments;
        }

        $show_notices = !in_array($type, array('totals', 'refresh'));

		if($show_notices){

			$notice = $this->get_notice();

			if(!empty($notice)) {
				$fragments['.xt_woofc-notice'] = $notice;
			}
		}

		$total = xt_woofc_checkout_total();
		$count = WC()->cart->get_cart_contents_count();
        $previous_count = WC()->session->get( 'xt_woofc_previous_count', 0);
        $update_count_class = $previous_count !== $count ? ' xt_woofc-update-count' : '';
        WC()->session->set( 'xt_woofc_previous_count', $count);

		$fragments['.xt_woofc-checkout span.amount'] = '<span class="amount">' . $total . '</span>';
		$fragments['.xt_woofc-count'] = '<ul class="xt_woofc-count'.$update_count_class.'"><li>' . $previous_count . '</li><li>' . $count . '</li></ul>';

		if(in_array($type, array('totals', 'update', 'remove', 'restore'))) {

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				$product = xt_woofc_item_product( $cart_item, $cart_item_key );

				if ( $product && $product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {

                    $bundled_product = function_exists( 'wc_pb_is_bundled_cart_item' ) && wc_pb_is_bundled_cart_item( $cart_item );
                    $composite_product = !empty( $cart_item['composite_parent'] );

                    $vars = array(
                        'product'       => $product,
                        'cart_item'     => $cart_item,
						'cart_item_key' => $cart_item_key,
                        'is_bundle_item' => $bundled_product,
                        'is_composite_item'  => $composite_product
                    );

					$fragments[ 'li[data-key="' . $cart_item_key . '"] .xt_woofc-price' ]    = $this->core->get_template( 'parts/cart/list/product/price', $vars, true );
					$fragments[ 'li[data-key="' . $cart_item_key . '"] .xt_woofc-quantity' ] = $this->core->get_template( 'parts/cart/list/product/quantity', $vars, true );
				}
			}

		}else{

			$list = $this->core->get_template( 'parts/cart/list', array(), true );
			$fragments['.xt_woofc-list-wrap'] = $list;
		}

		if ( $this->core->access_manager()->can_use_premium_code__premium_only() ) {

            if( $frontend->coupon_form_enabled() ) {

                $coupon_error = WC()->session->get('xt_woofc_coupon_error');
                $shake_class = !empty($coupon_error) ? ' xt_woofc-shake' : '';
                $fragments['.xt_woofc-coupon-error'] = '<p class="xt_woofc-coupon-error'.esc_attr($shake_class).'">'.(!empty($coupon_error) ? $coupon_error : '').'</p>';
                WC()->session->set('xt_woofc_coupon_error', null);
            }

            if( $frontend->coupon_list_enabled() ) {

                ob_start();
                $frontend->render_coupon_list();
                $fragments['.xt_woofc-coupons'] = ob_get_clean();
            }

            $fragments['.xt_woofc-body .woocommerce-checkout-review-order'] = xtfw_ob_get_clean(function() use($frontend){
                $frontend->render_totals();
            });

            $fragments['.xt_woofc-shipping-bar'] = xtfw_ob_get_clean(function() use($frontend){
                $frontend->render_shipping_bar();
            });

            $fragments['.xt_woofc-sp'] = xtfw_ob_get_clean(function() use($frontend){
                $frontend->render_suggested_products();
            });

        }

        $fragments['.xt_woofc-wc-notices'] = xtfw_ob_get_clean(function() {
            $this->core->frontend()->render_wc_notices();
        });

		return $fragments;
	}

	/**
	 * Update item qty
	 */
	public function update_qty() {

        $this->core->frontend()->define_cart_constant();

		$cart_item_key = ! empty( $_POST['cart_item_key'] ) ? sanitize_text_field( $_POST['cart_item_key'] ) : null;

		if (!empty( $cart_item_key ) ) {

            $cart_item = xt_woofc_get_cart_item($cart_item_key);
            $_product = xt_woofc_item_product( $cart_item, $cart_item_key );

            $cart_item_qty = intval( $_POST['cart_item_qty'] );

            // Update cart validation
            $passed_validation = apply_filters( 'woocommerce_update_cart_validation', true, $cart_item_key, $cart_item, $cart_item_qty );

            // is_sold_individually
            if ( $_product->is_sold_individually() && $cart_item_qty > 1 ) {
                $passed_validation = false;
            }

            if ( $passed_validation ) {
                WC()->cart->set_quantity( $cart_item_key, $cart_item_qty, false );
            }

		}

		WC_Ajax::get_refreshed_fragments();
	}

	/**
	 * Remove item
	 */
	public function remove_item() {

        $this->core->frontend()->define_cart_constant();

		$cart_item_key = ! empty( $_POST['cart_item_key'] ) ? sanitize_text_field( $_POST['cart_item_key'] ) : null;

		if (!empty( $cart_item_key ) ) {

			WC()->cart->remove_cart_item( $cart_item_key );

			$this->set_notice( sprintf(
				esc_html__( 'Item Removed. %s', 'woo-floating-cart' ),
				'<a class="xt_woofc-undo" href="#">'.esc_html__( 'Undo', 'woo-floating-cart' ).'</a>'
			));
		}

		WC_Ajax::get_refreshed_fragments();
	}


	/**
	 * Restore last removed item
	 */
	public function restore_item() {

        $this->core->frontend()->define_cart_constant();

		$cart_item_key = ! empty( $_POST['cart_item_key'] ) ? sanitize_text_field( $_POST['cart_item_key'] ) : null;

		if ( !empty( $cart_item_key ) ) {

			WC()->cart->restore_cart_item( $cart_item_key );
			$this->set_notice( esc_html__( 'Item restored successfully!', 'woo-floating-cart' ) );
		}

		WC_Ajax::get_refreshed_fragments();
	}

    /**
     * AJAX clear cart
     */
    public function clear() {

        $this->core->frontend()->define_cart_constant();

        WC()->session->set('xt_woofc_removed_cart_contents', WC()->cart->get_cart_contents());
        WC()->cart->empty_cart();

        $this->set_notice( sprintf(
            esc_html__( 'Cart Cleared! %s', 'woo-floating-cart' ),
            '<a class="xt_woofc-undo-clear" href="#">'.esc_html__( 'Undo', 'woo-floating-cart' ) .'</a>'
        ));

        WC_Ajax::get_refreshed_fragments();

    }

    /**
     * Restore last cleared items
     */
    public function clear_restore() {

        $this->core->frontend()->define_cart_constant();

        $removed_cart_contents = WC()->session->get('xt_woofc_removed_cart_contents');

        $removed_cart_contents = array_reverse($removed_cart_contents);

        WC()->cart->set_removed_cart_contents($removed_cart_contents);

        foreach ( $removed_cart_contents as $cart_item_key => $values ) {

            WC()->cart->restore_cart_item( $cart_item_key );
        }

        $this->set_notice( esc_html__( 'Cart restored successfully!', 'woo-floating-cart' ) );

        WC_Ajax::get_refreshed_fragments();
    }

	/**
	 * AJAX apply coupon on checkout page.
	 */
	public function apply_coupon() {

        $this->core->frontend()->define_cart_constant();

		if ( ! empty( $_POST['coupon_code'] ) ) {

			$coupon_code =  wc_format_coupon_code( wp_unslash( $_POST['coupon_code'] ));

			if(!WC()->cart->has_discount($coupon_code)) {

				if ( WC()->cart->apply_coupon( $coupon_code ) ) {
					$this->set_notice( esc_html__( 'Coupon applied successfully!', 'woo-floating-cart' ) );
				} else {

                    $coupon = new WC_Coupon($coupon_code);
                    $discounts = new WC_Discounts(WC()->cart);
                    $valid = $discounts->is_coupon_valid($coupon);
                    if (is_wp_error($valid)) {
                        WC()->session->set('xt_woofc_coupon_error', $valid->get_error_message());
                    }
                    $this->set_notice(esc_html__('Coupon is invalid!', 'woo-floating-cart'), 'error');
				}
			}else{
				$this->set_notice( esc_html__( 'Coupon already applied!', 'woo-floating-cart'), 'error' );
			}

		} else {

			 $this->set_notice( esc_html__( 'Please enter a coupon!', 'woo-floating-cart' ), 'error' );
		}

		wc_clear_notices();

		WC_Ajax::get_refreshed_fragments();
	}


	/**
	 * AJAX remove coupon on cart and checkout page.
	 */
	public function remove_coupon() {

        $this->core->frontend()->define_cart_constant();

		$coupon = isset( $_POST['coupon'] ) ? wc_format_coupon_code( wp_unslash( $_POST['coupon'] ) ) : false;
		if ( empty( $coupon ) ) {
			$this->set_notice( esc_html__( 'Failed removing coupon!', 'woo-floating-cart' ), 'error' );
		} else {
			WC()->cart->remove_coupon( $coupon );
			$this->set_notice( esc_html__( 'Coupon has been removed!', 'woo-floating-cart' ) );
		}

		WC_Ajax::get_refreshed_fragments();
	}

	/**
	 * AJAX update shipping method on cart page.
	 * Override native function because the nonce check is failing if caching plugin enabled
	 */
	public function update_shipping_method() {

        $this->core->frontend()->define_cart_constant();

		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
		$posted_shipping_methods = isset( $_POST['shipping_method'] ) ? wc_clean( wp_unslash( $_POST['shipping_method'] ) ) : array();

		if ( is_array( $posted_shipping_methods ) ) {
			foreach ( $posted_shipping_methods as $i => $value ) {
				$chosen_shipping_methods[ $i ] = $value;
			}
		}

		WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );

		$this->set_notice( esc_html__( 'Shipping info updated', 'woo-floating-cart' ) );

		WC_Ajax::get_refreshed_fragments();

	}

	public function remove_cart_item( $cart_item_key, $cart ) {

		$position = array_search( $cart_item_key, array_keys( $cart->cart_contents ) );
		WC()->session->set( 'xt_woofc_removed_position', $position );
	}

	public function cart_item_restored( $cart_item_key, $cart ) {

		$bundled_product = function_exists( 'wc_pb_is_bundled_cart_item' );

		if ( ! $bundled_product ) {

			$position      = WC()->session->get( 'xt_woofc_removed_position' );
			$restored_item = $cart->cart_contents[ $cart_item_key ];

			array_splice( $cart->cart_contents, $position, 0, array( $restored_item ) );

			$cart->cart_contents = $this->replace_array_key( $cart->cart_contents, "0", $cart_item_key );
		}

		WC()->session->__unset( 'xt_woofc_removed_position' );
	}

	public function added_to_cart(){

		$this->set_notice( esc_html__( 'Item added to cart.', 'woo-floating-cart' ) );
	}

	public function replace_array_key( $arr, $oldkey, $newkey ) {

		if ( array_key_exists( $oldkey, $arr ) ) {
			$keys                                   = array_keys( $arr );
			$keys[ array_search( $oldkey, $keys ) ] = $newkey;

			return array_combine( $keys, $arr );
		}

		return $arr;
	}

}
