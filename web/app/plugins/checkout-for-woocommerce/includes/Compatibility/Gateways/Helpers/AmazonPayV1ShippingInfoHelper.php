<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways\Helpers;

class AmazonPayV1ShippingInfoHelper {

	protected $gateway = null;

	public function __construct() {
		if ( class_exists( '\\WC_Amazon_Payments_Advanced_API' ) ) {
			add_action( 'cfw_amazon_payment_gateway_found', array( $this, 'get_gateway' ), 10, 1 );
		}
	}

	/**
	 * Set the gateway after we find it in woocommerce_payment_gateways
	 *
	 * @param $gateway
	 */
	function get_gateway( $gateway ) {
		$settings = \WC_Amazon_Payments_Advanced_API::get_settings();

		// Set the gateway
		$this->gateway = $gateway;

		if ( 'yes' === $settings['enabled'] ) {
			// Set the add/remove
			$this->add_remove_shipping_info_function();
		}
	}

	/**
	 * Remove the gateway store_shipping_info_in_session and add ours
	 */
	function add_remove_shipping_info_function() {
		// Remove amazon's store_shipping_info_in_session
		remove_action( 'woocommerce_checkout_update_order_review', array( $this->gateway, 'store_shipping_info_in_session' ), 10 );

		// Add ours
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'store_shipping_info_in_session' ) );

		// Disable payment method refresh
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'disable_refresh' ) );
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
		$reference_id = \WC_Amazon_Payments_Advanced_API::get_reference_id();

		if ( ! $reference_id ) {
			return;
		}

		$order_details = $this->gateway->get_amazon_order_details( $reference_id );

		// @codingStandardsIgnoreStart
		if ( ! $order_details || ! isset( $order_details->Destination->PhysicalDestination ) ) {
			return;
		}

		$address = \WC_Amazon_Payments_Advanced_API::format_address( $order_details->Destination->PhysicalDestination );
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

	function disable_refresh() {
		// Get the reference id
		$reference_id = \WC_Amazon_Payments_Advanced_API::get_reference_id();

		if ( ! $reference_id ) {
			return;
		}

		add_filter( 'cfw_update_payment_methods', '__return_false' );
	}

	/**
	 * Normalized address after formatted.
	 * Our version of the WC_Gateway_Amazon_Payments_Advanced normalize_address
	 *
	 * @since 1.8.0
	 * @version 1.8.0
	 *
	 * @param array $address Address.
	 *
	 * @return array Address.
	 */
	private function normalize_address( $address ) {
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
			$state = array_search( $address['state'], $states );
		}
		if ( ! $state && ! empty( $address['city'] ) ) {
			$state = array_search( $address['city'], $states );
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
	 * @since 1.7.0
	 *
	 * @param string $setter_suffix Setter suffix.
	 * @param mixed  $value         Value to set.
	 */
	private function set_customer_info( $setter_suffix, $value ) {
		$setter             = array( WC()->customer, 'set_' . $setter_suffix );
		$is_shipping_setter = strpos( $setter_suffix, 'shipping_' ) !== false;

		if ( version_compare( WC_VERSION, '3.0', '>=' ) && ! $is_shipping_setter ) {
			$setter = array( WC()->customer, 'set_billing_' . $setter_suffix );
		}

		call_user_func( $setter, $value );
	}
}
