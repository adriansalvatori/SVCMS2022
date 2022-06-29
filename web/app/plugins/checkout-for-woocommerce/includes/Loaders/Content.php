<?php
namespace Objectiv\Plugins\Checkout\Loaders;

/**
 * Class Content
 *
 * Loads pages into normal WP content
 *
 * @link checkoutwc.com
 * @since 3.6.0
 * @package Objectiv\Plugins\Checkout\Core
 * @author Clifton Griffin <clif@checkoutwc.com>
 */
class Content extends LoaderAbstract {
	/**
	 *
	 * @since 3.6.0
	 * @access public
	 *
	 */
	public static function checkout() {
		/**
		 * Filters whether to load checkout template
		 *
		 * @since 2.0.0
		 *
		 * @param bool $load True load, false don't load
		 */
		if ( ! apply_filters( 'cfw_load_checkout_template', cfw_is_checkout() ) ) {
			return;
		}

		$global_template_parameters = self::init_checkout();

		add_action( 'wp_head', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_header_scripts' ), 20, 4 );
		add_action( 'wp_head', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_styles' ), 40, 5 );
		add_action( 'wp_footer', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_footer_scripts' ) );

		add_shortcode(
			'woocommerce_checkout',
			function() use ( $global_template_parameters ) {
				// Setup checkout
				ob_start();

				// Output the contents of the <body></body> section
				self::display( $global_template_parameters, 'content.php' );

				return ob_get_clean();
			}
		);
	}

	public static function order_pay() {
		/**
		 * Filters whether to load order pay template
		 *
		 * @since 2.0.0
		 *
		 * @param bool $load True load, false don't load
		 */
		if ( ! apply_filters( 'cfw_load_order_pay_template', is_checkout_pay_page() ) ) {
			return;
		}

		$global_template_parameters = self::init_order_pay();

		add_action( 'wp_head', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_header_scripts' ), 20, 4 );
		add_action( 'wp_head', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_styles' ), 40, 5 );
		add_action( 'wp_footer', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_footer_scripts' ) );

		add_shortcode(
			'woocommerce_checkout',
			function() use ( $global_template_parameters ) {
				// Setup checkout
				ob_start();

				// Output the contents of the <body></body> section
				self::display( $global_template_parameters, 'order-pay.php' );

				return ob_get_clean();
			}
		);
	}

	public static function order_received() {
		/**
		 * Filters whether to load order received template
		 *
		 * @since 2.0.0
		 *
		 * @param bool $load True load, false don't load
		 */
		if ( ! apply_filters( 'cfw_load_order_received_template', is_order_received_page() ) ) {
			return;
		}

		$global_template_parameters = self::init_thank_you();

		add_action( 'wp_head', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_header_scripts' ), 20, 4 );
		add_action( 'wp_head', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_styles' ), 40, 5 );
		add_action( 'wp_footer', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_footer_scripts' ) );


		add_shortcode(
			'woocommerce_checkout',
			function() use ( $global_template_parameters ) {
				// Setup checkout
				ob_start();

				// Output the contents of the <body></body> section
				self::display( $global_template_parameters, 'thank-you.php' );

				return ob_get_clean();
			}
		);
	}

	/**
	 * @deprecated 5.0.0
	 */
	public static function wp_head() {
		add_action( 'wp_head', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_header_scripts' ), 20, 4 );
		add_action( 'wp_head', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_styles' ), 40, 5 );
	}

	/**
	 * @deprecated 5.0.0
	 */
	public static function wp_footer() {
		add_action( 'wp_footer', array( 'Objectiv\Plugins\Checkout\Loaders\Content', 'output_custom_footer_scripts' ) );
	}
}
