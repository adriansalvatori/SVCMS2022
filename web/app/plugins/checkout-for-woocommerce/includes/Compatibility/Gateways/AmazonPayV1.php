<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\Helpers\AmazonPayV1ShippingInfoHelper;

class AmazonPayV1 extends CompatibilityAbstract {

	/**
	 * @var \WC_Amazon_Payments_Advanced
	 */
	protected $amazon_payments = null;

	/**
	 * @var \WC_Gateway_Amazon_Payments_Advanced
	 */
	protected $wc_gateway = null;

	protected $ref_id = '';

	protected $shipping_info_helper = null;

	protected $gateway_classes = array(
		'WC_Gateway_Amazon_Payments_Advanced_Subscriptions',
		'WC_Gateway_Amazon_Payments_Advanced',
	);

	protected $available = false;

	/**
	 * Kick off the search for the instantiated gateway
	 */
	public function pre_init() {
		$this->shipping_info_helper = new AmazonPayV1ShippingInfoHelper();
	}

	/**
	 * @param $gateways
	 *
	 * @return void
	 */
	public function get_amazon_gateway() {
		$gateways = WC()->payment_gateways->payment_gateways();

		foreach ( $gateways as $gateway ) {
			// If class is a string. It's not the gateway
			if ( is_string( $gateway ) ) {
				continue;
			}

			// Get the gateway class
			$class = get_class( $gateway );

			// If in the set classes we are looking for, set the wc_gateway
			if ( in_array( $class, $this->gateway_classes, true ) ) {
				$this->wc_gateway = $gateway;
			}
		}

		/**
		 * Fires after amazon gateway is found
		 *
		 * @since 2.0.0
		 *
		 * @param mixed $wc_gateway The gateway
		 */
		do_action( 'cfw_amazon_payment_gateway_found', $this->wc_gateway );
	}

	public function is_available(): bool {
		return defined( 'WC_AMAZON_PAY_VERSION' ) && version_compare( WC_AMAZON_PAY_VERSION, '2.0.0', '<' );
	}

	public function run_on_wp_loaded() {
		try {
			$settings = \WC_Amazon_Payments_Advanced_API::get_settings();
		} catch ( \Exception $e ) {
			$settings = array();
		}

		if ( 'yes' === $settings['enabled'] ) {
			$this->amazon_payments = $GLOBALS['wc_amazon_payments_advanced'];
			$reference_id          = \WC_Amazon_Payments_Advanced_API::get_reference_id();
			$access_token          = \WC_Amazon_Payments_Advanced_API::get_access_token();

			if ( ! empty( $reference_id ) || ! empty( $access_token ) ) {
				add_filter( 'cfw_enable_enhanced_login', '__return_false' ); // disable our login UX
				remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 ); // disable default WooCommerce login UX
				add_action( 'cfw_checkout_customer_info_tab', array( $this, 'shim_email_field' ), 30 );
				add_action( 'cfw_wp_head', array( $this, 'runtime_styles' ) );
			}

			add_action( 'woocommerce_amazon_checkout_init', array( $this, 'queue_widgets' ) );
			add_action( 'woocommerce_checkout_init', array( $this, 'checkout_init' ), 11 );
			add_action( 'woocommerce_checkout_init', array( $this, 'remove_banners' ), 100 );

			$this->get_amazon_gateway();
		}
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

	public function remove_banners() {
		$reference_id = \WC_Amazon_Payments_Advanced_API::get_reference_id();
		$access_token = \WC_Amazon_Payments_Advanced_API::get_access_token();

		// Remove default locations
		$apa = wc_apa();

		remove_action( 'woocommerce_checkout_before_customer_details', array( $apa, 'payment_widget' ), 20 );
		remove_action( 'woocommerce_checkout_before_customer_details', array( $apa, 'address_widget' ), 10 );

		// Remove before the form messages
		if ( empty( $reference_id ) && empty( $access_token ) ) {
			remove_action( 'woocommerce_before_checkout_form', array( $this->amazon_payments, 'checkout_message' ), 5 );
			remove_action( 'before_woocommerce_pay', array( $this->amazon_payments, 'checkout_message' ), 5 );
		}

		remove_action( 'woocommerce_before_checkout_form', array( $this->amazon_payments, 'placeholder_checkout_message_container' ), 5 );
	}

	/**
	 * Mimics amazons checkout_init but with our hooks instead. See the same function name in WC_Amazon_Payments_Advanced
	 * for more details
	 */
	public function checkout_init() {
		$reference_id = \WC_Amazon_Payments_Advanced_API::get_reference_id();
		$access_token = \WC_Amazon_Payments_Advanced_API::get_access_token();

		if ( ! WC()->cart ) {
			return;
		}

		if ( empty( $reference_id ) && empty( $access_token ) ) {
			add_action( 'cfw_payment_request_buttons', array( $this->amazon_payments, 'checkout_message' ) );
			add_action( 'cfw_after_payment_request_buttons', 'cfw_add_separator', 11 );
			add_action( 'cfw_wp_head', array( $this, 'protect_shipping_fields' ) );
		} else {
			// Remove shipping address preview if a subscription is in the cart
			if ( class_exists( '\\WC_Subscriptions_Cart' ) && \WC_Subscriptions_Cart::cart_contains_subscription() ) {
				remove_action( 'cfw_checkout_shipping_method_tab', 'cfw_shipping_method_address_review_pane', 10 );
			}

			remove_all_actions( 'cfw_payment_request_buttons' );
			remove_action( 'cfw_checkout_customer_info_tab', 'cfw_customer_info_tab_login', 30 );

			add_filter( 'cfw_update_payment_methods', '__return_false' ); // TODO: Isn't this covered by the same call in AmazonShippingInfoHelper.php?
			add_filter( 'cfw_validate_required_registration', '__return_false' );

			if ( ! apply_filters( 'woocommerce_amazon_show_address_widget', WC()->cart->needs_shipping() ) ) {
				add_filter( 'cfw_show_customer_information_tab', '__return_false' );
			}
		}
	}

	public function queue_widgets() {
		add_action( 'cfw_checkout_before_customer_info_address', array( $this, 'address_widget' ), 10 );
		add_action( 'cfw_checkout_before_customer_info_address', array( $this, 'output_shim_divs_close' ), 11 );

		add_action( 'cfw_checkout_after_payment_methods', array( $this, 'output_shim_divs_open' ), 19 );
		add_action( 'cfw_checkout_after_payment_methods', array( $this, 'payment_widget' ), 20 );
	}

	public function address_widget() {
		$amazon_payments = wc_apa();

		ob_start();

		$amazon_payments->address_widget();

		$output = ob_get_clean();

		$output = str_replace( 'col-1', '', $output );

		echo $output;
	}

	public function payment_widget() {
		$amazon_payments = wc_apa();

		ob_start();

		$amazon_payments->payment_widget();

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

	public function typescript_class_and_params( array $compatibility ): array {

		$compatibility['AmazonPayV1'] = array(
			'class'  => 'AmazonPayV1',
			'params' => array(
				/**
				 * Filters whether to supress shipping field validation when logged into Amazon Pay
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
	 * Amazon Pay is toggling visibility of adress fields on init starting in 1.13.x
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
}
