<?php

namespace Objectiv\Plugins\Checkout\Features;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;

/**
 * @link checkoutwc.com
 * @since 5.0.0
 */
class HideOptionalAddressFields extends FeaturesAbstract {
	protected function run_if_cfw_is_enabled() {
		add_filter( 'cfw_get_billing_checkout_fields', array( $this, 'hide_optional_billing_address_fields' ), 10, 2 );
		add_filter( 'cfw_get_shipping_checkout_fields', array( $this, 'hide_optional_shipping_address_fields' ), 10, 2 );
	}

	function hide_optional_billing_address_fields( $fields ): array {
		return $this->hide_optional_address_fields( $fields, 'billing' );
	}

	function hide_optional_shipping_address_fields( $fields ): array {
		return $this->hide_optional_address_fields( $fields, 'shipping' );
	}

	function hide_optional_address_fields( array $fields, string $fieldset ): array {
		if ( ! is_cfw_page() ) {
			return $fields;
		}

		$address_2_field_key = "{$fieldset}_address_2";
		$company_field_key   = "{$fieldset}_company";

		if ( isset( $fields[ $address_2_field_key ] ) && ! $fields[ $address_2_field_key ]['required'] && apply_filters( 'cfw_hide_optional_fields_behind_links', true, 'address_2' ) ) {
			$fields[ $address_2_field_key ]['class'][] = 'cfw-hidden';

			// This link needs form-row because WooCommerce Checkout Field Editor is forcefully sorting the address fields
			$fields[ $address_2_field_key ]['before_html'] = sprintf( '<a href="javascript:" class="cfw-small cfw-add-field form-row"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>%s</a>', sprintf( '%s (%s)', __( 'Add Address Line 2', 'checkout-wc' ), cfw__( 'optional', 'woocommerce' ) ) );
		}

		if ( isset( $fields[ $company_field_key ] ) && ! $fields[ $company_field_key ]['required'] && apply_filters( 'cfw_hide_optional_fields_behind_links', true, 'company' ) ) {
			$fields[ $company_field_key ]['class'][] = 'cfw-hidden';

			// This link needs form-row because WooCommerce Checkout Field Editor is forcefully sorting the address fields
			$fields[ $company_field_key ]['before_html'] = sprintf( '<a href="javascript:" class="cfw-small cfw-add-field form-row"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>%s</a>', sprintf( '%s (%s)', __( 'Add Company', 'checkout-wc' ), cfw__( 'optional', 'woocommerce' ) ) );
		}

		return $fields;
	}
}
