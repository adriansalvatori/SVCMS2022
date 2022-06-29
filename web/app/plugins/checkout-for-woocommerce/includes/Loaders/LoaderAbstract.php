<?php

namespace Objectiv\Plugins\Checkout\Loaders;

use Objectiv\Plugins\Checkout\Managers\StyleManager;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * Helps load pages
 *
 * @link checkoutwc.com
 * @since 3.6.0
 * @package Objectiv\Plugins\Checkout\Core
 * @author Clifton Griffin <clif@checkoutwc.com>
 */

abstract class LoaderAbstract {
	public static function checkout() {}
	public static function order_pay() {}
	public static function order_received() {}

	/**
	 * @return array The global parameters
	 */
	public static function init_checkout() {
		/**
		 * Set Checkout Constant
		 */
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		/**
		 * Add body classes
		 */
		add_filter(
			'body_class',
			function( $css_classes ) {
				if ( ! cfw_show_shipping_tab() ) {
					$css_classes[] = 'cfw-hide-shipping';
				}

				return $css_classes;
			}
		);

		// This seems to be a 3.5 requirement
		// Ensure gateways and shipping methods are loaded early.
		WC()->payment_gateways();
		WC()->shipping();

		// When on the checkout with an empty cart, redirect to cart page
		// Check cart has contents.
		if ( WC()->cart->is_empty() && ! is_customize_preview() && apply_filters( 'woocommerce_checkout_redirect_empty_cart', true ) ) {
			wc_add_notice( cfw__( 'Checkout is not available whilst your cart is empty.', 'woocommerce' ), 'notice' );
			wp_redirect( wc_get_cart_url() );
			exit;
		}

		// Check cart contents for errors
		do_action( 'woocommerce_check_cart_items' );

		// Calc totals
		WC()->cart->calculate_totals();

		/**
		 * Filters global template parameters available to templates
		 *
		 * @since 3.0.0
		 *
		 * @param array $global_params The global template parameters
		 */
		return apply_filters( 'cfw_template_global_params', array() );
	}

