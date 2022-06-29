<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class AmazonPay extends CompatibilityAbstract {
	/** @var \WC_Gateway_Amazon_Payments_Advanced */
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

		return ! $this->legacy;
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
		if ( $this->get_gateway() && $this->get_gateway()->is_available() ) {
			if ( $this->is_logged_in() ) {
				add_filter( 'cfw_enable_enhanced_login', '__return_false' ); // disable our login UX
				remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 ); // disable default WooCommerce login UX
				add_action( 'cfw_checkout_customer_info_tab', array( $this, 'shim_email_field' ), 30 );
			}

			add_action( 'woocommerce_amazon_checkout_init', array( $this, 'queue_widgets' ) );
		}
	}

	public function checkout_init() {
		if ( ! $this->is_logged_in() ) {
			add_action( 'cfw_payment_request_buttons', array( $this->get_gateway(), 'checkout_message' ) );
			add_action( 'cfw_after_payment_request_buttons', 'cfw_add_separator', 11 );
		} else {
			// Remove shipping address preview if a subscription is in the cart
			if ( class_exists( '\\WC_Subscriptions_Cart' ) && \WC_Subscriptions_Cart::cart_contains_subscription() ) {
				remove_action( 'cfw_checkout_shipping_method_tab', 'cfw_shipping_method_address_review_pane', 10 );
			}

			// Disable SmartyStreets Address Validation
			add_filter( 'cfw_enable_smartystreets_integration', '__return_false' );

			remove_all_actions( 'cfw_payment_request_buttons' );
			remove_action( 'cfw_checkout_customer_info_tab', 'cfw_customer_info_tab_account', 30 );

			//add_filter( 'cfw_update_payment_methods', '__return_false' ); // TODO: Isn't this covered by the same call in AmazonShippingInfoHelper.php?
			add_filter( 'cfw_validate_required_registration', '__return_false' );

			if ( ! WC()->cart->needs_shipping() ) {
				add_filter( 'cfw_show_customer_information_tab', '__return_false' );
			}
		}
	}

	public function remove_banners() {
		remove_action( 'woocommerce_checkout_before_customer_details', array( $this->get_gateway(), 'display_amazon_customer_info' ) );

		// Remove before the form messages
		if ( ! $this->is_logged_in() ) {
			remove_action( 'woocommerce_before_checkout_form', array( $this->get_gateway(), 'checkout_message' ), 5 );
			remove_action( 'before_woocommerce_pay', array( $this->get_gateway(), 'checkout_message' ), 5 );
		}

		remove_action( 'woocommerce_before_checkout_form', array( $this->get_gateway(), 'placeholder_checkout_message_container' ), 5 );
	}

	public function shim_email_field() {
		$billing_fields      = WC()->checkout()->get_checkout_fields( 'billing' );
		$email_field         = $billing_fields['billing_email'];
		$email_field['type'] = 'hidden';

		woocommerce_form_field( 'billing_email', $email_field, WC()->checkout()->get_value( 'billing_email' ) );
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
		add_action( 'cfw_checkout_before_customer_info_address', array( $this, 'customer_info_widget' ), 10 );
	}

	public function customer_info_widget() {
		ob_start();

		$this->get_gateway()->display_amazon_customer_info();

		$output = ob_get_clean();

		$output = str_replace( 'col2-set', 'row', $output );
		$output = str_replace( 'col-1', 'col-lg-6', $output );
		$output = str_replace( 'col-2', 'col-lg-6', $output );

		echo $output;
	}

	public function is_logged_in(): bool {
		if ( is_null( WC()->session ) ) {
			return false;
		}

		$session_id = $this->get_gateway()->get_checkout_session_id();

		return ! empty( $session_id );
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

		$compatibility['AmazonPay'] = array(
			'class'  => 'AmazonPay',
			'params' => array(),
		);

		return $compatibility;
	}
}
