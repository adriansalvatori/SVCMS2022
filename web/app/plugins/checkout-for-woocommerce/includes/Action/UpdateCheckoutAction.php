<?php

namespace Objectiv\Plugins\Checkout\Action;

class UpdateCheckoutAction extends CFWAction {
	public function __construct() {
		parent::__construct( 'update_checkout' );
	}

	public function action() {
		check_ajax_referer( 'update-order-review', 'security' );

		\WC_Checkout::instance();
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		if ( WC()->cart->is_empty() && ! is_customize_preview() && apply_filters( 'woocommerce_checkout_update_order_review_expired', true ) ) {
			$target_selector = apply_filters( 'cfw_session_expired_target_element', 'form.woocommerce-checkout' );

			$this->out(
				array(
					'redirect'  => false,
					'fragments' => apply_filters(
						'woocommerce_update_order_review_fragments',
						array(
							$target_selector => '<div class="woocommerce-error">' . cfw__( 'Sorry, your session has expired.', 'woocommerce' ) . ' <a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '" class="wc-backward">' . cfw__( 'Return to shop', 'woocommerce' ) . '</a></div>',
						)
					),
				)
			);
		}

		/** This action is documented in woocommerce/includes/class-wc-ajax.php */
		do_action( 'woocommerce_checkout_update_order_review', isset( $_POST['post_data'] ) ? wp_unslash( $_POST['post_data'] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		/**
		 * Fires when updating CheckoutWC order review
		 *
		 * @since 2.0.0
		 *
		 * @param string $post_data The POST data
		 */
		do_action( 'cfw_checkout_update_order_review', isset( $_POST['post_data'] ) ? wp_unslash( $_POST['post_data'] ) : '' );

		parse_str( wp_unslash( $_POST['post_data'] ), $post_data );

		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

		if ( isset( $_POST['shipping_method'] ) && is_array( $_POST['shipping_method'] ) ) {
			foreach ( $_POST['shipping_method'] as $i => $value ) {
				$chosen_shipping_methods[ $i ] = wc_clean( $value );
			}
		}

		WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );

		if ( ! empty( $_POST['payment_method'] ) ) {
			WC()->session->set( 'chosen_payment_method', $_POST['payment_method'] );
		}

		WC()->customer->set_props(
			array(
				'billing_country'   => isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : null,
				'billing_state'     => isset( $_POST['state'] ) ? wc_clean( wp_unslash( $_POST['state'] ) ) : null,
				'billing_postcode'  => isset( $_POST['postcode'] ) ? trim( wc_clean( wp_unslash( $_POST['postcode'] ) ) ) : null,
				'billing_city'      => isset( $_POST['city'] ) ? wc_clean( wp_unslash( $_POST['city'] ) ) : null,
				'billing_address_1' => isset( $_POST['address'] ) ? wc_clean( wp_unslash( $_POST['address'] ) ) : null,
				'billing_address_2' => isset( $_POST['address_2'] ) ? wc_clean( wp_unslash( $_POST['address_2'] ) ) : null,
			)
		);

		if ( wc_ship_to_billing_address_only() || ! WC()->cart->needs_shipping() ) {
			WC()->customer->set_props(
				array(
					'shipping_country'   => isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : null,
					'shipping_state'     => isset( $_POST['state'] ) ? wc_clean( wp_unslash( $_POST['state'] ) ) : null,
					'shipping_postcode'  => isset( $_POST['postcode'] ) ? trim( wc_clean( wp_unslash( $_POST['postcode'] ) ) ) : null,
					'shipping_city'      => isset( $_POST['city'] ) ? wc_clean( wp_unslash( $_POST['city'] ) ) : null,
					'shipping_address_1' => isset( $_POST['address'] ) ? wc_clean( wp_unslash( $_POST['address'] ) ) : null,
					'shipping_address_2' => isset( $_POST['address_2'] ) ? wc_clean( wp_unslash( $_POST['address_2'] ) ) : null,
				)
			);
		} else {
			WC()->customer->set_props(
				array(
					'shipping_country'   => isset( $_POST['s_country'] ) ? wc_clean( wp_unslash( $_POST['s_country'] ) ) : null,
					'shipping_state'     => isset( $_POST['s_state'] ) ? wc_clean( wp_unslash( $_POST['s_state'] ) ) : null,
					'shipping_postcode'  => isset( $_POST['s_postcode'] ) ? trim( wc_clean( wp_unslash( $_POST['s_postcode'] ) ) ) : null,
					'shipping_city'      => isset( $_POST['s_city'] ) ? wc_clean( wp_unslash( $_POST['s_city'] ) ) : null,
					'shipping_address_1' => isset( $_POST['s_address'] ) ? wc_clean( wp_unslash( $_POST['s_address'] ) ) : null,
					'shipping_address_2' => isset( $_POST['s_address_2'] ) ? wc_clean( wp_unslash( $_POST['s_address_2'] ) ) : null,
				)
			);
		}

		$calculated_shipping = isset( $_POST['has_full_address'] ) && wc_string_to_bool( wc_clean( wp_unslash( $_POST['has_full_address'] ) ) );
		WC()->customer->set_calculated_shipping( $calculated_shipping );

		WC()->customer->save();

		do_action( 'cfw_update_checkout_after_customer_save', $post_data );

		/**
		 * Filters whether to redirect the checkout page during refresh
		 *
		 * @since 2.0.0
		 *
		 * @param bool|string Boolean false means don't redirect, string means redirect to URL
		 */
		$redirect = apply_filters( 'cfw_update_checkout_redirect', false );

		// Calculate shipping before totals. This will ensure any shipping methods that affect things like taxes are chosen prior to final totals being calculated. Ref: #22708.
		WC()->cart->calculate_shipping();
		WC()->cart->calculate_totals();

		unset( WC()->session->refresh_totals, WC()->session->reload_checkout );

		/**
		 * Fetch available gateways and make sure at least one is set
		 *
		 * This is to fix an issue where removing a free coupon doesn't show a selected gateway
		 * until the second refresh - not idea!
		 */
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

		reset( $available_gateways );

		$first_gateway       = key( $available_gateways );
		$have_chosen_gateway = false;

		foreach ( $available_gateways as $available_gateway ) {
			if ( $available_gateway->chosen ) {
				$have_chosen_gateway = true;
			}
		}

		if ( ! $have_chosen_gateway ) {
			if ( isset( $available_gateways[ WC()->session->get( 'chosen_payment_method' ) ] ) ) {
				$available_gateways[ WC()->session->get( 'chosen_payment_method' ) ]->chosen = true;
			} elseif ( ! empty( $available_gateways[ $first_gateway ] ) ) {
				$available_gateways[ $first_gateway ]->chosen = true;
				WC()->session->set( 'chosen_payment_method', $first_gateway );
			}
		}

		/**
		 * Filters payment methods during update_checkout refresh
		 *
		 * @since 2.0.0
		 *
		 * @param bool|string Boolean false means don't redirect, string means redirect to URL
		 */
		$updated_payment_methods = apply_filters( 'cfw_update_payment_methods', cfw_get_payment_methods( $available_gateways, false, true, false ) );

		/** This action is documented in woocommerce/includes/class-wc-checkout.php */
		do_action( 'woocommerce_check_cart_items' );

		// Chosen shipping methods
		$chosen_shipping_methods_labels = array();

		$packages = WC()->shipping->get_packages();

		foreach ( $packages as $i => $package ) {
			$chosen_method     = WC()->session->get( 'chosen_shipping_methods' )[ $i ] ?? false;
			$available_methods = $package['rates'];

			if ( $chosen_method && method_exists( $available_methods[ $chosen_method ], 'get_label' ) ) {
				$chosen_shipping_methods_labels[] = $available_methods[ $chosen_method ]->get_label();
			}
		}

		/**
		 * Filters chosen shipping methods label
		 *
		 * @since 2.0.0
		 *
		 * @param string $chosen_shipping_methods_labels The chosen shipping methods
		 */
		$chosen_shipping_methods_labels = apply_filters( 'cfw_payment_method_address_review_shipping_method', $chosen_shipping_methods_labels );

		$update_checkout_output = array(
			'needs_payment'             => WC()->cart->needs_payment(),
			'fragments'                 => apply_filters(
				'woocommerce_update_order_review_fragments', /** This filter is documented in woocommerce/includes/class-wc-ajax.php */
				array(
					'.cfw-review-pane-shipping-address-value' => '<div class="cfw-review-pane-content cfw-review-pane-shipping-address-value">' . cfw_get_review_pane_shipping_address( WC()->checkout() ) . '</div>',
					'.cfw-review-pane-contact-value'    => '<div class="cfw-review-pane-content cfw-review-pane-contact-value">' . apply_filters( 'cfw_review_pane_contact_value', WC()->checkout()->get_value( 'billing_email' ) ) . '</div>',
					'.cfw-review-pane-shipping-method-value' => '<div class="cfw-review-pane-content cfw-review-pane-shipping-method-value">' . join( ', ', $chosen_shipping_methods_labels ) . '</div>',
					'.cfw-review-pane-payment-method-value' => '<div class="cfw-review-pane-content cfw-review-pane-payment-method-value">' . cfw_get_review_pane_payment_method() . '</div>',
					'#cfw-checkout-before-order-review' => $this->get_action_output( 'woocommerce_checkout_before_order_review', 'cfw-checkout-before-order-review' ),
					'#cfw-checkout-after-order-review'  => $this->get_action_output( 'woocommerce_checkout_after_order_review', 'cfw-checkout-after-order-review' ),
					'#cfw-place-order'                  => cfw_get_place_order(),
					'#cfw-totals-list'                  => cfw_get_totals_html(),
					'#cfw-cart'                         => cfw_get_checkout_item_summary_table(),
					'#cfw-mobile-total'                 => '<span id="cfw-mobile-total" class="total amount cfw-display-table-cell">' . WC()->cart->get_total() . '</span>',
					'#cfw-billing-methods'              => $updated_payment_methods,
					'#cfw-shipping-methods'             => '<div id="cfw-shipping-methods" class="cfw-module">' . cfw_get_shipping_methods_html() . '</div>',
					'#cfw-review-order-totals'          => cfw_return_function_output( 'cfw_order_review_step_totals_review_pane' ),
				)
			),
			'redirect'                  => $redirect,
			'show_shipping_tab'         => cfw_show_shipping_tab(),
			'has_valid_shipping_method' => cfw_has_valid_shipping_methods(),
		);

		// Do this last so that anything that runs above can bubble up a notice
		$update_checkout_output['notices'] = cfw_get_woocommerce_notices();

		$this->out( $update_checkout_output );
	}

	protected function get_action_output( $action, $container = '' ) {
		ob_start();

		echo '<div id="' . $container . '">';
		do_action( $action );
		echo '</div>';

		return ob_get_clean();
	}
}