	/**
	 * @return array The global template parameters
	 */
	public static function init_order_pay() {
		global $wp;

		/**
		 * Filters global template parameters available to templates
		 *
		 * @since 3.0.0
		 *
		 * @param array $global_template_parameters The global template parameters
		 */
		$global_template_parameters = apply_filters(
			'cfw_template_global_params',
			array(
				'call_receipt_hook'  => false,
				'order_button_text'  => apply_filters( 'woocommerce_pay_order_button_text', cfw__( 'Pay for order', 'woocommerce' ) ),
				'available_gateways' => array(),
			)
		);

		do_action( 'before_woocommerce_pay' );

		$order_id = absint( $wp->query_vars['order-pay'] );

		// Pay for existing order.
		if ( isset( $_GET['pay_for_order'], $_GET['key'] ) && $order_id ) { // WPCS: input var ok, CSRF ok.
			try {
				$order_key          = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : ''; // WPCS: input var ok, CSRF ok.
				$order              = wc_get_order( $order_id );
				$hold_stock_minutes = (int) get_option( 'woocommerce_hold_stock_minutes', 0 );

				// Order or payment link is invalid.
				if ( ! $order || $order->get_id() !== $order_id || ! hash_equals( $order->get_order_key(), $order_key ) ) {
					throw new \Exception( cfw__( 'Sorry, this order is invalid and cannot be paid for.', 'woocommerce' ) );
				}

				if ( ! current_user_can( 'pay_for_order', $order->get_id() ) && ! is_user_logged_in() ) {
					wc_add_notice( cfw__( 'Please log in to your account below to continue to the payment form.', 'woocommerce' ), 'error' );
				}

				// Add notice if logged in customer is trying to pay for guest order.
				if ( ! $order->get_user_id() && is_user_logged_in() ) {
					// If order has does not have same billing email then current logged in user then show warning.
					if ( $order->get_billing_email() !== wp_get_current_user()->user_email ) {
						wc_add_notice( cfw__( 'You are paying for a guest order. Please continue with payment only if you recognize this order.', 'woocommerce' ), 'notice' );
					}
				}

				// Logged in customer trying to pay for someone else's order.
				if ( ! current_user_can( 'pay_for_order', $order_id ) && is_user_logged_in() ) {
					throw new \Exception( cfw__( 'This order cannot be paid for. Please contact us if you need assistance.', 'woocommerce' ) );
				}

				// Does not need payment.
				if ( ! $order->needs_payment() ) {
					/* translators: %s: order status */
					throw new \Exception( sprintf( cfw__( 'This order&rsquo;s status is &ldquo;%s&rdquo;&mdash;it cannot be paid for. Please contact us if you need assistance.', 'woocommerce' ), wc_get_order_status_name( $order->get_status() ) ) );
				}

				// Ensure order items are still stocked if paying for a failed order. Pending orders do not need this check because stock is held.
				if ( ! $order->has_status( wc_get_is_pending_statuses() ) ) {
					$quantities = array();

					foreach ( $order->get_items() as $item_key => $item ) {
						if ( $item && is_callable( array( $item, 'get_product' ) ) ) {
							$product = $item->get_product();

							if ( ! $product ) {
								continue;
							}

							$quantities[ $product->get_stock_managed_by_id() ] = isset( $quantities[ $product->get_stock_managed_by_id() ] ) ? $quantities[ $product->get_stock_managed_by_id() ] + $item->get_quantity() : $item->get_quantity();
						}
					}

					foreach ( $order->get_items() as $item_key => $item ) {
						if ( $item && is_callable( array( $item, 'get_product' ) ) ) {
							$product = $item->get_product();

							if ( ! $product ) {
								continue;
							}

							if ( ! apply_filters( 'woocommerce_pay_order_product_in_stock', $product->is_in_stock(), $product, $order ) ) {
								/* translators: %s: product name */
								throw new \Exception( sprintf( cfw__( 'Sorry, "%s" is no longer in stock so this order cannot be paid for. We apologize for any inconvenience caused.', 'woocommerce' ), $product->get_name() ) );
							}

							// We only need to check products managing stock, with a limited stock qty.
							if ( ! $product->managing_stock() || $product->backorders_allowed() ) {
								continue;
							}

							// Check stock based on all items in the cart and consider any held stock within pending orders.
							$held_stock     = ( $hold_stock_minutes > 0 ) ? wc_get_held_stock_quantity( $product, $order->get_id() ) : 0;
							$required_stock = $quantities[ $product->get_stock_managed_by_id() ];

							if ( $product->get_stock_quantity() < ( $held_stock + $required_stock ) ) {
								/* translators: 1: product name 2: quantity in stock */
								throw new \Exception( sprintf( cfw__( 'Sorry, we do not have enough "%1$s" in stock to fulfill your order (%2$s available). We apologize for any inconvenience caused.', 'woocommerce' ), $product->get_name(), wc_format_stock_quantity_for_display( $product->get_stock_quantity() - $held_stock, $product ) ) );
							}
						}
					}
				}

				WC()->customer->set_props(
					array(
						'billing_country'  => $order->get_billing_country() ? $order->get_billing_country() : null,
						'billing_state'    => $order->get_billing_state() ? $order->get_billing_state() : null,
						'billing_postcode' => $order->get_billing_postcode() ? $order->get_billing_postcode() : null,
					)
				);
				WC()->customer->save();

				$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

				if ( count( $available_gateways ) ) {
					current( $available_gateways )->set_current();
				}

				$global_template_parameters['order']              = $order;
				$global_template_parameters['available_gateways'] = $available_gateways;
			} catch ( \Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' );
			}
		} elseif ( $order_id ) {

			// Pay for order after checkout step.
			$order_key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : ''; // WPCS: input var ok, CSRF ok.
			$order     = wc_get_order( $order_id );

			if ( $order && $order->get_id() === $order_id && hash_equals( $order->get_order_key(), $order_key ) ) {

				if ( $order->needs_payment() ) {

					$global_template_parameters['order']             = $order;
					$global_template_parameters['call_receipt_hook'] = true;

				} else {
					/* translators: %s: order status */
					wc_add_notice( sprintf( cfw__( 'This order&rsquo;s status is &ldquo;%s&rdquo;&mdash;it cannot be paid for. Please contact us if you need assistance.', 'woocommerce' ), wc_get_order_status_name( $order->get_status() ) ), 'error' );
				}
			} else {
				wc_add_notice( cfw__( 'Sorry, this order is invalid and cannot be paid for.', 'woocommerce' ), 'error' );
			}
		} else {
			wc_add_notice( cfw__( 'Invalid order.', 'woocommerce' ), 'error' );
		}

		return $global_template_parameters;
	}

