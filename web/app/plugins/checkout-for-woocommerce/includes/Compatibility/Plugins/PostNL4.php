<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\AddressFieldsAugmenter;

class PostNL4 extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'WCPOST' ) && version_compare( WCPOST()->version, '4.0.0', '>=' );
	}

	public function run() {
		if ( WCPOST()->setting_collection->isEnabled( 'use_split_address_fields' ) ) {
			$this->disable_nl_hooks();

			add_filter( 'woocommerce_default_address_fields', array( $this, 'add_new_fields' ), 100001, 1 ); // run after our normal hook
			add_filter( 'woocommerce_get_country_locale', array( $this, 'prevent_postcode_sort_change' ), 100001 );

			// Fix shipping preview
			add_filter( 'cfw_get_shipping_details_address', array( $this, 'fix_shipping_preview' ), 10, 2 );
		}

		add_filter( 'cfw_enable_zip_autocomplete', '__return_false' );

		// Move delivery options
		add_filter( 'wc_wcpn_delivery_options_location', array( $this, 'move_delivery_options' ), 20 );
	}

	public function disable_nl_hooks() {
		$priority = apply_filters( 'wcpn_checkout_fields_priority', 10, 'billing' );
		$instance = cfw_get_hook_instance_object( 'woocommerce_billing_fields', 'modifyBillingFields', $priority );

		if ( ! $instance ) {
			return;
		}

		remove_filter( 'woocommerce_billing_fields', array( $instance, 'modifyBillingFields' ), $priority );
		remove_filter( 'woocommerce_shipping_fields', array( $instance, 'modifyShippingFields' ), $priority );
		remove_filter( 'woocommerce_default_address_fields', array( $instance, 'default_address_fields' ) );
	}

	public function add_new_fields( $fields ) {
		// Adjust postcode field
		$fields['postcode']['priority'] = 22;

		// Add street name
		$fields['street_name'] = array(
			'label'             => cfw__( 'Street name', 'woocommerce-postnl' ),
			'placeholder'       => cfw_esc_attr__( 'Street name', 'woocommerce-postnl' ),
			'required'          => true,
			'class'             => array(),
			'autocomplete'      => '',
			'input_class'       => array(),
			'priority'          => 25,
			'columns'           => 12,
			'custom_attributes' => array(
				'data-parsley-trigger' => 'change focusout',
			),
		);

		// Then add house number
		$fields['house_number'] = array(
			'label'             => cfw__( 'Nr.', 'woocommerce-postnl' ),
			'placeholder'       => cfw_esc_attr__( 'Nr.', 'woocommerce-postnl' ),
			'required'          => true,
			'class'             => array(),
			'autocomplete'      => '',
			'input_class'       => array(),
			'priority'          => 23,
			'custom_attributes' => array(
				'data-parsley-trigger' => 'change focusout',
			),
			'columns'           => 4,
		);

		// Then house number suffix
		$fields['house_number_suffix'] = array(
			'label'             => cfw__( 'Suffix', 'woocommerce-postnl' ),
			'placeholder'       => cfw_esc_attr__( 'Suffix', 'woocommerce-postnl' ),
			'required'          => false,
			'class'             => array(),
			'autocomplete'      => '',
			'input_class'       => array(),
			'priority'          => 24,
			'columns'           => 4,
			'custom_attributes' => array(
				'data-parsley-trigger' => 'change focusout',
			),
		);

		$fields['state']['columns'] = 8;

		// Set address 1 / address 2 to hidden
		$fields['address_1']['type']  = 'hidden';
		$fields['address_1']['start'] = false;
		$fields['address_1']['end']   = false;
		unset( $fields['address_1']['custom_attributes'] );
		unset( $fields['address_1']['input_class'] );
		$fields['address_2']['type']  = 'hidden';
		$fields['address_2']['start'] = false;
		$fields['address_2']['end']   = false;
		unset( $fields['address_2']['custom_attributes'] );
		unset( $fields['address_2']['input_class'] );

		return $fields;
	}

	public function prevent_postcode_sort_change( array $locales ): array {
		foreach ( $locales as $key => $value ) {
			if ( ! empty( $value['postcode'] ) && ! empty( $value['postcode']['priority'] ) ) {
				$locales[ $key ]['postcode']['priority'] = 22;
			}
		}

		return $locales;
	}

	public function fix_shipping_preview( $address, $checkout ) {
		$address['address_1'] = $checkout->get_value( 'shipping_street_name' ) . ' ' . $checkout->get_value( 'shipping_house_number' );

		if ( ! empty( $checkout->get_value( 'shipping_house_number_suffix' ) ) ) {
			$address['address_1'] = $address['address_1'] . '-' . $checkout->get_value( 'shipping_house_number_suffix' );
		}

		return $address;
	}

	/**
	 * @param $hook
	 * @return string
	 */
	public function move_delivery_options( $hook ): string {
		return 'cfw_checkout_after_shipping_methods';
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'PostNL',
			'params' => array(),
		);

		return $compatibility;
	}
}
