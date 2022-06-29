<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;

class Stripe extends CompatibilityAbstract {

	protected $stripe_request_button_height = 'default';

	public function is_available(): bool {
		return defined( 'WC_STRIPE_VERSION' ) && version_compare( WC_STRIPE_VERSION, '4.0.0' ) >= 0;
	}

	public function pre_init() {
		/**
		 * Filters whether to override Stripe payment request button heights
		 *
		 * @since 2.0.0
		 *
		 * @param bool $override Whether to override Stripe payment request button heights
		 */
		if ( apply_filters( 'cfw_stripe_compat_override_request_btn_height', true ) ) {
			add_filter( 'option_woocommerce_stripe_settings', array( $this, 'override_btn_height_settings_on_update' ), 10, 1 );
		}

		/**
		 * Filters whether to override Stripe payment request button heights
		 *
		 * @since 4.3.3
		 *
		 * @param bool $allow Whether to ignore shipping phone requirement during payment requests
		 */
		if ( apply_filters( 'cfw_stripe_payment_requests_ignore_shipping_phone', true ) ) {
			add_action( 'wc_ajax_wc_stripe_create_order', array( $this, 'process_payment_request_ajax_checkout' ), 1 );
		}
	}

	public function run() {
		// Apple Pay
		$this->add_payment_request_buttons();
	}

	public function override_btn_height_settings_on_update( $value ) {
		$value['payment_request_button_size'] = $this->stripe_request_button_height;

		return $value;
	}

	public function add_payment_request_buttons() {
		// Setup Apple Pay
		if ( class_exists( '\\WC_Stripe_Payment_Request' ) && cfw_is_checkout() ) {
			$stripe_payment_request = \WC_Stripe_Payment_Request::instance();

			add_filter( 'wc_stripe_show_payment_request_on_checkout', '__return_true' );

			// Remove default stripe request placement
			remove_action( 'woocommerce_checkout_before_customer_details', array( $stripe_payment_request, 'display_payment_request_button_html' ), 1 );
			remove_action( 'woocommerce_checkout_before_customer_details', array( $stripe_payment_request, 'display_payment_request_button_separator_html' ), 2 );

			// Add our own stripe requests
			add_action( 'cfw_payment_request_buttons', array( $stripe_payment_request, 'display_payment_request_button_html' ), 1 );
			add_action( 'cfw_after_payment_request_buttons', array( $this, 'add_payment_request_separator' ), 12 ); // This should be 12, which is after 11, which is the hook other gateways use
		}
	}

	public function add_payment_request_separator() {
		cfw_add_separator( '', 'wc-stripe-payment-request-button-separator', 'text-align: center;' );
	}

	public function process_payment_request_ajax_checkout() {
		$payment_request_type = isset( $_POST['payment_request_type'] ) ? wc_clean( $_POST['payment_request_type'] ) : '';

		// Disable shipping phone validation when using payment request
		if ( ! empty( $payment_request_type ) ) {
			add_filter(
				'woocommerce_checkout_fields',
				function( $fields ) {
					if ( isset( $fields['shipping']['shipping_phone'] ) ) {
						$fields['shipping']['shipping_phone']['required'] = false;
						$fields['shipping']['shipping_phone']['validate'] = array();
					}

					if ( 'yes' === SettingsManager::instance()->get_setting( 'use_fullname_field' ) ) {
						unset( $fields['shipping']['shipping_full_name'] );
						unset( $fields['billing']['billing_full_name'] );
					}

					if ( 'yes' === SettingsManager::instance()->get_setting( 'enable_discreet_address_1_fields' ) ) {
						unset( $fields['shipping']['shipping_house_number'] );
						unset( $fields['billing']['billing_house_number'] );
						unset( $fields['shipping']['shipping_street_name'] );
						unset( $fields['billing']['billing_street_name'] );
					}

					return $fields;
				},
				1
			);
		}
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'Stripe',
			'params' => array(),
		);

		return $compatibility;
	}
}