	/**
	 * @return array The global template parameters
	 * @throws \WC_Data_Exception
	 */
	public static function init_thank_you() {
		$order            = cfw_get_order_received_order();
		$settings_manager = SettingsManager::instance();

		/**
		 * Filters global template parameters available to templates
		 *
		 * @since 3.0.0
		 *
		 * @param array $global_template_parameters The global template parameters
		 */
		$global_template_parameters = apply_filters( 'cfw_template_global_params', array() );

		// Empty awaiting payment session.
		unset( WC()->session->order_awaiting_payment );

		// In case order is created from admin, but paid by the actual customer, store the ip address of the payer.
		if ( $order && $order->is_created_via( 'admin' ) ) {
			$order->set_customer_ip_address( \WC_Geolocation::get_ip_address() );
			$order->save();
		}

		if ( ! $order ) {
			return $global_template_parameters;
		}

		$valid_order_statuses = array_flip( array_intersect_key( array_flip( (array) $settings_manager->get_setting( 'thank_you_order_statuses' ) ), wc_get_order_statuses() ) );

		$current_status = 'wc-' . $order->get_status();

		if ( ! in_array( $current_status, $valid_order_statuses, true ) ) {
			$valid_order_statuses[] = $current_status;
		}

		$global_template_parameters['order']          = $order;
		$global_template_parameters['order_statuses'] = str_replace( 'wc-', '', $valid_order_statuses );
		$global_template_parameters['show_downloads'] = $order->has_downloadable_item() && $order->is_download_permitted();
		$global_template_parameters['downloads']      = $order->get_downloadable_items();

		do_action( 'cfw_checkout_loaded_pre_head' );

		// Empty current cart.
		if ( ! isset( $_GET['view'] ) ) {
			wc_clear_cart_after_payment();
		}

		return $global_template_parameters;
	}

	/**
	 * @since 1.0.0
	 * @access public
	 * @param array $global_template_parameters
	 * @param string $template_file
	 */
	public static function display( array $global_template_parameters, string $template_file ) {
		/**
		 * Fires before template pieces are loaded
		 *
		 * @since 3.0.0
		 *
		 * @param string $template_file The template file
		 */
		do_action( 'cfw_template_before_load', $template_file );

		// Load content template
		cfw_get_active_template()->view( $template_file, $global_template_parameters );

		/**
		 * Fires after template pieces are loaded
		 *
		 * @since 3.0.0
		 *
		 * @param string $template_file The template file
		 */
		do_action( 'cfw_template_after_load', $template_file );
	}

	/**
	 *
	 */
	public static function output_meta_tags() {
		?>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<?php
	}

	/**
	 * Output content of WP Admin > CheckoutWC > Advanced > Header Scripts
	 */
	public static function output_custom_header_scripts() {
		echo SettingsManager::instance()->get_setting( 'header_scripts' );

		if ( cfw_is_checkout() ) {
			echo SettingsManager::instance()->get_setting( 'header_scripts_checkout' );
		}

		if ( cfw_is_order_received_page() ) {
			echo SettingsManager::instance()->get_setting( 'header_scripts_thank_you' );
		}

		if ( cfw_is_checkout_pay_page() ) {
			echo SettingsManager::instance()->get_setting( 'header_scripts_order_pay' );
		}
	}

	/**
	 * Output content of WP Admin > CheckoutWC > Advanced > Footer Scripts
	 */
	public static function output_custom_footer_scripts() {
		echo SettingsManager::instance()->get_setting( 'footer_scripts' );

		if ( cfw_is_checkout() ) {
			echo SettingsManager::instance()->get_setting( 'footer_scripts_checkout' );
		}

		if ( cfw_is_order_received_page() ) {
			echo SettingsManager::instance()->get_setting( 'footer_scripts_thank_you' );
		}

		if ( cfw_is_checkout_pay_page() ) {
			echo SettingsManager::instance()->get_setting( 'footer_scripts_order_pay' );
		}
	}

	public static function output_page_title() {
		// We use this instead of _wp_render_title_tag because it requires the theme support title-tag capability.
		echo '<title>' . wp_get_document_title() . '</title>' . "\n";
	}

	/**
	 * Output custom styles
	 */
	public static function output_custom_styles() {
		do_action( 'cfw_custom_styles' );
	}
}
