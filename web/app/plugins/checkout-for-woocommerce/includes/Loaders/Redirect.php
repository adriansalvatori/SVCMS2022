<?php
namespace Objectiv\Plugins\Checkout\Loaders;

/**
 * Class Redirect
 *
 * Loads pages in portal by taking control of all output
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Core
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class Redirect extends LoaderAbstract {

	/**
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public static function checkout() {
		/**
		 * Filters whether to load checkout template
		 *
		 * @since 3.0.0
		 *
		 * @param bool $load Whether to load checkout template
		 */
		if ( apply_filters( 'cfw_load_checkout_template', cfw_is_checkout() ) ) {
			// Setup checkout
			$global_template_parameters = self::init_checkout();

			self::suppress_errors();
			self::disable_caching();
			self::suppress_assets();
			self::hook_cfw_wp_head();
			self::hook_cfw_wp_footer();

			$css_classes = apply_filters( 'body_class', array( 'checkout-wc', 'woocommerce', 'woocommerce-checkout', cfw_get_active_template()->get_slug() ), array() );

			if ( ! cfw_show_shipping_tab() ) {
				$css_classes[] = 'cfw-hide-shipping';
			}

			/**
			 * Filter CheckoutWC specific body classes
			 *
			 * @since 3.0.0
			 *
			 * @param array $css_classes The body css classes
			 */
			$cfw_body_classes = apply_filters( 'cfw_body_classes', $css_classes );

			// Output the contents of the <head></head> section
			self::head( (array) $cfw_body_classes );

			// Output the contents of the <body></body> section
			self::display( $global_template_parameters, 'content.php' );

			// Output a closing </body> and closing </html> tag
			self::footer();

			// Exit out before WordPress can do anything else
			exit;
		}
	}

	/**
	 * @since 1.0.0
	 * @access public
	 */
	public static function order_pay() {
		/**
		 * Filters whether to load order pay template
		 *
		 * @since 3.0.0
		 *
		 * @param bool $load Whether to load order pay template
		 */
		if ( apply_filters( 'cfw_load_order_pay_template', is_checkout_pay_page() ) ) {
			$global_template_parameters = self::init_order_pay();

			self::suppress_errors();
			self::disable_caching();
			self::suppress_assets();
			self::hook_cfw_wp_head();
			self::hook_cfw_wp_footer();

			$css_classes = array( 'checkout-wc', 'woocommerce', 'woocommerce-checkout', cfw_get_active_template()->get_slug() );

			/**
			 * Filter CheckoutWC specific body classes
			 *
			 * @since 3.0.0
			 *
			 * @param array $css_classes The body css classes
			 */
			$cfw_body_classes = apply_filters( 'cfw_body_classes', $css_classes );

			// Output the contents of the <head></head> section
			self::head( $cfw_body_classes );

			// Output the contents of the <body></body> section
			self::display( $global_template_parameters, 'order-pay.php' );

			// Output a closing </body> and closing </html> tag
			self::footer();

			do_action( 'after_woocommerce_pay' );

			// Exit out before WordPress can do anything else
			exit;
		}
	}

	/**
	 * @since 2.39.0
	 * @access public
	 */
	public static function order_received() {
		/**
		 * Filters whether to load order received template
		 *
		 * @since 3.0.0
		 *
		 * @param bool $load Whether to load order received template
		 */
		if ( apply_filters( 'cfw_load_order_received_template', is_order_received_page() ) ) {
			$global_template_parameters = self::init_thank_you();

			if ( empty( $global_template_parameters['order'] ) ) {
				return; // prevent PHP warnings
			}

			self::suppress_errors();
			self::disable_caching();
			self::suppress_assets();
			self::hook_cfw_wp_head();
			self::hook_cfw_wp_footer();

			$css_classes = array( 'checkout-wc', 'woocommerce', cfw_get_active_template()->get_slug() );

			/**
			 * Filter CheckoutWC specific body classes
			 *
			 * @since 3.0.0
			 *
			 * @param array $css_classes The body css classes
			 */
			$cfw_body_classes = apply_filters( 'cfw_body_classes', $css_classes );

			// Output the contents of the <head></head> section
			self::head( $cfw_body_classes );

			// Output the contents of the <body></body> section
			self::display( $global_template_parameters, 'thank-you.php' );

			// Output a closing </body> and closing </html> tag
			self::footer();

			// Exit out before WordPress can do anything else
			exit;
		}
	}

	/**
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $classes
	 */
	public static function head( array $classes ) {
		/**
		 * Fires before document start
		 *
		 * @since 3.0.0
		 */
		do_action( 'cfw_checkout_loaded_pre_head' );

		if ( cfw_is_checkout() ) {
			$classes[] = 'checkout';
		}

		// Stop initial animations
		// This gets removed on DOMContentLoaded
		$classes[] = 'cfw-preload';
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<?php self::cfw_wp_head(); ?>
		</head>
		<body <?php body_class( $classes ); ?>>
		<?php
		/**
		 * Fires before header is output
		 *
		 * @since 3.0.0
		 */
		do_action( 'cfw_before_header' );

		if ( has_action( 'cfw_custom_header' ) ) {
			/**
			 * Fires when custom header is hooked
			 *
			 * @since 3.0.0
			 */
			do_action( 'cfw_custom_header' );
		} else {
			cfw_get_active_template()->view( 'header.php' );
		}

		/**
		 * Fires after header is output
		 *
		 * @since 3.0.0
		 */
		do_action( 'cfw_after_header' );
	}

	/**
	 * cfw_wp_head
	 */
	public static function cfw_wp_head() {
		// Make sure gateways load before we call wp_head()
		WC()->payment_gateways->get_available_payment_gateways();
		\WC_Payment_Gateways::instance();

		wp_head();
		/**
		 * Fires after wp_head()
		 *
		 * @since 3.0.0
		 */
		do_action( 'cfw_wp_head' );
	}

	/**
	 * Remove specifically excluded styles
	 */
	public static function remove_styles() {
		/**
		 * Filters blocked stylesheet handles
		 *
		 * @since 3.0.0
		 *
		 * @param array $blocked_style_handles The blocked stylesheet handles
		 */
		$blocked_style_handles = apply_filters( 'cfw_blocked_style_handles', array() );

		foreach ( $blocked_style_handles as $blocked_style_handle ) {
			wp_dequeue_style( $blocked_style_handle );
			wp_deregister_style( $blocked_style_handle );
		}
	}

	/**
	 * Remove specifically excluded scripts
	 */
	public static function remove_scripts() {
		/**
		 * Filters blocked script handles
		 *
		 * @since 3.0.0
		 *
		 * @param array $blocked_script_handles The blocked script handles
		 */
		$blocked_script_handles = apply_filters( 'cfw_blocked_script_handles', array() );

		foreach ( $blocked_script_handles as $blocked_script_handle ) {
			wp_dequeue_script( $blocked_script_handle );
			wp_deregister_script( $blocked_script_handle );
		}
	}

	/**
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public static function footer() {
		if ( has_action( 'cfw_custom_footer' ) ) {
			/**
			 * Fires when custom footer is hooked
			 *
			 * @since 3.0.0
			 */
			do_action( 'cfw_custom_footer' );
		} else {
			cfw_get_active_template()->view( 'footer.php' );
		}

		// Prevent themes and plugins from injecting HTML on wp_footer
		echo '<div id="wp_footer">';
		wp_footer();
		echo '</div>';

		/**
		 * Fires after wp_footer() is called
		 *
		 * @since 3.0.0
		 */
		do_action( 'cfw_wp_footer' );
		?>
		</body>
		</html>
		<?php
	}

	public static function suppress_errors() {
		/**
		 * PHP Warning / Notice Suppression
		 */
		if ( ! defined( 'CFW_DEV_MODE' ) || ! CFW_DEV_MODE ) {
			ini_set( 'display_errors', 'Off' );
		}
	}

	/**
	 * Discourage Caching if Anyone Dares Try
	 */
	public static function disable_caching() {
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
	}

	/**
	 * Remove scripts and styles
	 *
	 * Do this at wp_head as well as wp_enqueue_scripts. This gives us two chances to win.
	 */
	public static function suppress_assets() {
		add_action( 'wp_head', array( '\Objectiv\Plugins\Checkout\Loaders\Redirect', 'remove_styles' ), 100000 );
		add_action( 'wp_head', array( '\Objectiv\Plugins\Checkout\Loaders\Redirect', 'remove_scripts' ), 100000 );
		add_action( 'wp_enqueue_scripts', array( '\Objectiv\Plugins\Checkout\Loaders\Redirect', 'remove_styles' ), 100000 );
		add_action( 'wp_enqueue_scripts', array( '\Objectiv\Plugins\Checkout\Loaders\Redirect', 'remove_scripts' ), 100000 );
		add_action( 'wp_footer', array( '\Objectiv\Plugins\Checkout\Loaders\Redirect', 'remove_styles' ), 19 ); // 20 is when footer scripts are output
		add_action( 'wp_footer', array( '\Objectiv\Plugins\Checkout\Loaders\Redirect', 'remove_scripts' ), 19 ); // 20 is when footer scripts are output
	}

	/**
	 * Setup cfw_wp_head actions
	 */
	public static function hook_cfw_wp_head() {
		// Setup default cfw_wp_head actions
		add_action( 'cfw_wp_head', array( '\Objectiv\Plugins\Checkout\Loaders\Redirect', 'output_meta_tags' ), 10, 4 );
		add_action( 'cfw_wp_head', array( '\Objectiv\Plugins\Checkout\Loaders\Redirect', 'output_custom_header_scripts' ), 20, 4 );
		add_action( 'cfw_wp_head', array( '\Objectiv\Plugins\Checkout\Loaders\Redirect', 'output_page_title' ), 30, 4 );
		add_action( 'cfw_wp_head', array( '\Objectiv\Plugins\Checkout\Loaders\Redirect', 'output_custom_styles' ), 40, 5 );
	}

	public static function hook_cfw_wp_footer() {
		add_action( 'cfw_wp_footer', array( 'Objectiv\Plugins\Checkout\Loaders\Redirect', 'output_custom_footer_scripts' ) );
	}

	public static function template_redirect() {
		global $wp;

		if ( isset( $_GET['order'] ) && isset( $_GET['key'] ) ) { // WPCS: input var ok, CSRF ok.
			wc_deprecated_argument( __CLASS__ . '->' . __FUNCTION__, '2.1', '"order" is no longer used to pass an order ID. Use the order-pay or order-received endpoint instead.' );

			// Get the order to work out what we are showing.
			$order_id = absint( $_GET['order'] ); // WPCS: input var ok.
			$order    = wc_get_order( $order_id );

			if ( $order && $order->has_status( 'pending' ) ) {
				$wp->query_vars['order-pay'] = absint( $_GET['order'] ); // WPCS: input var ok.
			} else {
				$wp->query_vars['order-received'] = absint( $_GET['order'] ); // WPCS: input var ok.
			}
		}

		if ( cfw_is_checkout() ) {
			Redirect::checkout();
		} elseif ( is_checkout_pay_page() ) {
			Redirect::order_pay();
		} elseif ( is_order_received_page() ) {
			Redirect::order_received();
		}
	}
}
