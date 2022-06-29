<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class AmazonPayLegacy extends CompatibilityAbstract {
	/** @var \WC_Gateway_Amazon_Payments_Advanced_Legacy */
	protected $gateway;

	protected $legacy = false;

	public function is_available(): bool {
		if ( ! function_exists( 'wc_apa' ) ) {
			return false;
		}

		if ( defined( 'WC_AMAZON_PAY_VERSION' ) && version_compare( WC_AMAZON_PAY_VERSION, '2.0.0', '<' ) ) {
			return false;
		}

		$api_version = get_option( 'amazon_api_version' );

		if ( 'V2' !== $api_version ) {
			$this->legacy = true;
		}

		return $this->legacy;
	}

	protected function get_gateway() {
		if ( empty( $this->gateway ) ) {
			$this->gateway = wc_apa()->get_gateway();
		}

		return $this->gateway;
	}

	public function pre_init() {
		if ( $this->is_available() ) {
			add_action( 'woocommerce_checkout_init', array( $this, 'checkout_init' ), 11 );
			add_action( 'woocommerce_checkout_init', array( $this, 'remove_banners' ), 100 );
			add_action( 'wp_loaded', array( $this, 'start' ), 0 );
		}
	}

	public function start() {
		if ( $this->get_gateway()->is_available() ) {
			if ( $this->is_logged_in() ) {
				add_filter( 'cfw_enable_enhanced_login', '__return_false' ); // disable our login UX
				remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 ); // disable default WooCommerce login UX
				add_action( 'cfw_checkout_customer_info_tab', array( $this, 'shim_email_field' ), 30 );
				add_action( 'cfw_wp_head', array( $this, 'runtime_styles' ) );
			}

			add_action( 'woocommerce_amazon_checkout_init', array( $this, 'queue_widgets' ) );

			// Remove amazon's store_shipping_info_in_session
			remove_action( 'woocommerce_checkout_update_order_review', array( $this->get_gateway(), 'store_shipping_info_in_session' ), 10 );

			// Add ours
			add_action( 'woocommerce_checkout_update_order_review', array( $this, 'store_shipping_info_in_session' ) );

			// Disable payment method refresh
			add_action( 'woocommerce_checkout_update_order_review', array( $this, 'disable_refresh' ) );
		}
	}

	public function checkout_init() {
		if ( ! $this->is_logged_in() ) {
			add_action( 'cfw_payment_request_buttons', array( $this->get_gateway(), 'checkout_message' ) );
			add_action( 'cfw_after_payment_request_buttons', 'cfw_add_separator', 11 );
			add_action( 'cfw_wp_head', array( $this, 'protect_shipping_fields' ) );
		} else {
			// Remove shipping address preview if a subscription is in the cart
			if ( class_exists( '\\WC_Subscriptions_Cart' ) && \WC_Subscriptions_Cart::cart_contains_subscription() ) {
				remove_action( 'cfw_checkout_shipping_method_tab', 'cfw_shipping_method_address_review_pane', 10 );
			}

			// Disable SmartyStreets Address Validation
			add_filter( 'cfw_enable_smartystreets_integration', '__return_false' );

			remove_all_actions( 'cfw_payment_request_buttons' );
			remove_action( 'cfw_checkout_customer_info_tab', 'cfw_customer_info_tab_login', 30 );

			add_filter( 'cfw_update_payment_methods', '__return_false' ); // TODO: Isn't this covered by the same call in AmazonShippingInfoHelper.php?
			add_filter( 'cfw_validate_required_registration', '__return_false' );

			if ( ! apply_filters( 'woocommerce_amazon_show_address_widget', WC()->cart->needs_shipping() ) ) {
				add_filter( 'cfw_show_customer_information_tab', '__return_false' );
			}
		}
	}

	public function remove_banners() {
		remove_action( 'woocommerce_checkout_before_customer_details', array( $this->get_gateway(), 'payment_widget' ), 20 );
		remove_action( 'woocommerce_checkout_before_customer_details', array( $this->get_gateway(), 'address_widget' ) );

		// Remove before the form messages
		if ( ! $this->is_logged_in() ) {
			remove_action( 'woocommerce_before_checkout_form', array( $this->get_gateway(), 'checkout_message' ), 5 );
			remove_action( 'before_woocommerce_pay', array( $this->get_gateway(), 'checkout_message' ), 5 );
		}

		remove_action( 'woocommerce_before_checkout_form', array( $this->get_gateway(), 'placeholder_checkout_message_container' ), 5 );
	}

	public function shim_email_field() {
		$billing_fields = WC()->checkout()->get_checkout_fields( 'billing' );
		$email_field    = $billing_fields['billing_email'];

		echo '<div style="display: none;">';
		woocommerce_form_field( 'billing_email', $email_field, WC()->checkout()->get_value( 'billing_email' ) );
		echo '</div>';
	}

	public function runtime_styles() {
		?>
		<style type="text/css">
			main.checkoutwc .create-account p {
				margin-bottom: 1em;
			}

			main.checkoutwc .cfw-payment-method-information-wrap {
				display: none;
			}
		</style>
		<?php
	}

	public function queue_widgets() {
		add_action( 'cfw_checkout_before_customer_info_address', array( $this, 'address_widget' ), 10 );
		add_action( 'cfw_checkout_before_customer_info_address', array( $this, 'output_shim_divs_close' ), 11 );

		add_action( 'cfw_checkout_after_payment_methods', array( $this, 'output_shim_divs_open' ), 19 );
		add_action( 'cfw_checkout_after_payment_methods', array( $this, 'payment_widget' ), 20 );
	}

	public function address_widget() {
		ob_start();

		$this->get_gateway()->address_widget();

		$output = ob_get_clean();

		$output = str_replace( 'col-1', '', $output );

		echo $output;
	}

	public function payment_widget() {
		ob_start();

		$this->get_gateway()->payment_widget();

		$output = ob_get_clean();

		$output = str_replace( 'col-2', '', $output );

		echo $output;
	}

	public function output_shim_divs_open() {
		echo '<div><div>';
	}

	public function output_shim_divs_close() {
		echo '</div></div>';
	}

	public function is_logged_in(): bool {
		$reference_id = \WC_Amazon_Payments_Advanced_API_Legacy::get_reference_id();
		$access_token = \WC_Amazon_Payments_Advanced_API_Legacy::get_access_token();

		if ( isset( $_SESSION['first_checkout_post_array']['amazon_reference_id'] ) ) {
			$reference_id = $_SESSION['first_checkout_post_array']['amazon_reference_id'];
		}

		if ( empty( $reference_id ) && empty( $access_token ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Amazon Pay is toggling visibility of address fields on init starting in 1.13.x
	 *
	 * This fixes that annoying behavior
	 */
	public function protect_shipping_fields() {
		?>
		<style type="text/css">
			body.checkout-wc .cfw-customer-info-address-container.hidden {
				display: block !important;
			}

			body.checkout-wc #shipping_state_field.hidden {
				display: block !important;
			}
		</style>
		<?php
	}

	public function typescript_class_and_params( array $compatibility ): array {

		$compatibility['AmazonPayLegacy'] = array(
			'class'  => 'AmazonPayLegacy',
			'params' => array(
				/**
				 * Filters whether to suppress shipping field validation when logged into Amazon Pay
				 *
				 * @since 2.0.0
				 *
				 * @param bool $suppress_validation True suppress validation (Default), false validate
				 */
				'cfw_amazon_suppress_shipping_field_validation' => apply_filters( 'cfw_amazon_suppress_shipping_field_validation', true ),
			),
		);

		return $compatibility;
	}

	/**
	 * Get the shipping address from Amazon and store in session.
	 *
	 * This makes tax/shipping rate calculation possible on AddressBook Widget selection.
	 *
	 * @since 1.0.0
	 * @version 1.8.0
	 */
	public function store_shipping_info_in_session() {
		// Get the reference id
		$reference_id = \WC_Amazon_Payments_Advanced_API_Legacy::get_reference_id();

		if ( ! $reference_id ) {
			return;
		}

		$order_details = $this->get_gateway()->get_amazon_order_details( $reference_id );

		// @codingStandardsIgnoreStart
		if ( ! $order_details || ! isset( $order_details->Destination->PhysicalDestination ) ) {
			return;
		}

		$address = \WC_Amazon_Payments_Advanced_API_Legacy::format_address( $order_details->Destination->PhysicalDestination );
		// Call our own version of this function (it's private on theirs)
		$address = $this->normalize_address( $address );
		// @codingStandardsIgnoreEnd

		foreach ( array( 'first_name', 'last_name', 'address_1', 'address_2', 'country', 'state', 'postcode', 'city' ) as $field ) {
			if ( ! isset( $address[ $field ] ) ) {
				continue;
			}

			// Call our own versions of this
			$this->set_customer_info( $field, $address[ $field ] );
			$this->set_customer_info( 'shipping_' . $field, $address[ $field ] );
		}
	}

	public function disable_refresh() {
		// Get the reference id
		$reference_id = \WC_Amazon_Payments_Advanced_API_Legacy::get_reference_id();

		if ( ! $reference_id ) {
			return;
		}

		add_filter( 'cfw_update_payment_methods', '__return_false' );
	}

	/**
	 * Normalized address after formatted.
	 * Our version of the WC_Gateway_Amazon_Payments_Advanced normalize_address
	 *
	 * @param array $address Address.
	 *
	 * @return array Address.
	 *@since 1.8.0
	 * @version 1.8.0
	 *
	 */
	private function normalize_address( array $address ) {
		/**
		 * US postal codes comes back as a ZIP+4 when in "Login with Amazon App"
		 * mode.
		 *
		 * This is too specific for the local delivery shipping method,
		 * and causes the zip not to match, so we remove the +4.
		 */
		if ( 'US' === $address['country'] ) {
			$code_parts          = explode( '-', $address['postcode'] );
			$address['postcode'] = $code_parts[0];
		}

		$states = WC()->countries->get_states( $address['country'] );
		if ( empty( $states ) ) {
			return $address;
		}

		// State might be in city, so use that if state is not passed by
		// Amazon. But if state is available we still need the WC state key.
		$state = '';
		if ( ! empty( $address['state'] ) ) {
			$state = array_search( $address['state'], $states, true );
		}
		if ( ! $state && ! empty( $address['city'] ) ) {
			$state = array_search( $address['city'], $states, true );
		}
		if ( $state ) {
			$address['state'] = $state;
		}

		return $address;
	}

	/**
	 * Set customer info.
	 *
	 * WC 3.0.0 deprecates some methods in customer setter, especially for billing
	 * related address. This method provides compatibility to set customer billing
	 * info.
	 *
	 * Our version of the WC_Gateway_Amazon_Payments_Advanced set_customer_info
	 *
	 * @param string $setter_suffix Setter suffix.
	 * @param mixed  $value         Value to set.
	 *@since 1.7.0
	 *
	 */
	private function set_customer_info( string $setter_suffix, $value ) {
		$setter             = array( WC()->customer, 'set_' . $setter_suffix );
		$is_shipping_setter = strpos( $setter_suffix, 'shipping_' ) !== false;

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0', '>=' ) && ! $is_shipping_setter ) {
			$setter = array( WC()->customer, 'set_billing_' . $setter_suffix );
		}

		call_user_func( $setter, $value );
	}
}
